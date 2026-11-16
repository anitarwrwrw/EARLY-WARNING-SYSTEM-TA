<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Imports\SiswaImport;
use App\Models\Kelas;
use App\Models\Siswa;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\StreamedResponse;

class SiswaController extends Controller
{
    public function index(Request $request)
    {
        $query = Siswa::with('kelas');

        if ($request->filled('kelas_id')) {
            $query->where('kelas_id', $request->kelas_id);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('nama_lengkap', 'like', "%{$search}%")
                  ->orWhere('nis', 'like', "%{$search}%")
                  ->orWhere('nisn', 'like', "%{$search}%");
            });
        }

        $siswaList = $query->orderBy('nama_lengkap')->paginate(20)->withQueryString();
        $kelasList = Kelas::where('is_active', true)->orderBy('tingkat')->orderBy('nama_kelas')->get();

        return view('admin.siswa.index', compact('siswaList', 'kelasList'));
    }

    public function create()
    {
        $kelasList = Kelas::where('is_active', true)->orderBy('tingkat')->orderBy('nama_kelas')->get();
        return view('admin.siswa.create', compact('kelasList'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nisn'          => 'nullable|string|max:20|unique:siswa,nisn',
            'nis'           => 'required|string|max:20|unique:siswa,nis',
            'nama_lengkap'  => 'required|string|max:255',
            'kelas_id'      => 'required|exists:kelas,id',
            'jenis_kelamin' => 'required|in:L,P',
            'nama_ortu'     => 'nullable|string|max:255',
            'no_wa_ortu'    => 'nullable|string|max:20',
            'alamat'        => 'nullable|string',
            'tanggal_lahir' => 'nullable|date',
        ]);

        Siswa::create($validated);

        return redirect()->route('admin.siswa.index')
            ->with('success', "Siswa {$validated['nama_lengkap']} berhasil ditambahkan.");
    }

    public function edit(Siswa $siswa)
    {
        $kelasList = Kelas::where('is_active', true)->orderBy('tingkat')->orderBy('nama_kelas')->get();
        return view('admin.siswa.edit', compact('siswa', 'kelasList'));
    }

    public function update(Request $request, Siswa $siswa)
    {
        $validated = $request->validate([
            'nisn'          => ['nullable', 'string', 'max:20', Rule::unique('siswa', 'nisn')->ignore($siswa->id)],
            'nis'           => ['required', 'string', 'max:20', Rule::unique('siswa', 'nis')->ignore($siswa->id)],
            'nama_lengkap'  => 'required|string|max:255',
            'kelas_id'      => 'required|exists:kelas,id',
            'jenis_kelamin' => 'required|in:L,P',
            'nama_ortu'     => 'nullable|string|max:255',
            'no_wa_ortu'    => 'nullable|string|max:20',
            'alamat'        => 'nullable|string',
            'tanggal_lahir' => 'nullable|date',
            'is_active'     => 'nullable|boolean',
        ]);

        $validated['is_active'] = $request->boolean('is_active');

        $siswa->update($validated);

        return redirect()->route('admin.siswa.index')
            ->with('success', "Data siswa {$siswa->nama_lengkap} berhasil diperbarui.");
    }

    public function destroy(Siswa $siswa)
    {
        $nama = $siswa->nama_lengkap;
        $siswa->delete();

        return redirect()->route('admin.siswa.index')
            ->with('success', "Siswa {$nama} berhasil dihapus.");
    }

    public function importExcel(Request $request)
    {
        $request->validate([
            'file_excel' => 'required|file|mimes:xlsx,xls,csv|max:5120',
            'kelas_id'   => 'required|exists:kelas,id',
        ]);

        $import = new SiswaImport((int) $request->kelas_id);

        try {
            Excel::import($import, $request->file('file_excel'));

            $imported = $import->getImportedCount();
            $updated  = $import->getUpdatedCount();

            return redirect()->route('admin.siswa.index', ['kelas_id' => $request->kelas_id])
                ->with('success', "Import berhasil: {$imported} siswa baru ditambahkan, {$updated} siswa diperbarui.");
        } catch (\Throwable $e) {
            return back()->with('error', 'Gagal mengimpor file: ' . $e->getMessage());
        }
    }

    public function unduhTemplate(): StreamedResponse
    {
        $headers = [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => 'attachment; filename="template_import_siswa.csv"',
        ];

        return response()->stream(function () {
            $handle = fopen('php://output', 'w');
            // Header baris pertama
            fputcsv($handle, ['nisn', 'nis', 'nama_lengkap', 'jenis_kelamin', 'nama_ortu', 'no_wa_ortu', 'alamat', 'tanggal_lahir']);
            // Contoh baris data
            fputcsv($handle, ['0051234567', '2025001', 'Ahmad Dani Pratama', 'L', 'Bapak Slamet', '081234567890', 'Jl. Merdeka No. 10', '2008-05-15']);
            fputcsv($handle, ['0057654321', '2025002', 'Siti Ayu Wulandari', 'P', 'Ibu Rahayu', '081298765432', 'Jl. Sudirman No. 5', '2008-09-20']);
            fclose($handle);
        }, 200, $headers);
    }
}
