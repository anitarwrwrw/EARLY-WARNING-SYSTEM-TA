<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LogWhatsapp extends Model
{
    protected $table = 'log_whatsapp';

    protected $fillable = [
        'siswa_id', 'dikirim_oleh', 'no_tujuan',
        'jenis_template', 'isi_pesan', 'status_kirim',
        'response_api', 'waktu_kirim',
    ];

    protected function casts(): array
    {
        return ['waktu_kirim' => 'datetime'];
    }

    public function siswa(): BelongsTo
    {
        return $this->belongsTo(Siswa::class, 'siswa_id');
    }

    public function dikirimOleh(): BelongsTo
    {
        return $this->belongsTo(User::class, 'dikirim_oleh');
    }
}

