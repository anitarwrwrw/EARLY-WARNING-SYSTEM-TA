<?php

namespace App\Http\Controllers\BK;

use App\Http\Controllers\Controller;
use App\Models\Siswa;
use App\Models\Kelas;
use App\Models\User;
use App\Models\Pelanggaran;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class MasterDataController extends Controller
{
    // ════════════════════════════════════════════════════════════════
    //  MANAJEMEN SISWA
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
                  ->orWhere('nis', 'like', "%{$request->search}%");
            });
        }

        $siswa  = $query->orderBy('nama_lengkap')->paginate(20)->withQueryString();
        $kelas  = Kelas::where('is_active', true)->orderBy('nama_kelas')->get();

        return view('bk.master.siswa.index', compact('siswa', 'kelas'));
    }

    public function createSiswa()
    {
        $kelas = Kelas::where('is_active', true)->orderBy('nama_kelas')->get();
        return view('bk.master.siswa.create', compact('kelas'));
    }

    public function storeSiswa(Request $request)
    {
        $validated = $request->validate([
            'nis'           => 'required|string|max:20|unique:siswa,nis',
            'nama_lengkap'  => 'required|string|max:255',
            'jenis_kelamin' => 'required|in:L,P',
            'kelas_id'      => 'required|exists:kelas,id',
            'nama_ortu'     => 'nullable|string|max:255',
            'no_wa_ortu'    => 'nullable|string|max:20',
            'alamat'        => 'nullable|string',
            'tanggal_lahir' => 'nullable|date',
            'buat_akun'     => 'boolean',
            'password'      => 'nullable|required_if:buat_akun,1|min:6',
        ]);

        // Buat akun login siswa jika diminta
        $userId = null;
        if ($request->boolean('buat_akun')) {
            $user = User::create([
                'name'     => $validated['nama_lengkap'],
                'username' => $validated['nis'],
                'password' => Hash::make($validated['password'] ?? $validated['nis']),
                'role'     => 'siswa',
            ]);
            $userId = $user->id;
        }

        Siswa::create([
            'user_id'       => $userId,
            'kelas_id'      => $validated['kelas_id'],
            'nis'           => $validated['nis'],
            'nama_lengkap'  => $validated['nama_lengkap'],
            'jenis_kelamin' => $validated['jenis_kelamin'],
            'nama_ortu'     => $validated['nama_ortu'] ?? null,
            'no_wa_ortu'    => $validated['no_wa_ortu'] ?? null,
            'alamat'        => $validated['alamat'] ?? null,
            'tanggal_lahir' => $validated['tanggal_lahir'] ?? null,
        ]);

        return redirect()->route('bk.master.siswa.index')
            ->with('success', "Siswa {$validated['nama_lengkap']} berhasil ditambahkan.");
    }

    public function editSiswa(Siswa $siswa)
    {
        $kelas = Kelas::where('is_active', true)->orderBy('nama_kelas')->get();
        return view('bk.master.siswa.edit', compact('siswa', 'kelas'));
    }

    public function updateSiswa(Request $request, Siswa $siswa)
    {
        $validated = $request->validate([
            'nis'           => ['required', 'string', 'max:20', Rule::unique('siswa', 'nis')->ignore($siswa->id)],
            'nama_lengkap'  => 'required|string|max:255',
            'jenis_kelamin' => 'required|in:L,P',
            'kelas_id'      => 'required|exists:kelas,id',
            'nama_ortu'     => 'nullable|string|max:255',
            'no_wa_ortu'    => 'nullable|string|max:20',
            'alamat'        => 'nullable|string',
            'tanggal_lahir' => 'nullable|date',
        ]);

        $siswa->update($validated);

        return redirect()->route('bk.master.siswa.index')
            ->with('success', "Data siswa berhasil diperbarui.");
    }

    public function destroySiswa(Siswa $siswa)
    {
        $siswa->update(['is_active' => false]); // Soft delete (nonaktifkan)
        return back()->with('success', "Siswa {$siswa->nama_lengkap} berhasil dinonaktifkan.");
    }

    // ════════════════════════════════════════════════════════════════
    //  MANAJEMEN KELAS
    // ════════════════════════════════════════════════════════════════

    public function indexKelas()
    {
        $kelas = Kelas::with('waliKelas')
            ->withCount('siswa')
            ->where('is_active', true)
            ->orderBy('tingkat')
            ->orderBy('nama_kelas')
            ->get();

        $waliKelasList = User::where('role', 'wali_kelas')
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('bk.master.kelas.index', compact('kelas', 'waliKelasList'));
    }

    public function storeKelas(Request $request)
    {
        $validated = $request->validate([
            'nama_kelas'     => 'required|string|max:50',
            'jurusan'        => 'required|string|max:100',
            'tingkat'        => 'required|in:X,XI,XII',
            'wali_kelas_id'  => 'nullable|exists:users,id',
        ]);

        Kelas::create($validated);

        return back()->with('success', 'Kelas berhasil ditambahkan.');
    }

    // ════════════════════════════════════════════════════════════════
    //  PENCATATAN PELANGGARAN
    // ════════════════════════════════════════════════════════════════

    public function indexPelanggaran(Request $request)
    {
        $query = Pelanggaran::with(['siswa.kelas', 'dicatatOleh'])
            ->orderByDesc('tanggal');

        if ($request->filled('siswa_id')) {
            $query->where('siswa_id', $request->siswa_id);
        }

        $pelanggaran = $query->paginate(20)->withQueryString();
        $siswa = Siswa::where('is_active', true)->orderBy('nama_lengkap')->get();

        return view('bk.master.pelanggaran.index', compact('pelanggaran', 'siswa'));
    }

    public function storePelanggaran(Request $request)
    {
        $validated = $request->validate([
            'siswa_id'         => 'required|exists:siswa,id',
            'tanggal'          => 'required|date|before_or_equal:today',
            'jenis_pelanggaran'=> 'required|string|max:255',
            'poin'             => 'required|integer|min:0|max:100',
            'keterangan'       => 'nullable|string|max:500',
        ]);

        Pelanggaran::create([
            ...$validated,
            'dicatat_oleh' => Auth::id(),
        ]);

        return back()->with('success', 'Pelanggaran berhasil dicatat.');
    }

    public function destroyPelanggaran(Pelanggaran $pelanggaran)
    {
        $pelanggaran->delete();
        return back()->with('success', 'Catatan pelanggaran berhasil dihapus.');
    }
}

