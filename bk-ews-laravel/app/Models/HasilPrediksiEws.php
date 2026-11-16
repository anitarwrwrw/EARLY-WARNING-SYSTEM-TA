<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class HasilPrediksiEws extends Model
{
    protected $table = 'hasil_prediksi_ews';

    protected $fillable = [
        'siswa_id', 'semester', 'tanggal_analisis', 'dianalisis_oleh',
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

    // ─── Relations ─────────────────────────────────────────────────────────────
    public function siswa(): BelongsTo
    {
        return $this->belongsTo(Siswa::class, 'siswa_id');
    }

    public function dianalisisoleh(): BelongsTo
    {
        return $this->belongsTo(User::class, 'dianalisis_oleh');
    }

    public function tindakLanjut(): HasMany
    {
        return $this->hasMany(TindakLanjutBk::class, 'hasil_ews_id');
    }

    // ─── Helpers ───────────────────────────────────────────────────────────────
    public function getWarnaBadgeAttribute(): string
    {
        return match ($this->level_prioritas) {
            1 => 'danger',   // Merah menyala
            2 => 'danger',   // Merah
            3 => 'warning',  // Oranye
            4 => 'warning',  // Kuning
            default => 'success', // Hijau
        };
    }

    public function getIkonPrioritasAttribute(): string
    {
        return match ($this->level_prioritas) {
            1 => '⚠️',
            2 => '🔴',
            3 => '🟠',
            4 => '🟡',
            default => '🟢',
        };
    }
}

