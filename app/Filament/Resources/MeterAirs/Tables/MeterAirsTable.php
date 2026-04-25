<?php

namespace App\Filament\Resources\MeterAirs\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

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
                        'Nonaktif' => 'Nonaktif',
                    ]),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
                DeleteAction::make()
                    ->before(function (DeleteAction $action, Model $record) {
                        if ($record->pencatatanMeters()->exists()) {
                            Notification::make()
                                ->danger()
                                ->title('Aksi Ditolak!')
                                ->body('Meter air ini tidak dapat dihapus karena sudah memiliki riwayat pencatatan.')
                                ->send();

                            $action->cancel();
                            return;
                        }

                        if ($record->status === 'Aktif') {
                            Notification::make()
                                ->danger()
                                ->title('Aksi Ditolak!')
                                ->body('Meter air berstatus Aktif tidak dapat dihapus. Ubah statusnya terlebih dahulu.')
                                ->send();

                            $action->cancel();
                        }
                    }),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->action(function (Collection $records) {
                            $blocked = $records->filter(fn (Model $record) =>
                                $record->pencatatanMeters()->exists() || $record->status === 'Aktif'
                            );

                            if ($blocked->isNotEmpty()) {
                                Notification::make()
                                    ->danger()
                                    ->title('Sebagian Aksi Ditolak!')
                                    ->body("{$blocked->count()} meter air tidak dapat dihapus karena masih aktif atau memiliki riwayat pencatatan.")
                                    ->send();

                                $safeRecords = $records->diff($blocked);
                                $safeRecords->each->delete();
                            } else {
                                $records->each->delete();
                            }
                        }),
                ]),
            ]);
    }
}
