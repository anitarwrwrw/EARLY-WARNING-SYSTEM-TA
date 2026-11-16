<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Kelas;
use App\Models\Siswa;
use App\Models\User;
use App\Models\PeriodeSemester;
use App\Models\PengaturanSistem;
use Illuminate\Support\Facades\Http;

class AdminDashboardController extends Controller
{
    /**
     * Dashboard ringkasan untuk Admin/Operator:
     * - Statistik Master (Kelas, Siswa, Guru BK, Wali Kelas)
     * - Periode Semester Aktif
     * - Status Koneksi Microservice Flask EWS API
     */
    public function index()
    {
        $totalKelas      = Kelas::count();
        $totalSiswa      = Siswa::where('is_active', true)->count();
        $totalGuruBk     = User::where('role', 'guru_bk')->where('is_active', true)->count();
        $totalWaliKelas  = User::where('role', 'wali_kelas')->where('is_active', true)->count();
        $semesterAktif   = PeriodeSemester::aktif();

        // Cek koneksi Flask API (health check dengan timeout pendek)
        $flaskUrl     = PengaturanSistem::get('flask_api_url', env('FLASK_API_URL', 'http://127.0.0.1:5000'));
        $flaskOnline  = false;
        $flaskMessage = 'Tidak Terhubung';

        try {
            $response = Http::timeout(2)->get("{$flaskUrl}/health");
            if ($response->successful()) {
                $flaskOnline  = true;
                $flaskMessage = 'Online (Terhubung)';
            }
        } catch (\Throwable $e) {
            $flaskOnline  = false;
            $flaskMessage = 'Offline: ' . $e->getMessage();
        }

        return view('admin.dashboard', compact(
            'totalKelas',
            'totalSiswa',
            'totalGuruBk',
            'totalWaliKelas',
            'semesterAktif',
            'flaskOnline',
            'flaskMessage',
            'flaskUrl'
        ));
    }
}
