<?php

namespace App\Filament\Resources\Tagihans\Pages;

use App\Filament\Resources\Tagihans\TagihanResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\ViewRecord;

class ViewTagihan extends ViewRecord
{
    protected static string $resource = TagihanResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()
                ->hidden(fn () => $this->getRecord()->status_bayar !== 'Belum Bayar')
                ->modalHeading('Konfirmasi Hapus Tagihan')
                ->modalDescription('Apakah Anda yakin? Tagihan yang dihapus tidak dapat dikembalikan.'),
        ];
    }
}
