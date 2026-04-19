<?php

use App\Models\GolonganTarif;
use App\Models\User;
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
        Schema::create('pelanggan', function (Blueprint $blueprint) {
            $blueprint->id();
            $blueprint->foreignIdFor(User::class)->unique()->constrained()->cascadeOnDelete();
            $blueprint->foreignIdFor(GolonganTarif::class)->constrained('golongan_tarif')->restrictOnDelete();
            $blueprint->string('no_pelanggan')->unique();
            $blueprint->text('alamat');
            $blueprint->string('no_hp')->nullable();
            $blueprint->boolean('status_aktif')->default(true);
            $blueprint->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pelanggan');
    }
};
