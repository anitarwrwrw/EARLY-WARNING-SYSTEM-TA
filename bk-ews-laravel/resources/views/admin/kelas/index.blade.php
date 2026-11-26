@extends('layouts.app')
@section('page-title', 'Manajemen Data Kelas')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold mb-0">Data Kelas & Wali Kelas</h4>
        <small class="text-muted">Atur daftar rombongan belajar dan penugasan wali kelas</small>
    </div>
    <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#modalTambahKelas">
        <i class="bi bi-plus-lg me-1"></i>Tambah Kelas
    </button>
</div>

<div class="card shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th style="width:50px">#</th>
                        <th>Kelas</th>
                        <th>Jurusan</th>
                        <th>Wali Kelas</th>
                        <th>Jumlah Siswa</th>
                        <th>Status</th>
                        <th class="text-end">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                @forelse($kelasList as $i => $k)
                    <tr>
                        <td class="text-muted small">{{ $i + 1 }}</td>
                        <td class="fw-semibold">{{ $k->tingkat }} {{ $k->nama_kelas }}</td>
                        <td>{{ $k->jurusan }}</td>
                        <td>{{ $k->waliKelas->name ?? '-' }}</td>
                        <td><span class="badge bg-light text-dark border">{{ $k->siswa_count }} siswa</span></td>
                        <td>
                            @if($k->is_active)
                                <span class="badge bg-success">Aktif</span>
                            @else
                                <span class="badge bg-secondary">Nonaktif</span>
                            @endif
                        </td>
                        <td class="text-end">
                            <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal"
                                    data-bs-target="#modalEditKelas{{ $k->id }}" title="Edit">
                                <i class="bi bi-pencil"></i>
                            </button>
                            <form method="POST" action="{{ route('admin.kelas.destroy', $k->id) }}" class="d-inline"
                                  onsubmit="return confirm('Hapus kelas {{ $k->tingkat }} {{ $k->nama_kelas }}?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger" title="Hapus">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>

                    {{-- Modal Edit Kelas --}}
                    <div class="modal fade" id="modalEditKelas{{ $k->id }}" tabindex="-1">
                        <div class="modal-dialog">
                            <form method="POST" action="{{ route('admin.kelas.update', $k->id) }}" class="modal-content">
                                @csrf
                                @method('PUT')
                                <div class="modal-header">
                                    <h6 class="modal-title fw-bold">Edit Kelas: {{ $k->tingkat }} {{ $k->nama_kelas }}</h6>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>
                                <div class="modal-body">
                                    <div class="row g-2 mb-3">
                                        <div class="col-4">
                                            <label class="form-label fw-medium">Tingkat <span class="text-danger">*</span></label>
                                            <select name="tingkat" class="form-select" required>
                                                <option value="X" {{ $k->tingkat === 'X' ? 'selected' : '' }}>X</option>
                                                <option value="XI" {{ $k->tingkat === 'XI' ? 'selected' : '' }}>XI</option>
                                                <option value="XII" {{ $k->tingkat === 'XII' ? 'selected' : '' }}>XII</option>
                                            </select>
                                        </div>
                                        <div class="col-8">
                                            <label class="form-label fw-medium">Nama Kelas <span class="text-danger">*</span></label>
                                            <input type="text" name="nama_kelas" class="form-control" value="{{ $k->nama_kelas }}" required>
                                        </div>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label fw-medium">Jurusan <span class="text-danger">*</span></label>
                                        <input type="text" name="jurusan" class="form-control" value="{{ $k->jurusan }}" required>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label fw-medium">Wali Kelas</label>
                                        <select name="wali_kelas_id" class="form-select">
                                            <option value="">-- Belum Ditentukan --</option>
                                            @foreach($waliList as $w)
                                                <option value="{{ $w->id }}" {{ $k->wali_kelas_id == $w->id ? 'selected' : '' }}>
                                                    {{ $w->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="is_active" value="1"
                                               id="aktifKelas{{ $k->id }}" {{ $k->is_active ? 'checked' : '' }}>
                                        <label class="form-check-label" for="aktifKelas{{ $k->id }}">Kelas Aktif</label>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Batal</button>
                                    <button type="submit" class="btn btn-primary btn-sm">Simpan Perubahan</button>
                                </div>
                            </form>
                        </div>
                    </div>
                @empty
                    <tr>
                        <td colspan="7" class="text-center py-4 text-muted">Belum ada data kelas.</td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- Modal Tambah Kelas --}}
<div class="modal fade" id="modalTambahKelas" tabindex="-1">
    <div class="modal-dialog">
        <form method="POST" action="{{ route('admin.kelas.store') }}" class="modal-content">
            @csrf
            <div class="modal-header">
                <h6 class="modal-title fw-bold">Tambah Kelas Baru</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row g-2 mb-3">
                    <div class="col-4">
                        <label class="form-label fw-medium">Tingkat <span class="text-danger">*</span></label>
                        <select name="tingkat" class="form-select" required>
                            <option value="X">X</option>
                            <option value="XI" selected>XI</option>
                            <option value="XII">XII</option>
                        </select>
                    </div>
                    <div class="col-8">
                        <label class="form-label fw-medium">Nama Kelas <span class="text-danger">*</span></label>
                        <input type="text" name="nama_kelas" class="form-control" placeholder="Contoh: TKJ 1" required>
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-medium">Jurusan <span class="text-danger">*</span></label>
                    <input type="text" name="jurusan" class="form-control" placeholder="Teknik Komputer & Jaringan" required>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-medium">Wali Kelas</label>
                    <select name="wali_kelas_id" class="form-select">
                        <option value="">-- Belum Ditentukan --</option>
                        @foreach($waliList as $w)
                            <option value="{{ $w->id }}">{{ $w->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-primary btn-sm">Simpan Kelas</button>
            </div>
        </form>
    </div>
</div>
@endsection
