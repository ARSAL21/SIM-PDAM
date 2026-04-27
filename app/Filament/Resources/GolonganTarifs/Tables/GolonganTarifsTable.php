<?php

namespace App\Filament\Resources\GolonganTarifs\Tables;


use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

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
                TextColumn::make('pelanggans_count')
                    ->label('Jumlah Pelanggan')
                    ->counts('pelanggans')
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make()
                    ->before(function (DeleteAction $action, Model $record) {
                        if ($record->pelanggans()->exists()) {
                            Notification::make()
                                ->danger()
                                ->title('Aksi Ditolak!')
                                ->body('Golongan tarif ini tidak dapat dihapus karena masih digunakan oleh pelanggan.')
                                ->send();

                            $action->cancel();
                        }
                    }),
            ])
            ->toolbarActions([
                //
            ]);
    }
}
