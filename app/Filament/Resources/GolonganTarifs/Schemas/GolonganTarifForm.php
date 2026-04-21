<?php

namespace App\Filament\Resources\GolonganTarifs\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class GolonganTarifForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('nama_golongan')
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->maxLength(255)
                    ->label('Nama Golongan'),
                TextInput::make('tarif_per_kubik')
                    ->required()
                    ->minValue(0)
                    ->numeric()
                    ->default(7000)
                    ->prefix('Rp')
                    ->label('Tarif per Kubik'),
                TextInput::make('biaya_admin')
                    ->required()
                    ->minValue(0)
                    ->numeric()
                    ->default(0)
                    ->prefix('Rp')
                    ->label('Biaya Admin'),
            ]);
    }
}
