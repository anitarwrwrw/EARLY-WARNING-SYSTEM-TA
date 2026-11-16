<?php

namespace App\Http\Controllers\BK;

use App\Http\Controllers\Controller;
use App\Models\HasilPrediksiEws;
use App\Models\Siswa;
use App\Models\Kelas;
use App\Models\AkademikAbsensi;
use App\Models\KuesionerPsikososial;
use App\Models\Pelanggaran;
use App\Models\TindakLanjutBk;
use App\Models\PeriodeSemester;
use App\Models\PengaturanSistem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class EwsController extends Controller
{
    /**
     * Dashboard utama EWS untuk Guru BK.
     * Menampilkan pemetaan prioritas 1-5 seluruh siswa dari hasil analisis semester aktif.
     */
    public function dashboard(Request $request)
    {
        $semesterAktif = PeriodeSemester::aktif();
        $semesterId    = $request->input('semester_id', $semesterAktif?->id);
        $periode       = $semesterId ? PeriodeSemester::find($semesterId) : $semesterAktif;

        $hasilEws = collect();
        if ($periode) {
            $hasilEws = HasilPrediksiEws::with(['siswa.kelas'])
                ->where('semester_id', $periode->id)
                ->whereIn('id', function ($query) use ($periode) {
                    $query->selectRaw('MAX(id)')
                        ->from('hasil_prediksi_ews')
                        ->where('semester_id', $periode->id)
                        ->groupBy('siswa_id');
                })
                ->orderBy('level_prioritas')
                ->get();
        }

        // Statistik ringkasan kartu dashboard
        $stats = [
            'total_siswa'   => Siswa::where('is_active', true)->count(),
            'prioritas_1'   => $hasilEws->where('level_prioritas', 1)->count(),
            'prioritas_2'   => $hasilEws->where('level_prioritas', 2)->count(),
            'prioritas_3'   => $hasilEws->where('level_prioritas', 3)->count(),
            'total_anomali' => $hasilEws->where('is_anomali', true)->count(),
            'risiko_rendah' => $hasilEws->where('status_risiko', 'Risiko Rendah')->count(),
            'risiko_sedang' => $hasilEws->where('status_risiko', 'Risiko Sedang')->count(),
            'risiko_tinggi' => $hasilEws->where('status_risiko', 'Risiko Tinggi')->count(),
        ];

        // Data untuk Chart.js: distribusi risiko per kelas
        $distribusiKelas = $hasilEws->groupBy('siswa.kelas.nama_kelas')
            ->map(fn($group) => [
                'rendah' => $group->where('status_risiko', 'Risiko Rendah')->count(),
                'sedang' => $group->where('status_risiko', 'Risiko Sedang')->count(),
                'tinggi' => $group->where('status_risiko', 'Risiko Tinggi')->count(),
            ]);

        $semuaPeriode = PeriodeSemester::orderByDesc('id')->get();

        return view('bk.dashboard', compact(
            'hasilEws', 'stats', 'distribusiKelas',
            'periode', 'semesterAktif', 'semuaPeriode'
        ));
    }

    /**
     * Eksekusi Analisis EWS: mengumpulkan 7 fitur prediktor siswa,
     * mengirim ke Flask API, dan menyimpan hasil prediksi machine learning ke database.
     */
    public function jalankanAnalisis(Request $request)
    {
        $semesterAktif = PeriodeSemester::aktif();

        if (! $semesterAktif) {
            return back()->with('error', 'Tidak ada periode semester yang sedang aktif. Silakan hubungi Administrator.');
        }

        $semesterId = $request->input('semester_id', $semesterAktif->id);
        $periode    = PeriodeSemester::findOrFail($semesterId);

        // Kumpulkan siswa aktif dengan data akademik & kuesioner lengkap
        $siswaDenganData   = [];
        $siswaBelumLengkap = [];

        $semuaSiswa = Siswa::where('is_active', true)->with('kelas')->get();

        foreach ($semuaSiswa as $siswa) {
            $akademik  = $siswa->akademikSemester($periode->id);
            $kuesioner = $siswa->kuesionerSemester($periode->id);

            if (! $akademik || ! $kuesioner) {
                $siswaBelumLengkap[] = [
                    'nis'    => $siswa->nis,
                    'nama'   => $siswa->nama_lengkap,
                    'kelas'  => "{$siswa->kelas?->tingkat} {$siswa->kelas?->nama_kelas}",
                    'alasan' => $this->alasanDataBelumLengkap($akademik, $kuesioner),
                ];
                continue;
            }

            // 7 Fitur Prediktor X sesuai Blueprint EWS
            $siswaDenganData[] = [
                'siswa_id'         => $siswa->id,
                'rata_nilai'       => (float) $akademik->rata_nilai,
                'tren_nilai'       => (float) $akademik->tren_nilai,
                'jumlah_alpha'     => (int) $akademik->jumlah_alpha,
                'poin_pelanggaran' => (int) $siswa->totalPoinPelanggaran(),
                'skor_motivasi'    => (int) $kuesioner->skor_motivasi,
                'skor_keluarga'    => (int) $kuesioner->skor_keluarga,
                'skor_keamanan'    => (int) $kuesioner->skor_keamanan,
            ];
        }

        if (empty($siswaDenganData)) {
            return back()->with('warning', 'Tidak ada data siswa yang lengkap untuk dianalisis pada semester ini. Pastikan nilai, absensi, dan kuesioner sudah diinput.');
        }

        // Ambil URL Flask dari pengaturan sistem dinamis
        $flaskUrl = PengaturanSistem::get('flask_api_url', env('FLASK_API_URL', 'http://127.0.0.1:5000'));
        $endpoint = rtrim($flaskUrl, '/') . '/api/predict-ews';

        try {
            $response = Http::timeout(10)->post($endpoint, [
                'semester'   => $periode->nama_semester,
                'data_siswa' => $siswaDenganData,
            ]);

            if (! $response->successful()) {
                return back()->with('error', "Server Flask API merespons dengan status {$response->status()}: " . $response->body());
            }

            $hasil = $response->json();

        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            return back()->with('error', "Gagal terhubung ke Machine Learning Flask API di '{$endpoint}'. Pastikan server Flask sudah dijalankan (python app.py).");
        } catch (\Throwable $e) {
            return back()->with('error', 'Terjadi kesalahan saat memproses analisis EWS: ' . $e->getMessage());
        }

        // Simpan hasil prediksi EWS ke database MySQL
        DB::transaction(function () use ($hasil, $periode, $siswaDenganData) {
            foreach ($hasil['hasil_prediksi'] as $pred) {
                $snapshotFitur = collect($siswaDenganData)->firstWhere('siswa_id', $pred['siswa_id']);

                HasilPrediksiEws::create([
                    'siswa_id'         => $pred['siswa_id'],
                    'semester_id'      => $periode->id,
                    'tanggal_analisis' => now(),
                    'dianalisis_oleh'  => Auth::id(),
                    'status_risiko'    => $pred['status_risiko'],
                    'is_anomali'       => $pred['is_anomali'],
                    'anomaly_score'    => $pred['anomaly_score'] ?? null,
                    'level_prioritas'  => $pred['level_prioritas'],
                    'label_prioritas'  => $pred['label_prioritas'],
                    'pemicu_utama'     => $pred['pemicu_utama'],
                    'snapshot_fitur'   => $snapshotFitur,
                ]);
            }
        });

        $totalDiproses     = $hasil['total_diproses'] ?? count($siswaDenganData);
        $totalBelumLengkap = count($siswaBelumLengkap);

        $message = "Analisis EWS ({$periode->nama_semester}) berhasil! {$totalDiproses} siswa berhasil dipetakan.";
        if ($totalBelumLengkap > 0) {
            $message .= " ({$totalBelumLengkap} siswa dilewati karena data belum lengkap).";
        }

        return redirect()->route('bk.dashboard', ['semester_id' => $periode->id])
            ->with('success', $message)
            ->with('siswa_belum_lengkap', $siswaBelumLengkap);
    }

    /**
     * Tampilkan detail tren satu siswa (Line Chart riwayat perkembangan).
     */
    public function detailSiswa(Siswa $siswa)
    {
        $riwayatEws = HasilPrediksiEws::with('semester')
            ->where('siswa_id', $siswa->id)
            ->orderBy('tanggal_analisis')
            ->get();

        $riwayatAkademik = AkademikAbsensi::with('semester')
            ->where('siswa_id', $siswa->id)
            ->orderBy('created_at')
            ->get();

        $tindakLanjut = TindakLanjutBk::with('guruBk')
            ->where('siswa_id', $siswa->id)
            ->orderByDesc('tanggal_konseling')
            ->get();

        // Data untuk Line Chart tren risiko
        $chartData = [
            'labels'          => $riwayatEws->map(fn($h) => $h->semester?->nama_semester ?? '-'),
            'level_prioritas' => $riwayatEws->pluck('level_prioritas'),
            'rata_nilai'      => $riwayatAkademik->pluck('rata_nilai'),
        ];

        return view('bk.siswa.detail', compact(
            'siswa', 'riwayatEws', 'riwayatAkademik', 'tindakLanjut', 'chartData'
        ));
    }

    // ─── Private Helpers ───────────────────────────────────────────────────────

    private function alasanDataBelumLengkap($akademik, $kuesioner): string
    {
        $alasan = [];
        if (! $akademik)  $alasan[] = 'Nilai rapor / absensi belum diinput oleh Wali Kelas';
        if (! $kuesioner) $alasan[] = 'Kuesioner psikososial belum diisi siswa';
        return implode(' & ', $alasan);
    }
}
