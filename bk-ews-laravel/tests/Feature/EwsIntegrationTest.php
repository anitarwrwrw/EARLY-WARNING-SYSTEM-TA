<?php

namespace Tests\Feature;

use App\Models\HasilPrediksiEws;
use App\Models\PeriodeSemester;
use App\Models\Siswa;
use App\Models\TindakLanjutBk;
use App\Models\User;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class EwsIntegrationTest extends TestCase
{
    public function test_jalankan_analisis_ews_mengirim_7_fitur_dan_menyimpan_hasil(): void
    {
        $guruBk  = User::where('role', 'guru_bk')->first();
        $periode = PeriodeSemester::aktif();
        $siswa   = Siswa::where('is_active', true)->first();

        $this->assertNotNull($guruBk);
        $this->assertNotNull($periode);
        $this->assertNotNull($siswa);

        $sebelumCount = HasilPrediksiEws::count();

        Http::fake([
            '*/api/predict-ews' => function ($request) {
                $payload = $request->data();
                $first   = $payload['data_siswa'][0] ?? [];

                // Pastikan ke-7 fitur X terkirim
                foreach ([
                    'rata_nilai', 'tren_nilai', 'jumlah_alpha', 'poin_pelanggaran',
                    'skor_motivasi', 'skor_keluarga', 'skor_keamanan',
                ] as $kolom) {
                    $this->assertArrayHasKey($kolom, $first);
                }

                $hasil = array_map(fn($s) => [
                    'siswa_id'        => $s['siswa_id'],
                    'status_risiko'   => 'Risiko Sedang',
                    'is_anomali'      => false,
                    'anomaly_score'   => 0.05,
                    'level_prioritas' => 4,
                    'label_prioritas' => 'Prioritas 4 (Pembinaan Berkala)',
                    'pemicu_utama'    => 'Uji integrasi EWS',
                ], $payload['data_siswa']);

                return Http::response([
                    'status'         => 'success',
                    'total_diproses' => count($hasil),
                    'hasil_prediksi' => $hasil,
                ], 200);
            },
        ]);

        $response = $this->actingAs($guruBk)->post('/bk/ews/jalankan', [
            'semester_id' => $periode->id,
        ]);

        $response->assertRedirect(route('bk.dashboard', ['semester_id' => $periode->id]));
        $response->assertSessionHas('success');

        $terbaru = HasilPrediksiEws::where('pemicu_utama', 'Uji integrasi EWS')->get();
        $this->assertGreaterThan(0, $terbaru->count());

        // Bersihkan data hasil pengujian agar database dummy tetap bersih
        HasilPrediksiEws::where('pemicu_utama', 'Uji integrasi EWS')->delete();
        $this->assertEquals($sebelumCount, HasilPrediksiEws::count());
    }

    public function test_jalankan_analisis_menampilkan_error_ramah_saat_flask_offline(): void
    {
        $guruBk  = User::where('role', 'guru_bk')->first();
        $periode = PeriodeSemester::aktif();

        Http::fake([
            '*/api/predict-ews' => function () {
                throw new ConnectionException('Connection refused (simulasi Flask mati)');
            },
        ]);

        $response = $this->actingAs($guruBk)->post('/bk/ews/jalankan', [
            'semester_id' => $periode->id,
        ]);

        $response->assertSessionHas('error');
    }

    public function test_guru_bk_dapat_mencatat_tindak_lanjut_konseling(): void
    {
        $guruBk = User::where('role', 'guru_bk')->first();
        $siswa  = Siswa::first();

        $response = $this->actingAs($guruBk)->post('/bk/tindaklanjut', [
            'siswa_id'          => $siswa->id,
            'tanggal_konseling' => date('Y-m-d'),
            'bentuk_penanganan' => 'Konseling Individual',
            'status_kasus'      => 'Proses',
            'catatan_konseling' => 'Catatan uji coba integrasi konseling',
        ]);

        $response->assertRedirect(route('bk.tindaklanjut.index'));
        $response->assertSessionHas('success');

        $tl = TindakLanjutBk::where('catatan_konseling', 'Catatan uji coba integrasi konseling')->first();
        $this->assertNotNull($tl);
        $tl->delete();
    }
}
