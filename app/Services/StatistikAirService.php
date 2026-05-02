<?php

namespace App\Services;

use App\Models\Pelanggan;
use App\Models\Tagihan;

class StatistikAirService
{
    /**
     * Mengambil ringkasan total tagihan yang belum dibayar.
     */
    public function getRingkasanTagihan(int $pelangganId): float
    {
        return Tagihan::where('pelanggan_id', $pelangganId)
            ->whereIn('status_bayar', ['Belum Bayar', 'Ditolak'])
            ->sum('jumlah_tagihan');
    }

    /**
     * Mengambil ringkasan dashboard warga dalam bentuk status yang eksplisit.
     */
    public function getDashboardSummary(Pelanggan $pelanggan): array
    {
        $pelanggan->loadMissing(['meterAirs', 'meterAktif', 'golonganTarif']);

        $meterAktif = $pelanggan->meterAktif;
        $punyaMeter = $pelanggan->meterAirs->isNotEmpty();
        $punyaBacaan = $meterAktif?->pencatatanMeters()->exists() ?? false;

        if (! $punyaMeter) {
            $meterStatus = [
                'label' => 'Belum Terpasang',
                'tone' => 'amber',
                'description' => 'Data pelanggan sudah ada, tetapi meter air belum tercatat di sistem.',
            ];
        } elseif (! $meterAktif) {
            $meterStatus = [
                'label' => 'Tidak Ada Meter Aktif',
                'tone' => 'amber',
                'description' => 'Riwayat meter ada, tetapi saat ini belum ada meter berstatus aktif.',
            ];
        } elseif ($meterAktif->tanggal_pasang?->isFuture()) {
            $meterStatus = [
                'label' => 'Menunggu Pemasangan',
                'tone' => 'sky',
                'description' => 'Meter sudah didaftarkan, tetapi tanggal pasangnya belum tiba.',
            ];
        } elseif (! $punyaBacaan) {
            $meterStatus = [
                'label' => 'Meter Baru',
                'tone' => 'sky',
                'description' => 'Meter aktif sudah tercatat, tetapi belum ada pembacaan pemakaian.',
            ];
        } else {
            $meterStatus = [
                'label' => 'Aktif',
                'tone' => 'emerald',
                'description' => 'Meter aktif dan sudah memiliki riwayat pembacaan.',
            ];
        }

        $tagihanQuery = Tagihan::where('pelanggan_id', $pelanggan->id);
        $totalTagihan = (clone $tagihanQuery)->count();
        $jumlahBelumBayar = (clone $tagihanQuery)->where('status_bayar', 'Belum Bayar')->count();
        $jumlahMenungguVerifikasi = (clone $tagihanQuery)->where('status_bayar', 'Menunggu Verifikasi')->count();
        $jumlahLunas = (clone $tagihanQuery)->where('status_bayar', 'Lunas')->count();

        $nominalBelumBayar = (clone $tagihanQuery)
            ->where('status_bayar', 'Belum Bayar')
            ->sum('jumlah_tagihan');

        $nominalMenungguVerifikasi = (clone $tagihanQuery)
            ->where('status_bayar', 'Menunggu Verifikasi')
            ->sum('jumlah_tagihan');

        if ($totalTagihan === 0) {
            $tagihanStatus = [
                'label' => 'Belum Ada Tagihan',
                'tone' => 'slate',
                'headline' => 'Tagihan pertama belum diterbitkan',
                'description' => 'Belum ada invoice yang terbit untuk akun ini.',
                'amount' => 0,
            ];
        } elseif ($jumlahBelumBayar > 0) {
            $tagihanStatus = [
                'label' => 'Belum Lunas',
                'tone' => 'rose',
                'headline' => 'Masih ada tagihan yang harus dibayar',
                'description' => $jumlahMenungguVerifikasi > 0
                    ? "{$jumlahBelumBayar} tagihan belum dibayar, {$jumlahMenungguVerifikasi} sedang diverifikasi."
                    : "{$jumlahBelumBayar} tagihan belum dibayar.",
                'amount' => $nominalBelumBayar,
            ];
        } elseif ($jumlahMenungguVerifikasi > 0) {
            $tagihanStatus = [
                'label' => 'Menunggu Verifikasi',
                'tone' => 'sky',
                'headline' => 'Pembayaran sedang diproses admin',
                'description' => "{$jumlahMenungguVerifikasi} pembayaran sedang diverifikasi.",
                'amount' => $nominalMenungguVerifikasi,
            ];
        } else {
            $tagihanStatus = [
                'label' => 'Lunas',
                'tone' => 'emerald',
                'headline' => 'Tidak ada tagihan aktif saat ini',
                'description' => $jumlahLunas > 0
                    ? 'Semua tagihan yang sudah diterbitkan telah lunas.'
                    : 'Belum ada kewajiban aktif pada periode ini.',
                'amount' => 0,
            ];
        }

        return [
            'meter' => [
                ...$meterStatus,
                'nomor_meter' => $meterAktif?->nomor_meter,
                'merek' => $meterAktif?->merek,
                'tanggal_pasang' => $meterAktif?->tanggal_pasang,
                'punya_meter_aktif' => $meterAktif !== null,
            ],
            'tagihan' => [
                ...$tagihanStatus,
                'total_tagihan' => $totalTagihan,
                'jumlah_belum_bayar' => $jumlahBelumBayar,
                'jumlah_menunggu_verifikasi' => $jumlahMenungguVerifikasi,
                'jumlah_lunas' => $jumlahLunas,
                'nominal_belum_bayar' => $nominalBelumBayar,
                'nominal_menunggu_verifikasi' => $nominalMenungguVerifikasi,
            ],
        ];
    }

    /**
     * Mengambil tren pemakaian air 6 bulan terakhir.
     */
    public function getTrenPemakaian(int $pelangganId): array
    {
        // Query data pemakaian dari pencatatan_meter melalui meter_air
        $data = \App\Models\PencatatanMeter::whereHas('meterAir', function ($query) use ($pelangganId) {
            $query->where('pelanggan_id', $pelangganId);
        })
        ->orderBy('periode_tahun', 'desc')
        ->orderBy('periode_bulan', 'desc')
        ->limit(6)
        ->get(['periode_bulan', 'periode_tahun', 'angka_awal', 'angka_akhir'])
        ->reverse();

        return $data->map(function ($item) {
            $bulanName = date("M", mktime(0, 0, 0, $item->periode_bulan, 10));
            return [
                'bulan' => $bulanName,
                'kubikasi' => max(0, $item->pemakaian_m3 ?? 0),
            ];
        })->values()->toArray();
    }
}
