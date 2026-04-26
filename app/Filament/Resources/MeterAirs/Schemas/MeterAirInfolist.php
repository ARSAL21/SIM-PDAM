<?php

namespace App\Filament\Resources\MeterAirs\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;
use Filament\Support\Enums\FontWeight;
use Filament\Support\Enums\TextSize;
use Carbon\Carbon;
use Filament\Schemas\Components\Group as ComponentsGroup;
use Filament\Schemas\Components\Section as ComponentsSection;

class MeterAirInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->schema([
                ComponentsSection::make('Informasi Pelanggan & Lokasi')
                    ->icon('heroicon-o-map-pin')
                    ->description('Identitas kepemilikan dan lokasi pemasangan meteran.')
                    ->columns(2)
                    ->schema([
                        TextEntry::make('pelanggan.user.name')
                            ->label('Nama Pemilik')
                            ->weight(FontWeight::Bold)
                            ->color('primary')
                            ->icon('heroicon-m-user'),

                        TextEntry::make('pelanggan.no_pelanggan')
                            ->label('No. Pelanggan')
                            ->copyable()
                            ->copyMessage('Disalin!'),

                        TextEntry::make('pelanggan.golonganTarif.nama_golongan')
                            ->label('Golongan Tarif')
                            ->badge()
                            ->color('info')
                            ->icon('heroicon-m-currency-dollar'),

                        TextEntry::make('pelanggan.alamat')
                            ->label('Alamat Pemasangan')
                            ->icon('heroicon-m-building-office-2')
                            ->columnSpanFull(),
                    ]),

                ComponentsSection::make('Spesifikasi & Status Alat')
                    ->icon('heroicon-o-wrench-screwdriver')
                    ->columns(3)
                    ->schema([
                        ComponentsGroup::make([
                            TextEntry::make('nomor_meter')
                                ->label('Nomor Seri Meter')
                                ->size(TextSize::Large)
                                ->weight(FontWeight::ExtraBold)
                                ->copyable(),

                            TextEntry::make('merek')
                                ->label('Merek Pabrikan')
                                ->default('Tidak diketahui')
                                ->color('gray'),
                        ]),

                        ComponentsGroup::make([
                            TextEntry::make('angka_awal')
                                ->label('Angka Awal Pemasangan')
                                ->suffix(' m³')
                                ->weight(FontWeight::Bold),

                            TextEntry::make('status')
                                ->label('Status Alat')
                                ->badge()
                                ->size(TextSize::Large)
                                ->color(fn (string $state): string => match ($state) {
                                    'Aktif'    => 'success',
                                    'Rusak'    => 'warning',
                                    'Nonaktif' => 'danger',
                                    default    => 'gray',
                                }),
                        ]),

                        // Peringatan jika rusak/nonaktif
                        TextEntry::make('keterangan')
                            ->label('Catatan Status / Riwayat')
                            ->color(fn ($record) => $record->status === 'Aktif' ? 'gray' : 'danger')
                            ->weight(fn ($record) => $record->status === 'Aktif' ? FontWeight::Normal : FontWeight::Bold)
                            ->default('-')
                            ->columnSpan(1),
                    ]),

                // 3. KELOMPOK STATISTIK OPERASIONAL
                ComponentsSection::make('Statistik Operasional (Life Cycle)')
                    ->icon('heroicon-o-chart-pie')
                    ->description('Rekam jejak dan beban kerja meteran air ini sejak dipasang.')
                    ->columns(3)
                    ->schema([
                        TextEntry::make('tanggal_pasang')
                            ->label('Tanggal Pasang')
                            ->date('d F Y')
                            ->icon('heroicon-m-calendar')
                            ->helperText(function ($record) {
                                $tanggalPasang = Carbon::parse($record->tanggal_pasang);
                                return 'Telah beroperasi: ' . $tanggalPasang->diffForHumans(['parts' => 2]);
                            }),

                        TextEntry::make('pencatatan_terakhir')
                            ->label('Tgl Pencatatan Terakhir')
                            ->state(function ($record) {
                                $lastRecord = $record->pencatatanMeters()->latest('created_at')->first();
                                return $lastRecord ? $lastRecord->created_at->translatedFormat('d F Y') : 'Belum pernah dicatat';
                            })
                            ->icon('heroicon-m-clock')
                            ->color('gray'),

                        TextEntry::make('total_pencatatan')
                            ->label('Total Riwayat Catat')
                            ->state(function ($record) {
                                return $record->pencatatanMeters()->count() . ' Bulan / Kali';
                            })
                            ->icon('heroicon-m-clipboard-document-list')
                            ->color('gray'),

                        TextEntry::make('angka_terkini')
                            ->label('Angka Meter Terkini')
                            ->state(function ($record) {
                                $lastRecord = $record->pencatatanMeters()->latest('periode_tahun')->latest('periode_bulan')->first();
                                return $lastRecord ? $lastRecord->angka_akhir . ' m³' : $record->angka_awal . ' m³';
                            })
                            ->size(TextSize::Large)
                            ->weight(FontWeight::ExtraBold)
                            ->color('info')
                            ->helperText('Berdasarkan pencatatan terakhir.'),

                        TextEntry::make('total_volume_air')
                            ->label('Total Beban Volume Air')
                            ->state(function ($record) {
                                $totalAir = $record->pencatatanMeters()->sum('pemakaian_m3');
                                return number_format($totalAir, 0, ',', '.') . ' m³';
                            })
                            ->size(TextSize::Large)
                            ->weight(FontWeight::ExtraBold)
                            ->color('primary')
                            ->helperText('Total kubikasi yang telah diukur.'),
                    ]),
            ]);
    }
}