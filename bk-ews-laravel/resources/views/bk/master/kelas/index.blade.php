@extends('layouts.app')
@section('page-title', 'Data Master Kelas')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold mb-0">Manajemen Data Kelas</h4>
        <small class="text-muted">Kelola kelas, jurusan, dan penugasan wali kelas</small>
    </div>
    <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#modalTambahKelas">
        <i class="bi bi-plus-circle me-1"></i>Tambah Kelas Baru
    </button>
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

{{-- Modal Tambah Kelas --}}
<div class="modal fade" id="modalTambahKelas" tabindex="-1">
    <div class="modal-dialog">
        <form method="POST" action="{{ route('bk.master.kelas.store') }}" class="modal-content">
            @csrf
            <div class="modal-header">
                <h6 class="modal-title fw-bold"><i class="bi bi-building-add text-primary me-2"></i>Tambah Kelas Baru</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label fw-medium">Tingkat Kelas <span class="text-danger">*</span></label>
                    <select name="tingkat" class="form-select" required>
                        <option value="">-- Pilih Tingkat --</option>
                        <option value="X">Kelas X</option>
                        <option value="XI">Kelas XI</option>
                        <option value="XII">Kelas XII</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-medium">Nama / Kode Kelas <span class="text-danger">*</span></label>
                    <input type="text" name="nama_kelas" class="form-control" placeholder="Contoh: TKJ 1 atau RPL 2" required>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-medium">Kompetensi Keahlian (Jurusan) <span class="text-danger">*</span></label>
                    <input type="text" name="jurusan" class="form-control" placeholder="Contoh: Teknik Komputer & Jaringan" required>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-medium">Wali Kelas</label>
                    <select name="wali_kelas_id" class="form-select">
                        <option value="">-- Pilih Guru Wali Kelas (Opsional) --</option>
                        @foreach($waliKelasList as $w)
                            <option value="{{ $w->id }}">{{ $w->name }} ({{ $w->username }})</option>
                        @endforeach
                    </select>
                    <small class="text-muted">Guru wali kelas dapat login untuk mengisi nilai & memantau hasil EWS kelas.</small>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-primary btn-sm">
                    <i class="bi bi-save me-1"></i>Simpan Kelas
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

