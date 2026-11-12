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
        Schema::create('attendances', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('employee_id')->unsigned();
            $table->date('tanggal');
            $table->time('jam_masuk')->nullable();
            $table->time('jam_pulang')->nullable();
            $table->enum('status_masuk', ['tepat_waktu', 'terlambat', 'belum_absen']);
            $table->enum('status_pulang', ['tepat_waktu', 'awal_pulang', 'belum_absen']);
            $table->string('lokasi_masuk_lat')->nullable();
            $table->string('lokasi_masuk_lng')->nullable();
            $table->string('lokasi_pulang_lat')->nullable();
            $table->string('lokasi_pulang_lng')->nullable();
            $table->integer('jarak_masuk_meter')->nullable();
            $table->integer('jarak_pulang_meter')->nullable();
            $table->string('foto_masuk')->nullable();
            $table->string('foto_pulang')->nullable();
            $table->text('keterangan')->nullable();
            $table->bigInteger('validated_by')->nullable();
            $table->datetime('validated_at')->nullable();
            $table->bigInteger('verification_by')->nullable();
            $table->datetime('verification_at')->nullable();
            $table->enum('status_validasi', ['pending', 'disetujui', 'ditolak']);
            $table->enum('status_verifikasi', ['belum', 'diverifikasi', 'ditolak']);
            $table->boolean('is_corrected')->default(false);
            $table->bigInteger('corrected_by')->nullable();
            $table->datetime('corrected_at')->nullable();
            $table->timestamps();

            $table->unique(['employee_id', 'tanggal']);

            $table->foreign('employee_id')->references('id')->on('employees')->onDelete('cascade');
            $table->foreign('validated_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('verification_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('corrected_by')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attendances');
    }
};