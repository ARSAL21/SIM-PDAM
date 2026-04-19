<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['nama_golongan', 'tarif_per_kubik', 'biaya_admin'])]
class GolonganTarif extends Model
{
    use HasFactory;

    public function pelanggans(): HasMany
    {
        return $this->hasMany(Pelanggan::class);
    }
}
