<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            PengaturanSistemSeeder::class,
            PeriodeSemesterSeeder::class,
            KategoriPelanggaranSeeder::class,
            PertanyaanKuesionerSeeder::class,
            UserSeeder::class,
            KelasSeeder::class,
            SiswaSeeder::class,
            DataDummySeeder::class,
        ]);
    }
}
