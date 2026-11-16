<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class HasilPrediksiEws extends Model
{
    protected $table = 'hasil_prediksi_ews';

    protected $fillable = [
        'siswa_id', 'semester_id', 'tanggal_analisis', 'dianalisis_oleh',
        'status_risiko', 'is_anomali', 'anomaly_score',
        'level_prioritas', 'label_prioritas', 'pemicu_utama', 'snapshot_fitur',
    ];

    protected function casts(): array
    {
        return [
            'tanggal_analisis' => 'datetime',
            'is_anomali'       => 'boolean',
            'anomaly_score'    => 'float',
            'snapshot_fitur'   => 'array',
        ];
    }

    // ─── Relasi ─────────────────────────────────────────────────────────────────

    public function siswa(): BelongsTo
    {
        return $this->belongsTo(Siswa::class, 'siswa_id');
    }

    public function semester(): BelongsTo
    {
        return $this->belongsTo(PeriodeSemester::class, 'semester_id');
    }

    public function dianalisisOleh(): BelongsTo
    {
        return $this->belongsTo(User::class, 'dianalisis_oleh');
    }

    public function tindakLanjut(): HasMany
    {
        return $this->hasMany(TindakLanjutBk::class, 'hasil_ews_id');
    }

    // ─── Helpers ─────────────────────────────────────────────────────────────────

    /**
     * Warna badge Bootstrap sesuai level prioritas.
     */
    public function getWarnaBadgeAttribute(): string
    {
        return match ($this->level_prioritas) {
            1       => 'danger',   // Merah menyala - Kritis
            2       => 'danger',   // Merah - Sangat Tinggi
            3       => 'warning',  // Oranye - Tinggi
            4       => 'info',     // Biru - Perlu Pantau
            default => 'success',  // Hijau - Aman
        };
    }

    /**
     * Ikon emoji sesuai level prioritas.
     */
    public function getIkonPrioritasAttribute(): string
    {
        return match ($this->level_prioritas) {
            1       => '🚨',
            2       => '🔴',
            3       => '🟠',
            4       => '🟡',
            default => '🟢',
        };
    }
}
