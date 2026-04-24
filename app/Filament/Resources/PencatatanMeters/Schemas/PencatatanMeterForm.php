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
                        MeterAir::where('status', 'Aktif')
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
                                $meter->id => "[{$meter->nomor_meter}] " .
                                              $meter->pelanggan->user->name
                            ])
                    )
                    ->getOptionLabelUsing(function ($value) {
                        $meter = MeterAir::with('pelanggan.user')->find($value);
                        return $meter ? "[{$meter->nomor_meter}] {$meter->pelanggan->user->name}" : null;
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

                Grid::make(2)->schema([
                    Select::make('periode_bulan')
                        ->label('Bulan')
                        ->required()
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
                        '<div style="padding: 0.75rem; background: #FAEEDA;
                                     border-radius: 8px; color: #633806;">
                            <strong>Perhatian:</strong> Pencatatan ini sudah memiliki tagihan
                            aktif. Perubahan angka di sini <strong>tidak otomatis mengupdate
                            jumlah tagihan</strong>. Batalkan tagihan lama dan generate ulang
                            setelah koreksi ini disimpan.
                        </div>'
                    ))
                    ->visibleOn('edit')
                    ->visible(fn ($record) =>
                        $record?->tagihan?->status_bayar !== null &&
                        $record?->tagihan?->status_bayar !== 'Lunas'
                    ),
            ]);
    }
}
