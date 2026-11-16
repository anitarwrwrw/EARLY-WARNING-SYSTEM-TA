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
        // user_id dihapus — siswa tidak lagi punya akun login
        'nisn', 'kelas_id', 'nis', 'nama_lengkap', 'jenis_kelamin',
        'nama_ortu', 'no_wa_ortu', 'alamat', 'tanggal_lahir', 'is_active',
    ];

    protected function casts(): array
    {
        return [
            'tanggal_lahir' => 'date',
            'is_active'     => 'boolean',
        ];
    }

    // ─── Relasi ─────────────────────────────────────────────────────────────────

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

    // ─── Helpers ─────────────────────────────────────────────────────────────────

    /**
     * Ambil total poin pelanggaran siswa (semua waktu).
     */
    public function totalPoinPelanggaran(): int
    {
        return (int) $this->pelanggaran()->sum('poin');
    }

    /**
     * Ambil data akademik & absensi berdasarkan semester_id.
     */
    public function akademikSemester(int $semesterId): ?AkademikAbsensi
    {
        return $this->akademikAbsensi()->where('semester_id', $semesterId)->first();
    }

    /**
     * Ambil kuesioner berdasarkan semester_id.
     */
    public function kuesionerSemester(int $semesterId): ?KuesionerPsikososial
    {
        return $this->kuesioner()->where('semester_id', $semesterId)->first();
    }

    /**
     * Ambil hasil EWS terbaru.
     */
    public function hasilEwsTerakhir(): ?HasilPrediksiEws
    {
        return $this->hasilPrediksiEws()->latest('tanggal_analisis')->first();
    }

    /**
     * Cek apakah siswa sudah mengisi kuesioner di semester tertentu.
     */
    public function sudahIsiKuesioner(int $semesterId): bool
    {
        return $this->kuesioner()->where('semester_id', $semesterId)->exists();
    }
}
