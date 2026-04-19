<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['tagihan_id', 'tanggal_bayar', 'jumlah_bayar', 'status_verifikasi'])]
class Pembayaran extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'tanggal_bayar' => 'datetime',
        ];
    }

    public function tagihan(): BelongsTo
    {
        return $this->belongsTo(Tagihan::class);
    }
}
