<?php

namespace App\Filament\Resources\PencatatanMeters\Pages;

use App\Filament\Resources\PencatatanMeters\PencatatanMeterResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListPencatatanMeters extends ListRecords
{
    protected static string $resource = PencatatanMeterResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
