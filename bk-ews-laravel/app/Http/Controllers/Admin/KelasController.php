<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Kelas;
use App\Models\User;
use Illuminate\Http\Request;

class KelasController extends Controller
{
    public function index()
    {
        $kelasList  = Kelas::with('waliKelas')->withCount('siswa')->orderBy('tingkat')->orderBy('nama_kelas')->get();
        $waliList   = User::where('role', 'wali_kelas')->where('is_active', true)->orderBy('name')->get();

        return view('admin.kelas.index', compact('kelasList', 'waliList'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama_kelas'    => 'required|string|max:50',
            'jurusan'       => 'required|string|max:100',
            'tingkat'       => 'required|string|max:10',
            'wali_kelas_id' => 'nullable|exists:users,id',
        ]);

        Kelas::create($validated);

        return redirect()->route('admin.kelas.index')
            ->with('success', "Kelas {$validated['tingkat']} {$validated['nama_kelas']} berhasil ditambahkan.");
    }

    public function update(Request $request, Kelas $kelas)
    {
        $validated = $request->validate([
            'nama_kelas'    => 'required|string|max:50',
            'jurusan'       => 'required|string|max:100',
            'tingkat'       => 'required|string|max:10',
            'wali_kelas_id' => 'nullable|exists:users,id',
            'is_active'     => 'nullable|boolean',
        ]);

        $validated['is_active'] = $request->boolean('is_active');

        $kelas->update($validated);

        return redirect()->route('admin.kelas.index')
            ->with('success', "Kelas {$kelas->tingkat} {$kelas->nama_kelas} berhasil diperbarui.");
    }

    public function destroy(Kelas $kelas)
    {
        if ($kelas->siswa()->exists()) {
            return back()->with('error', 'Kelas tidak dapat dihapus karena masih memiliki data siswa terdaftar.');
        }

        $nama = "{$kelas->tingkat} {$kelas->nama_kelas}";
        $kelas->delete();

        return redirect()->route('admin.kelas.index')
            ->with('success', "Kelas {$nama} berhasil dihapus.");
    }
}
