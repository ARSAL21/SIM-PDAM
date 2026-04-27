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
        Schema::table('pencatatan_meter', function (Blueprint $table) {
            $table->text('catatan_koreksi')
                  ->nullable()
                  ->after('pemakaian_m3')
                  ->comment('Wajib diisi saat edit. Kosong jika ini input pertama kali.');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pencatatan_meter', function (Blueprint $table) {
            $table->dropColumn('catatan_koreksi');
        });
    }
};
