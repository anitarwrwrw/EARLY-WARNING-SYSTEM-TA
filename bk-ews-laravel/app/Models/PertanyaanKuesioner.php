<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PertanyaanKuesioner extends Model
{
    protected $table = 'pertanyaan_kuesioner';

    protected $fillable = [
        'no_urut',
        'aspek',
        'pernyataan',
        'is_reverse',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_reverse' => 'boolean',
            'is_active'  => 'boolean',
        ];
    }

    public function getLabelAspekAttribute(): string
    {
        return match ($this->aspek) {
            'motivasi' => 'Motivasi Belajar',
            'keluarga' => 'Dukungan Keluarga',
            'keamanan' => 'Perasaan Aman di Sekolah',
            default    => ucfirst($this->aspek),
        };
    }
}
