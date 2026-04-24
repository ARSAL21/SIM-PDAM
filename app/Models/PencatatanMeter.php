<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

#[Fillable([
    'meter_air_id', 'periode_bulan', 'periode_tahun',
    'angka_awal', 'angka_akhir', 'pemakaian_m3',
    'catatan_koreksi', 'dicatat_oleh',
])]
class PencatatanMeter extends Model
{
    use HasFactory;

    protected $table = 'pencatatan_meter';

    public function meterAir(): BelongsTo
    {
        return $this->belongsTo(MeterAir::class);
    }

    public function tagihan(): HasOne
    {
        return $this->hasOne(Tagihan::class);
    }
    // Relasi untuk menarik data siapa petugas yang menginput
    public function petugas(): BelongsTo
    {
        // Parameter kedua wajib diisi karena nama foreign key-nya bukan standard (user_id), melainkan dicatat_oleh
        return $this->belongsTo(User::class, 'dicatat_oleh'); 
    }
}
