<?php

namespace App\Filament\Resources\PencatatanMeters\Schemas;

use Filament\Infolists\Components\TextEntry;
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
                Section::make('Identitas Meter')
                    ->columns(2)
                    ->schema([
                        TextEntry::make('meterAir.nomor_meter')
                            ->label('Nomor Meter'),

                        TextEntry::make('meterAir.pelanggan.user.name')
                            ->label('Nama Pelanggan'),

                        TextEntry::make('meterAir.pelanggan.no_pelanggan')
                            ->label('No. Pelanggan'),

                        TextEntry::make('meterAir.pelanggan.golonganTarif.nama_golongan')
                            ->label('Golongan Tarif'),
                    ]),

                Section::make('Data Pencatatan')
                    ->columns(2)
                    ->schema([
                        TextEntry::make('periode')
                            ->label('Periode')
                            ->state(fn ($record) =>
                                Carbon::create(
                                    $record->periode_tahun,
                                    $record->periode_bulan
                                )->translatedFormat('F Y')
                            ),

                        TextEntry::make('petugas.name')
                            ->label('Dicatat Oleh'),

                        TextEntry::make('angka_awal')
                            ->label('Angka Awal (m³)')
                            ->numeric(),

                        TextEntry::make('angka_akhir')
                            ->label('Angka Akhir (m³)')
                            ->numeric(),

                        TextEntry::make('pemakaian_m3')
                            ->label('Pemakaian (m³)')
                            ->numeric()
                            ->weight(FontWeight::Bold),

                        TextEntry::make('created_at')
                            ->label('Waktu Input')
                            ->dateTime('d M Y, H:i'),
                    ]),

                Section::make('Status Tagihan')
                    ->schema([
                        TextEntry::make('tagihan.status_bayar')
                            ->label('Status')
                            ->badge()
                            ->default('Belum Digenerate')
                            ->color(fn (string $state): string => match ($state) {
                                'Belum Bayar'         => 'warning',
                                'Menunggu Verifikasi' => 'info',
                                'Lunas'               => 'success',
                                default               => 'gray',
                            }),

                        TextEntry::make('tagihan.no_tagihan')
                            ->label('No. Tagihan')
                            ->placeholder('—')
                            ->visible(fn ($record) => $record->tagihan()->exists()),

                        TextEntry::make('tagihan.jumlah_tagihan')
                            ->label('Jumlah Tagihan')
                            ->money('IDR')
                            ->visible(fn ($record) => $record->tagihan()->exists()),
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
