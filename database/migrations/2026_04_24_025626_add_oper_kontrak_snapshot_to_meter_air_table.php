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
            $table->string('oper_dari_nomor_meter')->nullable()->after('tanggal_oper_kontrak');
            $table->string('oper_dari_nama_pelanggan')->nullable()->after('oper_dari_nomor_meter');
            $table->integer('oper_angka_serah_terima')->nullable()->after('oper_dari_nama_pelanggan');
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
