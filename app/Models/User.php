<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;

#[Fillable(['name', 'email', 'password', 'nomor_pelanggan', 'no_whatsapp'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable implements FilamentUser
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, HasRoles;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
        ];
    }

    public function pelanggan(): HasOne
    {
        return $this->hasOne(Pelanggan::class);
    }

    /**
     * Tentukan siapa saja yang boleh mengakses Filament Admin Panel.
     * Secara otomatis mengusir user biasa (pelanggan) meskipun mereka sudah login di guard web.
     */
    public function canAccessPanel(Panel $panel): bool
    {
        return $this->hasRole(['super_admin', 'admin', 'admin-PDAM']);
    }
}
