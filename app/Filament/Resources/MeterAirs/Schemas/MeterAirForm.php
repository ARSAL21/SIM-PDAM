<?php

namespace App\Filament\Resources\MeterAirs\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class MeterAirForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('pelanggan_id')
                    ->label('Pelanggan')
                    ->relationship(
                        name: 'pelanggan',
                        titleAttribute: 'no_pelanggan',
                        modifyQueryUsing: fn (Builder $query, string $operation) => 
                            $query->with('user')
                                ->when($operation === 'create', function ($q) {
                                    // SAKTI: Hanya tampilkan pelanggan yang TIDAK punya meteran 'Aktif'
                                    $q->whereDoesntHave('meterAirs', function ($subQuery) {
                                        $subQuery->where('status', 'Aktif');
                                    });
                                })
                    )
                    ->getOptionLabelFromRecordUsing(fn (Model $record) => "{$record->user->name} ({$record->no_pelanggan})")
                    ->searchable(['no_pelanggan'])
                    ->preload()
                    ->required(),

                TextInput::make('nomor_meter')
                    ->label('Nomor Meter')
                    ->unique(ignoreRecord: true)
                    ->required()
                    ->placeholder('Contoh: MT-2026-XYZ'),

                TextInput::make('merek')
                    ->label('Merek')
                    ->nullable(),

                DatePicker::make('tanggal_pasang')
                    ->label('Tanggal Pasang')
                    ->default(now())
                    ->required(),

                TextInput::make('angka_awal')
                    ->label('Angka Awal')
                    ->numeric()
                    ->default(0)
                    ->required()
                    ->helperText('Angka pada meteran saat pertama kali dipasang ke rumah pelanggan.'),

                Select::make('status')
                    ->label('Status')
                    ->options([
                        'Aktif' => 'Aktif',
                        'Rusak' => 'Rusak',
                        'Diganti' => 'Diganti',
                    ])
                    ->default('Aktif')
                    ->required(),
            ]);
    }
}
