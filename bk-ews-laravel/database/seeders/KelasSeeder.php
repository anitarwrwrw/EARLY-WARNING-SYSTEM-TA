<?php

namespace Database\Seeders;

use App\Models\Kelas;
use App\Models\User;
use Illuminate\Database\Seeder;

class KelasSeeder extends Seeder
{
    public function run(): void
    {
        $wali = User::where('role', 'wali_kelas')->get();

        $kelasData = [
            ['nama_kelas' => 'TKJ 1', 'jurusan' => 'Teknik Komputer & Jaringan',    'tingkat' => 'XI',  'wali_index' => 0],
            ['nama_kelas' => 'RPL 1', 'jurusan' => 'Rekayasa Perangkat Lunak',      'tingkat' => 'XI',  'wali_index' => 1],
            ['nama_kelas' => 'TKJ 1', 'jurusan' => 'Teknik Komputer & Jaringan',    'tingkat' => 'XII', 'wali_index' => 2],
            ['nama_kelas' => 'AK 1',  'jurusan' => 'Akuntansi & Keuangan Lembaga',  'tingkat' => 'X',   'wali_index' => null],
        ];

        foreach ($kelasData as $data) {
            Kelas::create([
                'nama_kelas'    => $data['nama_kelas'],
                'jurusan'       => $data['jurusan'],
                'tingkat'       => $data['tingkat'],
                'wali_kelas_id' => $data['wali_index'] !== null ? $wali->get($data['wali_index'])?->id : null,
            ]);
        }
    }
}

