<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class KategoriPelanggaran extends Model
{
    protected $table = 'kategori_pelanggaran';

    protected $fillable = [
        'nama',
        'kategori',
        'bobot_poin',
        'keterangan',
    ];

    // ─── Relasi ─────────────────────────────────────────────────────────────────

    /**
     * Semua catatan pelanggaran yang menggunakan kategori ini.
     */
    public function pelanggaran(): HasMany
    {
        return $this->hasMany(Pelanggaran::class, 'kategori_pelanggaran_id');
    }
}
