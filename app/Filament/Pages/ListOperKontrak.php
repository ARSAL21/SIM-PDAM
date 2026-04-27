<?php

namespace App\Filament\Pages;

use App\Filament\Pages\Schemas\OperKontrakInfolist;
use App\Models\MeterAir;

use Filament\Pages\Page;
use Filament\Actions\ViewAction;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ListOperKontrak extends Page implements HasTable
{
    use InteractsWithTable;

    protected string $view = 'filament.pages.list-oper-kontrak';

    protected static ?string $navigationLabel = 'Riwayat Oper Kontrak';

    protected static string|\UnitEnum|null $navigationGroup = 'Data Meter Air';

    protected static ?int $navigationSort = 2;

    protected static ?string $title = 'Riwayat Oper Kontrak';

    protected function getHeaderActions(): array
    {
        return [
            \Filament\Actions\Action::make('proses_oper_kontrak')
                ->label('Proses Oper Kontrak')
                ->icon('heroicon-o-arrow-path')
                ->url(OperKontrak::getUrl()),
        ];
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                MeterAir::query()->whereNotNull('melanjutkan_dari_id')
            )
            ->columns([
                TextColumn::make('oper_dari_nomor_meter')
                    ->label('Nomor Meter')
                    ->searchable(),

                TextColumn::make('oper_dari_nama_pelanggan')
                    ->label('Pelanggan Sebelumnya')
                    ->searchable(),

                TextColumn::make('pelanggan.user.name')
                    ->label('Pelanggan Baru')
                    ->searchable(),

                TextColumn::make('pelanggan.no_pelanggan')
                    ->label('No. Pelanggan Baru'),

                TextColumn::make('oper_dari_tanggal_nonaktif')
                    ->label('Tgl Berhenti Pelanggan Lama')
                    ->date('d M Y'),

                TextColumn::make('tanggal_oper_kontrak')
                    ->label('Tgl Oper Kontrak')
                    ->date('d M Y')
                    ->sortable(),

                TextColumn::make('oper_angka_serah_terima')
                    ->label('Angka Serah Terima')
                    ->numeric(),

                TextColumn::make('operDilakukanOleh.name')
                    ->label('Diproses Oleh')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('tanggal_oper_kontrak', 'desc')
            ->filters([
                \Filament\Tables\Filters\Filter::make('tahun')
                    ->form([
                        \Filament\Forms\Components\Select::make('tahun')
                            ->options(
                                MeterAir::whereNotNull('melanjutkan_dari_id')
                                    ->selectRaw('DISTINCT YEAR(tanggal_oper_kontrak) as tahun')
                                    ->orderByDesc('tahun')
                                    ->pluck('tahun', 'tahun')
                            )
                            ->placeholder('Semua Tahun'),
                    ])
                    ->query(fn (Builder $query, array $data) =>
                        $query->when($data['tahun'], fn ($q) =>
                            $q->whereYear('tanggal_oper_kontrak', $data['tahun'])
                        )
                    ),
            ])
            ->actions([
                ViewAction::make()
                    ->modalHeading('Detail Riwayat Oper Kontrak')
                    ->modalWidth('4xl')
                    ->infolist(fn (Schema $schema) => OperKontrakInfolist::configure($schema)),
            ])
            ->recordAction(ViewAction::class);
    }
}