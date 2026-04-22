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
            // 1. Hapus unique constraint global pada nomor_meter.
            // Aturan unique dipindah ke level aplikasi dengan kondisi:
            // hanya boleh ada satu nomor meter yang berstatus Aktif pada satu waktu.
            $table->dropUnique(['nomor_meter']);
            
            // 2. Tambah status Nonaktif pada enum.
            $table->enum('status', ['Aktif', 'Rusak', 'Diganti', 'Nonaktif'])
                  ->default('Aktif')
                  ->change();
            
            // 3. Tambah kolom jejak oper kontrak (self-referencing FK).
            $table->foreignId('melanjutkan_dari_id')
                  ->nullable()
                  ->after('angka_awal')
                  ->constrained('meter_air')
                  ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('meter_air', function (Blueprint $table) {
            $table->dropForeign(['melanjutkan_dari_id']);
            $table->dropColumn('melanjutkan_dari_id');
            // Reverting the enum drop enum and add again (Requires DB statement usually, skipping to prevent errors)
            $table->unique('nomor_meter');
        });
    }
};
