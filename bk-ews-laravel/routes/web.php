<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\KuesionerPublikController;
use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\PeriodeSemesterController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\KelasController;
use App\Http\Controllers\Admin\SiswaController as AdminSiswaController;
use App\Http\Controllers\Admin\PengaturanController;
use App\Http\Controllers\BK\EwsController;
use App\Http\Controllers\BK\KuesionerMasterController;
use App\Http\Controllers\BK\MasterDataController;
use App\Http\Controllers\BK\TindakLanjutController;
use App\Http\Controllers\WaliKelas\WaliKelasController;

// ─── Halaman Utama ─────────────────────────────────────────────────────────────
Route::get('/', fn() => redirect()->route('login'));

// ─── Kuesioner Siswa (Akses Publik Tanpa Login) ─────────────────────────────────
Route::get('/kuesioner-siswa', [KuesionerPublikController::class, 'index'])->name('kuesioner.publik');
Route::post('/kuesioner-siswa', [KuesionerPublikController::class, 'store'])->name('kuesioner.store');
Route::get('/kuesioner-siswa/sukses', [KuesionerPublikController::class, 'sukses'])->name('kuesioner.sukses');
Route::get('/api/siswa-by-kelas/{kelas}', [KuesionerPublikController::class, 'getSiswaByKelas'])->name('api.siswa-by-kelas');

// ─── Auth Routes ───────────────────────────────────────────────────────────────
Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login'])->name('login.post');
});

Route::post('/logout', [LoginController::class, 'logout'])
    ->middleware('auth')
    ->name('logout');

// ─── 1. ROLE ADMIN (OPERATOR SEKOLAH / TATA USAHA) ────────────────────────────
Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {

    // Dashboard
    Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');

    // Manajemen Periode Semester
    Route::prefix('periode')->name('periode.')->group(function () {
        Route::get('/', [PeriodeSemesterController::class, 'index'])->name('index');
        Route::post('/', [PeriodeSemesterController::class, 'store'])->name('store');
        Route::post('/{periode}/aktif', [PeriodeSemesterController::class, 'setAktif'])->name('set-aktif');
        Route::post('/{periode}/toggle-kuesioner', [PeriodeSemesterController::class, 'toggleKuesioner'])->name('toggle-kuesioner');
        Route::delete('/{periode}', [PeriodeSemesterController::class, 'destroy'])->name('destroy');
    });

    // Manajemen Pengguna (Guru BK & Wali Kelas)
    Route::prefix('users')->name('users.')->group(function () {
        Route::get('/', [UserController::class, 'index'])->name('index');
        Route::post('/', [UserController::class, 'store'])->name('store');
        Route::put('/{user}', [UserController::class, 'update'])->name('update');
        Route::post('/{user}/reset-password', [UserController::class, 'resetPassword'])->name('reset-password');
        Route::delete('/{user}', [UserController::class, 'destroy'])->name('destroy');
    });

    // Manajemen Kelas
    Route::prefix('kelas')->name('kelas.')->group(function () {
        Route::get('/', [KelasController::class, 'index'])->name('index');
        Route::post('/', [KelasController::class, 'store'])->name('store');
        Route::put('/{kelas}', [KelasController::class, 'update'])->name('update');
        Route::delete('/{kelas}', [KelasController::class, 'destroy'])->name('destroy');
    });

    // Manajemen Data Induk Siswa & Import Excel
    Route::prefix('siswa')->name('siswa.')->group(function () {
        Route::get('/', [AdminSiswaController::class, 'index'])->name('index');
        Route::get('/create', [AdminSiswaController::class, 'create'])->name('create');
        Route::post('/', [AdminSiswaController::class, 'store'])->name('store');
        Route::get('/{siswa}/edit', [AdminSiswaController::class, 'edit'])->name('edit');
        Route::put('/{siswa}', [AdminSiswaController::class, 'update'])->name('update');
        Route::delete('/{siswa}', [AdminSiswaController::class, 'destroy'])->name('destroy');
        Route::post('/import', [AdminSiswaController::class, 'importExcel'])->name('import');
        Route::get('/template', [AdminSiswaController::class, 'unduhTemplate'])->name('template');
    });

    // Pengaturan Sistem (Flask URL & WhatsApp Gateway)
    Route::prefix('pengaturan')->name('pengaturan.')->group(function () {
        Route::get('/', [PengaturanController::class, 'index'])->name('index');
        Route::post('/', [PengaturanController::class, 'update'])->name('update');
        Route::post('/test-flask', [PengaturanController::class, 'testFlask'])->name('test-flask');
        Route::post('/test-wa', [PengaturanController::class, 'testWhatsapp'])->name('test-wa');
    });
});

