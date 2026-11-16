@extends('layouts.app')
@section('page-title', 'Dashboard Wali Kelas')

@section('content')
@if(!$kelas)
    <div class="alert alert-warning">
        <i class="bi bi-exclamation-triangle me-2"></i>{{ $pesan }}
    </div>
@else
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold mb-0">Dashboard Kelas {{ $kelas->tingkat }} {{ $kelas->nama_kelas }}</h4>
        <small class="text-muted">{{ $kelas->jurusan }} — Semester: <strong>{{ $semester }}</strong></small>
    </div>
</div>

{{-- Statistik Ringkasan --}}
<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="card shadow-sm">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="rounded-circle bg-primary bg-opacity-10 d-flex align-items-center justify-content-center" style="width:48px;height:48px">
                    <i class="bi bi-people text-primary fs-5"></i>
                </div>
                <div>
                    <div class="fs-4 fw-bold">{{ $stats['total_siswa'] }}</div>
                    <small class="text-muted">Total Siswa</small>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card shadow-sm border-success border-opacity-25">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="rounded-circle bg-success bg-opacity-10 d-flex align-items-center justify-content-center" style="width:48px;height:48px">
                    <i class="bi bi-clipboard-check text-success fs-5"></i>
                </div>
                <div>
                    <div class="fs-4 fw-bold text-success">{{ $stats['sudah_kuesioner'] }}</div>
                    <small class="text-muted">Sudah Isi Kuesioner</small>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card shadow-sm border-warning border-opacity-25">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="rounded-circle bg-warning bg-opacity-10 d-flex align-items-center justify-content-center" style="width:48px;height:48px">
                    <i class="bi bi-clipboard-x text-warning fs-5"></i>
                </div>
                <div>
                    <div class="fs-4 fw-bold text-warning">{{ $stats['belum_kuesioner'] }}</div>
                    <small class="text-muted">Belum Isi Kuesioner</small>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Hasil EWS Kelas --}}
<div class="card shadow-sm mb-4">
    <div class="card-header bg-transparent border-0 fw-semibold">
        <i class="bi bi-speedometer2 me-2 text-primary"></i>Pemetaan Risiko Siswa Kelas Ini
    </div>
    <div class="card-body p-0">
        @if($hasilEws->isEmpty())
            <div class="text-center py-4 text-muted">
                <i class="bi bi-inbox d-block fs-1 mb-2"></i>
                Belum ada hasil analisis EWS untuk kelas ini.
            </div>
        @else
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Siswa</th>
                        <th>Status Risiko</th>
                        <th>Prioritas</th>
                        <th>Pemicu</th>
                    </tr>
                </thead>
                <tbody>
                @foreach($hasilEws as $ews)
                <tr>
                    <td>
                        <div class="fw-semibold">{{ $ews->siswa->nama_lengkap }}</div>
                        <small class="text-muted">{{ $ews->siswa->nis }}</small>
                    </td>
                    <td>
                        <span class="badge rounded-pill
                            @if($ews->status_risiko === 'Risiko Tinggi') bg-danger
                            @elseif($ews->status_risiko === 'Risiko Sedang') bg-warning text-dark
                            @else bg-success @endif">
                            {{ $ews->status_risiko }}
                        </span>
                    </td>
                    <td><small>{{ $ews->ikon_prioritas }} {{ $ews->label_prioritas }}</small></td>
                    <td><small class="text-muted">{{ $ews->pemicu_utama }}</small></td>
                </tr>
                @endforeach
                </tbody>
            </table>
        </div>
        @endif
    </div>
</div>

{{-- Status Kuesioner --}}
<div class="card shadow-sm">
    <div class="card-header bg-transparent border-0 fw-semibold">
        <i class="bi bi-clipboard-heart me-2 text-primary"></i>Status Pengisian Kuesioner Siswa
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr><th>Siswa</th><th>NIS</th><th>Status Kuesioner</th></tr>
                </thead>
                <tbody>
                @foreach($siswaDenganStatusKuesioner as $s)
                <tr>
                    <td>{{ $s->nama_lengkap }}</td>
                    <td>{{ $s->nis }}</td>
                    <td>
                        @if($s->sudah_isi_kuesioner)
                            <span class="badge bg-success"><i class="bi bi-check2 me-1"></i>Sudah Mengisi</span>
                        @else
                            <span class="badge bg-warning text-dark"><i class="bi bi-hourglass me-1"></i>Belum Mengisi</span>
                        @endif
                    </td>
                </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endif
@endsection

