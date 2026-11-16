<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PengaturanSistem extends Model
{
    protected $table = 'pengaturan_sistem';

    protected $fillable = [
        'kunci',
        'nilai',
        'keterangan',
    ];

    /**
     * Ambil nilai pengaturan berdasarkan kunci.
     * Jika tidak ada di DB, gunakan fallback (default atau dari config/env).
     */
    public static function get(string $kunci, ?string $default = null): ?string
    {
        $setting = static::where('kunci', $kunci)->first();
        if ($setting && $setting->nilai !== null) {
            return $setting->nilai;
        }

        return $default;
    }

    /**
     * Simpan atau update nilai pengaturan.
     */
    public static function set(string $kunci, ?string $nilai, ?string $keterangan = null): self
    {
        return static::updateOrCreate(
            ['kunci' => $kunci],
            [
                'nilai'      => $nilai,
                'keterangan' => $keterangan ?? $kunci,
            ]
        );
    }
}
