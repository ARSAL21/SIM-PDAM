<?php

namespace App\Filament\Resources\GolonganTarifs\Pages;

use App\Filament\Resources\GolonganTarifs\GolonganTarifResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewGolonganTarif extends ViewRecord
{
    protected static string $resource = GolonganTarifResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
