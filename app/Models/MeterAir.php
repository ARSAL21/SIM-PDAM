<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable([
    'pelanggan_id', 'nomor_meter', 'merek',
    'tanggal_pasang', 'angka_awal', 'status',
    'melanjutkan_dari_id', 'tanggal_oper_kontrak','tanggal_nonaktif', 'oper_dari_nomor_meter','oper_dari_nama_pelanggan','oper_angka_serah_terima', 'oper_dari_tanggal_nonaktif', 'oper_dilakukan_oleh',
])]
class MeterAir extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'meter_air';

    protected function casts(): array
    {
        return [
            'tanggal_pasang' => 'date',
            'tanggal_oper_kontrak' => 'date',
            'tanggal_nonaktif' => 'date',
            'oper_dari_tanggal_nonaktif' => 'date',
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

    // Meter ini adalah kelanjutan dari meter mana? (Skenario C)
    public function melanjutkanDari(): BelongsTo
    {
        return $this->belongsTo(MeterAir::class, 'melanjutkan_dari_id');
    }

    // Meter mana yang meneruskan meter ini? (Skenario C)
    public function dilanjutkanOleh(): HasOne
    {
        return $this->hasOne(MeterAir::class, 'melanjutkan_dari_id');
    }

    public function operDilakukanOleh(): BelongsTo
    {
        return $this->belongsTo(User::class, 'oper_dilakukan_oleh');
    }
}
