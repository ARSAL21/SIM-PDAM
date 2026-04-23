# Issue: Refactor Skema `meter_air` — Dukungan Oper Kontrak,
# Status Nonaktif, dan Keterlacakan Riwayat Antar Pelanggan

## 📌 Latar Belakang & Analisis Bisnis

Sistem saat ini memiliki constraint `UNIQUE` global pada kolom `nomor_meter`
di tabel `meter_air`. Constraint ini memblok skenario bisnis yang valid dan
umum terjadi di lapangan PDAM: **oper kontrak** — kondisi di mana pelanggan
baru menempati lokasi yang sama dengan pelanggan lama dan menggunakan meteran
fisik yang sama.

### Tiga Skenario Bisnis Definitif

**Skenario A — Pelanggan Baru, Lokasi Baru:**
Pelanggan mendaftar di lokasi yang belum pernah ada pelanggan sebelumnya.
Selalu buat record `meter_air` baru dengan nomor meter baru. Tidak ada
hubungan ke meter manapun yang sudah ada di database. `angka_awal` diisi
sesuai kondisi fisik meter saat dipasang.

**Skenario B — Pelanggan Nonaktif, Tidak Ada Pengganti:**
Pelanggan berhenti berlangganan dan tidak ada pelanggan pengganti di lokasi
tersebut. Pelanggan dinonaktifkan → meter otomatis berubah ke status `Nonaktif`
via observer. Seluruh data tersimpan sebagai historis selamanya. Tidak ada
aksi lanjutan yang diperlukan.

**Skenario C — Oper Kontrak (Lokasi Sama, Pelanggan Baru):**
Pelanggan baru menempati rumah/lokasi yang sama dengan pelanggan lama.
Pelanggan lama dinonaktifkan → meter lama otomatis menjadi `Nonaktif` →
admin membuat record `meter_air` **baru** untuk pelanggan baru dengan nomor
fisik yang sama. `angka_awal` diisi dari `angka_akhir` terakhir meter lama.
Kedua record terhubung secara eksplisit di database via kolom
`melanjutkan_dari_id`.

### Prinsip yang Tidak Boleh Dilanggar

- **Data tidak boleh dihapus.** Pelanggan lama dan meterannya tetap ada di
  database sebagai catatan historis.
- **Meter bekas tidak boleh diberikan sembarangan.** Hanya boleh dilanjutkan
  jika pelanggan baru benar-benar menempati lokasi yang sama (oper kontrak).
  Pelanggan di lokasi baru wajib mendapat nomor meter baru.
- **Keterlacakan wajib ada.** Harus bisa ditelusuri: meter ini melanjutkan
  dari meter siapa, dan meter ini diteruskan ke siapa.

---

## 🗄️ Task 1: Migration — Refactor Tabel `meter_air`

Buat file migration baru untuk tiga perubahan sekaligus.

```php
// php artisan make:migration refactor_meter_air_for_oper_kontrak

Schema::table('meter_air', function (Blueprint $table) {

    // 1. Hapus unique constraint global pada nomor_meter.
    //    Aturan unique dipindah ke level aplikasi dengan kondisi:
    //    hanya boleh ada satu nomor meter yang berstatus Aktif pada satu waktu.
    $table->dropUnique(['nomor_meter']);

    // 2. Tambah status Nonaktif pada enum.
    //    Nonaktif = meter idle karena pelanggan berhenti, fisik masih ada
    //    di lapangan, belum tentu rusak atau diganti.
    $table->enum('status', ['Aktif', 'Rusak', 'Diganti', 'Nonaktif'])
          ->default('Aktif')
          ->change();

    // 3. Tambah kolom jejak oper kontrak (self-referencing FK).
    //    Diisi hanya pada Skenario C. Null = bukan kelanjutan dari meter manapun.
    $table->foreignId('melanjutkan_dari_id')
          ->nullable()
          ->after('angka_awal')
          ->constrained('meter_air')
          ->nullOnDelete();
});
```

### Makna Setiap Status Setelah Refactor

| Status | Makna |
|---|---|
| `Aktif` | Terpasang, digunakan, bisa menerima pencatatan baru |
| `Nonaktif` | Idle karena pelanggan berhenti. Fisik ada, tapi tidak dipakai |
| `Rusak` | Kerusakan fisik pada alat. Perlu diganti unit baru |
| `Diganti` | Sudah diganti dengan unit meter baru secara fisik |

---

## 🔗 Task 2: Update Model `MeterAir`

Tambahkan dua relasi self-referencing untuk mendukung keterlacakan
oper kontrak, dan update cast enum.

