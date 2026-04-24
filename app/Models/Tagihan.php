<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['pencatatan_meter_id', 'pelanggan_id', 'no_tagihan', 'jumlah_tagihan', 'status_bayar'])]
class Tagihan extends Model
{
    protected $table = 'tagihan';
    use HasFactory;

    public function pencatatanMeter(): BelongsTo
    {
        return $this->belongsTo(PencatatanMeter::class);
    }

    public function pelanggan(): BelongsTo
    {
        return $this->belongsTo(Pelanggan::class);
    }

    public function pembayarans(): HasMany
    {
        return $this->hasMany(Pembayaran::class);
    }
}
