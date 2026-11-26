@extends('layouts.app')
@section('page-title', 'Dashboard Admin')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold mb-0">Dashboard Admin</h4>
        <small class="text-muted">Ringkasan data master sekolah & status layanan sistem</small>
    </div>
    @if($semesterAktif)
        <span class="badge bg-primary bg-opacity-10 text-primary border border-primary px-3 py-2">
            <i class="bi bi-calendar-check me-1"></i>Semester Aktif: {{ $semesterAktif->nama_semester }}
        </span>
    @else
        <span class="badge bg-warning text-dark px-3 py-2">
            <i class="bi bi-exclamation-triangle me-1"></i>Belum Ada Semester Aktif
        </span>
    @endif
</div>

{{-- Kartu Statistik Master --}}
<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="card stat-card shadow-sm">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="rounded-circle bg-primary bg-opacity-10 d-flex align-items-center justify-content-center" style="width:48px;height:48px">
                    <i class="bi bi-building text-primary fs-5"></i>
                </div>
                <div>
                    <div class="fs-4 fw-bold">{{ $totalKelas }}</div>
                    <small class="text-muted">Total Kelas</small>
                </div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card stat-card shadow-sm">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="rounded-circle bg-success bg-opacity-10 d-flex align-items-center justify-content-center" style="width:48px;height:48px">
                    <i class="bi bi-people text-success fs-5"></i>
                </div>
                <div>
                    <div class="fs-4 fw-bold">{{ $totalSiswa }}</div>
                    <small class="text-muted">Siswa Aktif</small>
                </div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card stat-card shadow-sm">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="rounded-circle bg-info bg-opacity-10 d-flex align-items-center justify-content-center" style="width:48px;height:48px">
                    <i class="bi bi-person-heart text-info fs-5"></i>
                </div>
                <div>
                    <div class="fs-4 fw-bold">{{ $totalGuruBk }}</div>
                    <small class="text-muted">Akun Guru BK</small>
                </div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card stat-card shadow-sm">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="rounded-circle bg-warning bg-opacity-10 d-flex align-items-center justify-content-center" style="width:48px;height:48px">
                    <i class="bi bi-person-workspace text-warning fs-5"></i>
                </div>
                <div>
                    <div class="fs-4 fw-bold">{{ $totalWaliKelas }}</div>
                    <small class="text-muted">Wali Kelas</small>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Status Koneksi Flask API & Akses Cepat --}}
<div class="row g-3">
    <div class="col-md-6">
        <div class="card shadow-sm h-100">
            <div class="card-header bg-transparent border-0 fw-semibold">
                <i class="bi bi-cpu me-2 text-primary"></i>Status Server Machine Learning (Flask)
            </div>
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <div>
                        <div class="small text-muted">Endpoint Flask API:</div>
                        <code>{{ $flaskUrl }}</code>
                    </div>
                    @if($flaskOnline)
                        <span class="badge bg-success px-3 py-2"><i class="bi bi-check-circle me-1"></i>{{ $flaskMessage }}</span>
                    @else
                        <span class="badge bg-danger px-3 py-2"><i class="bi bi-x-circle me-1"></i>Offline</span>
                    @endif
                </div>
                @if(!$flaskOnline)
                    <div class="alert alert-warning py-2 small mb-2">
                        {{ $flaskMessage }}
                    </div>
                @endif
                <a href="{{ route('admin.pengaturan.index') }}" class="btn btn-outline-primary btn-sm">
                    <i class="bi bi-gear me-1"></i>Kelola Pengaturan API
                </a>
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="card shadow-sm h-100">
            <div class="card-header bg-transparent border-0 fw-semibold">
                <i class="bi bi-lightning-charge me-2 text-primary"></i>Pintasan Menu Admin
            </div>
            <div class="card-body d-flex flex-wrap gap-2 align-content-start">
                <a href="{{ route('admin.periode.index') }}" class="btn btn-outline-secondary btn-sm">
                    <i class="bi bi-calendar3 me-1"></i>Atur Semester
                </a>
                <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary btn-sm">
                    <i class="bi bi-person-plus me-1"></i>Kelola Akun Guru
                </a>
                <a href="{{ route('admin.kelas.index') }}" class="btn btn-outline-secondary btn-sm">
                    <i class="bi bi-building me-1"></i>Kelola Kelas
                </a>
                <a href="{{ route('admin.siswa.index') }}" class="btn btn-outline-secondary btn-sm">
                    <i class="bi bi-people me-1"></i>Kelola & Import Siswa
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
