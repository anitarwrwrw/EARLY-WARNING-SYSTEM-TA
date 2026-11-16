<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('akademik_absensi', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('siswa_id');
            $table->foreign('siswa_id')->references('id')->on('siswa')->cascadeOnDelete();
            $table->string('semester');                         // Contoh: "Ganjil 2026/2027"
            $table->unsignedBigInteger('input_oleh')->nullable();
            $table->foreign('input_oleh')->references('id')->on('users')->nullOnDelete();
            $table->decimal('rata_rata_nilai', 5, 2)->default(0);   // 0.00 - 100.00
            $table->decimal('tren_nilai', 6, 2)->default(0);        // Selisih vs semester lalu (+/-)
            $table->unsignedSmallInteger('jumlah_alpha')->default(0); // Ketidakhadiran tanpa ket.
            $table->unsignedSmallInteger('jumlah_izin')->default(0);
            $table->unsignedSmallInteger('jumlah_sakit')->default(0);
            $table->timestamps();

            $table->unique(['siswa_id', 'semester']); // Satu siswa, satu semester, satu record
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('akademik_absensi');
    }
};

