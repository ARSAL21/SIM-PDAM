<?php

namespace App\Filament\Resources\Tagihans\Schemas;

use Carbon\Carbon;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Group as ComponentsGroup;
use Filament\Schemas\Components\Section as ComponentsSection;
use Filament\Schemas\Schema;
use Filament\Support\Enums\FontWeight;
use Filament\Support\Enums\TextSize;

class TagihanInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->schema([
                // 1. HEADER TAGIHAN
                ComponentsSection::make('Informasi Tagihan')
                    ->icon('heroicon-o-document-text')
                    ->columns(2)
                    ->schema([
                        ComponentsGroup::make([
                            TextEntry::make('no_tagihan')
                                ->label('Nomor Invoice')
                                ->size(TextSize::Large)
                                ->weight(FontWeight::ExtraBold)
                                ->copyable()
                                ->copyMessage('No. tagihan disalin!'),

                            TextEntry::make('created_at')
                                ->label('Tanggal Terbit')
                                ->dateTime('d F Y, H:i')
                                ->icon('heroicon-m-calendar'),
                        ]),

                        ComponentsGroup::make([
                            TextEntry::make('status_bayar')
                                ->label('Status Pembayaran')
                                ->badge()
                                ->size(TextSize::Large)
                                ->color(fn (string $state): string => match ($state) {
                                    'Belum Bayar'         => 'warning',
                                    'Menunggu Verifikasi' => 'info',
                                    'Lunas'               => 'success',
                                    default               => 'gray',
                                }),

                            TextEntry::make('jumlah_tagihan')
                                ->label('Total Tagihan')
                                ->money('IDR')
                                ->size(TextSize::Large)
                                ->weight(FontWeight::ExtraBold)
                                ->color('success'),
                        ]),
                    ]),

                // 2. IDENTITAS PELANGGAN
                ComponentsSection::make('Data Pelanggan')
                    ->icon('heroicon-o-user-circle')
                    ->columns(2)
                    ->schema([
                        TextEntry::make('pelanggan.user.name')
                            ->label('Nama Pelanggan')
                            ->weight(FontWeight::Bold)
                            ->icon('heroicon-m-user'),

                        TextEntry::make('pelanggan.no_pelanggan')
                            ->label('No. Pelanggan')
                            ->copyable()
                            ->copyMessage('Disalin!'),

                        TextEntry::make('pencatatanMeter.meterAir.nomor_meter')
                            ->label('Nomor Meter')
                            ->badge()
                            ->color('gray'),

                        TextEntry::make('pelanggan.alamat')
                            ->label('Alamat')
                            ->icon('heroicon-m-map-pin')
                            ->columnSpanFull(),
                    ]),

                // 3. DATA PEMAKAIAN
                ComponentsSection::make('Data Pemakaian')
                    ->icon('heroicon-o-chart-bar-square')
                    ->columns(3)
                    ->schema([
                        TextEntry::make('periode')
                            ->label('Periode')
                            ->state(fn ($record) => Carbon::create(
                                $record->pencatatanMeter->periode_tahun,
                                $record->pencatatanMeter->periode_bulan
                            )->translatedFormat('F Y'))
                            ->weight(FontWeight::Bold)
                            ->icon('heroicon-m-calendar-days'),

                        TextEntry::make('pencatatanMeter.pemakaian_m3')
                            ->label('Pemakaian')
                            ->suffix(' m³')
                            ->size(TextSize::Large)
                            ->weight(FontWeight::ExtraBold)
                            ->color('primary'),

                        TextEntry::make('angka_meter')
                            ->label('Angka Meter')
                            ->state(fn ($record) =>
                                $record->pencatatanMeter->angka_awal . ' → ' .
                                $record->pencatatanMeter->angka_akhir . ' m³'
                            ),
                    ]),

                // 4. RINCIAN KALKULASI
                ComponentsSection::make('Rincian Kalkulasi')
                    ->icon('heroicon-o-calculator')
                    ->description('Transparansi formula perhitungan tagihan.')
                    ->schema([
                        Grid::make(3)->schema([
                            TextEntry::make('biaya_pemakaian')
                                ->label('Biaya Pemakaian')
                                ->state(fn ($record) =>
                                    number_format($record->pencatatanMeter->pemakaian_m3) . ' m³ × Rp ' .
                                    number_format($record->pelanggan->golonganTarif->tarif_per_kubik, 0, ',', '.')
                                )
                                ->helperText(fn ($record) =>
                                    '= Rp ' . number_format(
                                        $record->pencatatanMeter->pemakaian_m3 * $record->pelanggan->golonganTarif->tarif_per_kubik,
                                        0, ',', '.'
                                    )
                                ),

                            TextEntry::make('biaya_beban_display')
                                ->label('Biaya Beban')
                                ->html()
                                ->state(function ($record) {
                                    $biayaNormal = $record->pelanggan->golonganTarif->biaya_admin;
                                    $biayaPemakaian = $record->pencatatanMeter->pemakaian_m3 * $record->pelanggan->golonganTarif->tarif_per_kubik;
                                    $biayaBebanAktual = $record->jumlah_tagihan - $biayaPemakaian;

                                    $fmt = fn ($n) => 'Rp ' . number_format($n, 0, ',', '.');

                                    if ($biayaBebanAktual < $biayaNormal) {
                                        return '<span style="text-decoration: line-through; color: #9CA3AF;">' . $fmt($biayaNormal) . '</span><br>' .
                                               '<span style="color: #10B981; font-weight: bold;">' . $fmt($biayaBebanAktual) . ' — Bebas Biaya (Ganti Meter)</span>';
                                    }
                                    return $fmt($biayaNormal);
                                }),

                            TextEntry::make('jumlah_tagihan')
                                ->label('Total Akhir')
                                ->money('IDR')
                                ->size(TextSize::Large)
                                ->weight(FontWeight::ExtraBold)
                                ->color('success'),
                        ]),
                    ]),
            ]);
    }
}
