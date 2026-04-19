<?php

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
        Schema::create('golongan_tarif', function (Blueprint $blueprint) {
            $blueprint->id();
            $blueprint->string('nama_golongan');
            $blueprint->integer('tarif_per_kubik')->default(7000);
            $blueprint->integer('biaya_admin')->default(0);
            $blueprint->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('golongan_tarif');
    }
};
