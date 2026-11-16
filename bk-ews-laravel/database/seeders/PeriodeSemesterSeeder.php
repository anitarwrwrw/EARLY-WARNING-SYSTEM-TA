<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\PeriodeSemester;

class PeriodeSemesterSeeder extends Seeder
{
    /**
     * Seed data periode/semester akademik.
     * Hanya 1 yang is_aktif = true pada satu waktu.
     */
    public function run(): void
    {
        $periodes = [
            [
                'nama_semester'  => 'Ganjil 2024/2025',
                'tahun_ajaran'   => '2024/2025',
                'jenis'          => 'Ganjil',
                'is_aktif'       => false,
                'buka_kuesioner' => false,
            ],
            [
                'nama_semester'  => 'Genap 2024/2025',
                'tahun_ajaran'   => '2024/2025',
                'jenis'          => 'Genap',
                'is_aktif'       => false,
                'buka_kuesioner' => false,
            ],
            [
                'nama_semester'  => 'Ganjil 2025/2026',
                'tahun_ajaran'   => '2025/2026',
                'jenis'          => 'Ganjil',
                'is_aktif'       => true,   // ← Semester yang sedang aktif
                'buka_kuesioner' => true,   // ← Kuesioner siswa sedang terbuka
            ],
        ];

        foreach ($periodes as $data) {
            PeriodeSemester::create($data);
        }

        $this->command->info('✅ PeriodeSemesterSeeder: ' . count($periodes) . ' periode berhasil dibuat.');
        $this->command->info('   Semester aktif: Ganjil 2025/2026');
    }
}
