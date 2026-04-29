<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Issue #21: Rename status_verifikasi → status_pembayaran
     * agar konsisten dengan terminologi bisnis yang digunakan di Filament UI.
     */
    public function up(): void
    {
        Schema::table('pembayaran', function (Blueprint $table) {
            $table->renameColumn('status_verifikasi', 'status_pembayaran');
        });
    }

    public function down(): void
    {
        Schema::table('pembayaran', function (Blueprint $table) {
            $table->renameColumn('status_pembayaran', 'status_verifikasi');
        });
    }
};
