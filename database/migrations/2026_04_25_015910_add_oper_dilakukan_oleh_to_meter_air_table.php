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
            $table->foreignId('oper_dilakukan_oleh')
                ->nullable()
                ->after('oper_dari_tanggal_nonaktif')
                ->constrained('users')
                ->nullOnDelete();
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
