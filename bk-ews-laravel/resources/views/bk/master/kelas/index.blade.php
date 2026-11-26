@extends('layouts.app')
@section('page-title', 'Data Master Kelas')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold mb-0">Daftar Data Kelas</h4>
        <small class="text-muted">Daftar rombongan belajar dan wali kelas (Read-Only — dikelola Admin)</small>
    </div>
</div>

<div class="card shadow-sm">
    <div class="card-body p-0">
        @if($kelas->isEmpty())
            <div class="text-center py-5 text-muted">
                <i class="bi bi-building fs-1 d-block mb-2"></i>
                Belum ada data kelas yang terdaftar.
            </div>
        @else
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th style="width: 50px;">#</th>
                        <th>Tingkat</th>
                        <th>Nama Kelas</th>
                        <th>Kompetensi Keahlian / Jurusan</th>
                        <th>Wali Kelas</th>
                        <th>Jumlah Siswa</th>
                    </tr>
                </thead>
                <tbody>
                @foreach($kelas as $i => $k)
                    <tr>
                        <td class="text-muted small">{{ $i + 1 }}</td>
                        <td><span class="badge bg-secondary">{{ $k->tingkat }}</span></td>
                        <td class="fw-semibold">{{ $k->nama_kelas }}</td>
                        <td>{{ $k->jurusan }}</td>
                        <td>
                            @if($k->waliKelas)
                                <div class="fw-medium text-primary">
                                    <i class="bi bi-person-badge me-1"></i>{{ $k->waliKelas->name }}
                                </div>
                                <small class="text-muted">Username: {{ $k->waliKelas->username }}</small>
                            @else
                                <span class="badge bg-light text-muted border">Belum ditentukan</span>
                            @endif
                        </td>
                        <td>
                            <a href="{{ route('bk.master.siswa.index', ['kelas_id' => $k->id]) }}" class="badge bg-primary bg-opacity-10 text-primary text-decoration-none">
                                {{ $k->siswa_count }} Siswa
                            </a>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
        @endif
    </div>
</div>
@endsection

