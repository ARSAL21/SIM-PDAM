<?php

use App\Models\Pelanggan;
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
        Schema::create('pencatatan_meter', function (Blueprint $blueprint) {
            $blueprint->id();
            $blueprint->foreignIdFor(Pelanggan::class)->constrained('pelanggan')->cascadeOnDelete();
            $blueprint->string('periode_bulan'); // YYYY-MM
            $blueprint->integer('meter_bulan_lalu');
            $blueprint->integer('meter_bulan_ini');
            $blueprint->integer('total_pemakaian');
            $blueprint->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pencatatan_meter');
    }
};
