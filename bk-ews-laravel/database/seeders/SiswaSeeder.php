<?php

namespace Database\Seeders;

use App\Models\Kelas;
use App\Models\Siswa;
use Illuminate\Database\Seeder;

class SiswaSeeder extends Seeder
{
    /**
     * Seed data siswa tanpa akun login.
     * Siswa kini diidentifikasi via NISN untuk verifikasi kuesioner publik.
     */
    public function run(): void
    {
        $kelas   = Kelas::all();
        $counter = 1;

        $namaLaki      = ['Ahmad','Budi','Dani','Eko','Fajar','Gilang','Hendra','Irfan','Joko','Kevin'];
        $namaPerempuan = ['Ayu','Bela','Citra','Dewi','Eka','Fitri','Hana','Intan','Jihan','Kiki'];
        $namaBelakang  = ['Santoso','Purnomo','Rahayu','Susanto','Wibowo','Kurniawan','Hidayat','Nugroho','Sari','Pratama'];
        $namaOrtu      = ['Bapak Budi','Bapak Slamet','Bapak Heru','Bapak Agus','Bapak Dodi',
                          'Bapak Yusuf','Bapak Imam','Bapak Teguh','Bapak Andi','Bapak Rudi'];

        foreach ($kelas as $k) {
            $jumlahSiswa = 10; // 10 siswa demo per kelas

            for ($i = 0; $i < $jumlahSiswa; $i++) {
                $jenisKelamin = ($i % 2 === 0) ? 'L' : 'P';
                $namaDepan    = $jenisKelamin === 'L'
                    ? $namaLaki[$i % count($namaLaki)]
                    : $namaPerempuan[$i % count($namaPerempuan)];
                $namaBelk     = $namaBelakang[$counter % count($namaBelakang)];
                $namaLengkap  = "{$namaDepan} {$namaBelk}";

                // NIS: format 2024XXXX
                $nis  = '2024' . str_pad($counter, 4, '0', STR_PAD_LEFT);
                // NISN: format 10 digit unik (simulasi data real)
                $nisn = '30' . str_pad($counter, 8, '0', STR_PAD_LEFT);

                Siswa::create([
                    // Tidak ada user_id — siswa tidak punya akun login
                    'nisn'          => $nisn,
                    'kelas_id'      => $k->id,
                    'nis'           => $nis,
                    'nama_lengkap'  => $namaLengkap,
                    'jenis_kelamin' => $jenisKelamin,
                    'nama_ortu'     => $namaOrtu[$i % count($namaOrtu)] . ' (Ortu ' . $namaLengkap . ')',
                    'no_wa_ortu'    => '0812' . rand(10000000, 99999999),
                    'tanggal_lahir' => now()->subYears(rand(15, 18))->subDays(rand(1, 365))->format('Y-m-d'),
                    'is_active'     => true,
                ]);

                $counter++;
            }
        }

        $this->command->info('✅ SiswaSeeder: ' . ($counter - 1) . ' siswa berhasil dibuat (tanpa akun login).');
    }
}
