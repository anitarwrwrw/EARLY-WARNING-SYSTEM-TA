<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\BK\EwsController;
use App\Http\Controllers\BK\MasterDataController;
use App\Http\Controllers\BK\TindakLanjutController;
use App\Http\Controllers\WaliKelas\WaliKelasController;
use App\Http\Controllers\Siswa\KuesionerController;

// ─── Halaman Utama ─────────────────────────────────────────────────────────────
Route::get('/', fn() => redirect()->route('login'));

// ─── Auth Routes ───────────────────────────────────────────────────────────────
Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login'])->name('login.post');
});

Route::post('/logout', [LoginController::class, 'logout'])
    ->middleware('auth')
    ->name('logout');

// ─── GURU BK ───────────────────────────────────────────────────────────────────
Route::middleware(['auth', 'role:bk'])->prefix('bk')->name('bk.')->group(function () {

    // Dashboard EWS
    Route::get('/dashboard', [EwsController::class, 'dashboard'])->name('dashboard');
    Route::post('/ews/jalankan', [EwsController::class, 'jalankanAnalisis'])->name('ews.jalankan');
    Route::get('/ews/siswa/{siswa}', [EwsController::class, 'detailSiswa'])->name('ews.detail-siswa');

    // Manajemen Data Master - Siswa
    Route::prefix('master')->name('master.')->group(function () {
        Route::get('/siswa', [MasterDataController::class, 'indexSiswa'])->name('siswa.index');
        Route::get('/siswa/tambah', [MasterDataController::class, 'createSiswa'])->name('siswa.create');
        Route::post('/siswa', [MasterDataController::class, 'storeSiswa'])->name('siswa.store');
        Route::get('/siswa/{siswa}/edit', [MasterDataController::class, 'editSiswa'])->name('siswa.edit');
        Route::put('/siswa/{siswa}', [MasterDataController::class, 'updateSiswa'])->name('siswa.update');
        Route::delete('/siswa/{siswa}', [MasterDataController::class, 'destroySiswa'])->name('siswa.destroy');

        // Kelas
        Route::get('/kelas', [MasterDataController::class, 'indexKelas'])->name('kelas.index');
        Route::post('/kelas', [MasterDataController::class, 'storeKelas'])->name('kelas.store');

        // Pelanggaran
        Route::get('/pelanggaran', [MasterDataController::class, 'indexPelanggaran'])->name('pelanggaran.index');
        Route::post('/pelanggaran', [MasterDataController::class, 'storePelanggaran'])->name('pelanggaran.store');
        Route::delete('/pelanggaran/{pelanggaran}', [MasterDataController::class, 'destroyPelanggaran'])->name('pelanggaran.destroy');
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
    Route::post('/whatsapp/kirim', [TindakLanjutController::class, 'kirimWhatsapp'])->name('whatsapp.kirim');
});

// ─── WALI KELAS ────────────────────────────────────────────────────────────────
Route::middleware(['auth', 'role:wali_kelas'])->prefix('wali')->name('wali.')->group(function () {
    Route::get('/dashboard', [WaliKelasController::class, 'dashboard'])->name('dashboard');
    Route::get('/input-nilai', [WaliKelasController::class, 'inputNilai'])->name('input-nilai');
    Route::get('/template-excel', [WaliKelasController::class, 'unduhTemplate'])->name('template-excel');
    Route::post('/simpan-nilai', [WaliKelasController::class, 'simpanNilai'])->name('simpan-nilai');
    Route::post('/import-excel', [WaliKelasController::class, 'importExcel'])->name('import-excel');
});

// ─── SISWA ─────────────────────────────────────────────────────────────────────
Route::middleware(['auth', 'role:siswa'])->prefix('siswa')->name('siswa.')->group(function () {
    Route::get('/kuesioner', [KuesionerController::class, 'index'])->name('kuesioner');
    Route::post('/kuesioner', [KuesionerController::class, 'store'])->name('kuesioner.store');
});
