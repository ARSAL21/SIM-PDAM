<?php

namespace App\Filament\Resources\PencatatanMeters\Pages;

use App\Filament\Resources\PencatatanMeters\PencatatanMeterResource;
use Filament\Resources\Pages\CreateRecord;

class CreatePencatatanMeter extends CreateRecord
{
    protected static string $resource = PencatatanMeterResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Strict Chronological — server side
        $adaPeriodeLebihBaru = \App\Models\PencatatanMeter::where('meter_air_id', $data['meter_air_id'])
            ->where(fn ($q) => $q
                ->where('periode_tahun', '>', (int) $data['periode_tahun'])
                ->orWhere(fn ($q) => $q
                    ->where('periode_tahun', (int) $data['periode_tahun'])
                    ->where('periode_bulan', '>', (int) $data['periode_bulan'])
                )
            )
            ->exists();

        if ($adaPeriodeLebihBaru) {
            \Filament\Notifications\Notification::make()
                ->danger()
                ->title('Periode Tidak Valid')
                ->body(
                    'Sudah ada pencatatan di periode yang lebih baru untuk meter ini. ' .
                    'Hapus pencatatan setelahnya terlebih dahulu.'
                )
                ->send();

            $this->halt();
        }

        // Hitung ulang di server — tidak percaya kalkulasi dari client
        $data['pemakaian_m3'] = (int) $data['angka_akhir'] - (int) $data['angka_awal'];

        // Isi petugas yang mencatat secara otomatis
        $data['dicatat_oleh'] = auth()->id();

        // Pastikan catatan_koreksi kosong saat create
        $data['catatan_koreksi'] = null;

        return $data;
    }
}
