<?php

namespace App\Filament\Resources\Tagihans\Schemas;

use App\Models\PencatatanMeter;
use App\Models\Tagihan;
use App\Services\GenerateTagihanService;
use Carbon\Carbon;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Placeholder;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section as ComponentsSection;
use Filament\Schemas\Schema;
use Illuminate\Support\HtmlString;

class TagihanForm
{
    public static function configure(Schema $schema): Schema
    {
        // Ambil data pencatatan dari parameter URL untuk preview proforma
        $pencatatanId = request()->query('pencatatan_id');
        $pencatatan = $pencatatanId
            ? PencatatanMeter::with(['meterAir.pelanggan.golonganTarif', 'meterAir.pelanggan.user'])
                ->find($pencatatanId)
            : null;

        // Hitung simulasi kalkulasi
        $simulasiTotal = 0;
        $biayaPemakaian = 0;
        $biayaBeban = 0;
        $tarif = 0;
        $isWaiver = false;

        if ($pencatatan) {
            $golongan = $pencatatan->meterAir->pelanggan->golonganTarif;
            $tarif = $golongan->tarif_per_kubik;
            $biayaPemakaian = $pencatatan->pemakaian_m3 * $tarif;

            // Deteksi Smart Waiver (ganti meter di bulan yang sama)
            $isWaiver = Tagihan::where('pelanggan_id', $pencatatan->meterAir->pelanggan_id)
                ->whereHas('pencatatanMeter', fn ($q) => $q
                    ->where('periode_bulan', $pencatatan->periode_bulan)
                    ->where('periode_tahun', $pencatatan->periode_tahun)
                    ->where('id', '!=', $pencatatan->id)
                )->exists();

            $biayaBeban = $isWaiver ? 0 : $golongan->biaya_admin;
            $simulasiTotal = GenerateTagihanService::calculateAmount($pencatatan);
        }

        $formatIDR = fn ($angka) => 'Rp ' . number_format($angka, 0, ',', '.');

        return $schema
            ->components([
                // Hidden fields — data aktual yang dikirim ke server
                Hidden::make('pencatatan_meter_id')
                    ->default($pencatatanId)
                    ->required(),

                Hidden::make('pelanggan_id')
                    ->default($pencatatan?->meterAir?->pelanggan_id)
                    ->required(),

                // ── SECTION 1: Identitas Pelanggan ──
                ComponentsSection::make('Identitas Pelanggan')
                    ->icon('heroicon-o-user-circle')
                    ->visible(fn () => $pencatatan !== null)
                    ->columns(2)
                    ->schema([
                        Placeholder::make('info_pelanggan')
                            ->label('Nama Pelanggan')
                            ->content($pencatatan?->meterAir?->pelanggan?->nama_lengkap ?? '-'),

                        Placeholder::make('info_no_pelanggan')
                            ->label('No. Pelanggan')
                            ->content($pencatatan?->meterAir?->pelanggan?->no_pelanggan ?? '-'),

                        Placeholder::make('info_meter')
                            ->label('Nomor Meter')
                            ->content($pencatatan?->meterAir?->nomor_meter ?? '-'),

                        Placeholder::make('info_periode')
                            ->label('Periode Tagihan')
                            ->content($pencatatan
                                ? Carbon::create($pencatatan->periode_tahun, $pencatatan->periode_bulan)->translatedFormat('F Y')
                                : '-'),
                    ]),

                // ── SECTION 2: Proforma Invoice ──
                ComponentsSection::make('Rincian Kalkulasi (Proforma Invoice)')
                    ->icon('heroicon-o-calculator')
                    ->description('Validasi rincian biaya di bawah ini sebelum menekan tombol "Simpan".')
                    ->visible(fn () => $pencatatan !== null)
                    ->schema([
                        Grid::make(3)->schema([
                            Placeholder::make('info_pemakaian')
                                ->label('Pemakaian Air')
                                ->content(new HtmlString(
                                    '<span style="font-size: 1.2em; font-weight: bold;">' .
                                    ($pencatatan?->pemakaian_m3 ?? 0) . ' m³</span>'
                                )),

                            Placeholder::make('info_tarif')
                                ->label('Harga Perkubik')
                                ->content($formatIDR($tarif)),

                            Placeholder::make('info_biaya_pemakaian')
                                ->label('Biaya Pemakaian')
                                ->content(new HtmlString(
                                    '<span style="font-weight: bold;">' . $formatIDR($biayaPemakaian) . '</span>' .
                                    '<br><span style="font-size: 0.85em; color: #6B7280;">' .
                                    ($pencatatan?->pemakaian_m3 ?? 0) . ' m³ × ' . $formatIDR($tarif) . '</span>'
                                )),
                        ]),

                        Grid::make(2)->schema([
                            Placeholder::make('info_biaya_beban')
                                ->label('Biaya Beban')
                                ->content(new HtmlString(
                                    $isWaiver
                                        ? '<span style="text-decoration: line-through; color: #9CA3AF;">' .
                                          $formatIDR($pencatatan->meterAir->pelanggan->golonganTarif->biaya_admin) .
                                          '</span><br><span style="color: #10B981; font-weight: bold;">' .
                                          $formatIDR(0) . ' — Bebas Biaya (Ganti Meter)</span>'
                                        : '<span style="font-weight: bold;">' . $formatIDR($biayaBeban) . '</span>'
                                )),

                            Placeholder::make('info_total')
                                ->label('⭐ TOTAL TAGIHAN')
                                ->content(new HtmlString(
                                    '<span style="font-size: 1.8em; font-weight: bold; color: #059669;">' .
                                    $formatIDR($simulasiTotal) . '</span>'
                                )),
                        ]),
                    ]),

                // ── Error: Tidak ada parameter ──
                ComponentsSection::make('Data Tidak Ditemukan')
                    ->icon('heroicon-o-exclamation-triangle')
                    ->visible(fn () => $pencatatan === null)
                    ->schema([
                        Placeholder::make('error')
                            ->label('')
                            ->content(new HtmlString(
                                '<div style="padding: 1rem; background: #FEF2F2; border-radius: 8px; color: #991B1B; border: 1px solid #F87171;">' .
                                '<strong>ERROR:</strong> Parameter <code>pencatatan_id</code> tidak ditemukan. ' .
                                'Tagihan hanya dapat dibuat melalui halaman <strong>Pencatatan Meter</strong>.</div>'
                            )),
                    ]),
            ]);
    }
}
