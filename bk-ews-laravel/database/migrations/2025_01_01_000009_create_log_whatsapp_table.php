<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('log_whatsapp', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('siswa_id');
            $table->foreign('siswa_id')->references('id')->on('siswa')->cascadeOnDelete();
            $table->unsignedBigInteger('dikirim_oleh')->nullable();
            $table->foreign('dikirim_oleh')->references('id')->on('users')->nullOnDelete();
            $table->string('no_tujuan', 20);
            $table->enum('jenis_template', ['koordinasi_preventif', 'undangan_konseling', 'apresiasi_positif']);
            $table->text('isi_pesan');
            $table->enum('status_kirim', ['Berhasil', 'Gagal'])->default('Gagal');
            $table->string('response_api')->nullable(); // Respons dari Fonnte API
            $table->timestamp('waktu_kirim')->useCurrent();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('log_whatsapp');
    }
};

