<?php

namespace App\Filament\Resources\GolonganTarifs\Pages;

use App\Filament\Resources\GolonganTarifs\GolonganTarifResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListGolonganTarifs extends ListRecords
{
    protected static string $resource = GolonganTarifResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
