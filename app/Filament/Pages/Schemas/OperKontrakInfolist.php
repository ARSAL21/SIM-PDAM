<?php

namespace App\Filament\Pages\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Enums\FontWeight;

class OperKontrakInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make('Informasi Serah Terima')
                    ->icon('heroicon-o-document-check')
                    ->columns(3)
                    ->schema([
                        TextEntry::make('tanggal_oper_kontrak')
                            ->label('Tanggal Oper Kontrak')
                            ->date('d F Y')
                            ->weight(FontWeight::Bold)
                            ->color('primary'),

                        TextEntry::make('nomor_meter')
                            ->label('Nomor Meter')
                            ->weight(FontWeight::Bold),

                        TextEntry::make('operDilakukanOleh.name')
                            ->label('Diproses Oleh')
                            ->icon('heroicon-m-user-circle')
                            ->default('Sistem'),
                    ]),

                Grid::make(2)
                    ->schema([
                        // --- PELANGGAN SEBELUMNYA (LAMA) ---
                        Section::make('Pelanggan Sebelumnya')
                            ->icon('heroicon-o-user-minus')
                            ->description('Informasi pada saat penutupan kontrak lama')
                            ->columnSpan(1)
                            ->schema([
                                TextEntry::make('oper_dari_nama_pelanggan')
                                    ->label('Nama Pelanggan')
                                    ->weight(FontWeight::Bold)
                                    // 💡 TRIK PRO: Gunakan 'lg' (Lebih bersih dan anti-error!)
                                    ->size('lg'), 

                                TextEntry::make('oper_dari_tanggal_nonaktif')
                                    ->label('Tanggal Berhenti / Nonaktif')
                                    ->date('d F Y')
                                    ->placeholder('Data tidak tersedia'),

                                TextEntry::make('oper_angka_serah_terima')
                                    ->label('Angka Terakhir Meter')
                                    ->numeric()
                                    ->suffix(' m³')
                                    ->color('danger')
                                    ->weight(FontWeight::Bold),
                            ]),

                        // --- PELANGGAN BARU (PENERUS) ---
                        Section::make('Pelanggan Baru (Penerus)')
                            ->icon('heroicon-o-user-plus')
                            ->description('Informasi pelanggan yang melanjutkan kontrak')
                            ->columnSpan(1)
                            ->schema([
                                TextEntry::make('pelanggan.nama_lengkap')
                                    ->label('Nama Pelanggan Baru')
                                    ->weight(FontWeight::Bold)
                                    // 💡 TRIK PRO: Gunakan 'lg'
                                    ->size('lg'),

                                TextEntry::make('pelanggan.no_pelanggan')
                                    ->label('Nomor Pelanggan'),

                                TextEntry::make('pelanggan.alamat')
                                    ->label('Alamat Pemasangan')
                                    // Jika tidak pakai Markdown di text, bisa dihilangkan
                            ]),
                    ]),

                Section::make('Catatan Audit')
                    ->collapsed()
                    ->schema([
                        TextEntry::make('created_at')
                            ->label('Waktu Pencatatan Sistem')
                            ->dateTime('d/m/Y H:i:s'),
                    ]),
            ]);
    }
}