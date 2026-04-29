<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'tagihan_id',
    'tanggal_bayar',
    'jumlah_bayar',
    'status_pembayaran',
    'metode_bayar',
    'diverifikasi_oleh',
    'diverifikasi_pada',
    'catatan_admin',
])]
class Pembayaran extends Model
{
    use HasFactory;
     protected $table = 'pembayaran';
    protected function casts(): array
    {
        return [
            'tanggal_bayar' => 'datetime',
            'diverifikasi_pada' => 'datetime',
        ];
    }

    // Registrasi koleksi khusus bukti transfer (Hanya izinkan 1 file per record)
    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('bukti_pembayaran')
             ->singleFile(); // Memaksa replace jika ada upload ulang di record yang sama
    }

    public function tagihan(): BelongsTo
    {
        return $this->belongsTo(Tagihan::class);
    }

    // Relasi Verifikator (Audit)
    public function verifikator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'diverifikasi_oleh');
    }
}

