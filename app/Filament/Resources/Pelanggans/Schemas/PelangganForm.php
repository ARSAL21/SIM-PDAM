<?php

namespace App\Filament\Resources\Pelanggans\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class PelangganForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                // ── Grup Autentikasi ──
                Select::make('user_id')
                    ->label('Akun User')
                    ->relationship(name: 'user', titleAttribute: 'name')
                    ->searchable()
                    ->preload()
                    ->required()
                    ->createOptionForm([
                        TextInput::make('name')
                            ->label('Nama Lengkap')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('email')
                            ->label('Email')
                            ->email()
                            ->required()
                            ->maxLength(255),
                        TextInput::make('password')
                            ->label('Password')
                            ->password()
                            ->required()
                            ->minLength(8),
                    ]),

                // ── Identitas Pelanggan ──
                TextInput::make('no_pelanggan')
                    ->label('No. Pelanggan')
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->maxLength(255)
                    ->placeholder('Contoh: PLG-0001'),

                // ── Kategori ──
                Select::make('golongan_tarif_id')
                    ->label('Golongan Tarif')
                    ->relationship(name: 'golonganTarif', titleAttribute: 'nama_golongan')
                    ->searchable()
                    ->preload()
                    ->required(),

                // ── Kontak ──
                TextInput::make('no_hp')
                    ->label('No. HP')
                    ->tel()
                    ->maxLength(20)
                    ->nullable(),

                Textarea::make('alamat')
                    ->label('Alamat Lengkap')
                    ->required()
                    ->rows(3)
                    ->maxLength(500),

                // ── Status Kontrol ──
                Toggle::make('status_aktif')
                    ->label('Status Langganan Aktif')
                    ->default(true),
            ]);
    }
}
