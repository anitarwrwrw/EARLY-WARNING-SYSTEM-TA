<?php

namespace App\Http\Controllers\WaliKelas;

use App\Http\Controllers\Controller;
use App\Models\AkademikAbsensi;
use App\Models\Siswa;
use App\Models\Kelas;
use App\Models\HasilPrediksiEws;
use App\Models\PeriodeSemester;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\AkademikAbsensiImport;
use Symfony\Component\HttpFoundation\StreamedResponse;

class WaliKelasController extends Controller
{
    /**
     * Dashboard Wali Kelas:
     * - Statistik monitoring kuesioner kelasnya
     * - Tabel pemetaan EWS khusus siswa di kelasnya
     * - Generator link share kuesioner ke WhatsApp grup
     */
    public function dashboard(Request $request)
    {
        $user  = Auth::user();
        $kelas = Kelas::where('wali_kelas_id', $user->id)->first();

        if (! $kelas) {
            return view('wali.dashboard', [
                'kelas' => null,
                'pesan' => 'Anda belum ditugaskan sebagai wali kelas untuk kelas manapun. Hubungi Administrator.',
            ]);
        }

        $semesterAktif = PeriodeSemester::aktif();
        $semesterId    = $request->input('semester_id', $semesterAktif?->id);
        $periode       = $semesterId ? PeriodeSemester::find($semesterId) : $semesterAktif;

        // Ambil hasil EWS siswa di kelas ini untuk semester terpilih
        $hasilEws = collect();
        if ($periode) {
            $hasilEws = HasilPrediksiEws::with('siswa')
                ->whereHas('siswa', fn($q) => $q->where('kelas_id', $kelas->id))
                ->where('semester_id', $periode->id)
                ->whereIn('id', function ($query) use ($periode, $kelas) {
                    $query->selectRaw('MAX(h.id)')
                        ->from('hasil_prediksi_ews as h')
                        ->join('siswa as s', 'h.siswa_id', '=', 's.id')
                        ->where('h.semester_id', $periode->id)
                        ->where('s.kelas_id', $kelas->id)
                        ->groupBy('h.siswa_id');
                })
                ->orderBy('level_prioritas')
                ->get();
        }

        // Monitoring status pengisian kuesioner siswa di kelas perwalian
        $siswaStatusKuesioner = $kelas->siswa()
            ->where('is_active', true)
            ->with(['kuesioner' => function ($q) use ($periode) {
                if ($periode) {
                    $q->where('semester_id', $periode->id);
                }
            }])
            ->orderBy('nama_lengkap')
            ->get()
            ->map(function ($s) {
                $s->sudah_isi_kuesioner = $s->kuesioner->isNotEmpty();
                $s->tanggal_isi = $s->kuesioner->first()?->tanggal_isi;
                return $s;
            });

        $stats = [
            'total_siswa'     => $siswaStatusKuesioner->count(),
            'sudah_kuesioner' => $siswaStatusKuesioner->where('sudah_isi_kuesioner', true)->count(),
            'belum_kuesioner' => $siswaStatusKuesioner->where('sudah_isi_kuesioner', false)->count(),
            'risiko_tinggi'   => $hasilEws->where('status_risiko', 'Risiko Tinggi')->count(),
            'total_anomali'   => $hasilEws->where('is_anomali', true)->count(),
        ];

        // Generator Link Share WhatsApp
        $linkKuesioner = route('kuesioner.publik', ['kelas_id' => $kelas->id]);
        $pesanWaText   = "Assalamu'alaikum wr. wb.\n\nKepada seluruh siswa kelas *{$kelas->tingkat} {$kelas->nama_kelas}*, mohon segera mengisi Kuesioner Kesejahteraan & Kenyamanan Belajar Siswa melalui tautan berikut:\n\n🔗 {$linkKuesioner}\n\n*Catatan:* Siapkan NISN Anda untuk verifikasi identitas. Terima kasih atas kerja samanya.\n- Wali Kelas ({$user->name})";
        $waShareUrl    = "https://api.whatsapp.com/send?text=" . urlencode($pesanWaText);

        $semuaPeriode = PeriodeSemester::orderByDesc('id')->get();

        return view('wali.dashboard', compact(
            'kelas', 'hasilEws', 'siswaStatusKuesioner', 'stats',
            'periode', 'semesterAktif', 'semuaPeriode', 'linkKuesioner', 'waShareUrl'
        ));
    }

