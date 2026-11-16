<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AkademikAbsensi extends Model
{
    protected $table = 'akademik_absensi';

    protected $fillable = [
        'siswa_id', 'semester', 'input_oleh',
        'rata_rata_nilai', 'tren_nilai',
        'jumlah_alpha', 'jumlah_izin', 'jumlah_sakit',
    ];

    protected function casts(): array
    {
        return [
            'rata_rata_nilai' => 'float',
            'tren_nilai'      => 'float',
        ];
    }

    public function siswa(): BelongsTo
    {
        return $this->belongsTo(Siswa::class, 'siswa_id');
    }

    public function inputOleh(): BelongsTo
    {
        return $this->belongsTo(User::class, 'input_oleh');
    }
}

