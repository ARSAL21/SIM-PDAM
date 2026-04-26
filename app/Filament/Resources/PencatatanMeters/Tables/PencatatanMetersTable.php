<?php

namespace App\Filament\Resources\PencatatanMeters\Tables;

use App\Models\PencatatanMeter;
use Carbon\Carbon;
use Filament\Actions\Action as ActionsAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction as ActionsDeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction as ActionsEditAction;
use Filament\Support\Enums\FontWeight;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class PencatatanMetersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->recordUrl(fn ($record) => \App\Filament\Resources\PencatatanMeters\PencatatanMeterResource::getUrl('view', ['record' => $record]))
            ->columns([
                TextColumn::make('meterAir.nomor_meter')
                    ->label('Nomor Meter')
                    ->searchable()
                    ->toggleable(),

                TextColumn::make('meterAir.pelanggan.user.name')
                    ->label('Nama Pelanggan')
                    ->searchable()
                    ->weight(FontWeight::Bold)
                    ->toggleable(),

                TextColumn::make('periode')
                    ->label('Periode')
                    ->badge()
                    ->color('info')
                    ->getStateUsing(fn ($record) =>
                        Carbon::create($record->periode_tahun, $record->periode_bulan)
                            ->translatedFormat('F Y')
                    )
                    ->sortable(query: fn ($query, $direction) =>
                        $query->orderBy('periode_tahun', $direction)
                              ->orderBy('periode_bulan', $direction)
                    )
                    ->toggleable(),

                TextColumn::make('angka_awal')
                    ->label('Angka Awal')
                    ->numeric()
                    ->alignRight()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('angka_akhir')
                    ->label('Angka Akhir')
                    ->numeric()
                    ->alignRight()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('pemakaian_m3')
                    ->label('Pemakaian (m³)')
                    ->numeric()
                    ->alignRight()
                    ->weight(FontWeight::ExtraBold)
                    ->color('primary')
                    ->toggleable(),

                TextColumn::make('tagihan.status_bayar')
                    ->label('Status Tagihan')
                    ->badge()
                    ->default('Belum Digenerate')
                    ->color(fn (string $state): string => match ($state) {
                        'Belum Bayar'         => 'warning',
                        'Menunggu Verifikasi' => 'info',
                        'Lunas'               => 'success',
                        default               => 'gray',
                    })
                    ->toggleable(),

                IconColumn::make('catatan_koreksi')
                    ->label('Dikoreksi')
                    ->boolean()
                    ->trueIcon('heroicon-o-pencil-square')
                    ->falseIcon('')
                    ->trueColor('warning')
                    ->tooltip(fn ($record) => $record->catatan_koreksi ?? '')
                    ->getStateUsing(fn ($record) => filled($record->catatan_koreksi))
                    ->toggleable(),

                TextColumn::make('petugas.name')
                    ->label('Dicatat Oleh')
                    ->toggleable(isToggledHiddenByDefault: true),
                
                TextColumn::make('created_at')
                    ->label('Tgl Input')
                    ->dateTime('d M Y, H:i')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('periode_tahun')
                    ->label('Tahun')
                    ->options(
                        PencatatanMeter::selectRaw('DISTINCT periode_tahun')
                            ->orderByDesc('periode_tahun')
                            ->pluck('periode_tahun', 'periode_tahun')
                    ),

                SelectFilter::make('periode_bulan')
                    ->label('Bulan')
                    ->options([
                        1 => 'Januari',  2 => 'Februari', 3 => 'Maret',
                        4 => 'April',    5 => 'Mei',       6 => 'Juni',
                        7 => 'Juli',     8 => 'Agustus',   9 => 'September',
                        10 => 'Oktober', 11 => 'November', 12 => 'Desember',
                    ]),

                Filter::make('belum_digenerate')
                    ->label('Belum Ada Tagihan')
                    ->query(fn ($query) => $query->doesntHave('tagihan')),

                Filter::make('belum_bayar')
                    ->label('Belum Bayar')
                    ->query(fn ($query) => $query->whereHas('tagihan', fn ($q) => 
                        $q->where('status_bayar', 'Belum Bayar')
                    )),

                Filter::make('sudah_dikoreksi')
                    ->label('Pernah Dikoreksi')
                    ->query(fn ($query) => $query->whereNotNull('catatan_koreksi')),
            ])
            ->recordActions([
                ActionsEditAction::make()
                    ->disabled(fn ($record) => ! \App\Filament\Resources\PencatatanMeters\PencatatanMeterResource::canEdit($record))
                    ->tooltip(fn ($record) =>
                        in_array($record->tagihan?->status_bayar, ['Menunggu Verifikasi', 'Lunas'])
                            ? 'Tidak dapat diedit — tagihan sedang diproses atau lunas.'
                            : 'Edit pencatatan'
                    ),

                ActionsAction::make('generate_tagihan')
                    ->label('Generate Tagihan')
                    ->icon('heroicon-o-document-plus')
                    ->color('success')
                    ->hidden(fn ($record) => $record->tagihan()->exists())
                    ->requiresConfirmation()
                    ->action(function ($record) {
                        $tagihan = \App\Services\GenerateTagihanService::execute($record);

                        \Filament\Notifications\Notification::make()
                            ->success()
                            ->title('Tagihan berhasil digenerate.')
                            ->body("No. Tagihan: {$tagihan->no_tagihan}")
                            ->send();
                    }),

                ActionsDeleteAction::make()
                    ->disabled(function ($record) {
                        // Kunci jika ada tagihan
                        if ($record->tagihan()->exists()) {
                            return true;
                        }

                        // Kunci jika ada data di bulan setelahnya (Middle-Chain)
                        $adaPeriodeLebihBaru = PencatatanMeter::where('meter_air_id', $record->meter_air_id)
                            ->where('id', '!=', $record->id)
                            ->where(fn ($q) => $q
                                ->where('periode_tahun', '>', $record->periode_tahun)
                                ->orWhere(fn ($q) => $q
                                    ->where('periode_tahun', $record->periode_tahun)
                                    ->where('periode_bulan', '>', $record->periode_bulan)
                                )
                            )
                            ->exists();

                        return $adaPeriodeLebihBaru;
                    })
                    
                    ->tooltip(function ($record) {
                        if ($record->tagihan()->exists()) {
                            return 'Tidak dapat dihapus — sudah memiliki tagihan.';
                        }

                        $adaPeriodeLebihBaru = PencatatanMeter::where('meter_air_id', $record->meter_air_id)
                            ->where('id', '!=', $record->id)
                            ->where(fn ($q) => $q
                                ->where('periode_tahun', '>', $record->periode_tahun)
                                ->orWhere(fn ($q) => $q
                                    ->where('periode_tahun', $record->periode_tahun)
                                    ->where('periode_bulan', '>', $record->periode_bulan)
                                )
                            )
                            ->exists();

                        if ($adaPeriodeLebihBaru) {
                            return 'Tidak dapat dihapus — ada pencatatan di bulan setelahnya. Hapus yang terbaru dulu.';
                        }

                        return 'Hapus pencatatan';
                    }),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->successNotification(null) // Matikan bawaan Filament
                        ->action(function (\Illuminate\Database\Eloquent\Collection $records) {
                            
                            // 1. Identifikasi data yang diblokir dan data yang aman
                            $blocked = $records->filter(fn ($record) => $record->tagihan()->exists());
                            $safeRecords = $records->diff($blocked);

                            // 2. Eksekusi data yang aman & kirim notif sukses
                            if ($safeRecords->isNotEmpty()) {
                                $safeRecords->each->delete(); // Hapus satu per satu agar event model tetap berjalan

                                \Filament\Notifications\Notification::make()
                                    ->success()
                                    ->title('Berhasil Dihapus')
                                    ->body("{$safeRecords->count()} data pencatatan telah dihapus.")
                                    ->send();
                            }

                            // 3. Kirim notif peringatan untuk data yang ditolak
                            if ($blocked->isNotEmpty()) {
                                \Filament\Notifications\Notification::make()
                                    ->danger()
                                    ->title('Sebagian Aksi Ditolak!')
                                    ->body("{$blocked->count()} pencatatan tidak dapat dihapus karena sudah memiliki tagihan aktif.")
                                    ->send();
                            }
                        }),
                ]),
            ]);
    }
}
