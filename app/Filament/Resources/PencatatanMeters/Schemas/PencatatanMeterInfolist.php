<?php

namespace App\Filament\Resources\PencatatanMeters\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;
use Filament\Support\Enums\FontWeight;
use Filament\Support\Enums\TextSize;
use Carbon\Carbon;
use Filament\Schemas\Components\Fieldset as ComponentsFieldset;
use Filament\Schemas\Components\Grid as ComponentsGrid;
use Filament\Schemas\Components\Group as ComponentsGroup;
use Filament\Schemas\Components\Section as ComponentsSection;
use Laravel\Prompts\Grid as PromptsGrid;

class PencatatanMeterInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->schema([
                // 1. SECTION IDENTITAS
                ComponentsSection::make('Identitas Pelanggan & Meter')
                    ->icon('heroicon-o-user-circle')
                    ->description('Detail pelanggan dan perangkat meter yang tercatat.')
                    ->columns(2)
                    ->schema([
                        TextEntry::make('meterAir.pelanggan.user.name')
                            ->label('Nama Pelanggan')
                            ->weight(FontWeight::Bold)
                            ->color('primary')
                            ->icon('heroicon-m-user'),

                        TextEntry::make('meterAir.pelanggan.no_pelanggan')
                            ->label('No. Pelanggan')
                            ->copyable()
                            ->copyMessage('Nomor disalin!')
                            ->icon('heroicon-m-identification'),

                        TextEntry::make('meterAir.nomor_meter')
                            ->label('Nomor Meter')
                            ->badge()
                            ->color('gray'),

                        TextEntry::make('meterAir.pelanggan.golonganTarif.nama_golongan')
                            ->label('Golongan Tarif')
                            ->hint(fn ($record) => 'Tarif: IDR ' . number_format($record->meterAir->pelanggan->golonganTarif->tarif_per_kubik) . '/m³'),

                        TextEntry::make('meterAir.pelanggan.alamat')
                            ->label('Alamat Lengkap')
                            ->columnSpanFull()
                            ->icon('heroicon-m-map-pin'),
                    ]),

                // 2. SECTION PEMAKAIAN
                ComponentsSection::make('Data Pemakaian Air')
                    ->icon('heroicon-o-chart-bar-square')
                    ->description('Detail angka meter dan volume pemakaian pada periode ini.')
                    ->schema([
                        // Baris 1: Meta Data (Grid 3)
                        ComponentsGrid::make(3)->schema([
                            TextEntry::make('periode')
                                ->label('Periode Catat')
                                ->weight(FontWeight::Bold)
                                ->icon('heroicon-m-calendar-days')
                                ->state(fn ($record) =>
                                    Carbon::create(
                                        $record->periode_tahun,
                                        $record->periode_bulan
                                    )->translatedFormat('F Y')
                                ),

                            TextEntry::make('petugas.name')
                                ->label('Petugas Pencatat')
                                ->icon('heroicon-m-user-circle'),

                            TextEntry::make('created_at')
                                ->label('Waktu Input Sistem')
                                ->dateTime('d M Y, H:i')
                                ->icon('heroicon-m-clock'),
                        ]),

                        // Baris 2: Fieldset Khusus Angka (Lebih menonjol)
                        ComponentsFieldset::make('Rincian Angka Meter')
                            ->columns(3)
                            ->schema([
                                TextEntry::make('angka_awal')
                                    ->label('Angka Awal')
                                    ->suffix(' m³')
                                    ->color('gray')
                                    ->size(TextSize::Large),

                                TextEntry::make('angka_akhir')
                                    ->label('Angka Akhir')
                                    ->suffix(' m³')
                                    ->color('gray')
                                    ->size(TextSize::Large),

                                TextEntry::make('pemakaian_m3')
                                    ->label('Total Pemakaian')
                                    ->suffix(' m³')
                                    ->weight(FontWeight::ExtraBold)
                                    ->color('primary')
                                    ->size(TextSize::Large),
                            ]),
                    ]),

                // 3. SECTION TAGIHAN (Dibuat Flat, Tanpa Nested Section)
                ComponentsSection::make('Informasi Tagihan')
                    ->icon('heroicon-o-receipt-refund')
                    ->description('Status dan rincian kalkulasi biaya.')
                    ->visible(fn ($record) => $record->tagihan()->exists())
                    ->columns(2) // Bagi layar jadi 2 kolom
                    ->schema([
                        // Kolom Kiri: Status & Info Umum
                        ComponentsGroup::make([
                            TextEntry::make('tagihan.status_bayar')
                                ->label('Status Pembayaran')
                                ->badge()
                                ->size(TextSize::Large)
                                ->color(fn (string $state): string => match ($state) {
                                    'Belum Bayar'         => 'warning',
                                    'Menunggu Verifikasi' => 'info',
                                    'Lunas'               => 'success',
                                    default               => 'gray',
                                }),

                            TextEntry::make('tagihan.no_tagihan')
                                ->label('Nomor Invoice')
                                ->copyable()
                                ->copyMessage('Invoice disalin!')
                                ->weight(FontWeight::Bold),
                        ]),

                        // Kolom Kanan: Rincian Kalkulasi (Dikelompokkan rapi)
                        ComponentsGroup::make([
                            TextEntry::make('kalkulasi_pemakaian')
                                ->label('Biaya Pemakaian')
                                ->state(fn ($record) => 
                                    number_format($record->pemakaian_m3) . ' m³ x IDR ' . 
                                    number_format($record->meterAir->pelanggan->golonganTarif->tarif_per_kubik)
                                )
                                ->suffix(fn ($record) => 
                                    ' = IDR ' . number_format($record->pemakaian_m3 * $record->meterAir->pelanggan->golonganTarif->tarif_per_kubik)
                                ),

                            TextEntry::make('meterAir.pelanggan.golonganTarif.biaya_admin')
                                ->label('Biaya Beban (Rp)')
                                ->html() 
                                ->state(function ($record) {
                                    $pencatatan = $record; 
                                    $pelanggan = $pencatatan->meterAir->pelanggan;
                                    $biayaNormal = $pelanggan->golonganTarif->biaya_admin;

                                    $sudahAdaTagihan = \App\Models\Tagihan::where('pelanggan_id', $pelanggan->id)
                                        ->whereHas('pencatatanMeter', function ($q) use ($pencatatan) {
                                            $q->where('periode_bulan', $pencatatan->periode_bulan)
                                              ->where('periode_tahun', $pencatatan->periode_tahun)
                                              ->where('id', '!=', $pencatatan->id);
                                        })
                                        ->exists();

                                    $formatIDR = fn($angka) => 'IDR ' . number_format($angka, 0, ',', '.');

                                    if ($sudahAdaTagihan) {
                                        return '<span style="text-decoration: line-through; color: #9CA3AF;">' . $formatIDR($biayaNormal) . '</span><br>' .
                                               '<span style="color: #10B981; font-weight: bold;">' . $formatIDR(0) . 'Bebas Biaya (Ganti Meter)</span>';
                                    }

                                    return $formatIDR($biayaNormal);
                                }),

                            TextEntry::make('tagihan.jumlah_tagihan')
                                ->label('Total Tagihan Akhir')
                                ->money('IDR')
                                ->weight(FontWeight::ExtraBold)
                                ->size(TextSize::Large)
                                ->color('success'),
                        ]),
                    ]),

                // 4. SECTION CATATAN KOREKSI
                ComponentsSection::make('Catatan Koreksi')
                    ->icon('heroicon-o-exclamation-triangle')
                    ->iconColor('warning')
                    ->visible(fn ($record) => filled($record->catatan_koreksi))
                    ->schema([
                        TextEntry::make('catatan_koreksi')
                            ->label('Alasan Perubahan Data')
                            ->color('warning')
                            ->weight(FontWeight::Medium)
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}