<?php

namespace App\Filament\Resources\PencatatanMeters\Pages;

use App\Filament\Resources\PencatatanMeters\PencatatanMeterResource;
use Filament\Resources\Pages\CreateRecord;

class CreatePencatatanMeter extends CreateRecord
{
    protected static string $resource = PencatatanMeterResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Hitung ulang di server — tidak percaya kalkulasi dari client
        $data['pemakaian_m3'] = (int) $data['angka_akhir']
                              - (int) $data['angka_awal'];

        // Isi petugas yang mencatat secara otomatis
        $data['dicatat_oleh'] = auth()->id();

        // Pastikan catatan_koreksi kosong saat create
        $data['catatan_koreksi'] = null;

        return $data;
    }
}
