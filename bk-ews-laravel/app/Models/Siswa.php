<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Siswa extends Model
{
    protected $table = 'siswa';

    protected $fillable = [
        'user_id', 'kelas_id', 'nis', 'nama_lengkap', 'jenis_kelamin',
        'nama_ortu', 'no_wa_ortu', 'alamat', 'tanggal_lahir', 'is_active',
    ];

    protected function casts(): array
    {
        return [
            'tanggal_lahir' => 'date',
            'is_active'     => 'boolean',
        ];
    }

    // ─── Relations ─────────────────────────────────────────────────────────────
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function kelas(): BelongsTo
    {
        return $this->belongsTo(Kelas::class, 'kelas_id');
    }

    public function akademikAbsensi(): HasMany
    {
        return $this->hasMany(AkademikAbsensi::class, 'siswa_id');
    }

    public function pelanggaran(): HasMany
    {
        return $this->hasMany(Pelanggaran::class, 'siswa_id');
    }

    public function kuesioner(): HasMany
    {
        return $this->hasMany(KuesionerPsikososial::class, 'siswa_id');
    }

    public function hasilPrediksiEws(): HasMany
    {
        return $this->hasMany(HasilPrediksiEws::class, 'siswa_id');
    }

    public function tindakLanjutBk(): HasMany
    {
        return $this->hasMany(TindakLanjutBk::class, 'siswa_id');
    }

    public function logWhatsapp(): HasMany
    {
        return $this->hasMany(LogWhatsapp::class, 'siswa_id');
    }

    // ─── Helpers ───────────────────────────────────────────────────────────────
    /**
     * Ambil total poin pelanggaran untuk semester tertentu.
     */
    public function totalPoinPelanggaran(?string $semester = null): int
    {
        $query = $this->pelanggaran();
        // Pelanggaran tidak difilter per semester di blueprint, tapi bisa ditambahkan
        return (int) $query->sum('poin');
    }

    /**
     * Ambil data akademik & absensi semester tertentu.
     */
    public function akademikSemester(string $semester): ?AkademikAbsensi
    {
        return $this->akademikAbsensi()->where('semester', $semester)->first();
    }

    /**
     * Ambil kuesioner semester tertentu.
     */
    public function kuesionerSemester(string $semester): ?KuesionerPsikososial
    {
        return $this->kuesioner()->where('semester', $semester)->first();
    }

    /**
     * Ambil hasil EWS terbaru.
     */
    public function hasilEwsTerakhir(): ?HasilPrediksiEws
    {
        return $this->hasilPrediksiEws()->latest('tanggal_analisis')->first();
    }
}

