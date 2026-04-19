<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['user_id', 'golongan_tarif_id', 'no_pelanggan', 'alamat', 'no_hp', 'status_aktif'])]
class Pelanggan extends Model
{
    use HasFactory;

    protected $table = 'pelanggan';

    protected function casts(): array
    {
        return [
            'status_aktif' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function golonganTarif(): BelongsTo
    {
        return $this->belongsTo(GolonganTarif::class);
    }

    public function pencatatanMeters(): HasMany
    {
        return $this->hasMany(PencatatanMeter::class);
    }

    public function tagihans(): HasMany
    {
        return $this->hasMany(Tagihan::class);
    }
}
