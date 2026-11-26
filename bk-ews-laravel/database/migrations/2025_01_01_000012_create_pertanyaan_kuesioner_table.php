<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tabel master butir pertanyaan kuesioner psikososial.
     * Memungkinkan Guru BK mengelola daftar pernyataan survei per aspek.
     */
    public function up(): void
    {
        Schema::create('pertanyaan_kuesioner', function (Blueprint $table) {
            $table->id();
            $table->unsignedSmallInteger('no_urut')->default(1);
            $table->enum('aspek', ['motivasi', 'keluarga', 'keamanan']);
            $table->text('pernyataan');
            $table->boolean('is_reverse')->default(false); // Reverse scoring (6 - nilai)
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pertanyaan_kuesioner');
    }
};