```php
// app/Models/MeterAir.php

protected function casts(): array
{
    return [
        'tanggal_pasang' => 'date',
        // Tambahkan jika menggunakan PHP Enum untuk status
        // 'status' => StatusMeterEnum::class,
    ];
}

// Meter ini adalah kelanjutan dari meter mana? (Skenario C)
public function melanjutkanDari(): BelongsTo
{
    return $this->belongsTo(MeterAir::class, 'melanjutkan_dari_id');
}

// Meter mana yang meneruskan meter ini? (Skenario C)
public function dilanjutkanOleh(): HasOne
{
    return $this->hasOne(MeterAir::class, 'melanjutkan_dari_id');
}
```

---

## ⚙️ Task 3: Observer `Pelanggan` — Otomasi Nonaktifkan Meter

Ketika `status_aktif` pelanggan diubah menjadi `false`, semua meter miliknya
yang berstatus `Aktif` harus otomatis berubah ke `Nonaktif`. Jangan biarkan
ini dilakukan manual — terlalu mudah terlewat.

### 3a. Buat Observer

```php
// php artisan make:observer PelangganObserver --model=Pelanggan

// app/Observers/PelangganObserver.php

public function updated(Pelanggan $pelanggan): void
{
    if ($pelanggan->wasChanged('status_aktif') && !$pelanggan->status_aktif) {
        $pelanggan->meterAirs()
                  ->where('status', 'Aktif')
                  ->update(['status' => 'Nonaktif']);
    }
}
```

### 3b. Daftarkan Observer

```php
// app/Providers/AppServiceProvider.php

public function boot(): void
{
    Pelanggan::observe(PelangganObserver::class);
}
```

---

## 🖥️ Task 4: Update `MeterAirResource` — Form & Tampilan

### 4a. Validasi `nomor_meter` — Pindah dari DB ke Aplikasi

Ganti logika unique yang sebelumnya ditangani DB dengan validasi kondisional:
nomor meter boleh muncul lebih dari satu kali di database, tapi tidak boleh
ada dua meter dengan nomor yang sama dan status `Aktif` secara bersamaan.

```php
TextInput::make('nomor_meter')
    ->nullable()
    ->rule(function (Get $get, ?Model $record) {
        return function (string $attribute, mixed $value, Closure $fail)
            use ($record) {
                if (blank($value)) return;

                $exists = MeterAir::where('nomor_meter', $value)
                    ->where('status', 'Aktif')
                    ->when($record, fn($q) => $q->whereNot('id', $record->id))
                    ->exists();

                if ($exists) {
                    $fail("Nomor meter {$value} sudah digunakan oleh
                           meter lain yang masih berstatus Aktif.");
                }
        };
    }),
```

### 4b. Tambah Field `melanjutkan_dari_id` (Oper Kontrak)

Field ini bersifat opsional. Hanya muncul jika admin memilih untuk
mendaftarkan meter sebagai kelanjutan dari meter nonaktif sebelumnya.
Ketika dipilih, `angka_awal` otomatis ter-populate dari `angka_akhir`
pencatatan terakhir meter yang dipilih.

```php
Select::make('melanjutkan_dari_id')
    ->label('Melanjutkan dari Meter (Oper Kontrak)')
    ->helperText('Isi hanya jika pelanggan baru menempati lokasi
                  pelanggan lama dan menggunakan meteran fisik yang sama.')
    ->nullable()
    ->searchable()
    ->getSearchResultsUsing(fn (string $search) =>
        MeterAir::where('status', 'Nonaktif')
            ->where(fn ($q) => $q
                ->where('nomor_meter', 'like', "%{$search}%")
                ->orWhereHas('pelanggan.user', fn ($q) =>
                    $q->where('name', 'like', "%{$search}%")
                )
            )
            ->with('pelanggan.user')
            ->limit(10)
            ->get()
            ->mapWithKeys(fn ($meter) => [
                $meter->id => "{$meter->nomor_meter} — " .
                              $meter->pelanggan->user->name
            ])
    )
    ->live()
    ->afterStateUpdated(function ($state, Set $set) {
        if (!$state) return;

        $meterLama = MeterAir::with('pencatatanTerakhir')->find($state);
        if (!$meterLama) return;

        // Auto-populate angka_awal dari angka_akhir pencatatan terakhir
        $angkaAwal = $meterLama->pencatatanTerakhir?->angka_akhir
                     ?? $meterLama->angka_awal;

        $set('angka_awal', $angkaAwal);
        $set('nomor_meter', $meterLama->nomor_meter);
    }),
```

### 4c. Tampilan Keterlacakan di Halaman Detail / View

Jika record `meter_air` memiliki `melanjutkan_dari_id` atau `dilanjutkanOleh`,
tampilkan informasi rantai oper kontrak secara eksplisit.

