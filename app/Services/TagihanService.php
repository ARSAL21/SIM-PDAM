<?php

namespace App\Services;

use App\Models\Pelanggan;
use App\Models\Tagihan;
use Illuminate\Database\Eloquent\Collection;

class TagihanService
{
    /**
     * Mengambil daftar tagihan berdasarkan pelanggan, filter tab, dan pencarian.
     */
    public function getDaftarTagihan(Pelanggan $pelanggan, string $filter = 'Semua', string $search = ''): Collection
    {
        $query = Tagihan::with(['pencatatanMeter', 'pembayarans' => function ($q) {
            $q->latest();
        }])->where('pelanggan_id', $pelanggan->id);

        // Pencarian berdasarkan periode (misal: "Mei 2026")
        // Karena Tagihan saat ini tidak punya string periode langsung, 
        // kita bisa mencari berdasarkan status, atau id, atau no_tagihan jika ada.
        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                // Asumsi no_tagihan bisa dicari
                if (in_array('no_tagihan', \Illuminate\Support\Facades\Schema::getColumnListing('tagihan'))) {
                    $q->where('no_tagihan', 'like', "%{$search}%");
                }
                $q->orWhere('status_bayar', 'like', "%{$search}%");
                
                // Pencarian berdasarkan bulan/tahun pencatatan
                $q->orWhereHas('pencatatanMeter', function ($qm) use ($search) {
                    $qm->where('periode_tahun', 'like', "%{$search}%");
                });
            });
        }

        // Filtering berdasarkan Tab Status
        if ($filter !== 'Semua') {
            $query->where('status_bayar', $filter);
        }

        // Urutkan yang terbaru / belum dibayar ke atas
        return $query->orderByRaw("FIELD(status_bayar, 'Belum Bayar', 'Ditolak', 'Menunggu Verifikasi', 'Lunas')")
                     ->latest()
                     ->get();
    }
}
