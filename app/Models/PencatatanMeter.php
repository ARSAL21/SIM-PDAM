<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

#[Fillable(['pelanggan_id', 'periode_bulan', 'meter_bulan_lalu', 'meter_bulan_ini', 'total_pemakaian'])]
class PencatatanMeter extends Model
{
    use HasFactory;

    public function pelanggan(): BelongsTo
    {
        return $this->belongsTo(Pelanggan::class);
    }

    public function tagihan(): HasOne
    {
        return $this->hasOne(Tagihan::class);
    }
}
