<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

#[Fillable([
    'pelanggan_id', 'nomor_meter', 'merek',
    'tanggal_pasang', 'angka_awal', 'status',
])]
class MeterAir extends Model
{
    use HasFactory;

    protected $table = 'meter_air';

    protected function casts(): array
    {
        return [
            'tanggal_pasang' => 'date',
        ];
    }

    public function pelanggan(): BelongsTo
    {
        return $this->belongsTo(Pelanggan::class);
    }

    public function pencatatanMeters(): HasMany
    {
        return $this->hasMany(PencatatanMeter::class);
    }

    // Dipakai untuk auto-populate angka_awal di form pencatatan baru
    public function pencatatanTerakhir(): HasOne
    {
        return $this->hasOne(PencatatanMeter::class)->latestOfMany();
    }
}
