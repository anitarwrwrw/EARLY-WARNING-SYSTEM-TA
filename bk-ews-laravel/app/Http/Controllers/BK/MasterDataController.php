<?php

namespace App\Http\Controllers\BK;

use App\Http\Controllers\Controller;
use App\Models\Siswa;
use App\Models\Kelas;
use App\Models\User;
use App\Models\Pelanggaran;
use App\Models\KategoriPelanggaran;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MasterDataController extends Controller
{
    // ════════════════════════════════════════════════════════════════
    //  DATA SISWA (READ-ONLY UNTUK GURU BK)
    // ════════════════════════════════════════════════════════════════

    public function indexSiswa(Request $request)
    {
        $query = Siswa::with('kelas')
            ->where('is_active', true);

        if ($request->filled('kelas_id')) {
            $query->where('kelas_id', $request->kelas_id);
        }
        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('nama_lengkap', 'like', "%{$request->search}%")
                  ->orWhere('nis', 'like', "%{$request->search}%")
                  ->orWhere('nisn', 'like', "%{$request->search}%");
            });
        }

        $siswa  = $query->orderBy('nama_lengkap')->paginate(20)->withQueryString();
        $kelas  = Kelas::where('is_active', true)->orderBy('tingkat')->orderBy('nama_kelas')->get();

        return view('bk.master.siswa.index', compact('siswa', 'kelas'));
    }

    // ════════════════════════════════════════════════════════════════
    //  DATA KELAS (READ-ONLY UNTUK GURU BK)
    // ════════════════════════════════════════════════════════════════

    public function indexKelas()
    {
        $kelas = Kelas::with('waliKelas')
            ->withCount('siswa')
            ->where('is_active', true)
            ->orderBy('tingkat')
            ->orderBy('nama_kelas')
            ->get();

        return view('bk.master.kelas.index', compact('kelas'));
    }

    // ════════════════════════════════════════════════════════════════
    //  PENCATATAN PELANGGARAN & KATEGORI (GURU BK)
    // ════════════════════════════════════════════════════════════════

    public function indexPelanggaran(Request $request)
    {
        $query = Pelanggaran::with(['siswa.kelas', 'dicatatOleh', 'kategori'])
            ->orderByDesc('tanggal');

        if ($request->filled('siswa_id')) {
            $query->where('siswa_id', $request->siswa_id);
        }

        $pelanggaran   = $query->paginate(20)->withQueryString();
        $siswaList     = Siswa::where('is_active', true)->orderBy('nama_lengkap')->get();
        $kategoriList  = KategoriPelanggaran::orderBy('kategori')->orderBy('nama')->get();

        return view('bk.master.pelanggaran.index', compact('pelanggaran', 'siswaList', 'kategoriList'));
    }

    public function storePelanggaran(Request $request)
    {
        $validated = $request->validate([
            'siswa_id'                => 'required|exists:siswa,id',
            'kategori_pelanggaran_id' => 'required|exists:kategori_pelanggaran,id',
            'tanggal'                 => 'required|date|before_or_equal:today',
            'keterangan'              => 'nullable|string|max:500',
        ]);

        $kategori = KategoriPelanggaran::findOrFail($validated['kategori_pelanggaran_id']);

        Pelanggaran::create([
            'siswa_id'                => $validated['siswa_id'],
            'dicatat_oleh'            => Auth::id(),
            'kategori_pelanggaran_id' => $kategori->id,
            'tanggal'                 => $validated['tanggal'],
            'jenis_pelanggaran'       => $kategori->nama,
            'poin'                    => $kategori->bobot_poin,
            'keterangan'              => $validated['keterangan'] ?? null,
        ]);

        return back()->with('success', "Pelanggaran '{$kategori->nama}' ({$kategori->bobot_poin} poin) berhasil dicatat.");
    }

    public function destroyPelanggaran(Pelanggaran $pelanggaran)
    {
        $pelanggaran->delete();
        return back()->with('success', 'Catatan pelanggaran berhasil dihapus.');
    }

    // ════════════════════════════════════════════════════════════════
    //  KELOLA KATEGORI POIN PELANGGARAN (GURU BK)
    // ════════════════════════════════════════════════════════════════

    public function indexKategoriPelanggaran()
    {
        $kategoriList = KategoriPelanggaran::withCount('pelanggaran')
            ->orderByRaw("FIELD(kategori, 'Ringan', 'Sedang', 'Berat')")
            ->orderBy('nama')
            ->get();

        return view('bk.master.kategori.index', compact('kategoriList'));
    }

    public function storeKategoriPelanggaran(Request $request)
    {
        $validated = $request->validate([
            'nama'       => 'required|string|max:255',
            'kategori'   => 'required|in:Ringan,Sedang,Berat',
            'bobot_poin' => 'required|integer|min:1|max:100',
            'keterangan' => 'nullable|string|max:500',
        ]);

        KategoriPelanggaran::create($validated);

        return redirect()->route('bk.master.kategori.index')
            ->with('success', "Kategori '{$validated['nama']}' (+{$validated['bobot_poin']} poin) berhasil ditambahkan.");
    }

    public function updateKategoriPelanggaran(Request $request, KategoriPelanggaran $kategori)
    {
        $validated = $request->validate([
            'nama'       => 'required|string|max:255',
            'kategori'   => 'required|in:Ringan,Sedang,Berat',
            'bobot_poin' => 'required|integer|min:1|max:100',
            'keterangan' => 'nullable|string|max:500',
        ]);

        $kategori->update($validated);

        return redirect()->route('bk.master.kategori.index')
            ->with('success', "Kategori '{$kategori->nama}' berhasil diperbarui.");
    }

    public function destroyKategoriPelanggaran(KategoriPelanggaran $kategori)
    {
        if ($kategori->pelanggaran()->exists()) {
            return back()->with('error', 'Kategori tidak dapat dihapus karena sudah digunakan pada catatan pelanggaran siswa.');
        }

        $nama = $kategori->nama;
        $kategori->delete();

        return redirect()->route('bk.master.kategori.index')
            ->with('success', "Kategori '{$nama}' berhasil dihapus.");
    }
}
