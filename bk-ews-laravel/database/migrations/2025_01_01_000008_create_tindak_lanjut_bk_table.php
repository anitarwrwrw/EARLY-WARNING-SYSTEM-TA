<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tindak_lanjut_bk', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('siswa_id');
            $table->foreign('siswa_id')->references('id')->on('siswa')->cascadeOnDelete();
            $table->unsignedBigInteger('hasil_ews_id')->nullable();
            $table->foreign('hasil_ews_id')->references('id')->on('hasil_prediksi_ews')->nullOnDelete();
            $table->unsignedBigInteger('guru_bk_id');
            $table->foreign('guru_bk_id')->references('id')->on('users');
            $table->date('tanggal_konseling');
            $table->string('bentuk_penanganan')->nullable(); // Individual, Kelompok, Orang Tua
            $table->text('catatan_konseling')->nullable();
            $table->enum('status_kasus', ['Proses', 'Selesai'])->default('Proses');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tindak_lanjut_bk');
    }
};