// ─── 2. ROLE GURU BK ──────────────────────────────────────────────────────────
Route::middleware(['auth', 'role:guru_bk'])->prefix('bk')->name('bk.')->group(function () {

    // Dashboard EWS
    Route::get('/dashboard', [EwsController::class, 'dashboard'])->name('dashboard');
    Route::post('/ews/jalankan', [EwsController::class, 'jalankanAnalisis'])->name('ews.jalankan');
    Route::get('/ews/siswa/{siswa}', [EwsController::class, 'detailSiswa'])->name('ews.detail-siswa');

    // Master Data (Read-only Siswa & Kelas, Full CRUD Pelanggaran)
    Route::prefix('master')->name('master.')->group(function () {
        Route::get('/siswa', [MasterDataController::class, 'indexSiswa'])->name('siswa.index');
        Route::get('/siswa/{siswa}', [EwsController::class, 'detailSiswa'])->name('siswa.detail');
        Route::get('/kelas', [MasterDataController::class, 'indexKelas'])->name('kelas.index');

        // Pelanggaran & Kategori
        Route::get('/pelanggaran', [MasterDataController::class, 'indexPelanggaran'])->name('pelanggaran.index');
        Route::post('/pelanggaran', [MasterDataController::class, 'storePelanggaran'])->name('pelanggaran.store');
        Route::delete('/pelanggaran/{pelanggaran}', [MasterDataController::class, 'destroyPelanggaran'])->name('pelanggaran.destroy');

        Route::get('/kategori-pelanggaran', [MasterDataController::class, 'indexKategoriPelanggaran'])->name('kategori.index');
        Route::post('/kategori-pelanggaran', [MasterDataController::class, 'storeKategoriPelanggaran'])->name('kategori.store');
        Route::put('/kategori-pelanggaran/{kategori}', [MasterDataController::class, 'updateKategoriPelanggaran'])->name('kategori.update');
        Route::delete('/kategori-pelanggaran/{kategori}', [MasterDataController::class, 'destroyKategoriPelanggaran'])->name('kategori.destroy');
    });

    // Kelola Pertanyaan Kuesioner & Monitoring Global
    Route::prefix('kuesioner')->name('kuesioner.')->group(function () {
        Route::get('/', [KuesionerMasterController::class, 'index'])->name('index');
        Route::post('/', [KuesionerMasterController::class, 'store'])->name('store');
        Route::put('/{pertanyaan}', [KuesionerMasterController::class, 'update'])->name('update');
        Route::delete('/{pertanyaan}', [KuesionerMasterController::class, 'destroy'])->name('destroy');
    });

    // Tindak Lanjut Konseling
    Route::prefix('tindaklanjut')->name('tindaklanjut.')->group(function () {
        Route::get('/', [TindakLanjutController::class, 'index'])->name('index');
        Route::get('/tambah', [TindakLanjutController::class, 'create'])->name('create');
        Route::post('/', [TindakLanjutController::class, 'store'])->name('store');
        Route::get('/{tindakLanjut}/edit', [TindakLanjutController::class, 'edit'])->name('edit');
        Route::put('/{tindakLanjut}', [TindakLanjutController::class, 'update'])->name('update');
    });

    // WhatsApp
    Route::get('/whatsapp/log', [TindakLanjutController::class, 'logWhatsapp'])->name('whatsapp.log');
    Route::post('/whatsapp/kirim', [TindakLanjutController::class, 'kirimWhatsapp'])->name('whatsapp.kirim');
});

// ─── 3. ROLE WALI KELAS ───────────────────────────────────────────────────────
Route::middleware(['auth', 'role:wali_kelas'])->prefix('wali')->name('wali.')->group(function () {
    Route::get('/dashboard', [WaliKelasController::class, 'dashboard'])->name('dashboard');
    Route::get('/input-nilai', [WaliKelasController::class, 'inputNilai'])->name('input-nilai');
    Route::get('/template-excel', [WaliKelasController::class, 'unduhTemplate'])->name('template-excel');
    Route::post('/simpan-nilai', [WaliKelasController::class, 'simpanNilai'])->name('simpan-nilai');
    Route::post('/import-excel', [WaliKelasController::class, 'importExcel'])->name('import-excel');
});
