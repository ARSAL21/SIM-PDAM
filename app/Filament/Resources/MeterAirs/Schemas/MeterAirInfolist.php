<?php

namespace App\Filament\Resources\MeterAirs\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\Section;
use Filament\Schemas\Components\Section as ComponentsSection;
use Filament\Schemas\Schema;

class MeterAirInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('pelanggan.user.name')
                    ->label('Pemilik'),
                TextEntry::make('pelanggan.no_pelanggan')
                    ->label('No. Pelanggan'),
                TextEntry::make('nomor_meter')
                    ->label('Nomor Meter'),
                TextEntry::make('merek')
                    ->label('Merek'),
                TextEntry::make('tanggal_pasang')
                    ->date(),
                TextEntry::make('angka_awal'),
                TextEntry::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'Aktif' => 'success',
                        'Rusak' => 'danger',
                        'Diganti' => 'warning',
                        default => 'gray',
                    }),
                    
                ComponentsSection::make('Riwayat Oper Kontrak')
                    ->visible(fn ($record) => $record->melanjutkan_dari_id || $record->dilanjutkanOleh )
                    ->schema([
                        TextEntry::make('melanjutkanDari.nomor_meter')
                            ->label('Melanjutkan dari Meter')
                            ->visible(fn ($record) => $record->melanjutkan_dari_id)
                            ->formatStateUsing(fn ($state, $record) => "{$state} — milik " . $record->melanjutkanDari->pelanggan->user->name
                            ),
                        TextEntry::make('melanjutkanDari.updated_at')
                            ->label('Pelanggan Sebelumnya Berhenti Pada')
                            ->visible(fn ($record) => $record->melanjutkan_dari_id)
                            ->date('d M Y'),
                        TextEntry::make('tanggal_oper_kontrak')
                            ->label('Tanggal Oper Kontrak (Pelanggan Baru Mulai)')
                            ->visible(fn ($record) => $record->tanggal_oper_kontrak)
                            ->date('d M Y'),
                        TextEntry::make('dilanjutkanOleh.pelanggan.user.name')
                            ->label('Diteruskan ke Pelanggan')
                            ->visible(fn ($record) => $record->dilanjutkanOleh)
                            ->formatStateUsing(fn ($state, $record) => "{$state} — mulai: " . ($record->dilanjutkanOleh->tanggal_oper_kontrak?->format('d M Y') ?? '-')
                            ),
                    ]),
            ]);
    }
}
