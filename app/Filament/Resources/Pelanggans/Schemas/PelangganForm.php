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
                // ── Grup Autentikasi ──
                Select::make('user_id')
                    ->label('Akun User')
                    ->relationship(
                        name: 'user', 
                        titleAttribute: 'name',
                        // Memfilter dropdown agar aman dari Error 500
                        modifyQueryUsing: fn (Builder $query, string $operation) => $query
                            // 1. Jika sedang membuat (create), jangan tampilkan user yang sudah punya data pelanggan
                            ->when($operation === 'create', fn($q) => $q->whereDoesntHave('pelanggan'))
                            // 2. Jangan tampilkan user yang punya role adminPDAM / super_admin
                            ->whereDoesntHave('roles', fn($q) => $q->whereIn('name', ['admin-PDAM', 'super_admin']))
                    )
                    ->searchable()
                    ->preload()
                    ->disabledOn('edit')
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
                            ->maxLength(255)
                            ->unique('users', 'email'),
                        TextInput::make('password')
                            ->label('Password')
                            ->password()
                            ->required()
                            ->minLength(8),
                        Select::make('roles')
                            ->relationship(
                                name: 'roles', 
                                titleAttribute: 'name',
                                // Filter agar role admin-PDAM tidak muncul di pilihan manapun
                                modifyQueryUsing: fn (Builder $query) => $query->where('name', '!=', 'admin-PDAM')
                            )
                            ->multiple()
                            ->required()
                            ->preload()
                            ->searchable()
                            ->label('Role/Hak Akses'),
                    ])
                    ->createOptionAction(fn (Action $action, string $operation) => $action->visible($operation === 'create')),

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