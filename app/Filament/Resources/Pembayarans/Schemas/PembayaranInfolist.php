<?php

namespace App\Filament\Resources\Pembayarans\Schemas;

use Carbon\Carbon;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Group as ComponentsGroup;
use Filament\Schemas\Components\Section as ComponentsSection;
use Filament\Schemas\Schema;
use Filament\Support\Enums\FontWeight;
use Filament\Support\Enums\TextSize;

class PembayaranInfolist
{
    public static function configure(Schema $schema): Schema
    {
        $formatIDR = fn ($n) => 'Rp ' . number_format($n, 0, ',', '.');

        return $schema
            ->schema([
                // 1. STATUS & RINGKASAN PEMBAYARAN
                ComponentsSection::make('Status Pembayaran')
                    ->icon('heroicon-o-receipt-percent')
                    ->columns(2)
                    ->schema([
                        ComponentsGroup::make([
                            TextEntry::make('status_pembayaran')
                                ->label('Status')
                                ->badge()
                                ->size(TextSize::Large)
                                ->color(fn (string $state): string => match ($state) {
                                    'Pending'   => 'warning',
                                    'Disetujui' => 'success',
                                    'Ditolak'   => 'danger',
                                    default     => 'gray',
                                }),

                            TextEntry::make('metode_bayar')
                                ->label('Metode Pembayaran')
                                ->badge()
                                ->color(fn (string $state): string => match ($state) {
                                    'tunai'    => 'success',
                                    'transfer' => 'info',
                                    default    => 'gray',
                                })
                                ->formatStateUsing(fn (string $state): string => match ($state) {
                                    'tunai'    => '💵 Tunai (Loket)',
                                    'transfer' => '🏦 Transfer Bank',
                                    default    => $state,
                                }),
                        ]),

                        ComponentsGroup::make([
                            TextEntry::make('jumlah_bayar')
                                ->label('Nominal Dibayar')
                                ->money('IDR')
                                ->size(TextSize::Large)
                                ->weight(FontWeight::ExtraBold)
                                ->color('success'),

                            TextEntry::make('tanggal_bayar')
                                ->label('Tanggal Bayar')
                                ->dateTime('d F Y, H:i')
                                ->icon('heroicon-m-calendar'),
                        ]),
                    ]),

                // 2. DATA PELANGGAN & TAGIHAN
                ComponentsSection::make('Data Pelanggan & Tagihan')
                    ->icon('heroicon-o-user-circle')
                    ->columns(2)
                    ->schema([
                        TextEntry::make('tagihan.pelanggan.nama_lengkap')
                            ->label('Nama Pelanggan')
                            ->weight(FontWeight::Bold)
                            ->icon('heroicon-m-user'),

                        TextEntry::make('tagihan.pelanggan.no_pelanggan')
                            ->label('No. Pelanggan')
                            ->copyable()
                            ->copyMessage('Disalin!'),

                        TextEntry::make('tagihan.no_tagihan')
                            ->label('No. Invoice Tagihan')
                            ->weight(FontWeight::Bold)
                            ->copyable()
                            ->copyMessage('No. tagihan disalin!')
                            ->url(fn ($record) => $record->tagihan
                                ? \App\Filament\Resources\Tagihans\TagihanResource::getUrl('view', ['record' => $record->tagihan])
                                : null
                            )
                            ->color('primary'),

                        TextEntry::make('tagihan.jumlah_tagihan')
                            ->label('Nominal Tagihan Asli')
                            ->money('IDR'),

                        TextEntry::make('tagihan.pelanggan.alamat')
                            ->label('Alamat')
                            ->icon('heroicon-m-map-pin')
                            ->columnSpanFull(),
                    ]),

                // 3. DETAIL PEMAKAIAN (dari Pencatatan Meter)
                ComponentsSection::make('Detail Pemakaian Air')
                    ->icon('heroicon-o-chart-bar-square')
                    ->columns(3)
                    ->schema([
                        TextEntry::make('meter_info')
                            ->label('Nomor Meter')
                            ->state(fn ($record) => $record->tagihan?->pencatatanMeter?->meterAir?->nomor_meter ?? '-')
                            ->badge()
                            ->color('gray'),

                        TextEntry::make('periode_tagihan')
                            ->label('Periode')
                            ->state(fn ($record) => $record->tagihan?->pencatatanMeter
                                ? Carbon::create(
                                    $record->tagihan->pencatatanMeter->periode_tahun,
                                    $record->tagihan->pencatatanMeter->periode_bulan
                                )->translatedFormat('F Y')
                                : '-'
                            )
                            ->icon('heroicon-m-calendar-days')
                            ->weight(FontWeight::Bold),

                        TextEntry::make('pemakaian_air')
                            ->label('Pemakaian')
                            ->state(fn ($record) =>
                                ($record->tagihan?->pencatatanMeter?->pemakaian_m3 ?? 0) . ' m³'
                            )
                            ->size(TextSize::Large)
                            ->weight(FontWeight::ExtraBold)
                            ->color('primary'),

                        TextEntry::make('angka_meter')
                            ->label('Angka Meter')
                            ->state(fn ($record) => $record->tagihan?->pencatatanMeter
                                ? $record->tagihan->pencatatanMeter->angka_awal . ' → ' . $record->tagihan->pencatatanMeter->angka_akhir . ' m³'
                                : '-'
                            )
                            ->columnSpanFull(),
                    ]),

                // 4. RINCIAN KALKULASI TAGIHAN
                ComponentsSection::make('Rincian Kalkulasi Tagihan')
                    ->icon('heroicon-o-calculator')
                    ->columns(3)
                    ->schema([
                        TextEntry::make('biaya_pemakaian')
                            ->label('Biaya Pemakaian')
                            ->state(function ($record) use ($formatIDR) {
                                $pencatatan = $record->tagihan?->pencatatanMeter;
                                $tarif = $record->tagihan?->pelanggan?->golonganTarif?->tarif_per_kubik ?? 0;
                                $pemakaian = $pencatatan?->pemakaian_m3 ?? 0;
                                return $formatIDR($pemakaian * $tarif);
                            })
                            ->helperText(fn ($record) =>
                                ($record->tagihan?->pencatatanMeter?->pemakaian_m3 ?? 0) . ' m³ × ' .
                                'Rp ' . number_format($record->tagihan?->pelanggan?->golonganTarif?->tarif_per_kubik ?? 0, 0, ',', '.')
                            ),

                        TextEntry::make('biaya_beban_display')
                            ->label('Biaya Beban')
                            ->state(function ($record) use ($formatIDR) {
                                $tarif = $record->tagihan?->pelanggan?->golonganTarif?->tarif_per_kubik ?? 0;
                                $pemakaian = $record->tagihan?->pencatatanMeter?->pemakaian_m3 ?? 0;
                                $biayaPemakaian = $pemakaian * $tarif;
                                $biayaBeban = ($record->tagihan?->jumlah_tagihan ?? 0) - $biayaPemakaian;
                                return $formatIDR(max(0, $biayaBeban));
                            }),

                        TextEntry::make('tagihan.jumlah_tagihan')
                            ->label('Total Tagihan')
                            ->money('IDR')
                            ->weight(FontWeight::ExtraBold)
                            ->color('success'),
                    ]),

                // 5. JEJAK AUDIT (Verifikasi)
                ComponentsSection::make('Jejak Audit Verifikasi')
                    ->icon('heroicon-o-shield-check')
                    ->columns(2)
                    ->schema([
                        TextEntry::make('verifikator.name')
                            ->label('Diverifikasi Oleh')
                            ->default('Belum diverifikasi')
                            ->icon('heroicon-m-user-circle')
                            ->weight(FontWeight::Bold),

                        TextEntry::make('diverifikasi_pada')
                            ->label('Tanggal Verifikasi')
                            ->dateTime('d F Y, H:i:s')
                            ->icon('heroicon-m-clock')
                            ->default('Belum diverifikasi'),

                        TextEntry::make('catatan_admin')
                            ->label('Catatan / Alasan Admin')
                            ->placeholder('Tidak ada catatan.')
                            ->columnSpanFull()
                            ->color(fn ($record) => $record->status_pembayaran === 'Ditolak' ? 'danger' : 'gray')
                            ->weight(fn ($record) => $record->status_pembayaran === 'Ditolak' ? FontWeight::Bold : FontWeight::Normal),

                        TextEntry::make('created_at')
                            ->label('Record Dibuat')
                            ->dateTime('d F Y, H:i:s')
                            ->icon('heroicon-m-document-plus')
                            ->color('gray')
                            ->helperText('Waktu sistem saat record pembayaran ini pertama kali tersimpan.'),

                        TextEntry::make('updated_at')
                            ->label('Terakhir Diperbarui')
                            ->dateTime('d F Y, H:i:s')
                            ->icon('heroicon-m-arrow-path')
                            ->color('gray'),
                    ]),
            ]);
    }
}
