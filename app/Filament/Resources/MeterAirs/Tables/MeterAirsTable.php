<?php

namespace App\Filament\Resources\MeterAirs\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class MeterAirsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('pelanggan.user.name')
                    ->label('Pemilik')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('pelanggan.no_pelanggan')
                    ->label('No. Pelanggan')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('nomor_meter')
                    ->label('No. Meter')
                    ->searchable()
                    ->copyable(),

                TextColumn::make('merek')
                    ->label('Merek')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('tanggal_pasang')
                    ->label('Tgl Pasang')
                    ->date()
                    ->sortable(),

                TextColumn::make('angka_awal')
                    ->label('Angka Awal')
                    ->numeric(),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'Aktif' => 'success',
                        'Rusak' => 'danger',
                        'Diganti' => 'warning',
                        default => 'gray',
                    }),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        'Aktif' => 'Aktif',
                        'Rusak' => 'Rusak',
                        'Diganti' => 'Diganti',
                    ]),
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
