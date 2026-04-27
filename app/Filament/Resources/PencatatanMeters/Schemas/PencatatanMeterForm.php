<?php

namespace App\Filament\Resources\PencatatanMeters\Schemas;

use App\Models\MeterAir;
use Closure;
use Filament\Schemas\Components\Grid;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Support\HtmlString;

class PencatatanMeterForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('meter_air_id')
                    ->label('Meter Air')
                    ->required()
                    ->searchable()
                    ->getSearchResultsUsing(fn (string $search) =>
                        // 1. Buka akses untuk status Rusak
                        MeterAir::whereIn('status', ['Aktif', 'Rusak'])
                            ->whereHas('pelanggan', fn ($q) =>
                                $q->where('status_aktif', true)
                            )
                            ->where(fn ($q) => $q
                                ->where('nomor_meter', 'like', "%{$search}%")
                                ->orWhereHas('pelanggan.user', fn ($q) =>
                                    $q->where('name', 'like', "%{$search}%")
                                )
                            )
                            ->with('pelanggan.user')
                            ->limit(20)
                            ->get()
                            ->mapWithKeys(fn ($meter) => [
                                // 2. Tambahkan peringatan visual di dalam dropdown
                                $meter->id => "[{$meter->nomor_meter}] " .
                                              $meter->pelanggan->user->name .
                                              ($meter->status === 'Rusak' ? ' ⚠️ (RUSAK)' : '')
                            ])
                    )
                    ->getOptionLabelUsing(function ($value) {
                        $meter = MeterAir::with('pelanggan.user')->find($value);
                        return $meter ? "[{$meter->nomor_meter}] {$meter->pelanggan->user->name}" . ($meter->status === 'Rusak' ? ' ⚠️ (RUSAK)' : '') : null;
                    })
                    ->live()
                    ->afterStateUpdated(function ($state, Set $set) {
                        if (!$state) {
                            $set('angka_awal', null);
                            $set('pemakaian_m3', null);
                            return;
                        }

                        $meter = MeterAir::with('pencatatanTerakhir')->find($state);
                        if (!$meter) return;

                        $angkaAwal = $meter->pencatatanTerakhir?->angka_akhir
                                     ?? $meter->angka_awal;

                        $set('angka_awal', $angkaAwal);
                    })
                    ->disabledOn('edit')
                    ->dehydrated(true),

                // 3. KOTAK PANDUAN SOP (Hanya muncul jika meteran berstatus Rusak)
                Placeholder::make('panduan_meter_rusak')
                    ->label('')
                    ->content(new HtmlString(
                        '<div style="padding: 1rem; background: #FEF2F2;
                                     border-radius: 8px; color: #991B1B;
                                     border: 1px solid #F87171; margin-bottom: 1rem;">
                            <strong style="font-size: 1.1em;">⚠️ PANDUAN FINAL BILLING (METER RUSAK)</strong><br><br>
                            Anda memilih meteran yang saat ini berstatus <strong>Rusak</strong>. Sistem mengizinkan pencatatan ini <strong>hanya untuk menagih sisa hutang pemakaian (Final Billing)</strong> sebelum meteran ini dicabut.<br><br>
                            <em>SOP Admin: Setelah tagihan untuk pencatatan ini digenerate, pastikan meteran fisik sudah diganti dengan yang baru, lalu ubah status meteran ini menjadi "Nonaktif".</em>
                        </div>'
                    ))
                    ->columnSpanFull()
                    // Reaktif: Cek ke database status meteran setiap kali dropdown berubah
                    ->visible(function (Get $get) {
                        $meterId = $get('meter_air_id');
                        if (!$meterId) return false;

                        $meter = MeterAir::find($meterId);
                        return $meter?->status === 'Rusak';
                    }),

                Grid::make(2)->schema([
                    Select::make('periode_bulan')
                        ->label('Bulan')
                        ->required()
                        ->disabledOn('edit')
                        ->dehydrated(true)
                        ->options([
                            1 => 'Januari',  2 => 'Februari', 3 => 'Maret',
                            4 => 'April',    5 => 'Mei',       6 => 'Juni',
                            7 => 'Juli',     8 => 'Agustus',   9 => 'September',
                            10 => 'Oktober', 11 => 'November', 12 => 'Desember',
                        ])
                        ->default(now()->month),

                    TextInput::make('periode_tahun')
                        ->label('Tahun')
                        ->required()
                        ->disabledOn('edit')
                        ->dehydrated(true)
                        ->numeric()
                        ->minValue(2000)
                        ->maxValue(now()->year + 1)
                        ->default(now()->year),
                ]),

                TextInput::make('angka_awal')
                    ->label('Angka Awal (m³)')
                    ->required()
                    ->numeric()
                    ->readOnly()
                    ->dehydrated(true)
                    ->helperText(
                        'Terisi otomatis dari angka akhir bulan lalu. ' .
                        'Jika ini pencatatan pertama, diambil dari angka awal meter.'
                    )
                    ->disabledOn('edit'),

                TextInput::make('angka_akhir')
                    ->label('Angka Akhir (m³)')
                    ->required()
                    ->numeric()
                    ->minValue(0)
                    ->live(onBlur: true)
                    ->afterStateUpdated(function ($state, Get $get, Set $set) {
                        $angkaAwal  = (int) $get('angka_awal');
                        $angkaAkhir = (int) $state;

                        $set('pemakaian_m3', $angkaAkhir >= $angkaAwal
                            ? $angkaAkhir - $angkaAwal
                            : null
                        );
                    })
                    ->rules([
                        // Validasi 1: angka tidak boleh kurang dari angka_awal
                        fn (Get $get): Closure => function (
                            string $attribute,
                            mixed $value,
                            Closure $fail
                        ) use ($get) {
                            if ((int) $value < (int) $get('angka_awal')) {
                                $fail(
                                    'Angka akhir tidak boleh lebih kecil dari angka awal (' .
                                    number_format((int) $get('angka_awal')) . ' m³).'
                                );
                            }
                        },

                        // Validasi 2: Strict Chronological Insertion
                        fn (Get $get, ?\Illuminate\Database\Eloquent\Model $record): Closure => function (
                            string $attribute,
                            mixed $value,
                            Closure $fail
                        ) use ($get, $record) {
                            $meterId = $get('meter_air_id') ?? $record?->meter_air_id;
                            $bulan   = (int) $get('periode_bulan');
                            $tahun   = (int) $get('periode_tahun');

                            if (!$meterId || !$bulan || !$tahun) return;

                            $adaPeriodeLebihBaru = \App\Models\PencatatanMeter::where('meter_air_id', $meterId)
                                ->when($record?->id, fn ($q) => $q->where('id', '!=', $record->id))
                                ->where(fn ($q) => $q
                                    ->where('periode_tahun', '>', $tahun)
                                    ->orWhere(fn ($q) => $q
                                        ->where('periode_tahun', $tahun)
                                        ->where('periode_bulan', '>', $bulan)
                                    )
                                )
                                ->exists();

                            if ($adaPeriodeLebihBaru) {
                                $fail(
                                    'Tidak dapat menyimpan pencatatan untuk periode ini. ' .
                                    'Sudah ada pencatatan di periode yang lebih baru untuk meter ini. ' .
                                    'Hapus pencatatan setelahnya terlebih dahulu jika ingin menyisipkan data.'
                                );
                            }
                        },
                    ]),

                TextInput::make('pemakaian_m3')
                    ->label('Pemakaian (m³)')
                    ->numeric()
                    ->readOnly()
                    ->dehydrated(true)
                    ->helperText('Dihitung otomatis: angka akhir dikurangi angka awal.'),

                Textarea::make('catatan_koreksi')
                    ->label('Alasan Koreksi')
                    ->required()
                    ->rows(3)
                    ->helperText('Jelaskan alasan perubahan angka meter ini. Wajib diisi.')
                    ->visibleOn('edit'),

                Placeholder::make('peringatan_koreksi')
                    ->label('')
                    ->content(new HtmlString(
                        '<div style="padding: 0.75rem; background: #E0F2FE;
                                     border-radius: 8px; color: #075985;
                                     border: 1px solid #7DD3FC;">
                            <strong>ℹ️ Info Otomatisasi:</strong> Pencatatan ini memiliki tagihan 
                            aktif. Setelah koreksi angka meter ini disimpan, sistem akan 
                            <strong>otomatis menghitung ulang dan memperbarui</strong> jumlah 
                            tagihan agar sesuai dengan pemakaian terbaru.
                        </div>'
                    ))
                    ->visibleOn('edit')
                    ->visible(fn ($record) =>
                        $record?->tagihan?->status_bayar === 'Belum Bayar'
                    ),
            ]);
    }
}
