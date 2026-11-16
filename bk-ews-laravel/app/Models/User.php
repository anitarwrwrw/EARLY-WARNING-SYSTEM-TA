<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;

class User extends Authenticatable
{
    use Notifiable;

    protected $fillable = [
        'name', 'username', 'email', 'password', 'role', 'is_active',
    ];

    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return [
            'password'  => 'hashed',
            'is_active' => 'boolean',
        ];
    }

    // ─── Helpers Role ────────────────────────────────────────────────────────────

    /**
     * Cek apakah user adalah Admin / Operator.
     */
    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    /**
     * Cek apakah user adalah Guru BK.
     */
    public function isGuruBK(): bool
    {
        return $this->role === 'guru_bk';
    }

    /**
     * Cek apakah user adalah Wali Kelas.
     */
    public function isWaliKelas(): bool
    {
        return $this->role === 'wali_kelas';
    }

    // ─── Relasi ─────────────────────────────────────────────────────────────────

    /**
     * Kelas yang di-wali oleh user ini (hanya berlaku untuk role wali_kelas).
     */
    public function kelas(): HasOne
    {
        return $this->hasOne(Kelas::class, 'wali_kelas_id');
    }

    /**
     * Semua catatan pelanggaran yang dicatat oleh user ini (Guru BK).
     */
    public function pelanggaran(): HasMany
    {
        return $this->hasMany(Pelanggaran::class, 'dicatat_oleh');
    }
}
