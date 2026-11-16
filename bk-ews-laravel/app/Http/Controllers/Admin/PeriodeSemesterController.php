<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PeriodeSemester;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PeriodeSemesterController extends Controller
{
    public function index()
    {
        $periodes = PeriodeSemester::orderByDesc('id')->get();
        return view('admin.periode.index', compact('periodes'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama_semester' => 'required|string|max:100',
            'tahun_ajaran'  => 'required|string|max:20',
            'jenis'         => 'required|in:Ganjil,Genap',
            'is_aktif'      => 'nullable|boolean',
        ]);

        DB::transaction(function () use ($validated, $request) {
            $isAktif = $request->boolean('is_aktif');

            if ($isAktif) {
                // Nonaktifkan semester lain
                PeriodeSemester::query()->update(['is_aktif' => false]);
            }

            PeriodeSemester::create([
                'nama_semester'  => $validated['nama_semester'],
                'tahun_ajaran'   => $validated['tahun_ajaran'],
                'jenis'          => $validated['jenis'],
                'is_aktif'       => $isAktif,
                'buka_kuesioner' => false,
            ]);
        });

        return redirect()->route('admin.periode.index')
            ->with('success', "Periode {$validated['nama_semester']} berhasil ditambahkan.");
    }

    public function setAktif(PeriodeSemester $periode)
    {
        DB::transaction(function () use ($periode) {
            PeriodeSemester::query()->update(['is_aktif' => false]);
            $periode->update(['is_aktif' => true]);
        });

        return redirect()->route('admin.periode.index')
            ->with('success', "Semester aktif berhasil diubah ke: {$periode->nama_semester}.");
    }

    public function toggleKuesioner(PeriodeSemester $periode)
    {
        $newState = ! $periode->buka_kuesioner;
        $periode->update(['buka_kuesioner' => $newState]);

        $statusText = $newState ? 'DIBUKA' : 'DITUTUP';
        return redirect()->route('admin.periode.index')
            ->with('success', "Form kuesioner publik untuk {$periode->nama_semester} sekarang: {$statusText}.");
    }

    public function destroy(PeriodeSemester $periode)
    {
        // Cek data terkait
        if ($periode->is_aktif) {
            return back()->with('error', 'Tidak dapat menghapus semester yang sedang aktif.');
        }

        if ($periode->akademikAbsensi()->exists() || $periode->kuesioner()->exists() || $periode->hasilEws()->exists()) {
            return back()->with('error', 'Tidak dapat menghapus semester karena sudah memiliki rekaman nilai, kuesioner, atau riwayat EWS.');
        }

        $nama = $periode->nama_semester;
        $periode->delete();

        return redirect()->route('admin.periode.index')
            ->with('success', "Periode {$nama} berhasil dihapus.");
    }
}
