<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\PengaturanSistem;

class PengaturanSistemSeeder extends Seeder
{
    public function run(): void
    {
        $settings = [
            [
                'kunci'      => 'flask_api_url',
                'nilai'      => env('FLASK_API_URL', 'http://127.0.0.1:5000'),
                'keterangan' => 'URL Endpoint Microservice Machine Learning Flask',
            ],
            [
                'kunci'      => 'wa_gateway_token',
                'nilai'      => env('FONNTE_TOKEN', ''),
                'keterangan' => 'Token API WhatsApp Gateway (Fonnte)',
            ],
            [
                'kunci'      => 'nama_sekolah',
                'nilai'      => 'SMK Sunan Ampel Jombang',
                'keterangan' => 'Nama Instansi Sekolah',
            ],
        ];

        foreach ($settings as $s) {
            PengaturanSistem::set($s['kunci'], $s['nilai'], $s['keterangan']);
        }

        $this->command->info('✅ PengaturanSistemSeeder: Konfigurasi default berhasil disimpan.');
    }
}
