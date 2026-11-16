<?php

namespace Database\Seeders;

use App\Models\Kelas;
use App\Models\Siswa;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class SiswaSeeder extends Seeder
{
    public function run(): void
    {
        $kelas = Kelas::all();
        $counter = 1;

        $namaLaki = ['Ahmad','Budi','Dani','Eko','Fajar','Gilang','Hendra','Irfan','Joko','Kevin'];
        $namaPerempuan = ['Ayu','Bela','Citra','Dewi','Eka','Fitri','Hana','Intan','Jihan','Kiki'];
        $namaOrtu = ['Pak Budi','Pak Slamet','Pak Heru','Pak Agus','Pak Dodi','Pak Yusuf','Pak Imam','Pak Teguh','Pak Andi','Pak Rudi'];

        foreach ($kelas as $k) {
            $jumlahSiswa = 10; // 10 siswa demo per kelas
            for ($i = 0; $i < $jumlahSiswa; $i++) {
                $jenisKelamin = ($i % 2 === 0) ? 'L' : 'P';
                $namaDepan    = $jenisKelamin === 'L'
                    ? $namaLaki[$i % count($namaLaki)]
                    : $namaPerempuan[$i % count($namaPerempuan)];
                $namaLengkap  = "{$namaDepan} Siswa " . str_pad($counter, 3, '0', STR_PAD_LEFT);
                $nis          = '2024' . str_pad($counter, 4, '0', STR_PAD_LEFT);

                // Buat akun login siswa
                $user = User::create([
                    'name'     => $namaLengkap,
                    'username' => $nis,
                    'password' => Hash::make('password'),
                    'role'     => 'siswa',
                ]);

                Siswa::create([
                    'user_id'       => $user->id,
                    'kelas_id'      => $k->id,
                    'nis'           => $nis,
                    'nama_lengkap'  => $namaLengkap,
                    'jenis_kelamin' => $jenisKelamin,
                    'nama_ortu'     => $namaOrtu[$i % count($namaOrtu)] . ' ' . $namaLengkap,
                    'no_wa_ortu'    => '0812' . rand(10000000, 99999999),
                ]);

                $counter++;
            }
        }
    }
}

