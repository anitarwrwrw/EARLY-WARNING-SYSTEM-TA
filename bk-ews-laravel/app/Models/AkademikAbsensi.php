<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AkademikAbsensi extends Model
{
    protected $table = 'akademik_absensi';

    protected $fillable = [
        'siswa_id', 'semester_id', 'wali_kelas_id',
        'rata_nilai',   // Disingkat dari rata_rata_nilai untuk keterbacaan kode
        'tren_nilai',
        'jumlah_alpha', 'jumlah_izin', 'jumlah_sakit',
    ];

    protected function casts(): array
    {
        return [
            'rata_nilai' => 'float',
            'tren_nilai' => 'float',
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

    public function waliKelas(): BelongsTo
    {
        return $this->belongsTo(User::class, 'wali_kelas_id');
    }
}
