<?php

namespace App\Filament\Resources\PencatatanMeters\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Support\Enums\TextSize;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Enums\FontWeight;
use Carbon\Carbon;

class PencatatanMeterInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make('Identitas Pelanggan & Meter')
                    ->description('Detail pelanggan dan perangkat meter yang tercatat.')
                    ->columns(2)
                    ->schema([
                        TextEntry::make('meterAir.pelanggan.user.name')
                            ->label('Nama Pelanggan')
                            ->weight(FontWeight::Bold)
                            ->color('primary'),

                        TextEntry::make('meterAir.pelanggan.no_pelanggan')
                            ->label('No. Pelanggan')
                            ->copyable(),

                        TextEntry::make('meterAir.nomor_meter')
                            ->label('Nomor Meter')
                            ->badge()
                            ->color('gray'),

                        TextEntry::make('meterAir.pelanggan.golonganTarif.nama_golongan')
                            ->label('Golongan Tarif')
                            ->hint(fn ($record) => 'Tarif: IDR ' . number_format($record->meterAir->pelanggan->golonganTarif->tarif_per_kubik) . '/m³'),

                        TextEntry::make('meterAir.pelanggan.alamat')
                            ->label('Alamat Lengkap')
                            ->columnSpanFull()
                            ->icon('heroicon-m-map-pin'),
                    ]),

                Section::make('Data Pemakaian Air')
                    ->description('Detail angka meter dan volume pemakaian pada periode ini.')
                    ->columns(3)
                    ->schema([
                        TextEntry::make('periode')
                            ->label('Periode')
                            ->weight(FontWeight::Bold)
                            ->state(fn ($record) =>
                                Carbon::create(
                                    $record->periode_tahun,
                                    $record->periode_bulan
                                )->translatedFormat('F Y')
                            ),

                        TextEntry::make('petugas.name')
                            ->label('Petugas Pencatat')
                            ->icon('heroicon-m-user'),

                        TextEntry::make('created_at')
                            ->label('Waktu Input')
                            ->dateTime('d M Y, H:i'),

                        TextEntry::make('angka_awal')
                            ->label('Angka Awal')
                            ->suffix(' m³')
                            ->color('gray'),

                        TextEntry::make('angka_akhir')
                            ->label('Angka Akhir')
                            ->suffix(' m³')
                            ->color('gray'),

                        TextEntry::make('pemakaian_m3')
                            ->label('Total Pemakaian')
                            ->suffix(' m³')
                            ->weight(FontWeight::Bold)
                            ->color('primary')
                            ->size(TextSize::Large),
                    ]),

                Section::make('Informasi Tagihan')
                    ->description('Status dan rincian kalkulasi biaya.')
                    ->visible(fn ($record) => $record->tagihan()->exists())
                    ->columns(2)
                    ->schema([
                        Section::make('Status Pembayaran')
                            ->columnSpan(1)
                            ->schema([
                                TextEntry::make('tagihan.status_bayar')
                                    ->label('Status')
                                    ->badge()
                                    ->size(TextSize::Large)
                                    ->color(fn (string $state): string => match ($state) {
                                        'Belum Bayar'         => 'warning',
                                        'Menunggu Verifikasi' => 'info',
                                        'Lunas'               => 'success',
                                        default               => 'gray',
                                    }),

                                TextEntry::make('tagihan.no_tagihan')
                                    ->label('Nomor Invoice')
                                    ->copyable()
                                    ->weight(FontWeight::Bold),
                            ]),

                        Section::make('Rincian Kalkulasi')
                            ->columnSpan(1)
                            ->schema([
                                TextEntry::make('kalkulasi_pemakaian')
                                    ->label('Biaya Pemakaian')
                                    ->state(fn ($record) => 
                                        number_format($record->pemakaian_m3) . ' m³ x IDR ' . 
                                        number_format($record->meterAir->pelanggan->golonganTarif->tarif_per_kubik)
                                    )
                                    ->suffix(fn ($record) => 
                                        ' = IDR ' . number_format($record->pemakaian_m3 * $record->meterAir->pelanggan->golonganTarif->tarif_per_kubik)
                                    ),

                                TextEntry::make('meterAir.pelanggan.golonganTarif.biaya_admin')
                                    ->label('Biaya Administrasi')
                                    ->money('IDR'),

                                TextEntry::make('tagihan.jumlah_tagihan')
                                    ->label('Total Tagihan')
                                    ->money('IDR')
                                    ->weight(FontWeight::Bold)
                                    ->size(TextSize::Large)
                                    ->color('success'),
                            ]),
                    ]),

                Section::make('Catatan Koreksi')
                    ->visible(fn ($record) => filled($record->catatan_koreksi))
                    ->schema([
                        TextEntry::make('catatan_koreksi')
                            ->label('')
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
