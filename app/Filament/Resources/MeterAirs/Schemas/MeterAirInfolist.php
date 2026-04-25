<?php

namespace App\Filament\Resources\MeterAirs\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\Section;
use Filament\Schemas\Components\Section as ComponentsSection;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Model;

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
                    ->visible(fn ($record) => 
                        $record->melanjutkan_dari_id || 
                        filled($record->oper_dari_nomor_meter) || 
                        $record->dilanjutkanOleh
                    )
                    ->schema([
                        TextEntry::make('oper_dari_meter')
                            ->label('Melanjutkan dari Meter')
                            ->visible(fn ($record) => 
                                $record->melanjutkan_dari_id || filled($record->oper_dari_nomor_meter)
                            )
                            ->state(function (Model $record) {
                                $nomor = $record->melanjutkanDari?->nomor_meter
                                         ?? $record->oper_dari_nomor_meter;

                                $nama = $record->melanjutkanDari?->pelanggan?->user?->name
                                        ?? $record->oper_dari_nama_pelanggan;

                                if (!$nomor) return null;
                                return "{$nomor} — milik {$nama}";
                            }),

                        TextEntry::make('tanggal_nonaktif_history')
                            ->label('Pelanggan Sebelumnya Berhenti Pada')
                            ->state(function (Model $record) {
                                if ($record->melanjutkan_dari_id || filled($record->oper_dari_nomor_meter)) {
                                    return $record->melanjutkanDari?->tanggal_nonaktif
                                           ?? $record->oper_dari_tanggal_nonaktif;
                                }
                                return $record->tanggal_nonaktif;
                            })
                            ->date('d M Y')
                            ->placeholder('—'),

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
