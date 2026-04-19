<?php

use App\Models\Pelanggan;
use App\Models\PencatatanMeter;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('tagihan', function (Blueprint $blueprint) {
            $blueprint->id();
            $blueprint->foreignIdFor(PencatatanMeter::class)->unique()->constrained('pencatatan_meter')->cascadeOnDelete();
            $blueprint->foreignIdFor(Pelanggan::class)->constrained('pelanggan')->cascadeOnDelete();
            $blueprint->string('no_tagihan')->unique();
            $blueprint->integer('jumlah_tagihan');
            $blueprint->enum('status_bayar', ['Belum Bayar', 'Menunggu Verifikasi', 'Lunas'])->default('Belum Bayar');
            $blueprint->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tagihan');
    }
};
