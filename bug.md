Ada tiga masalah sekaligus yang muncul ketika meter lama dihapus:
Masalah 1 — Section visibility: Kondisi $record->melanjutkan_dari_id menjadi null karena nullOnDelete. Jika dilanjutkanOleh juga tidak ada, section seluruhnya tersembunyi.
Masalah 2 — "Melanjutkan dari Meter": TextEntry::make('melanjutkanDari.nomor_meter') bergantung pada relasi yang sudah null → tidak tampil.
Masalah 3 — tanggal_nonaktif_history: Saat melanjutkan_dari_id null, logic state() masuk ke branch return $record->tanggal_nonaktif — tapi meter baru tidak punya tanggal_nonaktif, hasilnya —.

Akar Masalah
Kolom snapshot yang sudah kita rancang (oper_dari_nomor_meter, oper_dari_nama_pelanggan) sudah ada di DB, tapi infolist masih bergantung pada relasi bukan snapshot. Ditambah satu kolom snapshot yang belum ada: oper_dari_tanggal_nonaktif.

Fix
1 — Tambah kolom snapshot oper_dari_tanggal_nonaktif
php// migration baru
Schema::table('meter_air', function (Blueprint $table) {
    $table->date('oper_dari_tanggal_nonaktif')
          ->nullable()
          ->after('oper_angka_serah_terima');
});
Tambahkan ke $fillable model MeterAir dan ke mutateFormDataBeforeCreate di CreateMeterAir:
php$data['oper_dari_tanggal_nonaktif'] = $meterLama->tanggal_nonaktif;

2 — Refactor infolist — gunakan snapshot, bukan relasi
phpComponentsSection::make('Riwayat Oper Kontrak')
    // Gunakan snapshot sebagai fallback — tidak bergantung pada FK
    ->visible(fn ($record) =>
        $record->melanjutkan_dari_id ||
        filled($record->oper_dari_nomor_meter) || // ← tambahan
        $record->dilanjutkanOleh
    )
    ->schema([
        TextEntry::make('oper_dari_meter')
            ->label('Melanjutkan dari Meter')
            ->visible(fn ($record) =>
                $record->melanjutkan_dari_id || filled($record->oper_dari_nomor_meter)
            )
            ->state(function (Model $record) {
                $nomor = $record->melanjutkanDari?->nomor_meter
                         ?? $record->oper_dari_nomor_meter; // fallback snapshot

                $nama = $record->melanjutkanDari?->pelanggan?->user?->name
                        ?? $record->oper_dari_nama_pelanggan; // fallback snapshot

                if (!$nomor) return null;
                return "{$nomor} — milik {$nama}";
            }),

        TextEntry::make('tanggal_nonaktif_history')
            ->label('Pelanggan Sebelumnya Berhenti Pada')
            ->state(function (Model $record) {
                if ($record->melanjutkan_dari_id || filled($record->oper_dari_nomor_meter)) {
                    // Meter baru — ambil dari relasi, fallback ke snapshot
                    return $record->melanjutkanDari?->tanggal_nonaktif
                           ?? $record->oper_dari_tanggal_nonaktif;
                }
                // Meter lama — ambil dari diri sendiri
                return $record->tanggal_nonaktif;
            })
            ->date('d M Y')
            ->placeholder('—'),

        TextEntry::make('tanggal_oper_kontrak')
            ->label('Tanggal Oper Kontrak (Pelanggan Baru Mulai)')
            ->visible(fn ($record) => $record->tanggal_oper_kontrak)
            ->date('d M Y'),

        TextEntry::make('dilanjutkanOleh.pelanggan.user.name')
            ->label('Diteruskan ke Pelanggan')
            ->visible(fn ($record) => $record->dilanjutkanOleh)
            ->formatStateUsing(fn ($state, $record) =>
                "{$state} — mulai: " .
                ($record->dilanjutkanOleh->tanggal_oper_kontrak?->format('d M Y') ?? '-')
            ),
    ]),

Prinsip yang Diterapkan
Sekarang setiap field punya dua sumber secara berurutan:
1. Coba dari relasi (jika meter lama masih ada)
2. Fallback ke snapshot (jika relasi sudah null)