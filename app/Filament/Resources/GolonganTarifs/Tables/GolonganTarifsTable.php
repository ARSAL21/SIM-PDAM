<?php

namespace App\Filament\Resources\GolonganTarifs\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class GolonganTarifsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('nama_golongan')
                    ->label('Nama Golongan')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),
                TextColumn::make('tarif_per_kubik')
                    ->label('Tarif / m³')
                    ->money('IDR', locale: 'id')
                    ->sortable(),
                TextColumn::make('biaya_admin')
                    ->label('Biaya Admin')
                    ->money('IDR', locale: 'id')
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
