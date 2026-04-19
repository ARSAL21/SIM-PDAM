<?php

namespace App\Filament\Resources\Pelanggans\Tables;

use App\Models\GolonganTarif;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class PelanggansTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                // ── Kolom Informasi Utama ──
                TextColumn::make('user.name')
                    ->label('Nama Pelanggan')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('no_pelanggan')
                    ->label('No. Pelanggan')
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->copyMessage('Nomor pelanggan disalin!'),

                TextColumn::make('golonganTarif.nama_golongan')
                    ->label('Golongan Tarif')
                    ->badge()
                    ->sortable(),

                TextColumn::make('no_hp')
                    ->label('No. HP')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('alamat')
                    ->label('Alamat')
                    ->limit(40)
                    ->tooltip(fn ($record) => $record->alamat)
                    ->toggleable(isToggledHiddenByDefault: true),

                // ── Kolom Interaktif ──
                ToggleColumn::make('status_aktif')
                    ->label('Aktif'),
            ])
            ->filters([
                // ── Filter Golongan Tarif ──
                SelectFilter::make('golongan_tarif_id')
                    ->label('Golongan Tarif')
                    ->relationship('golonganTarif', 'nama_golongan')
                    ->searchable()
                    ->preload(),
            ])
            ->defaultSort('created_at', 'desc')
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
