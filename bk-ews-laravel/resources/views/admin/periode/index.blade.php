@extends('layouts.app')
@section('page-title', 'Manajemen Periode Semester')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold mb-0">Periode Tahun Ajaran & Semester</h4>
        <small class="text-muted">Tentukan semester aktif dan status buka/tutup form kuesioner publik siswa</small>
    </div>
    <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#modalTambahPeriode">
        <i class="bi bi-plus-lg me-1"></i>Tambah Periode
    </button>
</div>

<div class="card shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th style="width:50px">#</th>
                        <th>Nama Semester</th>
                        <th>Tahun Ajaran</th>
                        <th>Jenis</th>
                        <th>Status Semester</th>
                        <th>Form Kuesioner Publik</th>
                        <th class="text-end">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                @forelse($periodes as $i => $p)
                    <tr>
                        <td class="text-muted small">{{ $i + 1 }}</td>
                        <td class="fw-semibold">{{ $p->nama_semester }}</td>
                        <td>{{ $p->tahun_ajaran }}</td>
                        <td>{{ $p->jenis }}</td>
                        <td>
                            @if($p->is_aktif)
                                <span class="badge bg-success"><i class="bi bi-check-circle me-1"></i>Aktif</span>
                            @else
                                <span class="badge bg-secondary bg-opacity-25 text-dark">Tidak Aktif</span>
                            @endif
                        </td>
                        <td>
                            <form method="POST" action="{{ route('admin.periode.toggle-kuesioner', $p->id) }}" class="d-inline">
                                @csrf
                                <button type="submit" class="btn btn-sm {{ $p->buka_kuesioner ? 'btn-success' : 'btn-outline-secondary' }}">
                                    <i class="bi {{ $p->buka_kuesioner ? 'bi-unlock' : 'bi-lock' }} me-1"></i>
                                    {{ $p->buka_kuesioner ? 'Dibuka' : 'Ditutup' }}
                                </button>
                            </form>
                        </td>
                        <td class="text-end">
                            @if(!$p->is_aktif)
                                <form method="POST" action="{{ route('admin.periode.set-aktif', $p->id) }}" class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-outline-primary">
                                        <i class="bi bi-check2-circle me-1"></i>Aktifkan
                                    </button>
                                </form>
                                <form method="POST" action="{{ route('admin.periode.destroy', $p->id) }}" class="d-inline"
                                      onsubmit="return confirm('Hapus periode {{ $p->nama_semester }}?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger" title="Hapus">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            @else
                                <span class="text-muted small">Sedang Digunakan</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center py-4 text-muted">Belum ada data periode semester.</td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- Modal Tambah Periode --}}
<div class="modal fade" id="modalTambahPeriode" tabindex="-1">
    <div class="modal-dialog">
        <form method="POST" action="{{ route('admin.periode.store') }}" class="modal-content">
            @csrf
            <div class="modal-header">
                <h6 class="modal-title fw-bold">Tambah Periode Semester</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label fw-medium">Nama Semester <span class="text-danger">*</span></label>
                    <input type="text" name="nama_semester" class="form-control" placeholder="Contoh: Ganjil 2026/2027" required>
                </div>
                <div class="row g-2 mb-3">
                    <div class="col-6">
                        <label class="form-label fw-medium">Tahun Ajaran <span class="text-danger">*</span></label>
                        <input type="text" name="tahun_ajaran" class="form-control" placeholder="2026/2027" required>
                    </div>
                    <div class="col-6">
                        <label class="form-label fw-medium">Jenis <span class="text-danger">*</span></label>
                        <select name="jenis" class="form-select" required>
                            <option value="Ganjil">Ganjil</option>
                            <option value="Genap">Genap</option>
                        </select>
                    </div>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="is_aktif" value="1" id="checkAktif">
                    <label class="form-check-label" for="checkAktif">Langsung jadikan semester aktif</label>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-primary btn-sm">Simpan</button>
            </div>
        </form>
    </div>
</div>
@endsection
