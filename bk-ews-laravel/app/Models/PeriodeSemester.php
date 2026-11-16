<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PeriodeSemester extends Model
{
    protected $table = 'periode_semester';

    protected $fillable = [
        'nama_semester',
        'tahun_ajaran',
        'jenis',
        'is_aktif',
        'buka_kuesioner',
    ];

    protected $casts = [
        'is_aktif'       => 'boolean',
        'buka_kuesioner' => 'boolean',
    ];

    // ─── Scope ──────────────────────────────────────────────────────────────────

    /**
     * Ambil periode semester yang sedang aktif.
     */
    public function scopeAktif($query)
    {
        return $query->where('is_aktif', true);
    }

    /**
     * Helper statis: kembalikan instance semester aktif (atau null).
     */
    public static function aktif(): ?self
    {
        return static::where('is_aktif', true)->first();
    }

    // ─── Relasi ─────────────────────────────────────────────────────────────────

    public function akademikAbsensi(): HasMany
    {
        return $this->hasMany(AkademikAbsensi::class, 'semester_id');
    }

    public function kuesioner(): HasMany
    {
        return $this->hasMany(KuesionerPsikososial::class, 'semester_id');
    }

    public function hasilEws(): HasMany
    {
        return $this->hasMany(HasilPrediksiEws::class, 'semester_id');
    }
}
