<?php

namespace App\Filament\Resources\Users\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Hash;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('Nama Lengkap')
                    ->required(),
                TextInput::make('email')
                    ->label('Alamat Email')
                    ->email()
                    ->required()
                    ->unique(ignoreRecord: true),
                TextInput::make('password')
                    ->label('Password (Isi jika ingin diubah)')
                    ->password()
                    ->dehydrateStateUsing(fn ($state) => Hash::make($state))
                    ->dehydrated(fn ($state) => filled($state))
                    ->required(fn (string $operation): bool => $operation === 'create'),
                Select::make('roles')
                    ->label('Peran (Roles)')
                    ->relationship(
                        name: 'roles', 
                        titleAttribute: 'name',
                        // SAKTI: Filter agar role admin-PDAM tidak muncul di pilihan manapun
                        modifyQueryUsing: fn (Builder $query) => $query->where('name', '!=', 'admin-PDAM')
                    )
                    ->multiple()
                    ->preload()
                    ->searchable()
                    // Opsional: Hanya izinkan admin yang login untuk melihat field ini
                    ->visible(fn () => auth()->user()->hasRole('admin-PDAM'))
            ]);
    }
}
