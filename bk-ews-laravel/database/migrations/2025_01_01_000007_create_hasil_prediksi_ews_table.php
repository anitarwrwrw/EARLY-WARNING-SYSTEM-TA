<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hasil_prediksi_ews', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('siswa_id');
            $table->foreign('siswa_id')->references('id')->on('siswa')->cascadeOnDelete();
            $table->string('semester');
            $table->timestamp('tanggal_analisis')->useCurrent();
            $table->unsignedBigInteger('dianalisis_oleh')->nullable();
            $table->foreign('dianalisis_oleh')->references('id')->on('users')->nullOnDelete();

            // Hasil dari Flask
            $table->enum('status_risiko', ['Risiko Rendah', 'Risiko Sedang', 'Risiko Tinggi']);
            $table->boolean('is_anomali')->default(false);
            $table->decimal('anomaly_score', 8, 4)->nullable();
            $table->unsignedTinyInteger('level_prioritas')->default(5); // 1 (Kritis) - 5 (Aman)
            $table->string('label_prioritas')->nullable();
            $table->string('pemicu_utama')->nullable();

            // Snapshot data fitur saat analisis dijalankan (untuk audit)
            $table->json('snapshot_fitur')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hasil_prediksi_ews');
    }
};

