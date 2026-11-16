<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Guru BK
        User::create([
            'name'     => 'Guru BK - SMK Sunan Ampel',
            'username' => 'gurubk',
            'email'    => 'bk@smksunampel.sch.id',
            'password' => Hash::make('password'),
            'role'     => 'bk',
        ]);

        // Wali Kelas
        $waliKelas = [
            ['name' => 'Budi Santoso, S.Pd.',    'username' => 'wali_xi_tkj1'],
            ['name' => 'Siti Rahayu, S.Pd.',     'username' => 'wali_xi_rpl1'],
            ['name' => 'Ahmad Fauzi, S.T.',       'username' => 'wali_xii_tkj1'],
        ];

        foreach ($waliKelas as $wk) {
            User::create([
                'name'     => $wk['name'],
                'username' => $wk['username'],
                'password' => Hash::make('password'),
                'role'     => 'wali_kelas',
            ]);
        }
    }
}

