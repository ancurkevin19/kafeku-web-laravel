<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('attendance_settings', function (Blueprint $table) {
            $table->id();
            $table->decimal('office_lat', 10, 8);
            $table->decimal('office_lng', 11, 8);
            $table->integer('office_radius_meter');
            $table->time('jam_masuk');
            $table->time('jam_pulang');
            $table->integer('toleransi_terlambat_menit')->default(15);
            $table->boolean('wajib_selfie')->default(true);
            $table->boolean('wajib_gps')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attendance_settings');
    }
};