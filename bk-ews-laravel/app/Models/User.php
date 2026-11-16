<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

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
            'password'   => 'hashed',
            'is_active'  => 'boolean',
        ];
    }

    // ─── Helpers ───────────────────────────────────────────────────────────────
    public function isBK(): bool      { return $this->role === 'bk'; }
    public function isWaliKelas(): bool { return $this->role === 'wali_kelas'; }
    public function isSiswa(): bool   { return $this->role === 'siswa'; }

    // ─── Relations ─────────────────────────────────────────────────────────────
    public function kelas()
    {
        return $this->hasOne(Kelas::class, 'wali_kelas_id');
    }

    public function siswaProfil()
    {
        return $this->hasOne(Siswa::class, 'user_id');
    }
}
