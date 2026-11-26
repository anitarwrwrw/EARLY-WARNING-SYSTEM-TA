<?php

namespace App\Http\Controllers\BK;

use App\Http\Controllers\Controller;
use App\Models\Kelas;
use App\Models\PeriodeSemester;
use App\Models\PertanyaanKuesioner;
use Illuminate\Http\Request;

class KuesionerMasterController extends Controller
{
    /**
     * Tampilkan halaman monitoring pengisian kuesioner global & manajemen pertanyaan kuesioner.
     */
    public function index(Request $request)
    {
        $pertanyaanList = PertanyaanKuesioner::orderBy('no_urut')->get();

        $semesterAktif = PeriodeSemester::aktif();
        $semesterId    = $request->input('semester_id', $semesterAktif?->id);
        $periode       = $semesterId ? PeriodeSemester::find($semesterId) : $semesterAktif;
        $semuaPeriode  = PeriodeSemester::orderByDesc('id')->get();

        // Monitoring pengisian kuesioner per kelas untuk semester terpilih
        $monitoringKelas = collect();
        if ($periode) {
            $monitoringKelas = Kelas::where('is_active', true)
                ->with('waliKelas')
                ->withCount(['siswa as total_siswa' => fn($q) => $q->where('is_active', true)])
                ->withCount(['siswa as sudah_isi' => function ($q) use ($periode) {
                    $q->where('is_active', true)
                        ->whereHas('kuesioner', fn($kq) => $kq->where('semester_id', $periode->id));
                }])
                ->orderBy('tingkat')
                ->orderBy('nama_kelas')
                ->get()
                ->map(function ($k) {
                    $k->belum_isi  = max(0, $k->total_siswa - $k->sudah_isi);
                    $k->persentase = $k->total_siswa > 0
                        ? round(($k->sudah_isi / $k->total_siswa) * 100)
                        : 0;
                    return $k;
                });
        }

        $totalSiswaAll = $monitoringKelas->sum('total_siswa');
        $sudahIsiAll   = $monitoringKelas->sum('sudah_isi');

        return view('bk.kuesioner.index', compact(
            'pertanyaanList', 'periode', 'semuaPeriode',
            'monitoringKelas', 'totalSiswaAll', 'sudahIsiAll'
        ));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'no_urut'    => 'required|integer|min:1|max:100',
            'aspek'      => 'required|in:motivasi,keluarga,keamanan',
            'pernyataan' => 'required|string|max:1000',
            'is_reverse' => 'nullable|boolean',
            'is_active'  => 'nullable|boolean',
        ]);

        PertanyaanKuesioner::create([
            'no_urut'    => $validated['no_urut'],
            'aspek'      => $validated['aspek'],
            'pernyataan' => $validated['pernyataan'],
            'is_reverse' => $request->boolean('is_reverse'),
            'is_active'  => $request->has('is_active') ? $request->boolean('is_active') : true,
        ]);

        return redirect()->route('bk.kuesioner.index')
            ->with('success', 'Pertanyaan kuesioner berhasil ditambahkan.');
    }

    public function update(Request $request, PertanyaanKuesioner $pertanyaan)
    {
        $validated = $request->validate([
            'no_urut'    => 'required|integer|min:1|max:100',
            'aspek'      => 'required|in:motivasi,keluarga,keamanan',
            'pernyataan' => 'required|string|max:1000',
            'is_reverse' => 'nullable|boolean',
            'is_active'  => 'nullable|boolean',
        ]);

        $pertanyaan->update([
            'no_urut'    => $validated['no_urut'],
            'aspek'      => $validated['aspek'],
            'pernyataan' => $validated['pernyataan'],
            'is_reverse' => $request->boolean('is_reverse'),
            'is_active'  => $request->boolean('is_active'),
        ]);

        return redirect()->route('bk.kuesioner.index')
            ->with('success', "Pertanyaan no. {$pertanyaan->no_urut} berhasil diperbarui.");
    }

    public function destroy(PertanyaanKuesioner $pertanyaan)
    {
        $no = $pertanyaan->no_urut;
        $pertanyaan->delete();

        return redirect()->route('bk.kuesioner.index')
            ->with('success', "Pertanyaan no. {$no} berhasil dihapus.");
    }
}
