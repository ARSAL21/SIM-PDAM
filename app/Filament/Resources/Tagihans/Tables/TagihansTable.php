<?php

namespace App\Filament\Resources\Tagihans\Tables;

use App\Filament\Resources\Tagihans\TagihanResource;
use Carbon\Carbon;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

class TagihansTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->recordUrl(fn ($record) => TagihanResource::getUrl('view', ['record' => $record]))
            ->columns([
                TextColumn::make('no_tagihan')
                    ->label('No. Invoice')
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->copyable()
                    ->copyMessage('No. tagihan disalin!'),

                TextColumn::make('pelanggan.user.name')
                    ->label('Pelanggan')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('periode')
                    ->label('Periode')
                    ->getStateUsing(fn ($record) => Carbon::create(
                        $record->pencatatanMeter->periode_tahun,
                        $record->pencatatanMeter->periode_bulan
                    )->translatedFormat('M Y'))
                    ->sortable(query: fn ($query, $direction) => $query
                        ->join('pencatatan_meter', 'tagihan.pencatatan_meter_id', '=', 'pencatatan_meter.id')
                        ->orderBy('pencatatan_meter.periode_tahun', $direction)
                        ->orderBy('pencatatan_meter.periode_bulan', $direction)
                    ),

                TextColumn::make('pencatatanMeter.pemakaian_m3')
                    ->label('Pemakaian')
                    ->suffix(' m³')
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('jumlah_tagihan')
                    ->label('Total Tagihan')
                    ->money('IDR', locale: 'id')
                    ->sortable()
                    ->weight('bold'),

                TextColumn::make('status_bayar')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'Belum Bayar'         => 'warning',
                        'Menunggu Verifikasi' => 'info',
                        'Lunas'               => 'success',
                        default               => 'gray',
                    })
                    ->sortable(),

                TextColumn::make('created_at')
                    ->label('Tanggal Terbit')
                    ->date('d M Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status_bayar')
                    ->label('Status Bayar')
                    ->options([
                        'Belum Bayar'         => 'Belum Bayar',
                        'Menunggu Verifikasi' => 'Menunggu Verifikasi',
                        'Lunas'               => 'Lunas',
                    ]),
            ])
            ->defaultSort('created_at', 'desc')
            ->recordActions([
                DeleteAction::make()
                    ->disabled(fn ($record) => $record->status_bayar !== 'Belum Bayar')
                    ->tooltip(fn ($record) => $record->status_bayar !== 'Belum Bayar'
                        ? 'Tidak dapat dihapus — tagihan sudah diproses atau lunas.'
                        : 'Hapus tagihan'
                    )
                    ->before(function (DeleteAction $action, Model $record) {
                        if ($record->status_bayar !== 'Belum Bayar') {
                            Notification::make()
                                ->danger()
                                ->title('Aksi Ditolak!')
                                ->body('Tagihan yang sudah diproses atau lunas tidak dapat dihapus.')
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
                            $blocked = $records->filter(fn ($record) =>
                                $record->status_bayar !== 'Belum Bayar'
                            );
                            $safeRecords = $records->diff($blocked);

                            if ($safeRecords->isNotEmpty()) {
                                $safeRecords->each->delete();
                                Notification::make()
                                    ->success()
                                    ->title('Penghapusan Berhasil')
                                    ->body("{$safeRecords->count()} tagihan telah dihapus.")
                                    ->send();
                            }

                            if ($blocked->isNotEmpty()) {
                                Notification::make()
                                    ->danger()
                                    ->title('Sebagian Aksi Ditolak!')
                                    ->body("{$blocked->count()} tagihan tidak dapat dihapus karena sudah diproses atau lunas.")
                                    ->send();
                            }
                        }),
                ]),
            ]);
    }
}
