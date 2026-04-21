<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

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

    public function meterAirs(): HasMany
    {
        return $this->hasMany(MeterAir::class);
    }

    public function pencatatanMeters(): HasManyThrough
    {
        return $this->hasManyThrough(PencatatanMeter::class, MeterAir::class);
    }

    public function meterAktif(): HasOne
    {
        return $this->hasOne(MeterAir::class)
                    ->where('status', 'Aktif')
                    ->latestOfMany();
    }

    public function tagihans(): HasMany
    {
        return $this->hasMany(Tagihan::class);
    }
}
