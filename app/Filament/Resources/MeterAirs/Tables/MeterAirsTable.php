<?php

namespace App\Filament\Resources\MeterAirs\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
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
            ->recordUrl(fn ($record) => \App\Filament\Resources\MeterAirs\MeterAirResource::getUrl('view', ['record' => $record]))
            ->columns([
                TextColumn::make('pelanggan.nama_lengkap')
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
                EditAction::make(),
                DeleteAction::make()
                    ->disabled(fn ($record) => $record->pencatatanMeters()->exists() || $record->status === 'Aktif')
                    ->tooltip(function ($record) {
                        if ($record->pencatatanMeters()->exists()) {
                            return 'Tidak dapat dihapus — memiliki riwayat pencatatan.';
                        }
                        if ($record->status === 'Aktif') {
                            return 'Tidak dapat dihapus — status masih aktif.';
                        }
                        return 'Hapus meter air';
                    })
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
                        ->successNotification(null)
                        ->action(function (Collection $records) {
                            
                            // 1. Filter Meter Air yang "Kotor" (Masih Aktif / Punya Riwayat)
                            $blocked = $records->filter(fn ($record) =>
                                $record->pencatatanMeters()->exists() || $record->status === 'Aktif'
                            );

                            $safeRecords = $records->diff($blocked);

                            // 2. Eksekusi Hapus & Notif Sukses untuk yang "Bersih"
                            if ($safeRecords->isNotEmpty()) {
                                $safeRecords->each->delete();

                                Notification::make()
                                    ->success()
                                    ->title('Penghapusan Berhasil')
                                    ->body("{$safeRecords->count()} meter air telah dihapus permanen.")
                                    ->send();
                            }

                            // 3. Notif Ditolak untuk yang "Kotor"
                            if ($blocked->isNotEmpty()) {
                                Notification::make()
                                    ->danger()
                                    ->title('Sebagian Aksi Ditolak!')
                                    ->body("{$blocked->count()} meter air tidak dapat dihapus karena masih aktif atau memiliki riwayat pencatatan.")
                                    ->send();
                            }
                        }),
                ]),
            ]);
    }
}
