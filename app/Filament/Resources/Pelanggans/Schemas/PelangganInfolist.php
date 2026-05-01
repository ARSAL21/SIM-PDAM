<?php

namespace App\Filament\Resources\Pelanggans\Schemas;

use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Schemas\Components\Grid;
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
                // BARIS 1: Identitas & Keuangan (Melebar ke samping, 2 kolom)
                Grid::make(2)
                    ->schema([
                        // ═══════════════════════════════════════
                        // SECTION 1: IDENTITAS PELANGGAN
                        // ═══════════════════════════════════════
                        Section::make('Identitas Pelanggan')
                            ->icon('heroicon-o-identification')
                            ->schema([
                                Grid::make(2)->schema([
                                    TextEntry::make('nama_lengkap')
                                        ->label('Nama Lengkap')
                                        ->size(TextSize::Large)
                                        ->weight(FontWeight::ExtraBold)
                                        ->color('primary')
                                        ->icon('heroicon-m-user'),

                                    TextEntry::make('status_aktif')
                                        ->label('Status Berlangganan')
                                        ->badge()
                                        ->formatStateUsing(fn ($state) => $state ? 'Aktif' : 'Nonaktif')
                                        ->color(fn ($state) => $state ? 'success' : 'danger'),
                                ]),

                                Grid::make(2)->schema([
                                    TextEntry::make('no_pelanggan')
                                        ->label('ID Pelanggan')
                                        ->weight(FontWeight::Bold)
                                        ->badge()
                                        ->color('gray')
                                        ->icon('heroicon-m-qr-code')
                                        ->copyable(),

                                    TextEntry::make('golonganTarif.nama_golongan')
                                        ->label('Kategori Tarif')
                                        ->badge()
                                        ->color('info')
                                        ->icon('heroicon-m-currency-dollar'),
                                ]),

                                Grid::make(2)->schema([
                                    TextEntry::make('no_hp')
                                        ->label('No. Telepon / WhatsApp')
                                        ->default('-')
                                        ->icon('heroicon-m-phone'),

                                    TextEntry::make('created_at')
                                        ->label('Terdaftar Sejak')
                                        ->date('d F Y')
                                        ->icon('heroicon-m-calendar')
                                        ->helperText(fn ($record) => $record->created_at->diffForHumans()),
                                ]),

                                TextEntry::make('alamat')
                                    ->label('Alamat Pemasangan')
                                    ->weight(FontWeight::Medium)
                                    ->icon('heroicon-m-map-pin')
                                    ->columnSpanFull(),
                            ]),

                        // ═══════════════════════════════════════
                        // SECTION 2: RINGKASAN KEUANGAN
                        // ═══════════════════════════════════════
                        Section::make('Ringkasan Keuangan')
                            ->icon('heroicon-o-banknotes')
                            ->schema([
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
                                        return $count > 0
                                            ? "Terdapat {$count} tagihan yang belum dilunasi."
                                            : 'Semua tagihan sudah lunas. ✓';
                                    }),

                                Grid::make(2)->schema([
                                    TextEntry::make('total_lunas')
                                        ->label('Total Pembayaran Sukses')
                                        ->state(fn ($record) =>
                                            $record->tagihans()
                                                ->where('status_bayar', 'Lunas')
                                                ->sum('jumlah_tagihan')
                                        )
                                        ->money('IDR')
                                        ->weight(FontWeight::Bold)
                                        ->color('primary')
                                        ->helperText('Total uang masuk ke PDAM.'),

                                    TextEntry::make('frekuensi_tagihan')
                                        ->label('Total Invoice')
                                        ->state(fn ($record) =>
                                            $record->tagihans()->count() . ' Tagihan'
                                        )
                                        ->icon('heroicon-m-document-text'),
                                ]),
                            ]),
                    ])->columnSpanFull(),

                // BARIS 2: Meter & Akun (Melebar ke samping, 2 kolom)
                Grid::make(2)
                    ->schema([
                        // ═══════════════════════════════════════
                        // SECTION 3: PERANGKAT METER AKTIF
                        // ═══════════════════════════════════════
                        Section::make('Perangkat Meter Aktif')
                            ->icon('heroicon-o-signal')
                            ->description('Alat pengukur yang saat ini terpasang di lokasi pelanggan.')
                            ->schema([
                                Grid::make(2)->schema([
                                    TextEntry::make('meterAktif.nomor_meter')
                                        ->label('Nomor Seri Meter')
                                        ->placeholder('⚠️ Belum dipasang')
                                        ->weight(FontWeight::Bold)
                                        ->color(fn ($record) => $record->meterAktif ? 'success' : 'danger')
                                        ->icon('heroicon-m-identification'),

                                    TextEntry::make('meterAktif.merek')
                                        ->label('Merek')
                                        ->placeholder('-')
                                        ->color('gray'),
                                ]),

                                Grid::make(2)->schema([
                                    TextEntry::make('meterAktif.tanggal_pasang')
                                        ->label('Tanggal Pemasangan')
                                        ->placeholder('-')
                                        ->date('d F Y')
                                        ->icon('heroicon-m-calendar-days'),

                                    TextEntry::make('meterAktif.angka_awal')
                                        ->label('Angka Awal Meter')
                                        ->placeholder('-')
                                        ->suffix(' m³')
                                        ->icon('heroicon-m-chart-bar'),
                                ]),

                                TextEntry::make('meterAktif.status')
                                    ->label('Status Alat')
                                    ->placeholder('Tidak ada meter aktif')
                                    ->badge()
                                    ->color(fn ($state) => match ($state) {
                                        'Aktif'    => 'success',
                                        'Rusak'    => 'warning',
                                        'Nonaktif' => 'danger',
                                        default    => 'gray',
                                    }),
                            ]),

                        // ═══════════════════════════════════════
                        // SECTION 4: TAUTAN AKUN PORTAL WARGA
                        // ═══════════════════════════════════════
                        Section::make('Tautan Akun Portal Warga')
                            ->icon('heroicon-o-computer-desktop')
                            ->description('Akun digital yang terhubung ke data pelanggan ini.')
                            ->schema([
                                IconEntry::make('status_tautan')
                                    ->label('Status Tautan Akun')
                                    ->getStateUsing(fn ($record) => $record->user_id !== null)
                                    ->boolean()
                                    ->trueIcon('heroicon-o-check-badge')
                                    ->falseIcon('heroicon-o-exclamation-circle')
                                    ->trueColor('success')
                                    ->falseColor('danger'),

                                Grid::make(2)
                                    ->visible(fn ($record) => $record->user_id !== null)
                                    ->schema([
                                        TextEntry::make('user.name')
                                            ->label('Nama Akun')
                                            ->weight(FontWeight::Bold)
                                            ->icon('heroicon-m-user'),

                                        TextEntry::make('user.email')
                                            ->label('Email Terdaftar')
                                            ->icon('heroicon-m-envelope')
                                            ->copyable(),

                                        TextEntry::make('user.created_at')
                                            ->label('Akun Dibuat Pada')
                                            ->dateTime('d F Y, H:i')
                                            ->icon('heroicon-m-calendar-days'),

                                        TextEntry::make('created_at_claim')
                                            ->label('Ditautkan ke Pelanggan')
                                            ->state(fn ($record) => $record->user?->created_at?->diffForHumans())
                                            ->icon('heroicon-m-link'),
                                    ]),

                                TextEntry::make('belum_tautan')
                                    ->label('')
                                    ->hidden(fn ($record) => $record->user_id !== null)
                                    ->state('Pelanggan ini belum mendaftarkan akun di portal web. Minta pelanggan untuk mendaftar menggunakan ID Pelanggan di atas.')
                                    ->color('danger')
                                    ->icon('heroicon-m-exclamation-triangle'),
                            ]),
                    ])->columnSpanFull(),

                // ═══════════════════════════════════════
                // SECTION 5: RIWAYAT METER AIR (Full Width)
                // ═══════════════════════════════════════
                Section::make('Riwayat Meter Air')
                    ->icon('heroicon-o-rectangle-stack')
                    ->description('Semua alat meter yang pernah terpasang pada pelanggan ini.')
                    ->columnSpanFull()
                    ->collapsed()
                    ->schema([
                        RepeatableEntry::make('meterAirs')
                            ->label('')
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
                                    ->label('Catatan')
                                    ->default('-')
                                    ->limit(30)
                                    ->tooltip(fn ($record) => $record->keterangan)
                                    ->color('gray'),
                            ]),
                    ]),
            ]);
    }
}