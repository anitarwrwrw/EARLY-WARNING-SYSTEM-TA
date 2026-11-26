@extends('layouts.app')
@section('page-title', 'Manajemen Data Induk Siswa')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold mb-0">Data Induk Siswa</h4>
        <small class="text-muted">Kelola biodata siswa, NISN verifikasi, dan nomor WhatsApp orang tua</small>
    </div>
    <div class="d-flex gap-2">
        <button class="btn btn-outline-success btn-sm" data-bs-toggle="modal" data-bs-target="#modalImportSiswa">
            <i class="bi bi-file-earmark-excel me-1"></i>Import Excel
        </button>
        <a href="{{ route('admin.siswa.create') }}" class="btn btn-primary btn-sm">
            <i class="bi bi-plus-lg me-1"></i>Tambah Siswa
        </a>
    </div>
</div>

{{-- Filter & Pencarian --}}
<div class="card shadow-sm mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('admin.siswa.index') }}" class="row g-2 align-items-center">
            <div class="col-md-4">
                <input type="text" name="search" class="form-control form-control-sm"
                       placeholder="Cari nama, NIS, atau NISN..." value="{{ request('search') }}">
            </div>
            <div class="col-md-3">
                <select name="kelas_id" class="form-select form-select-sm" onchange="this.form.submit()">
                    <option value="">Semua Kelas</option>
                    @foreach($kelasList as $k)
                        <option value="{{ $k->id }}" {{ request('kelas_id') == $k->id ? 'selected' : '' }}>
                            {{ $k->tingkat }} {{ $k->nama_kelas }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-secondary btn-sm w-100">
                    <i class="bi bi-search me-1"></i>Cari
                </button>
            </div>
            @if(request()->filled('search') || request()->filled('kelas_id'))
            <div class="col-md-2">
                <a href="{{ route('admin.siswa.index') }}" class="btn btn-outline-secondary btn-sm w-100">Reset</a>
            </div>
            @endif
        </form>
    </div>
</div>

{{-- Tabel Siswa --}}
<div class="card shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th style="width:50px">#</th>
                        <th>NISN / NIS</th>
                        <th>Nama Lengkap</th>
                        <th>Kelas</th>
                        <th>L/P</th>
                        <th>Orang Tua & No. WA</th>
                        <th>Status</th>
                        <th class="text-end">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                @forelse($siswaList as $i => $s)
                    <tr>
                        <td class="text-muted small">{{ $siswaList->firstItem() + $i }}</td>
                        <td>
                            <div class="fw-semibold"><code>{{ $s->nisn ?? '-' }}</code></div>
                            <small class="text-muted">NIS: {{ $s->nis }}</small>
                        </td>
                        <td class="fw-semibold">{{ $s->nama_lengkap }}</td>
                        <td>{{ $s->kelas->tingkat ?? '' }} {{ $s->kelas->nama_kelas ?? '-' }}</td>
                        <td>{{ $s->jenis_kelamin }}</td>
                        <td>
                            <div>{{ $s->nama_ortu ?? '-' }}</div>
                            <small class="text-muted"><i class="bi bi-whatsapp text-success me-1"></i>{{ $s->no_wa_ortu ?? '-' }}</small>
                        </td>
                        <td>
                            @if($s->is_active)
                                <span class="badge bg-success">Aktif</span>
                            @else
                                <span class="badge bg-secondary">Nonaktif</span>
                            @endif
                        </td>
                        <td class="text-end">
                            <a href="{{ route('admin.siswa.edit', $s->id) }}" class="btn btn-sm btn-outline-primary" title="Edit">
                                <i class="bi bi-pencil"></i>
                            </a>
                            <form method="POST" action="{{ route('admin.siswa.destroy', $s->id) }}" class="d-inline"
                                  onsubmit="return confirm('Hapus data siswa {{ $s->nama_lengkap }}?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger" title="Hapus">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center py-4 text-muted">Belum ada data siswa ditemukan.</td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
        @if($siswaList->hasPages())
            <div class="card-footer bg-transparent py-3">
                {{ $siswaList->links() }}
            </div>
        @endif
    </div>
</div>

{{-- Modal Import Excel Siswa --}}
<div class="modal fade" id="modalImportSiswa" tabindex="-1">
    <div class="modal-dialog">
        <form method="POST" action="{{ route('admin.siswa.import') }}" enctype="multipart/form-data" class="modal-content">
            @csrf
            <div class="modal-header">
                <h6 class="modal-title fw-bold"><i class="bi bi-file-earmark-excel text-success me-2"></i>Import Data Siswa</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label fw-medium">Pilih Kelas Tujuan <span class="text-danger">*</span></label>
                    <select name="kelas_id" class="form-select" required>
                        <option value="">-- Pilih Kelas --</option>
                        @foreach($kelasList as $k)
                            <option value="{{ $k->id }}">{{ $k->tingkat }} {{ $k->nama_kelas }} ({{ $k->jurusan }})</option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-medium">File Excel / CSV <span class="text-danger">*</span></label>
                    <input type="file" name="file_excel" class="form-control" accept=".xlsx,.xls,.csv" required>
                    <div class="form-text">Format: .xlsx / .xls / .csv, maksimal 5MB.</div>
                </div>
                <div class="alert alert-info py-2 small">
                    <strong>Header kolom:</strong><br>
                    <code>nisn | nis | nama_lengkap | jenis_kelamin | nama_ortu | no_wa_ortu | alamat | tanggal_lahir</code>
                </div>
                <a href="{{ route('admin.siswa.template') }}" class="btn btn-outline-secondary btn-sm">
                    <i class="bi bi-download me-1"></i>Unduh Template CSV
                </a>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-success btn-sm">
                    <i class="bi bi-upload me-1"></i>Import Sekarang
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
