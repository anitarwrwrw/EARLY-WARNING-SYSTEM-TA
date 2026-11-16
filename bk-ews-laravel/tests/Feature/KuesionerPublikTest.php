<?php

namespace Tests\Feature;

use App\Models\Kelas;
use App\Models\KuesionerPsikososial;
use App\Models\PeriodeSemester;
use App\Models\Siswa;
use Tests\TestCase;

class KuesionerPublikTest extends TestCase
{
    public function test_halaman_kuesioner_publik_bisa_diakses_tanpa_login(): void
    {
        $response = $this->get('/kuesioner-siswa');
        $response->assertStatus(200);
        $response->assertSee('Kuesioner Kesejahteraan Siswa');
    }

    public function test_api_siswa_by_kelas_mengembalikan_json(): void
    {
        $kelas = Kelas::first();
        $this->assertNotNull($kelas);

        $response = $this->getJson("/api/siswa-by-kelas/{$kelas->id}");
        $response->assertStatus(200);
        $response->assertJsonStructure([
            '*' => ['id', 'nama_lengkap', 'nis', 'sudah_isi']
        ]);
    }

    public function test_submit_kuesioner_berhasil_dan_hitung_reverse_scoring(): void
    {
        $semester = PeriodeSemester::aktif();
        $this->assertNotNull($semester);
        $semester->update(['buka_kuesioner' => true]);

        // Buat siswa pengujian baru agar belum punya data kuesioner
        $kelas = Kelas::first();
        $siswaTest = Siswa::create([
            'kelas_id'      => $kelas->id,
            'nis'           => 'TEST9999',
            'nisn'          => '3099999999',
            'nama_lengkap'  => 'Siswa Uji Coba Kuesioner',
            'jenis_kelamin' => 'L',
        ]);

        // Susun 15 jawaban: semua beri nilai 5, kecuali butir ke-3 (reverse) diberi nilai 1
        // Sehingga butir normal bernilai 5, butir reverse 1 menjadi (6-1) = 5
        // Total skor tiap aspek harusnya 5 * 5 = 25
        $jawaban = [];
        for ($i = 1; $i <= 15; $i++) {
            $jawaban[$i] = in_array($i, [3, 8, 13]) ? 1 : 5;
        }

        $payload = [
            'kelas_id' => $kelas->id,
            'siswa_id' => $siswaTest->id,
            'nisn'     => '3099999999',
            'jawaban'  => $jawaban,
        ];

        $response = $this->post('/kuesioner-siswa', $payload);
        $response->assertRedirect(route('kuesioner.sukses'));

        // Cek data tersimpan di DB
        $kuesioner = KuesionerPsikososial::where('siswa_id', $siswaTest->id)
            ->where('semester_id', $semester->id)
            ->first();

        $this->assertNotNull($kuesioner);
        // Reverse scoring: nilai 1 terbalik menjadi 5 -> total 5 * 5 = 25
        $this->assertEquals(25, $kuesioner->skor_motivasi);
        $this->assertEquals(25, $kuesioner->skor_keluarga);
        $this->assertEquals(25, $kuesioner->skor_keamanan);

        // Uji coba submit kedua (harus ditolak karena anti-dobel)
        $responseDobel = $this->post('/kuesioner-siswa', $payload);
        $responseDobel->assertSessionHas('warning');

        // Bersihkan data pengujian
        $kuesioner->delete();
        $siswaTest->delete();
    }

    public function test_submit_dengan_nisn_salah_ditolak(): void
    {
        $semester = PeriodeSemester::aktif();
        $semester->update(['buka_kuesioner' => true]);

        $kelas = Kelas::first();
        $siswa = Siswa::where('kelas_id', $kelas->id)->first();

        $payload = [
            'kelas_id' => $kelas->id,
            'siswa_id' => $siswa->id,
            'nisn'     => 'SALAH_NISN_123',
            'jawaban'  => array_fill(1, 15, 3),
        ];

        $response = $this->post('/kuesioner-siswa', $payload);
        $response->assertSessionHasErrors('nisn');
    }
}
