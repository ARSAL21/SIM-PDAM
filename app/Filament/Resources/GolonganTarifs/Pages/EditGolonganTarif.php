<?php

namespace App\Filament\Resources\GolonganTarifs\Pages;

use App\Filament\Resources\GolonganTarifs\GolonganTarifResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditGolonganTarif extends EditRecord
{
    protected static string $resource = GolonganTarifResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
