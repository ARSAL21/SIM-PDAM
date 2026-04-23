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
            $table->date('tanggal_oper_kontrak')
                  ->nullable()
                  ->after('melanjutkan_dari_id')
                  ->comment('Tanggal pelanggan baru mulai melanjutkan meter dari pelanggan sebelumnya');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('meter_air', function (Blueprint $table) {
            $table->dropColumn('tanggal_oper_kontrak');
        });
    }
};
