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

        // 1. Hitung biaya air murni
        $biayaPemakaian = $pencatatan->pemakaian_m3 * $golongan->tarif_per_kubik;

        // 2. GUARD THE DOUBLE BILL: Cek apakah di periode bulan & tahun yang sama, 
        // pelanggan ini sudah punya tagihan lain dari meter yang berbeda (Kasus Ganti Meter).
        $sudahAdaTagihanDiBulanIni = Tagihan::where('pelanggan_id', $pelanggan->id)
            ->whereHas('pencatatanMeter', function ($q) use ($pencatatan) {
                $q->where('periode_bulan', $pencatatan->periode_bulan)
                  ->where('periode_tahun', $pencatatan->periode_tahun)
                  ->where('id', '!=', $pencatatan->id); // Abaikan ID pencatatan ini sendiri (untuk kasus Edit/Auto-Sync)
            })
            ->exists();

        // 3. Bebaskan Biaya Admin jika ini adalah tagihan kedua di bulan yang sama
        $biayaAdmin = $sudahAdaTagihanDiBulanIni ? 0 : $golongan->biaya_admin;

        return $biayaPemakaian + $biayaAdmin;
    }

    public static function execute(PencatatanMeter $pencatatan): Tagihan
    {
        // Ambil pelanggan dari meter saat ini — BUKAN dari relasi lama
        $pelanggan     = $pencatatan->meterAir->pelanggan;
        $jumlahTagihan = self::calculateAmount($pencatatan);

        // Generate no_tagihan unik (INV-YYYY-RANDOM)
        $noTagihan = 'INV-' . now()->format('Y') . '-' . strtoupper(Str::random(5));

        $tagihan = Tagihan::create([
            'pencatatan_meter_id' => $pencatatan->id,
            'pelanggan_id'        => $pelanggan->id,
            'no_tagihan'          => $noTagihan,
            'jumlah_tagihan'      => $jumlahTagihan,
            'status_bayar'        => 'Belum Bayar',
        ]);

        // Jika meteran yang baru saja dibuatkan tagihan berstatus "Rusak", matikan!
        $meterAir = $pencatatan->meterAir;
        
        if ($meterAir->status === 'Rusak') {
            $catatanOtomatis = "Dinonaktifkan otomatis oleh sistem setelah Final Billing pada " . now()->translatedFormat('d F Y') . ".";
            
            // Gabungkan dengan catatan lama jika ada, atau buat baru
            // (Sesuaikan nama kolom 'catatan' atau 'keterangan' dengan yang ada di tabel meter_airs kamu)
            $catatanLama = $meterAir->keterangan ?? ''; 
            $catatanFinal = $catatanLama ? $catatanLama . " | " . $catatanOtomatis : $catatanOtomatis;

            $meterAir->update([
                'status' => 'Nonaktif',
                'keterangan' => $catatanFinal // Pastikan kolom ini ada di migration meter_airs
            ]);
        }

        return $tagihan;
    }
}
