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
        Schema::table('pembayaran', function (Blueprint $table) {
            $table->enum('metode_bayar', ['transfer', 'tunai'])
                  ->default('tunai')
                  ->after('tagihan_id');

            // FK untuk jejak audit Admin yang memverifikasi
            $table->foreignId('diverifikasi_oleh')
                  ->nullable()
                  ->constrained('users')
                  ->nullOnDelete() // Prevent cascade delete jika user admin dihapus
                  ->after('status_pembayaran');

            $table->timestamp('diverifikasi_pada')
                  ->nullable()
                  ->after('diverifikasi_oleh');

            $table->text('catatan_admin')
                  ->nullable()
                  ->after('diverifikasi_pada');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pembayaran', function (Blueprint $table) {
            $table->dropForeign(['diverifikasi_oleh']);
            $table->dropColumn([
                'metode_bayar', 
                'diverifikasi_oleh', 
                'diverifikasi_pada', 
                'catatan_admin'
            ]);
        });
    }
};
