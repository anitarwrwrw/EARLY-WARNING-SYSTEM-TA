<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TindakLanjutBk extends Model
{
    protected $table = 'tindak_lanjut_bk';

    protected $fillable = [
        'siswa_id', 'hasil_ews_id', 'guru_bk_id',
        'tanggal_konseling', 'catatan_pembinaan',
        'status_penanganan', 'tipe_konseling',
    ];

    protected function casts(): array
    {
        return ['tanggal_konseling' => 'date'];
    }

    public function siswa(): BelongsTo
    {
        return $this->belongsTo(Siswa::class, 'siswa_id');
    }

    public function hasilEws(): BelongsTo
    {
        return $this->belongsTo(HasilPrediksiEws::class, 'hasil_ews_id');
    }

    public function guruBk(): BelongsTo
    {
        return $this->belongsTo(User::class, 'guru_bk_id');
    }
}

