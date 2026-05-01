<?php

namespace App\Filament\Resources\Pembayarans\Tables;

use App\Filament\Resources\Pembayarans\PembayaranResource;
use Carbon\Carbon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class PembayaransTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->recordUrl(fn ($record) => PembayaranResource::getUrl('view', ['record' => $record]))
            ->columns([
                TextColumn::make('tagihan.no_tagihan')
                    ->label('No. Invoice')
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->copyable()
                    ->copyMessage('No. tagihan disalin!'),

                TextColumn::make('tagihan.pelanggan.nama_lengkap')
                    ->label('Pelanggan')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('periode')
                    ->label('Periode')
                    ->getStateUsing(fn ($record) => 
                        $record->tagihan?->pencatatanMeter 
                            ? Carbon::create(
                                $record->tagihan->pencatatanMeter->periode_tahun,
                                $record->tagihan->pencatatanMeter->periode_bulan
                              )->translatedFormat('M Y')
                            : '-'
                    ),

                TextColumn::make('metode_bayar')
                    ->label('Metode')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'tunai'    => 'success',
                        'transfer' => 'info',
                        default    => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'tunai'    => '💵 Tunai',
                        'transfer' => '🏦 Transfer',
                        default    => $state,
                    })
                    ->sortable(),

                TextColumn::make('jumlah_bayar')
                    ->label('Nominal')
                    ->money('IDR', locale: 'id')
                    ->sortable()
                    ->weight('bold'),

                TextColumn::make('status_pembayaran')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'Pending'   => 'warning',
                        'Disetujui' => 'success',
                        'Ditolak'   => 'danger',
                        default     => 'gray',
                    })
                    ->sortable(),

                TextColumn::make('verifikator.name')
                    ->label('Diverifikasi Oleh')
                    ->default('-')
                    ->toggleable(),

                TextColumn::make('tanggal_bayar')
                    ->label('Tgl Bayar')
                    ->dateTime('d M Y, H:i')
                    ->sortable(),

                TextColumn::make('diverifikasi_pada')
                    ->label('Tgl Verifikasi')
                    ->dateTime('d M Y, H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status_pembayaran')
                    ->label('Status')
                    ->options([
                        'Pending'   => 'Pending',
                        'Disetujui' => 'Disetujui',
                        'Ditolak'   => 'Ditolak',
                    ]),

                SelectFilter::make('metode_bayar')
                    ->label('Metode Bayar')
                    ->options([
                        'tunai'    => 'Tunai',
                        'transfer' => 'Transfer',
                    ]),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
