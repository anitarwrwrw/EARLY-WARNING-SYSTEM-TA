<?php

namespace Tests\Feature;

use App\Models\Siswa;
use App\Models\User;
use Tests\TestCase;

class AdminViewTest extends TestCase
{
    public function test_halaman_login_dapat_dirender_tanpa_error(): void
    {
        $response = $this->get('/login');
        $response->assertStatus(200);
        $response->assertSee('Selamat datang!');
        $response->assertSee('Isi Kuesioner Siswa');
    }

    public function test_semua_halaman_admin_dapat_dirender_tanpa_error(): void
    {
        $admin = User::where('role', 'admin')->first();
        $this->assertNotNull($admin);

        $siswa = Siswa::first();
        $this->assertNotNull($siswa);

        $urls = [
            '/admin/dashboard',
            '/admin/periode',
            '/admin/users',
            '/admin/kelas',
            '/admin/siswa',
            '/admin/siswa/create',
            "/admin/siswa/{$siswa->id}/edit",
            '/admin/pengaturan',
        ];

        foreach ($urls as $url) {
            $response = $this->actingAs($admin)->get($url);
            $response->assertStatus(200);
        }
    }

    public function test_halaman_admin_tidak_bisa_diakses_guru_bk(): void
    {
        $guruBk = User::where('role', 'guru_bk')->first();
        $this->assertNotNull($guruBk);

        $this->actingAs($guruBk)->get('/admin/dashboard')->assertStatus(403);
    }
}
