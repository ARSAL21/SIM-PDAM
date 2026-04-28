<?php

namespace App\Filament\Resources\Pelanggans\Pages;

use App\Filament\Resources\Pelanggans\PelangganResource;
use Filament\Resources\Pages\CreateRecord;

class CreatePelanggan extends CreateRecord
{
    protected static string $resource = PelangganResource::class;

    /**
     * UI Masking: Tarif Tunggal Desa Lanto.
     * Karena hanya ada satu tarif, kita inject ID-nya secara paksa di level logic,
     * bukan di level form UI. Ini menjamin data selalu terisi.
     */
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['golongan_tarif_id'] = 1;

        return $data;
    }
}
