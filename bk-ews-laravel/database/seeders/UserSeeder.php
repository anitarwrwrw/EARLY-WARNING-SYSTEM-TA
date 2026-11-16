<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class UserSeeder extends Seeder
{
    /**
     * Seed akun-akun pengguna sesuai 3 role baru:
     * admin, guru_bk, wali_kelas
     */
    public function run(): void
    {
        $users = [
            [
                'name'      => 'Administrator',
                'username'  => 'admin',
                'email'     => 'admin@smksunanampel.sch.id',
                'password'  => Hash::make('password'),
                'role'      => 'admin',
                'is_active' => true,
            ],
            [
                'name'      => 'Guru BK Utama',
                'username'  => 'gurubk',
                'email'     => 'gurubk@smksunanampel.sch.id',
                'password'  => Hash::make('password'),
                'role'      => 'guru_bk',
                'is_active' => true,
            ],
            [
                'name'      => 'Wali Kelas XI TKJ 1',
                'username'  => 'wali_xi_tkj1',
                'email'     => 'wali_xi_tkj1@smksunanampel.sch.id',
                'password'  => Hash::make('password'),
                'role'      => 'wali_kelas',
                'is_active' => true,
            ],
            [
                'name'      => 'Wali Kelas XI TKJ 2',
                'username'  => 'wali_xi_tkj2',
                'email'     => 'wali_xi_tkj2@smksunanampel.sch.id',
                'password'  => Hash::make('password'),
                'role'      => 'wali_kelas',
                'is_active' => true,
            ],
            [
                'name'      => 'Wali Kelas XII TKJ 1',
                'username'  => 'wali_xii_tkj1',
                'email'     => 'wali_xii_tkj1@smksunanampel.sch.id',
                'password'  => Hash::make('password'),
                'role'      => 'wali_kelas',
                'is_active' => true,
            ],
        ];

        foreach ($users as $userData) {
            User::create($userData);
        }

        $this->command->info('✅ UserSeeder: ' . count($users) . ' akun berhasil dibuat.');
    }
}
