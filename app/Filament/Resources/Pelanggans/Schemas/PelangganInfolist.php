<?php

namespace App\Filament\Resources\Pelanggans\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Enums\FontWeight;
use Filament\Support\Enums\TextSize;

class PelangganInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->schema([
                // 1. HEADER: PROFIL & KONTAK (Kiri - Kanan)
                Section::make('Profil & Kontak Pelanggan')
                    ->icon('heroicon-o-identification')
                    ->columns(2)
                    ->schema([
                        // Kolom Kiri: Identitas Utama
                        Group::make([
                            TextEntry::make('user.name')
                                ->label('Nama Lengkap')
                                ->size(TextSize::Large)
                                ->weight(FontWeight::ExtraBold)
                                ->color('primary')
                                ->icon('heroicon-m-user'),

                            TextEntry::make('no_pelanggan')
                                ->label('Nomor Pelanggan (ID)')
                                ->copyable()
                                ->copyMessage('ID disalin!')
                                ->weight(FontWeight::Bold)
                                ->icon('heroicon-m-qr-code'),

                            TextEntry::make('status_aktif')
                                ->label('Status Berlangganan')
                                ->badge()
                                ->formatStateUsing(fn ($state) => $state ? 'Aktif' : 'Nonaktif')
                                ->color(fn ($state) => $state ? 'success' : 'danger'),
                        ]),

                        // Kolom Kanan: Detail Kontak & Lokasi
                        Group::make([
                            TextEntry::make('user.email')
                                ->label('Email / Kontak')
                                ->icon('heroicon-m-envelope')
                                ->default('-'),

                            TextEntry::make('golonganTarif.nama_golongan')
                                ->label('Golongan Tarif')
                                ->badge()
                                ->color('info')
                                ->icon('heroicon-m-currency-dollar'),

                            TextEntry::make('alamat')
                                ->label('Alamat Lengkap')
                                ->icon('heroicon-m-map-pin')
                                ->columnSpanFull(),
                        ]),
                    ]),

                // 2. DASHBOARD FINANSIAL (Ringkasan Cepat)
                Section::make('Kesehatan Finansial (Billing Summary)')
                    ->icon('heroicon-o-chart-pie')
                    ->description('Ringkasan otomatis dari seluruh riwayat tagihan pelanggan ini.')
                    ->schema([
                        Grid::make(3)->schema([
                            // Metrik 1: Total Tagihan Belum Dibayar (Tunggakan)
                            TextEntry::make('tunggakan')
                                ->label('Total Tunggakan Aktif')
                                ->state(function ($record) {
                                    return $record->tagihans()
                                        ->whereIn('status_bayar', ['Belum Bayar', 'Menunggu Verifikasi'])
                                        ->sum('jumlah_tagihan');
                                })
                                ->money('IDR')
                                ->size(TextSize::Large)
                                ->weight(FontWeight::ExtraBold)
                                ->color(fn ($state) => $state > 0 ? 'danger' : 'success')
                                ->helperText(function ($record) {
                                    $count = $record->tagihans()
                                        ->whereIn('status_bayar', ['Belum Bayar', 'Menunggu Verifikasi'])
                                        ->count();
                                    return $count > 0 ? "Terdapat {$count} tagihan belum lunas." : 'Bebas tunggakan.';
                                }),

                            // Metrik 2: Total Pembayaran Sukses (LTV - Life Time Value)
                            TextEntry::make('total_lunas')
                                ->label('Total Pembayaran Sukses')
                                ->state(function ($record) {
                                    return $record->tagihans()
                                        ->where('status_bayar', 'Lunas')
                                        ->sum('jumlah_tagihan');
                                })
                                ->money('IDR')
                                ->weight(FontWeight::Bold)
                                ->color('primary')
                                ->helperText('Total uang masuk ke PDAM.'),

                            // Metrik 3: Tanggal Bergabung
                            TextEntry::make('created_at')
                                ->label('Menjadi Pelanggan Sejak')
                                ->date('d F Y')
                                ->icon('heroicon-m-calendar-days')
                                ->helperText(fn ($record) => $record->created_at->diffForHumans()),
                        ]),
                    ]),

                // 3. RIWAYAT ASET (Menggunakan RepeatableEntry agar rapi)
                Section::make('Riwayat Aset Meter Air')
                    ->icon('heroicon-o-wrench-screwdriver')
                    ->description('Daftar meteran yang pernah atau sedang digunakan oleh pelanggan ini.')
                    ->schema([
                        RepeatableEntry::make('meterAirs')
                            ->label('')
                            ->contained(false) // Tampilan flat modern
                            ->columns(4)
                            ->schema([
                                TextEntry::make('nomor_meter')
                                    ->label('No. Seri Meter')
                                    ->weight(FontWeight::Bold)
                                    ->copyable(),

                                TextEntry::make('tanggal_pasang')
                                    ->label('Tanggal Pasang')
                                    ->date('d M Y'),

                                TextEntry::make('status')
                                    ->label('Status Alat')
                                    ->badge()
                                    ->color(fn (string $state): string => match ($state) {
                                        'Aktif'    => 'success',
                                        'Rusak'    => 'warning',
                                        'Nonaktif' => 'danger',
                                        default    => 'gray',
                                    }),

                                TextEntry::make('keterangan')
                                    ->label('Catatan Riwayat')
                                    ->limit(30)
                                    ->tooltip(function (TextEntry $component): ?string {
                                        $state = $component->getState();
                                        return strlen($state) > 30 ? $state : null;
                                    })
                                    ->default('-')
                                    ->color('gray'),
                            ]),
                    ]),
            ]);
    }
}