<?php

namespace App\Observers;

use App\Models\Pembayaran;

class PembayaranObserver
{
    /**
     * Sinkronisasi status Tagihan saat record Pembayaran dibuat.
     * Kasus: Pembayaran Tunai langsung berstatus 'Disetujui'.
     */
    public function created(Pembayaran $pembayaran): void
    {
        $this->syncTagihanStatus($pembayaran);
    }

    /**
     * Sinkronisasi status Tagihan saat record Pembayaran diupdate.
     * Kasus: Verifikasi Transfer (Disetujui/Ditolak).
     */
    public function updated(Pembayaran $pembayaran): void
    {
        if ($pembayaran->wasChanged('status_pembayaran')) {
            $this->syncTagihanStatus($pembayaran);
        }
    }

    /**
     * Logic inti sinkronisasi — Decoupled dari Filament Actions.
     */
    private function syncTagihanStatus(Pembayaran $pembayaran): void
    {
        $tagihan = $pembayaran->tagihan;

        if (!$tagihan) {
            return;
        }

        match ($pembayaran->status_pembayaran) {
            'Disetujui' => $tagihan->update(['status_bayar' => 'Lunas']),
            'Ditolak'   => $tagihan->update(['status_bayar' => 'Belum Bayar']),
            default     => null,
        };
    }
}
