<?php

namespace App\Http\Controllers\WaliKelas;

use App\Http\Controllers\Controller;
use App\Models\AkademikAbsensi;
use App\Models\Siswa;
use App\Models\Kelas;
use App\Models\HasilPrediksiEws;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\AkademikAbsensiImport;

class WaliKelasController extends Controller
{
    /**
     * Dashboard wali kelas: tampilkan pemetaan EWS kelasnya saja.
     */
    public function dashboard(Request $request)
    {
        $user       = Auth::user();
        $kelas      = Kelas::where('wali_kelas_id', $user->id)->first();

        if (! $kelas) {
            return view('wali.dashboard', ['kelas' => null, 'pesan' => 'Anda belum ditugaskan sebagai wali kelas.']);
        }

        $semester = $request->input('semester', $this->semesterAktif());

        // Hasil EWS siswa di kelas ini
        $hasilEws = HasilPrediksiEws::with('siswa')
            ->whereHas('siswa', fn($q) => $q->where('kelas_id', $kelas->id))
            ->where('semester', $semester)
            ->whereIn('id', function ($query) use ($semester, $kelas) {
                $query->selectRaw('MAX(h.id)')
                    ->from('hasil_prediksi_ews as h')
                    ->join('siswa as s', 'h.siswa_id', '=', 's.id')
                    ->where('h.semester', $semester)
                    ->where('s.kelas_id', $kelas->id)
                    ->groupBy('h.siswa_id');
            })
            ->orderBy('level_prioritas')
            ->get();

        // Status kuesioner per siswa
        $siswaDenganStatusKuesioner = $kelas->siswa()
            ->with(['kuesioner' => fn($q) => $q->where('semester', $semester)])
            ->where('is_active', true)
            ->get()
            ->map(function ($siswa) {
                $siswa->sudah_isi_kuesioner = $siswa->kuesioner->isNotEmpty();
                return $siswa;
            });

        $stats = [
            'total_siswa'       => $kelas->siswa()->where('is_active', true)->count(),
            'sudah_kuesioner'   => $siswaDenganStatusKuesioner->where('sudah_isi_kuesioner', true)->count(),
            'belum_kuesioner'   => $siswaDenganStatusKuesioner->where('sudah_isi_kuesioner', false)->count(),
        ];

        return view('wali.dashboard', compact('kelas', 'hasilEws', 'siswaDenganStatusKuesioner', 'stats', 'semester'));
    }

    /**
     * Form input nilai & absensi per siswa (manual).
     */
    public function inputNilai(Request $request)
    {
        $user  = Auth::user();
        $kelas = Kelas::where('wali_kelas_id', $user->id)->first();

        if (! $kelas) {
            return redirect()->route('wali.dashboard')->with('error', 'Anda belum ditugaskan sebagai wali kelas.');
        }

        $semester = $request->input('semester', $this->semesterAktif());
        $siswa    = $kelas->siswa()->where('is_active', true)->with([
            'akademikAbsensi' => fn($q) => $q->where('semester', $semester),
        ])->get();

        return view('wali.input-nilai', compact('kelas', 'siswa', 'semester'));
    }

    /**
     * Simpan/update nilai & absensi per siswa satu per satu.
     */
    public function simpanNilai(Request $request)
    {
        $validated = $request->validate([
            'siswa_id'        => 'required|exists:siswa,id',
            'semester'        => 'required|string|max:20',
            'rata_rata_nilai' => 'required|numeric|min:0|max:100',
            'jumlah_alpha'    => 'required|integer|min:0',
            'jumlah_izin'     => 'required|integer|min:0',
            'jumlah_sakit'    => 'required|integer|min:0',
        ]);

        // Hitung tren nilai: selisih vs semester sebelumnya
        $semesterSebelumnya = $this->semesterSebelumnya($validated['semester']);
        $akademikSebelumnya = AkademikAbsensi::where('siswa_id', $validated['siswa_id'])
            ->where('semester', $semesterSebelumnya)
            ->first();

        $trenNilai = $akademikSebelumnya
            ? round($validated['rata_rata_nilai'] - $akademikSebelumnya->rata_rata_nilai, 2)
            : 0;

        AkademikAbsensi::updateOrCreate(
            ['siswa_id' => $validated['siswa_id'], 'semester' => $validated['semester']],
            [
                'input_oleh'      => Auth::id(),
                'rata_rata_nilai' => $validated['rata_rata_nilai'],
                'tren_nilai'      => $trenNilai,
                'jumlah_alpha'    => $validated['jumlah_alpha'],
                'jumlah_izin'     => $validated['jumlah_izin'],
                'jumlah_sakit'    => $validated['jumlah_sakit'],
            ]
        );

        return back()->with('success', 'Data nilai berhasil disimpan.');
    }

    /**
     * Import nilai & absensi dari file Excel/CSV.
     */
    public function importExcel(Request $request)
    {
        $request->validate([
            'file_excel' => 'required|file|mimes:xlsx,xls,csv|max:5120',
            'semester'   => 'required|string|max:20',
        ]);

        try {
            Excel::import(
                new AkademikAbsensiImport($request->semester, Auth::id()),
                $request->file('file_excel')
            );
            return back()->with('success', 'Data nilai & absensi berhasil diimport dari Excel.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal mengimport file: ' . $e->getMessage());
        }
    }

    /**
     * Unduh template CSV/Excel untuk import nilai & absensi kelas.
     */
    public function unduhTemplate()
    {
        $user  = Auth::user();
        $kelas = Kelas::where('wali_kelas_id', $user->id)->first();
        $siswa = $kelas ? $kelas->siswa()->where('is_active', true)->orderBy('nama_lengkap')->get() : collect();

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="template_nilai_absensi_' . ($kelas ? str_replace(' ', '_', $kelas->nama_kelas) : 'kelas') . '.csv"',
        ];

        $callback = function () use ($siswa) {
            $handle = fopen('php://output', 'w');
            // Menambahkan BOM UTF-8 agar file CSV terbaca rapi di Microsoft Excel Windows
            fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF));
            fputcsv($handle, ['nis', 'nama_siswa', 'rata_rata_nilai', 'jumlah_alpha', 'jumlah_izin', 'jumlah_sakit']);

            if ($siswa->isNotEmpty()) {
                foreach ($siswa as $s) {
                    fputcsv($handle, [$s->nis, $s->nama_lengkap, '', '0', '0', '0']);
                }
            } else {
                fputcsv($handle, ['20240001', 'Contoh Siswa 1', '85.5', '0', '1', '0']);
                fputcsv($handle, ['20240002', 'Contoh Siswa 2', '72.0', '4', '0', '2']);
            }
            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }

    // ─── Helpers ──────────────────────────────────────────────────────────────

    private function semesterAktif(): string
    {
        $bulan  = now()->month;
        $tahun  = now()->year;
        $jenis  = ($bulan >= 7) ? 'Ganjil' : 'Genap';
        $ta     = ($bulan >= 7) ? "{$tahun}/" . ($tahun + 1) : ($tahun - 1) . "/{$tahun}";
        return "{$jenis} {$ta}";
    }

    private function semesterSebelumnya(string $semester): string
    {
        [$jenis, $ta] = explode(' ', $semester, 2);
        [$tahunMulai] = explode('/', $ta);
        if ($jenis === 'Ganjil') {
            $tahunSebelumnya = $tahunMulai - 1;
            return "Genap {$tahunSebelumnya}/{$tahunMulai}";
        } else {
            return "Ganjil {$ta}";
        }
    }
}

