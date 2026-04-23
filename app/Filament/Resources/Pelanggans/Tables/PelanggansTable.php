<?php

namespace App\Filament\Resources\Pelanggans\Tables;

use App\Filament\Resources\MeterAirs\MeterAirResource;
use App\Models\GolonganTarif;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

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

                // ── Kolom Status Meter (Task 3b) ──
                TextColumn::make('status_meter')
                    ->label('Status Meter')
                    ->getStateUsing(function ($record) {
                        if ($record->meterAirs()->doesntExist()) {
                            return 'Belum Ada Meter';
                        }
                        if (!$record->meterAktif) {
                            return 'Tidak Ada Meter Aktif';
                        }
                        return 'Aktif';
                    })
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'Aktif'                 => 'success',
                        'Tidak Ada Meter Aktif' => 'warning',
                        'Belum Ada Meter'       => 'danger',
                        default                 => 'gray',
                    }),

                // ── Kolom Nomor Meter Aktif (Task 4a) ──
                TextColumn::make('meterAktif.nomor_meter')
                    ->label('No. Meter Aktif')
                    ->default('—')
                    ->placeholder('Belum terpasang')
                    ->toggleable(),

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

                // ── Filter Status Aktif ──
                TernaryFilter::make('status_aktif')
                    ->label('Status Langganan')
                    ->placeholder('Semua Pelanggan')
                    ->trueLabel('Aktif Saja')
                    ->falseLabel('Non-Aktif Saja'),

                // ── Filter Belum Punya Meter (Task 3a) ──
                Filter::make('belum_punya_meter')
                    ->label('Belum Punya Meter')
                    ->query(fn (Builder $query) =>
                        $query->whereDoesntHave('meterAirs')
                    )
                    ->toggle(),

                // ── Filter Tidak Ada Meter Aktif (Task 3a) ──
                Filter::make('tidak_ada_meter_aktif')
                    ->label('Tidak Ada Meter Aktif')
                    ->query(fn (Builder $query) =>
                        $query->whereDoesntHave('meterAirs', fn ($q) =>
                            $q->where('status', 'Aktif')
                        )
                    )
                    ->toggle(),
            ])
            ->defaultSort('created_at', 'desc')
            ->recordActions([
                EditAction::make(),

                // ── Action Navigasi "Lihat Meter" (Task 4b) ──
                Action::make('lihat_meter')
                    ->label('Lihat Meter')
                    ->icon('heroicon-o-signal')
                    ->url(fn ($record) =>
                        MeterAirResource::getUrl('index', [
                            'tableFilters[pelanggan_id][value]' => $record->id,
                        ])
                    )
                    ->color('info')
                    ->openUrlInNewTab(false),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
