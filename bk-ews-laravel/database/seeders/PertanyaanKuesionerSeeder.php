<?php

namespace Database\Seeders;

use App\Http\Controllers\KuesionerPublikController;
use App\Models\PertanyaanKuesioner;
use Illuminate\Database\Seeder;

class PertanyaanKuesionerSeeder extends Seeder
{
    public function run(): void
    {
        foreach (KuesionerPublikController::BUTIR_KUESIONER as $butir) {
            PertanyaanKuesioner::updateOrCreate(
                ['no_urut' => $butir['no']],
                [
                    'aspek'      => $butir['aspek'],
                    'pernyataan' => $butir['pernyataan'],
                    'is_reverse' => $butir['reverse'],
                    'is_active'  => true,
                ]
            );
        }

        $this->command->info('✅ PertanyaanKuesionerSeeder: 15 butir pertanyaan kuesioner berhasil di-seed.');
    }
}
