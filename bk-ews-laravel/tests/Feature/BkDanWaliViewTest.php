<?php

namespace Tests\Feature;

use App\Models\AkademikAbsensi;
use App\Models\KategoriPelanggaran;
use App\Models\Kelas;
use App\Models\Pelanggaran;
use App\Models\PeriodeSemester;
use App\Models\PertanyaanKuesioner;
use App\Models\Siswa;
use App\Models\User;
use Tests\TestCase;

class BkDanWaliViewTest extends TestCase
{
    public function test_semua_halaman_guru_bk_dapat_dirender_tanpa_error(): void
    {
        $guruBk = User::where('role', 'guru_bk')->first();
        $this->assertNotNull($guruBk);

        $siswa = Siswa::first();
        $this->assertNotNull($siswa);

        $urls = [
            '/bk/dashboard',
            "/bk/ews/siswa/{$siswa->id}",
            '/bk/master/siswa',
            '/bk/master/kelas',
            '/bk/master/pelanggaran',
            '/bk/master/kategori-pelanggaran',
            '/bk/kuesioner',
            '/bk/tindaklanjut',
            '/bk/tindaklanjut/tambah',
            '/bk/whatsapp/log',
        ];

        foreach ($urls as $url) {
            $response = $this->actingAs($guruBk)->get($url);
            $response->assertStatus(200);
        }
    }

    public function test_semua_halaman_wali_kelas_dapat_dirender_tanpa_error(): void
    {
        $wali = User::where('role', 'wali_kelas')->first();
        $this->assertNotNull($wali);

        foreach (['/wali/dashboard', '/wali/input-nilai'] as $url) {
            $response = $this->actingAs($wali)->get($url);
            $response->assertStatus(200);
        }
    }

    public function test_wali_kelas_simpan_nilai_menghitung_tren_otomatis(): void
    {
        $wali = User::where('role', 'wali_kelas')->first();
        $kelas = Kelas::where('wali_kelas_id', $wali->id)->first();
        $siswa = Siswa::where('kelas_id', $kelas->id)->first();

        $semAktif = PeriodeSemester::aktif();
        $semSebelum = PeriodeSemester::where('id', '<', $semAktif->id)->latest('id')->first();
        $this->assertNotNull($semAktif);
        $this->assertNotNull($semSebelum);

        $nilaiSebelum = AkademikAbsensi::where('siswa_id', $siswa->id)
            ->where('semester_id', $semSebelum->id)
            ->value('rata_nilai');

        $recordLama = AkademikAbsensi::where('siswa_id', $siswa->id)
            ->where('semester_id', $semAktif->id)
            ->first();

        $nilaiBaru = 80.5;
        $response = $this->actingAs($wali)->post('/wali/simpan-nilai', [
            'siswa_id'     => $siswa->id,
            'semester_id'  => $semAktif->id,
            'rata_nilai'   => $nilaiBaru,
            'jumlah_alpha' => 1,
            'jumlah_izin'  => 0,
            'jumlah_sakit' => 0,
        ]);

        $response->assertSessionHas('success');

        $terbaru = AkademikAbsensi::where('siswa_id', $siswa->id)
            ->where('semester_id', $semAktif->id)
            ->first();

        $this->assertEquals($nilaiBaru, $terbaru->rata_nilai);
        $this->assertEquals(round($nilaiBaru - (float) $nilaiSebelum, 2), $terbaru->tren_nilai);

        // Kembalikan data awal agar seeder dummy tetap bersih
        if ($recordLama) {
            $terbaru->update([
                'rata_nilai'   => $recordLama->rata_nilai,
                'tren_nilai'   => $recordLama->tren_nilai,
                'jumlah_alpha' => $recordLama->jumlah_alpha,
                'jumlah_izin'  => $recordLama->jumlah_izin,
                'jumlah_sakit' => $recordLama->jumlah_sakit,
                'wali_kelas_id'=> $recordLama->wali_kelas_id,
            ]);
        }
    }

    public function test_guru_bk_dapat_mencatat_pelanggaran_berbasis_kategori(): void
    {
        $guruBk = User::where('role', 'guru_bk')->first();
        $siswa  = Siswa::first();
        $kat    = KategoriPelanggaran::first();

        $response = $this->actingAs($guruBk)->post('/bk/master/pelanggaran', [
            'siswa_id'                => $siswa->id,
            'kategori_pelanggaran_id' => $kat->id,
            'tanggal'                 => date('Y-m-d'),
            'keterangan'              => 'Uji coba pencatatan pelanggaran otomatis',
        ]);

        $response->assertSessionHas('success');

        $pel = Pelanggaran::where('siswa_id', $siswa->id)
            ->where('keterangan', 'Uji coba pencatatan pelanggaran otomatis')
            ->first();

        $this->assertNotNull($pel);
        $this->assertEquals($kat->bobot_poin, $pel->poin);
        $pel->delete();
    }

    public function test_guru_bk_dapat_kelola_kategori_dan_pertanyaan_kuesioner(): void
    {
        $guruBk = User::where('role', 'guru_bk')->first();

        // Uji tambah & hapus kategori pelanggaran
        $resKat = $this->actingAs($guruBk)->post('/bk/master/kategori-pelanggaran', [
            'nama'       => 'Kategori Uji Coba',
            'kategori'   => 'Ringan',
            'bobot_poin' => 5,
            'keterangan' => 'Uji coba CRUD kategori',
        ]);
        $resKat->assertRedirect('/bk/master/kategori-pelanggaran');

        $kat = KategoriPelanggaran::where('nama', 'Kategori Uji Coba')->first();
        $this->assertNotNull($kat);
        $this->actingAs($guruBk)->delete("/bk/master/kategori-pelanggaran/{$kat->id}")
            ->assertRedirect('/bk/master/kategori-pelanggaran');

        // Uji tambah & hapus pertanyaan kuesioner
        $resPert = $this->actingAs($guruBk)->post('/bk/kuesioner', [
            'no_urut'    => 99,
            'aspek'      => 'motivasi',
            'pernyataan' => 'Pernyataan uji coba kuesioner',
            'is_reverse' => 0,
            'is_active'  => 0,
        ]);
        $resPert->assertRedirect('/bk/kuesioner');

        $pert = PertanyaanKuesioner::where('no_urut', 99)->first();
        $this->assertNotNull($pert);
        $this->actingAs($guruBk)->delete("/bk/kuesioner/{$pert->id}")
            ->assertRedirect('/bk/kuesioner');
    }
}
