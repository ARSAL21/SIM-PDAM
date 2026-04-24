<?php

namespace App\Filament\Resources\PencatatanMeters\Tables;

use App\Models\PencatatanMeter;
use Carbon\Carbon;
use Filament\Actions\DeleteAction as ActionsDeleteAction;
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
            ->columns([
                TextColumn::make('meterAir.nomor_meter')
                    ->label('Nomor Meter')
                    ->searchable(),

                TextColumn::make('meterAir.pelanggan.user.name')
                    ->label('Nama Pelanggan')
                    ->searchable(),

                TextColumn::make('periode')
                    ->label('Periode')
                    ->getStateUsing(fn ($record) =>
                        Carbon::create($record->periode_tahun, $record->periode_bulan)
                            ->translatedFormat('F Y')
                    )
                    ->sortable(query: fn ($query, $direction) =>
                        $query->orderBy('periode_tahun', $direction)
                              ->orderBy('periode_bulan', $direction)
                    ),

                TextColumn::make('angka_awal')
                    ->label('Angka Awal')
                    ->numeric()
                    ->alignRight(),

                TextColumn::make('angka_akhir')
                    ->label('Angka Akhir')
                    ->numeric()
                    ->alignRight(),

                TextColumn::make('pemakaian_m3')
                    ->label('Pemakaian (m³)')
                    ->numeric()
                    ->alignRight()
                    ->weight(FontWeight::Medium),

                TextColumn::make('tagihan.status_bayar')
                    ->label('Status Tagihan')
                    ->badge()
                    ->default('Belum Digenerate')
                    ->color(fn (string $state): string => match ($state) {
                        'Belum Bayar'         => 'warning',
                        'Menunggu Verifikasi' => 'info',
                        'Lunas'               => 'success',
                        default               => 'gray',
                    }),

                IconColumn::make('catatan_koreksi')
                    ->label('Dikoreksi')
                    ->boolean()
                    ->trueIcon('heroicon-o-pencil-square')
                    ->falseIcon('')
                    ->trueColor('warning')
                    ->tooltip(fn ($record) => $record->catatan_koreksi ?? '')
                    ->getStateUsing(fn ($record) => filled($record->catatan_koreksi)),

                TextColumn::make('petugas.name')
                    ->label('Dicatat Oleh')
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

                Filter::make('sudah_dikoreksi')
                    ->label('Pernah Dikoreksi')
                    ->query(fn ($query) => $query->whereNotNull('catatan_koreksi')),
            ])
            ->recordActions([
                ActionsEditAction::make()
                    ->tooltip(fn ($record) =>
                        $record->tagihan?->status_bayar === 'Lunas'
                            ? 'Tidak dapat diedit — tagihan sudah lunas.'
                            : 'Edit pencatatan'
                    ),

                ActionsDeleteAction::make()
                    ->tooltip(fn ($record) =>
                        $record->tagihan()->exists()
                            ? 'Tidak dapat dihapus — sudah memiliki tagihan.'
                            : 'Hapus pencatatan'
                    ),
            ]);
    }
}
