<?php

namespace App\Filament\Resources\MeterAirs\Pages;

use App\Filament\Resources\MeterAirs\MeterAirResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewMeterAir extends ViewRecord
{
    protected static string $resource = MeterAirResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
