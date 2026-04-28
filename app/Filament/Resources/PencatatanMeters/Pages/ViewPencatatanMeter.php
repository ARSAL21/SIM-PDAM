<?php

namespace App\Filament\Resources\PencatatanMeters\Pages;

use App\Filament\Resources\PencatatanMeters\PencatatanMeterResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewPencatatanMeter extends ViewRecord
{
    protected static string $resource = PencatatanMeterResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),

            \Filament\Actions\Action::make('buat_tagihan')
                ->label('Buat Tagihan')
                ->icon('heroicon-o-document-plus')
                ->color('success')
                ->hidden(fn () => $this->getRecord()->tagihan()->exists())
                ->url(fn () => \App\Filament\Resources\Tagihans\TagihanResource::getUrl('create', [
                    'pencatatan_id' => $this->getRecord()->id,
                ])),
        ];
    }
}
