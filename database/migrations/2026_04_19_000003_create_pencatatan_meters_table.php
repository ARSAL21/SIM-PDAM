<?php

use App\Models\MeterAir;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pencatatan_meter', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(MeterAir::class)
                  ->constrained('meter_air')
                  ->cascadeOnDelete();
            $table->tinyInteger('periode_bulan');  // 1–12
            $table->smallInteger('periode_tahun');
            $table->integer('angka_awal');
            $table->integer('angka_akhir');
            $table->integer('pemakaian_m3');
            $table->foreignId('dicatat_oleh')
                  ->constrained('users')
                  ->restrictOnDelete();
            $table->timestamps();

            // Satu meter tidak boleh dicatat dua kali dalam satu periode
            $table->unique(['meter_air_id', 'periode_bulan', 'periode_tahun']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pencatatan_meter');
    }
};