    /**
     * Halaman form input nilai & absensi per siswa di kelasnya.
     */
    public function inputNilai(Request $request)
    {
        $user  = Auth::user();
        $kelas = Kelas::where('wali_kelas_id', $user->id)->first();

        if (! $kelas) {
            return redirect()->route('wali.dashboard')->with('error', 'Anda belum ditugaskan sebagai wali kelas.');
        }

        $semesterAktif = PeriodeSemester::aktif();
        $semesterId    = $request->input('semester_id', $semesterAktif?->id);
        $periode       = $semesterId ? PeriodeSemester::find($semesterId) : $semesterAktif;

        $siswaList = collect();
        if ($periode) {
            $siswaList = $kelas->siswa()->where('is_active', true)->with([
                'akademikAbsensi' => fn($q) => $q->where('semester_id', $periode->id),
            ])->orderBy('nama_lengkap')->get();
        }

        $semuaPeriode = PeriodeSemester::orderByDesc('id')->get();

        return view('wali.input-nilai', compact('kelas', 'siswaList', 'periode', 'semesterAktif', 'semuaPeriode'));
    }

    /**
     * Simpan/update data nilai & absensi satu siswa secara manual.
     */
    public function simpanNilai(Request $request)
    {
        $validated = $request->validate([
            'siswa_id'     => 'required|exists:siswa,id',
            'semester_id'  => 'required|exists:periode_semester,id',
            'rata_nilai'   => 'required|numeric|between:0,100',
            'jumlah_alpha' => 'required|integer|min:0',
            'jumlah_izin'  => 'required|integer|min:0',
            'jumlah_sakit' => 'required|integer|min:0',
        ]);

        $semSebelumnya = PeriodeSemester::where('id', '<', $validated['semester_id'])->latest('id')->first();
        $trenNilai     = 0.0;

        if ($semSebelumnya) {
            $nilaiSebelumnya = AkademikAbsensi::where('siswa_id', $validated['siswa_id'])
                ->where('semester_id', $semSebelumnya->id)
                ->value('rata_nilai');

            if ($nilaiSebelumnya !== null) {
                $trenNilai = round($validated['rata_nilai'] - (float)$nilaiSebelumnya, 2);
            }
        }

        AkademikAbsensi::updateOrCreate(
            ['siswa_id' => $validated['siswa_id'], 'semester_id' => $validated['semester_id']],
            [
                'input_oleh'   => Auth::id(),
                'rata_nilai'   => $validated['rata_nilai'],
                'tren_nilai'   => $trenNilai,
                'jumlah_alpha' => $validated['jumlah_alpha'],
                'jumlah_izin'  => $validated['jumlah_izin'],
                'jumlah_sakit' => $validated['jumlah_sakit'],
            ]
        );

        return back()->with('success', 'Data nilai & absensi berhasil disimpan.');
    }

    /**
     * Import nilai & absensi dari file Excel/CSV.
     */
    public function importExcel(Request $request)
    {
        $request->validate([
            'file_excel'  => 'required|file|mimes:xlsx,xls,csv|max:5120',
            'semester_id' => 'required|exists:periode_semester,id',
        ]);

        try {
            $import = new AkademikAbsensiImport((int)$request->semester_id, Auth::id());
            Excel::import($import, $request->file('file_excel'));

            $imported = $import->getImportedCount();
            $updated  = $import->getUpdatedCount();

            return back()->with('success', "Import berhasil! {$imported} data baru disimpan, {$updated} data diperbarui.");
        } catch (\Throwable $e) {
            return back()->with('error', 'Gagal mengimpor file: ' . $e->getMessage());
        }
    }

    /**
     * Unduh template CSV untuk import nilai & absensi kelas.
     */
    public function unduhTemplate(): StreamedResponse
    {
        $user  = Auth::user();
        $kelas = Kelas::where('wali_kelas_id', $user->id)->first();
        $siswa = $kelas ? $kelas->siswa()->where('is_active', true)->orderBy('nama_lengkap')->get() : collect();

        $namaFile = 'template_nilai_' . ($kelas ? str_replace(' ', '_', $kelas->nama_kelas) : 'kelas') . '.csv';

        $headers = [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$namaFile}\"",
        ];

        return response()->stream(function () use ($siswa) {
            $handle = fopen('php://output', 'w');
            // Tambahkan UTF-8 BOM agar rapi di Microsoft Excel Windows
            fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF));
            fputcsv($handle, ['nis', 'nama_siswa', 'rata_nilai', 'jumlah_alpha', 'jumlah_izin', 'jumlah_sakit']);

            if ($siswa->isNotEmpty()) {
                foreach ($siswa as $s) {
                    fputcsv($handle, [$s->nis, $s->nama_lengkap, '', '0', '0', '0']);
                }
            } else {
                fputcsv($handle, ['20240001', 'Contoh Siswa 1', '85.5', '0', '1', '0']);
                fputcsv($handle, ['20240002', 'Contoh Siswa 2', '72.0', '4', '0', '2']);
            }
            fclose($handle);
        }, 200, $headers);
    }
}
