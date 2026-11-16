<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class KuesionerPsikososial extends Model
{
    protected $table = 'kuesioner_psikososial';

    protected $fillable = [
        'siswa_id', 'semester',
        'skor_motivasi', 'skor_keluarga', 'skor_keamanan',
        'jawaban_raw', 'tanggal_isi',
    ];

    protected function casts(): array
    {
        return [
            'jawaban_raw'  => 'array',
            'tanggal_isi'  => 'datetime',
        ];
    }

    public function siswa(): BelongsTo
    {
        return $this->belongsTo(Siswa::class, 'siswa_id');
    }

    /**
     * Hitung skor total dari jawaban mentah.
     * Butir ke-3 tiap aspek adalah reverse scoring (6 - jawaban).
     * Susunan: [mot1..mot5, kel1..kel5, aman1..aman5]
     */
    public static function hitungSkor(array $jawaban): array
    {
        // Indeks butir reverse scoring (0-based): butir ke-3 tiap aspek
        $reverseIndex = [2, 7, 12];

        $corrected = [];
        foreach ($jawaban as $i => $val) {
            $corrected[] = in_array($i, $reverseIndex) ? (6 - (int)$val) : (int)$val;
        }

        return [
            'skor_motivasi' => array_sum(array_slice($corrected, 0, 5)),
            'skor_keluarga' => array_sum(array_slice($corrected, 5, 5)),
            'skor_keamanan' => array_sum(array_slice($corrected, 10, 5)),
        ];
    }
}

