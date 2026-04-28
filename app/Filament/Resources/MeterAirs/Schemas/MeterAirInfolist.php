<?php

namespace App\Filament\Resources\MeterAirs\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;
use Filament\Support\Enums\FontWeight;
use Filament\Support\Enums\TextSize;
use Carbon\Carbon;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Group as ComponentsGroup;
use Filament\Schemas\Components\Section as ComponentsSection;

class MeterAirInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->schema([
                ComponentsSection::make('Informasi Pelanggan & Lokasi')
                    ->icon('heroicon-o-map-pin')
                    ->description('Identitas kepemilikan dan lokasi pemasangan meteran.')
                    ->columns(2)
                    ->schema([
                        TextEntry::make('pelanggan.user.name')
                            ->label('Nama Pemilik')
                            ->weight(FontWeight::Bold)
                            ->color('primary')
                            ->icon('heroicon-m-user'),

                        TextEntry::make('pelanggan.no_pelanggan')
                            ->label('No. Pelanggan')
                            ->copyable()
                            ->copyMessage('Disalin!'),

                        TextEntry::make('pelanggan.golonganTarif.nama_golongan')
                            ->label('Kategori Tarif')
                            ->badge()
                            ->color('info')
                            ->icon('heroicon-m-currency-dollar'),

                        TextEntry::make('pelanggan.alamat')
                            ->label('Alamat Pemasangan')
                            ->icon('heroicon-m-building-office-2')
                            ->columnSpanFull(),
                    ]),

                ComponentsSection::make('Spesifikasi & Status Alat')
                    ->icon('heroicon-o-wrench-screwdriver')
                    ->columns(3)
                    ->schema([
                        ComponentsGroup::make([
                            TextEntry::make('nomor_meter')
                                ->label('Nomor Seri Meter')
                                ->size(TextSize::Large)
                                ->weight(FontWeight::ExtraBold)
                                ->copyable(),

                            TextEntry::make('merek')
                                ->label('Merek Pabrikan')
                                ->default('Tidak diketahui')
                                ->color('gray'),
                        ]),

                        ComponentsGroup::make([
                            TextEntry::make('angka_awal')
                                ->label('Angka Awal Pemasangan')
                                ->suffix(' m³')
                                ->weight(FontWeight::Bold),

                            TextEntry::make('status')
                                ->label('Status Alat')
                                ->badge()
                                ->size(TextSize::Large)
                                ->color(fn (string $state): string => match ($state) {
                                    'Aktif'    => 'success',
                                    'Rusak'    => 'warning',
                                    'Nonaktif' => 'danger',
                                    default    => 'gray',
                                }),
                        ]),

                        // Catatan Status: Prioritas data dari database, auto-konteks hanya sebagai fallback
                        TextEntry::make('keterangan')
                            ->label('Catatan Status / Riwayat')
                            ->placeholder(fn ($record) => match ($record->status) {
                                'Rusak'    => '⚠️ Belum ada alasan — admin belum mengisi keterangan kerusakan.',
                                'Nonaktif' => 'Belum ada alasan — admin belum mengisi keterangan penonaktifan.',
                                default    => 'Tidak ada catatan.',
                            })
                            ->color(fn ($record) => match (true) {
                                $record->status === 'Aktif' => 'gray',
                                filled($record->keterangan) => 'warning',
                                default => 'danger',
                            })
                            ->weight(fn ($record) => $record->status === 'Aktif' ? FontWeight::Normal : FontWeight::Bold)
                            ->columnSpan(1),
                    ]),

                // JEJAK PERGANTIAN ALAT (AUDIT TRAIL)
                // Menggunakan Smart Query: mendeteksi pengganti/pendahulu dari pelanggan yang sama
                ComponentsSection::make('Jejak Pergantian Alat (Audit Trail)')
                    ->icon('heroicon-o-arrow-path-rounded-square')
                    ->description('Pelacakan otomatis hubungan antar alat meter milik pelanggan yang sama.')
                    ->visible(fn ($record) => 
                        // Tampilkan jika meter ini Rusak/Nonaktif/Diganti, 
                        // ATAU jika ia memiliki pendahulu (berarti ia adalah pengganti)
                        in_array($record->status, ['Rusak', 'Nonaktif', 'Diganti'])
                        || \App\Models\MeterAir::where('pelanggan_id', $record->pelanggan_id)
                            ->where('id', '<', $record->id)
                            ->whereIn('status', ['Rusak', 'Nonaktif', 'Diganti'])
                            ->exists()
                    )
                    ->schema([
                        Grid::make(2)->schema([
                            // Kolom Kiri: Siapa yang menggantikan meter ini?
                            TextEntry::make('meter_pengganti')
                                ->label('Digantikan Oleh (Meter Baru)')
                                ->html()
                                ->state(function ($record) {
                                    // Prioritas 1: Cek relasi FK OperKontrak
                                    $penerusFK = $record->dilanjutkanOleh;
                                    if ($penerusFK) {
                                        $url = \App\Filament\Resources\MeterAirs\MeterAirResource::getUrl('view', ['record' => $penerusFK->id]);
                                        return "<span style='color: #047857; font-weight: bold;'>🔗 {$penerusFK->nomor_meter}</span>"
                                            . "<br><span style='font-size: 0.85em; color: #6B7280;'>Via Oper Kontrak</span>"
                                            . "<br><a href='{$url}' style='color: #2563EB; text-decoration: underline; font-size: 0.9em;'>Lihat Meter Baru →</a>";
                                    }

                                    // Prioritas 2: Smart Query — cari meter milik pelanggan yang sama dengan ID lebih besar
                                    $penerusSmart = \App\Models\MeterAir::where('pelanggan_id', $record->pelanggan_id)
                                        ->where('id', '>', $record->id)
                                        ->orderBy('id', 'asc')
                                        ->first();

                                    if ($penerusSmart) {
                                        $url = \App\Filament\Resources\MeterAirs\MeterAirResource::getUrl('view', ['record' => $penerusSmart->id]);
                                        return "<span style='color: #047857; font-weight: bold;'>🔄 {$penerusSmart->nomor_meter}</span>"
                                            . "<br><span style='font-size: 0.85em; color: #6B7280;'>Penggantian unit (pelanggan sama)</span>"
                                            . "<br><a href='{$url}' style='color: #2563EB; text-decoration: underline; font-size: 0.9em;'>Lihat Meter Baru →</a>";
                                    }

                                    if (in_array($record->status, ['Rusak', 'Diganti'])) {
                                        return "<span style='color: #DC2626;'>⚠️ Belum ada meter pengganti yang terdaftar.</span>";
                                    }

                                    return "<span style='color: #9CA3AF;'>Tidak ada pergantian.</span>";
                                }),

                            // Kolom Kanan: Meter ini menggantikan siapa?
                            TextEntry::make('meter_pendahulu')
                                ->label('Menggantikan Meter Lama')
                                ->html()
                                ->state(function ($record) {
                                    // Prioritas 1: Cek relasi FK OperKontrak
                                    $pendahuluFK = $record->melanjutkanDari;
                                    if ($pendahuluFK) {
                                        $url = \App\Filament\Resources\MeterAirs\MeterAirResource::getUrl('view', ['record' => $pendahuluFK->id]);
                                        return "<span style='font-weight: bold;'>🔗 {$pendahuluFK->nomor_meter}</span>"
                                            . " <span style='font-size: 0.85em; color: #6B7280;'>(Serah terima: {$record->oper_dari_nomor_meter})</span>"
                                            . "<br><span style='font-size: 0.85em; color: #6B7280;'>Via Oper Kontrak</span>"
                                            . "<br><a href='{$url}' style='color: #2563EB; text-decoration: underline; font-size: 0.9em;'>← Lihat Meter Lama</a>";
                                    }

                                    // Prioritas 2: Smart Query — cari meter milik pelanggan sama dengan ID lebih kecil & status Rusak/Nonaktif
                                    $pendahuluSmart = \App\Models\MeterAir::where('pelanggan_id', $record->pelanggan_id)
                                        ->where('id', '<', $record->id)
                                        ->whereIn('status', ['Rusak', 'Nonaktif', 'Diganti'])
                                        ->orderBy('id', 'desc')
                                        ->first();

                                    if ($pendahuluSmart) {
                                        $url = \App\Filament\Resources\MeterAirs\MeterAirResource::getUrl('view', ['record' => $pendahuluSmart->id]);
                                        $statusBadge = match ($pendahuluSmart->status) {
                                            'Rusak'    => '🔴 Rusak',
                                            'Nonaktif' => '⚫ Nonaktif',
                                            'Diganti'  => '🟠 Diganti',
                                            default    => $pendahuluSmart->status,
                                        };
                                        return "<span style='font-weight: bold;'>🔄 {$pendahuluSmart->nomor_meter}</span>"
                                            . " <span style='font-size: 0.85em; color: #6B7280;'>({$statusBadge})</span>"
                                            . "<br><span style='font-size: 0.85em; color: #6B7280;'>Unit sebelumnya milik pelanggan ini</span>"
                                            . "<br><a href='{$url}' style='color: #2563EB; text-decoration: underline; font-size: 0.9em;'>← Lihat Meter Lama</a>";
                                    }

                                    return "<span style='color: #9CA3AF;'>Ini adalah meter pertama pelanggan ini.</span>";
                                }),
                        ]),
                    ]),

                // 3. KELOMPOK STATISTIK OPERASIONAL
                ComponentsSection::make('Statistik Operasional (Life Cycle)')
                    ->icon('heroicon-o-chart-pie')
                    ->description('Rekam jejak dan beban kerja meteran air ini sejak dipasang.')
                    ->columns(3)
                    ->schema([
                        TextEntry::make('tanggal_pasang')
                            ->label('Tanggal Pasang')
                            ->date('d F Y')
                            ->icon('heroicon-m-calendar')
                            ->helperText(function ($record) {
                                $tanggalPasang = Carbon::parse($record->tanggal_pasang)->startOfDay();
                                $now = Carbon::now()->startOfDay();

                                if ($tanggalPasang->isSameDay($now)) {
                                    return 'Baru dipasang hari ini.';
                                }

                                return 'Telah beroperasi: ' . $tanggalPasang->diffForHumans($now, ['parts' => 2]);
                            }),

                        TextEntry::make('pencatatan_terakhir')
                            ->label('Tgl Pencatatan Terakhir')
                            ->state(function ($record) {
                                $lastRecord = $record->pencatatanMeters()->latest('created_at')->first();
                                return $lastRecord ? $lastRecord->created_at->translatedFormat('d F Y') : 'Belum pernah dicatat';
                            })
                            ->icon('heroicon-m-clock')
                            ->color('gray'),

                        TextEntry::make('total_pencatatan')
                            ->label('Total Riwayat Catat')
                            ->state(function ($record) {
                                return $record->pencatatanMeters()->count() . ' Bulan / Kali';
                            })
                            ->icon('heroicon-m-clipboard-document-list')
                            ->color('gray'),

                        TextEntry::make('angka_terkini')
                            ->label('Angka Meter Terkini')
                            ->state(function ($record) {
                                $lastRecord = $record->pencatatanMeters()->latest('periode_tahun')->latest('periode_bulan')->first();
                                return $lastRecord ? $lastRecord->angka_akhir . ' m³' : $record->angka_awal . ' m³';
                            })
                            ->size(TextSize::Large)
                            ->weight(FontWeight::ExtraBold)
                            ->color('info')
                            ->helperText('Berdasarkan pencatatan terakhir.'),

                        TextEntry::make('total_volume_air')
                            ->label('Total Beban Volume Air')
                            ->state(function ($record) {
                                $totalAir = $record->pencatatanMeters()->sum('pemakaian_m3');
                                return number_format($totalAir, 0, ',', '.') . ' m³';
                            })
                            ->size(TextSize::Large)
                            ->weight(FontWeight::ExtraBold)
                            ->color('primary')
                            ->helperText('Total kubikasi yang telah diukur.'),
                    ]),
            ]);
    }
}