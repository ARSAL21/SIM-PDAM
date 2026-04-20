<?php

namespace App\Filament\Resources\MeterAirs\Pages;

use App\Filament\Resources\MeterAirs\MeterAirResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListMeterAirs extends ListRecords
{
    protected static string $resource = MeterAirResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
