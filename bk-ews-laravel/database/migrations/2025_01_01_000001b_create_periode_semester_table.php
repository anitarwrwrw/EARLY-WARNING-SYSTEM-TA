<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tabel master periode/semester akademik.
     * Menggantikan kolom 'semester' (string) di tabel-tabel lain dengan FK yang proper.
     */
    public function up(): void
    {
        Schema::create('periode_semester', function (Blueprint $table) {
            $table->id();
            $table->string('nama_semester');                        // Contoh: "Ganjil 2026/2027"
            $table->string('tahun_ajaran', 9);                     // Contoh: "2026/2027"
            $table->enum('jenis', ['Ganjil', 'Genap']);
            $table->boolean('is_aktif')->default(false);            // Hanya 1 yang aktif sekaligus
            $table->boolean('buka_kuesioner')->default(false);      // Toggle buka/tutup kuesioner siswa
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('periode_semester');
    }
};
