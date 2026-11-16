<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pelanggaran', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('siswa_id');
            $table->foreign('siswa_id')->references('id')->on('siswa')->cascadeOnDelete();
            $table->unsignedBigInteger('dicatat_oleh');
            $table->foreign('dicatat_oleh')->references('id')->on('users');
            // Referensi ke master kategori pelanggaran
            $table->unsignedBigInteger('kategori_pelanggaran_id')->nullable();
            $table->foreign('kategori_pelanggaran_id')->references('id')->on('kategori_pelanggaran')->nullOnDelete();
            $table->date('tanggal');
            $table->string('jenis_pelanggaran'); // Deskripsi tambahan / keterangan singkat
            $table->unsignedSmallInteger('poin')->default(0); // Diisi otomatis dari bobot kategori
            $table->text('keterangan')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pelanggaran');
    }
};

