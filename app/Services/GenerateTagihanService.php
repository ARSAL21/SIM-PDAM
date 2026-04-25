<?php

namespace App\Services;

use App\Models\PencatatanMeter;
use App\Models\Tagihan;
use Illuminate\Support\Str;

class GenerateTagihanService
{
    public static function calculateAmount(PencatatanMeter $pencatatan): float
    {
        $pelanggan = $pencatatan->meterAir->pelanggan;
        $golongan  = $pelanggan->golonganTarif;

        $biayaPemakaian = $pencatatan->pemakaian_m3 * $golongan->tarif_per_kubik;
        return $biayaPemakaian + $golongan->biaya_admin;
    }

    public static function execute(PencatatanMeter $pencatatan): Tagihan
    {
        // Ambil pelanggan dari meter saat ini — BUKAN dari relasi lama
        $pelanggan    = $pencatatan->meterAir->pelanggan;
        $jumlahTagihan = self::calculateAmount($pencatatan);

        // Generate no_tagihan unik (INV-YYYY-RANDOM)
        $noTagihan = 'INV-' . now()->format('Y') . '-' . strtoupper(Str::random(5));

        return Tagihan::create([
            'pencatatan_meter_id' => $pencatatan->id,
            'pelanggan_id'        => $pelanggan->id,
            'no_tagihan'          => $noTagihan,
            'jumlah_tagihan'      => $jumlahTagihan,
            'status_bayar'        => 'Belum Bayar',
        ]);
    }
}
