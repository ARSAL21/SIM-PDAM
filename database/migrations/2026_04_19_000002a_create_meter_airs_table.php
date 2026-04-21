<?php

use App\Models\Pelanggan;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('meter_air', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Pelanggan::class)
                  ->constrained('pelanggan')
                  ->cascadeOnDelete();
            $table->string('nomor_meter')->unique()->nullable();
            $table->string('merek')->nullable();
            $table->date('tanggal_pasang');
            $table->integer('angka_awal')->default(0);
            $table->enum('status', ['Aktif', 'Rusak', 'Diganti'])->default('Aktif');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('meter_air');
    }
};
