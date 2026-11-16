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
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class EwsController extends Controller
{
    /**
     * Dashboard utama EWS untuk Guru BK.
     * Menampilkan pemetaan prioritas siswa dari hasil analisis terakhir.
     */
    public function dashboard(Request $request)
    {
        $semester = $request->input('semester', $this->semesterAktif());

        // Ambil hasil EWS terbaru per siswa untuk semester ini
        $hasilEws = HasilPrediksiEws::with(['siswa.kelas'])
            ->where('semester', $semester)
            ->whereIn('id', function ($query) use ($semester) {
                $query->selectRaw('MAX(id)')
                    ->from('hasil_prediksi_ews')
                    ->where('semester', $semester)
                    ->groupBy('siswa_id');
            })
            ->orderBy('level_prioritas')
            ->get();

        // Statistik ringkasan kartu dashboard
        $stats = [
            'total_siswa'     => Siswa::where('is_active', true)->count(),
            'prioritas_1'     => $hasilEws->where('level_prioritas', 1)->count(),
            'prioritas_2'     => $hasilEws->where('level_prioritas', 2)->count(),
            'prioritas_3'     => $hasilEws->where('level_prioritas', 3)->count(),
            'total_anomali'   => $hasilEws->where('is_anomali', true)->count(),
            'risiko_rendah'   => $hasilEws->where('status_risiko', 'Risiko Rendah')->count(),
            'risiko_sedang'   => $hasilEws->where('status_risiko', 'Risiko Sedang')->count(),
            'risiko_tinggi'   => $hasilEws->where('status_risiko', 'Risiko Tinggi')->count(),
        ];

        // Data untuk Chart.js: distribusi per kelas
        $distribusiKelas = $hasilEws->groupBy('siswa.kelas.nama_kelas')
            ->map(fn($group) => [
                'rendah'  => $group->where('status_risiko', 'Risiko Rendah')->count(),
                'sedang'  => $group->where('status_risiko', 'Risiko Sedang')->count(),
                'tinggi'  => $group->where('status_risiko', 'Risiko Tinggi')->count(),
            ]);

        // Semester list untuk filter dropdown
        $semesterList = HasilPrediksiEws::distinct()
            ->pluck('semester')
            ->sortDesc();

        return view('bk.dashboard', compact(
            'hasilEws', 'stats', 'distribusiKelas',
            'semesterList', 'semester'
        ));
    }

    /**
     * Eksekusi Analisis EWS: kirim data ke Flask, simpan hasilnya.
     */
    public function jalankanAnalisis(Request $request)
    {
        $semester = $request->input('semester', $this->semesterAktif());

        // Kumpulkan semua siswa aktif dengan data lengkap
        $siswaDenganData = [];
        $siswaBelumLengkap = [];

        $semuaSiswa = Siswa::where('is_active', true)->get();

        foreach ($semuaSiswa as $siswa) {
            $akademik   = $siswa->akademikSemester($semester);
            $kuesioner  = $siswa->kuesionerSemester($semester);

            if (! $akademik || ! $kuesioner) {
                $siswaBelumLengkap[] = [
                    'nis'          => $siswa->nis,
                    'nama'         => $siswa->nama_lengkap,
                    'alasan'       => $this->alasanDataBelumLengkap($akademik, $kuesioner),
                ];
                continue;
            }

            $siswaDenganData[] = [
                'siswa_id'               => $siswa->id,
                'rata_rata_nilai'        => (float) $akademik->rata_rata_nilai,
                'tren_nilai'             => (float) $akademik->tren_nilai,
                'jumlah_alpha'           => (int) $akademik->jumlah_alpha,
                'total_poin_pelanggaran' => $siswa->totalPoinPelanggaran(),
                'skor_motivasi'          => (int) $kuesioner->skor_motivasi,
                'skor_keluarga'          => (int) $kuesioner->skor_keluarga,
                'skor_keamanan'          => (int) $kuesioner->skor_keamanan,
            ];
        }

        if (empty($siswaDenganData)) {
            return back()->with('warning', 'Tidak ada data siswa yang lengkap untuk dianalisis. Pastikan nilai, absensi, dan kuesioner sudah diisi.');
        }

        // Kirim ke Flask ML Service
        try {
            $response = Http::timeout((int) config('services.flask_ml.timeout', 10))
                ->post(config('services.flask_ml.url') . '/api/predict-ews', [
                    'semester'   => $semester,
                    'data_siswa' => $siswaDenganData,
                ]);

            if (! $response->successful()) {
                return back()->with('error', 'Server analisis ML mengembalikan error. Pastikan server Flask sudah berjalan.');
            }

            $hasil = $response->json();

        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            return back()->with('error', 'Tidak dapat terhubung ke server analisis ML (Flask). Pastikan server Python sudah dijalankan di port 5000.');
        } catch (\Exception $e) {
            return back()->with('error', 'Terjadi kesalahan saat analisis: ' . $e->getMessage());
        }

        // Simpan hasil prediksi ke database
        DB::transaction(function () use ($hasil, $semester, $siswaDenganData) {
            foreach ($hasil['hasil_prediksi'] as $pred) {
                // Cari snapshot fitur untuk siswa ini
                $snapshotFitur = collect($siswaDenganData)
                    ->firstWhere('siswa_id', $pred['siswa_id']);

                HasilPrediksiEws::create([
                    'siswa_id'         => $pred['siswa_id'],
                    'semester'         => $semester,
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

        $totalDiproses = $hasil['total_diproses'];
        $totalBelumLengkap = count($siswaBelumLengkap);

        $message = "Analisis EWS selesai! {$totalDiproses} siswa berhasil dianalisis.";
        if ($totalBelumLengkap > 0) {
            $message .= " {$totalBelumLengkap} siswa dilewati karena data belum lengkap.";
        }

        return redirect()->route('bk.dashboard')
            ->with('success', $message)
            ->with('siswa_belum_lengkap', $siswaBelumLengkap);
    }

    /**
     * Tampilkan detail tren satu siswa (untuk Line Chart).
     */
    public function detailSiswa(Siswa $siswa)
    {
        $riwayatEws = HasilPrediksiEws::where('siswa_id', $siswa->id)
            ->orderBy('tanggal_analisis')
            ->get();

        $riwayatAkademik = AkademikAbsensi::where('siswa_id', $siswa->id)
            ->orderBy('created_at')
            ->get();

        $tindakLanjut = TindakLanjutBk::with('guruBk')
            ->where('siswa_id', $siswa->id)
            ->orderByDesc('tanggal_konseling')
            ->get();

        // Data untuk Line Chart tren risiko
        $chartData = [
            'labels'         => $riwayatEws->pluck('semester'),
            'level_prioritas'=> $riwayatEws->pluck('level_prioritas'),
            'rata_rata_nilai' => $riwayatAkademik->pluck('rata_rata_nilai'),
        ];

        return view('bk.siswa.detail', compact(
            'siswa', 'riwayatEws', 'riwayatAkademik', 'tindakLanjut', 'chartData'
        ));
    }

    // ─── Private Helpers ───────────────────────────────────────────────────────

    private function semesterAktif(): string
    {
        $bulan  = now()->month;
        $tahun  = now()->year;
        $jenis  = ($bulan >= 7) ? 'Ganjil' : 'Genap';
        $ta     = ($bulan >= 7) ? "{$tahun}/" . ($tahun + 1) : ($tahun - 1) . "/{$tahun}";
        return "{$jenis} {$ta}";
    }

    private function alasanDataBelumLengkap($akademik, $kuesioner): string
    {
        $alasan = [];
        if (! $akademik)  $alasan[] = 'Data nilai & absensi belum diinput';
        if (! $kuesioner) $alasan[] = 'Kuesioner psikososial belum diisi';
        return implode(', ', $alasan);
    }
}

