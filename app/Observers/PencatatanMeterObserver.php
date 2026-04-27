<?php

namespace App\Observers;
use App\Models\PencatatanMeter;
use Exception;
use Illuminate\Validation\ValidationException;

class PencatatanMeterObserver
{
    /**
     * Handle the PencatatanMeter "created" event.
     */
    public function created(PencatatanMeter $pencatatanMeter): void
    {
        //
    }

    /**
     * Handle the PencatatanMeter "updated" event.
     */
    public function updated(PencatatanMeter $pencatatanMeter): void
    {
        //
    }

    /**
     * Handle the PencatatanMeter "deleted" event.
     */
    public function deleted(PencatatanMeter $pencatatan): void
    {
        // 1. Guard Tagihan
        if ($pencatatan->tagihan()->exists()) {
            throw ValidationException::withMessages([
                'error' => 'Sistem menolak: Pencatatan ini tidak dapat dihapus karena sudah memiliki tagihan aktif.'
            ]);
        }

        // 2. Guard Rantai Waktu (Middle-Chain Deletion)
        $adaPeriodeLebihBaru = PencatatanMeter::where('meter_air_id', $pencatatan->meter_air_id)
            ->where('id', '!=', $pencatatan->id)
            ->where(fn ($q) => $q
                ->where('periode_tahun', '>', $pencatatan->periode_tahun)
                ->orWhere(fn ($q) => $q
                    ->where('periode_tahun', $pencatatan->periode_tahun)
                    ->where('periode_bulan', '>', $pencatatan->periode_bulan)
                )
            )
            ->exists();

        if ($adaPeriodeLebihBaru) {
            throw ValidationException::withMessages([
                'error' => 'Sistem menolak: Terdapat data pencatatan di bulan setelahnya. Anda harus menghapus riwayat yang paling ujung/baru terlebih dahulu untuk menjaga integritas data.'
            ]);
        }
    }

    /**
     * Handle the PencatatanMeter "restored" event.
     */
    public function restored(PencatatanMeter $pencatatanMeter): void
    {
        //
    }

    /**
     * Handle the PencatatanMeter "force deleted" event.
     */
    public function forceDeleted(PencatatanMeter $pencatatanMeter): void
    {
        //
    }
}
