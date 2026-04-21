<?php

namespace App\Filament\Resources\MeterAirs\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Utilities\Get;
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
                                ->where('status_aktif', true)
                                ->when($operation === 'create', function ($q) {
                                    //Hanya tampilkan pelanggan yang TIDAK punya meteran 'Aktif'
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
                    ->minValue(0)
                    ->default(0)
                    ->required()
                    ->disabledOn('edit')
                    ->dehydrated(true)
                    ->helperText('Angka pada meteran saat pertama kali dipasang ke rumah pelanggan.'),

                Select::make('status')
                    ->label('Status')
                    ->options([
                        'Aktif' => 'Aktif',
                        'Rusak' => 'Rusak',
                        'Diganti' => 'Diganti',
                    ])
                    ->default('Aktif')
                    ->required()
                    ->rules([
                        fn (Get $get, ?Model $record): \Closure => function (string $attribute, $value, \Closure $fail) use ($get, $record) {
                            if ($value === 'Aktif') {
                                $pelangganId = $get('pelanggan_id');
                                if (!$pelangganId) return;

                                $hasActive = \App\Models\MeterAir::where('pelanggan_id', $pelangganId)
                                    ->where('status', 'Aktif')
                                    ->when($record, fn($q) => $q->where('id', '!=', $record->id))
                                    ->exists();

                                if ($hasActive) {
                                    $fail('Gagal menyimpan. Pelanggan ini sudah memiliki alat meter berstatus Aktif lainnya.');
                                }
                            }
                        },
                    ]),
            ]);
    }
}