```php
// Di InfoList atau halaman ViewMeterAir

// Tampil jika meter ini adalah kelanjutan dari meter lain
Section::make('Riwayat Oper Kontrak')
    ->visible(fn ($record) =>
        $record->melanjutkan_dari_id || $record->dilanjutkanOleh
    )
    ->schema([
        TextEntry::make('melanjutkanDari.nomor_meter')
            ->label('Melanjutkan dari Meter')
            ->visible(fn ($record) => $record->melanjutkan_dari_id)
            ->formatStateUsing(fn ($state, $record) =>
                "{$state} — " .
                $record->melanjutkanDari->pelanggan->user->name .
                " (berhenti: " .
                $record->melanjutkanDari->pencatatanTerakhir
                    ?->created_at->format('M Y') .
                ", angka terakhir: " .
                number_format($record->angka_awal) . " m³)"
            ),

        TextEntry::make('dilanjutkanOleh.pelanggan.user.name')
            ->label('Diteruskan ke Pelanggan')
            ->visible(fn ($record) => $record->dilanjutkanOleh)
            ->formatStateUsing(fn ($state, $record) =>
                "{$state} — mulai: " .
                $record->dilanjutkanOleh
                    ->pencatatanTerakhir
                    ?->created_at->format('M Y')
            ),
    ]),
```

---

## ✅ Skenario Uji

### Skenario A — Pelanggan Baru, Lokasi Baru

| # | Kondisi | Langkah | Hasil yang Diharapkan |
|---|---|---|---|
| ✅ | Normal | Buat meter baru, `melanjutkan_dari_id` kosong, nomor baru | Record tersimpan, tidak ada relasi ke meter lain |
| ❌ | Nomor duplikat aktif | Input `nomor_meter` yang sudah dipakai meter `Aktif` lain | Validasi gagal: "Nomor meter sudah digunakan meter yang masih Aktif" |
| ✅ | Nomor sama tapi lama sudah Nonaktif | Input nomor yang pernah dipakai meter `Nonaktif` | Validasi lolos — ini bukan oper kontrak, hanya kebetulan nomor sama |

### Skenario B — Pelanggan Nonaktif, Tidak Ada Pengganti

| # | Kondisi | Langkah | Hasil yang Diharapkan |
|---|---|---|---|
| ✅ | Normal | Toggle `status_aktif` pelanggan → false | Observer berjalan, semua meter `Aktif` miliknya → `Nonaktif` |
| ✅ | Pelanggan punya 2 meter | Nonaktifkan pelanggan dengan dua meter | Kedua meter berubah ke `Nonaktif` sekaligus |
| ❌ | Observer tidak terpasang | Nonaktifkan pelanggan tanpa observer | Meter tetap `Aktif` — bug, observer wajib terdaftar di `AppServiceProvider` |

### Skenario C — Oper Kontrak

| # | Kondisi | Langkah | Hasil yang Diharapkan |
|---|---|---|---|
| ✅ | Normal | Pilih meter `Nonaktif` di field `melanjutkan_dari_id` | `angka_awal` dan `nomor_meter` otomatis ter-isi dari meter lama |
| ✅ | Keterlacakan | Buka detail meter baru setelah tersimpan | Section "Riwayat Oper Kontrak" muncul, menampilkan info meter lama |
| ✅ | Keterlacakan balik | Buka detail meter lama | Tampil info: "Diteruskan ke Pelanggan B — mulai Feb 2025" |
| ❌ | Pilih meter yang masih Aktif | Coba pilih meter `Aktif` di dropdown oper kontrak | Dropdown hanya menampilkan meter `Nonaktif` — tidak bisa dipilih |
| ❌ | Edit `angka_awal` setelah oper kontrak dipilih | — | Field `angka_awal` menjadi `readOnly()` setelah `melanjutkan_dari_id` diisi |

---

## ⚠️ Urutan Eksekusi

1. Jalankan migration refactor `meter_air`
2. Update model `MeterAir` (relasi baru)
3. Buat dan daftarkan `PelangganObserver`
4. Update `MeterAirResource` (validasi, field oper kontrak, tampilan detail)
5. Jalankan `php artisan migrate` — tidak perlu `migrate:fresh`,
   migration ini bersifat alter (additive + modify), data lama aman.

---

## 💡 Catatan Penting

Kolom `melanjutkan_dari_id` menggunakan `nullOnDelete` — bukan
`cascadeOnDelete` dan bukan `restrictOnDelete`. Artinya jika record
meter lama suatu saat terhapus (meskipun seharusnya tidak karena
soft delete), kolom ini di record baru akan menjadi `null` secara
otomatis — tidak menyebabkan error dan tidak menghapus data meter baru.