<?php

namespace App\Filament\Resources\MeterAirs\Schemas;

use Filament\Infolists\Components\TextEntry;
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
            ]);
    }
}
