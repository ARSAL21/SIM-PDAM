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
            $table->dropForeign(['meter_air_id']);
            $table->foreign('meter_air_id')
                  ->references('id')
                  ->on('meter_air')
                  ->restrictOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pencatatan_meter', function (Blueprint $table) {
            $table->dropForeign(['meter_air_id']);
            $table->foreign('meter_air_id')
                  ->references('id')
                  ->on('meter_air')
                  ->cascadeOnDelete();
        });
    }
};
