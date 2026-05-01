<?php

namespace App\Filament\Resources\Pelanggans\Schemas;

use App\Models\Pelanggan;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;

class PelangganForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                // ── Identitas Pelanggan ──
                TextInput::make('no_pelanggan')
                    ->label('No. Pelanggan')
                    // AUTO-GENERATE: Format PAM-TahunBulan-NomorUrut (Cth: PAM-2604-0001)
                    ->default(function () {
                        $lastId = Pelanggan::max('id') ?? 0;
                        return 'PAM-' . date('ym') . '-' . str_pad($lastId + 1, 4, '0', STR_PAD_LEFT);
                    })
                    ->disabled() // Dikunci agar admin tidak bisa mengetik manual
                    ->dehydrated() // WAJIB ADA agar data yang dikunci tetap dikirim ke database
                    ->required()
                    ->unique(ignoreRecord: true),

                TextInput::make('nama_lengkap')
                    ->label('Nama Lengkap Pelanggan')
                    ->required()
                    ->maxLength(255)
                    ->placeholder('Nama sesuai KTP / identitas resmi'),

                // ── Kontak ──
                TextInput::make('no_hp')
                    ->label('No. HP')
                    ->tel()
                    ->maxLength(20)
                    ->nullable(),

                Textarea::make('alamat')
                    ->label('Alamat Lengkap')
                    ->required()
                    ->minLength(10)
                    ->rows(3)
                    ->maxLength(500),

                // ── Status Kontrol ──
                Toggle::make('status_aktif')
                    ->label('Status Langganan Aktif')
                    ->default(true),
            ]);
    }
}