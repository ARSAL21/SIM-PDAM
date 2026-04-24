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
        Schema::table('meter_air', function (Blueprint $table) {
            $table->date('oper_dari_tanggal_nonaktif')
                  ->nullable()
                  ->after('oper_angka_serah_terima');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('meter_air', function (Blueprint $table) {
            //
        });
    }
};
