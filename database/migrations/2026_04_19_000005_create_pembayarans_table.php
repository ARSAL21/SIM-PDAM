<?php

use App\Models\Tagihan;
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
        Schema::create('pembayaran', function (Blueprint $blueprint) {
            $blueprint->id();
            $blueprint->foreignIdFor(Tagihan::class)->constrained('tagihan')->cascadeOnDelete();
            $blueprint->dateTime('tanggal_bayar');
            $blueprint->integer('jumlah_bayar');
            $blueprint->enum('status_verifikasi', ['Pending', 'Disetujui', 'Ditolak'])->default('Pending');
            $blueprint->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pembayaran');
    }
};
