<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tabel master kategori pelanggaran beserta bobot poinnya.
     * Memungkinkan Guru BK mendefinisikan jenis-jenis pelanggaran dan bobotnya.
     */
    public function up(): void
    {
        Schema::create('kategori_pelanggaran', function (Blueprint $table) {
            $table->id();
            $table->string('nama');                                     // Nama pelanggaran
            $table->enum('kategori', ['Ringan', 'Sedang', 'Berat']);    // Tingkat keparahan
            $table->unsignedSmallInteger('bobot_poin');                 // Poin yang diberikan
            $table->text('keterangan')->nullable();                     // Penjelasan tambahan
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kategori_pelanggaran');
    }
};
