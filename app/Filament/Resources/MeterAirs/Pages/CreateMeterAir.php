<?php

namespace App\Filament\Resources\MeterAirs\Pages;

use App\Filament\Resources\MeterAirs\MeterAirResource;
use Filament\Resources\Pages\CreateRecord;

class CreateMeterAir extends CreateRecord
{
    protected static string $resource = MeterAirResource::class;
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        if (filled($data['melanjutkan_dari_id'] ?? null)) {
            $meterLama = \App\Models\MeterAir::with([
                'pelanggan.user',
                'pencatatanTerakhir',
            ])->find($data['melanjutkan_dari_id']);

            if ($meterLama) {
                $data['oper_dari_nomor_meter']    = $meterLama->nomor_meter;
                $data['oper_dari_nama_pelanggan'] = $meterLama->pelanggan?->nama_lengkap;
                $data['oper_angka_serah_terima']  = $meterLama->pencatatanTerakhir?->angka_akhir
                                                    ?? $meterLama->angka_awal;
                $data['oper_dari_tanggal_nonaktif'] = $meterLama->tanggal_nonaktif;
            }
        }

        return $data;
    }
}
