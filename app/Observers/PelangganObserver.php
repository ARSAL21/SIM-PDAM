<?php

namespace App\Observers;

use App\Models\Pelanggan;

class PelangganObserver
{
    /**
     * Handle the Pelanggan "updated" event.
     */
    public function updated(Pelanggan $pelanggan): void
    {
        if ($pelanggan->wasChanged('status_aktif') && !$pelanggan->status_aktif) {
            $pelanggan->meterAirs()
                ->where('status', 'Aktif')
                ->update([
                    'status' => 'Nonaktif',
                    'tanggal_nonaktif' => now()->toDateString(),
            ]);
        }
    }

    /**
     * Handle the Pelanggan "created" event.
     */
    public function created(Pelanggan $pelanggan): void
    {
        //
    }


    /**
     * Handle the Pelanggan "deleted" event.
     */
    public function deleted(Pelanggan $pelanggan): void
    {
        //
    }

    /**
     * Handle the Pelanggan "restored" event.
     */
    public function restored(Pelanggan $pelanggan): void
    {
        //
    }

    /**
     * Handle the Pelanggan "force deleted" event.
     */
    public function forceDeleted(Pelanggan $pelanggan): void
    {
        //
    }
}
