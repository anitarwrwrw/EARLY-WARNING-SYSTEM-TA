<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kuesioner_psikososial', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('siswa_id');
            $table->foreign('siswa_id')->references('id')->on('siswa')->cascadeOnDelete();
            // Referensi ke periode semester aktif
            $table->unsignedBigInteger('semester_id');
            $table->foreign('semester_id')->references('id')->on('periode_semester');
            $table->unsignedTinyInteger('skor_motivasi')->default(0);   // 5 - 25
            $table->unsignedTinyInteger('skor_keluarga')->default(0);   // 5 - 25
            $table->unsignedTinyInteger('skor_keamanan')->default(0);   // 5 - 25
            // Jawaban mentah tiap butir soal (disimpan sebagai JSON array 15 elemen)
            $table->json('jawaban_raw')->nullable();
            $table->timestamp('tanggal_isi')->nullable();
            $table->timestamps();

            $table->unique(['siswa_id', 'semester_id']); // Anti-dobel pengisian per semester
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kuesioner_psikososial');
    }
};

