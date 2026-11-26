@extends('layouts.app')
@section('page-title', 'Kategori Poin Pelanggaran')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold mb-0">Master Kategori Poin Pelanggaran</h4>
        <small class="text-muted">Kelola daftar jenis pelanggaran tata tertib dan bobot poinnya</small>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('bk.master.pelanggaran.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left me-1"></i>Kembali ke Pelanggaran
        </a>
        <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#modalTambahKategori">
            <i class="bi bi-plus-lg me-1"></i>Tambah Kategori
        </button>
    </div>
</div>

<div class="card shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th style="width:50px">#</th>
                        <th>Nama Jenis Pelanggaran</th>
                        <th>Tingkat / Kategori</th>
                        <th>Bobot Poin</th>
                        <th>Dipakai</th>
                        <th>Keterangan</th>
                        <th class="text-end">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                @forelse($kategoriList as $i => $kat)
                    <tr>
                        <td class="text-muted small">{{ $i + 1 }}</td>
                        <td class="fw-semibold">{{ $kat->nama }}</td>
                        <td>
                            @if($kat->kategori === 'Berat')
                                <span class="badge bg-danger">Berat</span>
                            @elseif($kat->kategori === 'Sedang')
                                <span class="badge bg-warning text-dark">Sedang</span>
                            @else
                                <span class="badge bg-info text-dark">Ringan</span>
                            @endif
                        </td>
                        <td><span class="badge bg-danger bg-opacity-10 text-danger border border-danger">+{{ $kat->bobot_poin }} Poin</span></td>
                        <td><small class="text-muted">{{ $kat->pelanggaran_count }}x tercatat</small></td>
                        <td><small class="text-muted">{{ $kat->keterangan ?: '-' }}</small></td>
                        <td class="text-end">
                            <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal"
                                    data-bs-target="#modalEditKategori{{ $kat->id }}" title="Edit">
                                <i class="bi bi-pencil"></i>
                            </button>
                            <form method="POST" action="{{ route('bk.master.kategori.destroy', $kat->id) }}" class="d-inline"
                                  onsubmit="return confirm('Hapus kategori {{ $kat->nama }}?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger" title="Hapus">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>

                    {{-- Modal Edit Kategori --}}
                    <div class="modal fade" id="modalEditKategori{{ $kat->id }}" tabindex="-1">
                        <div class="modal-dialog">
                            <form method="POST" action="{{ route('bk.master.kategori.update', $kat->id) }}" class="modal-content">
                                @csrf
                                @method('PUT')
                                <div class="modal-header">
                                    <h6 class="modal-title fw-bold">Edit Kategori Pelanggaran</h6>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>
                                <div class="modal-body">
                                    <div class="mb-3">
                                        <label class="form-label fw-medium">Nama Pelanggaran <span class="text-danger">*</span></label>
                                        <input type="text" name="nama" class="form-control" value="{{ $kat->nama }}" required>
                                    </div>
                                    <div class="row g-2 mb-3">
                                        <div class="col-6">
                                            <label class="form-label fw-medium">Kategori <span class="text-danger">*</span></label>
                                            <select name="kategori" class="form-select" required>
                                                <option value="Ringan" {{ $kat->kategori === 'Ringan' ? 'selected' : '' }}>Ringan</option>
                                                <option value="Sedang" {{ $kat->kategori === 'Sedang' ? 'selected' : '' }}>Sedang</option>
                                                <option value="Berat" {{ $kat->kategori === 'Berat' ? 'selected' : '' }}>Berat</option>
                                            </select>
                                        </div>
                                        <div class="col-6">
                                            <label class="form-label fw-medium">Bobot Poin <span class="text-danger">*</span></label>
                                            <input type="number" name="bobot_poin" class="form-control" min="1" max="100" value="{{ $kat->bobot_poin }}" required>
                                        </div>
                                    </div>
                                    <div class="mb-0">
                                        <label class="form-label fw-medium">Keterangan</label>
                                        <textarea name="keterangan" class="form-control" rows="2">{{ $kat->keterangan }}</textarea>
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
                        <td colspan="7" class="text-center py-4 text-muted">Belum ada data kategori pelanggaran.</td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- Modal Tambah Kategori --}}
<div class="modal fade" id="modalTambahKategori" tabindex="-1">
    <div class="modal-dialog">
        <form method="POST" action="{{ route('bk.master.kategori.store') }}" class="modal-content">
            @csrf
            <div class="modal-header">
                <h6 class="modal-title fw-bold">Tambah Kategori Pelanggaran</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label fw-medium">Nama Pelanggaran <span class="text-danger">*</span></label>
                    <input type="text" name="nama" class="form-control" placeholder="Contoh: Tidak memakai seragam lengkap" required>
                </div>
                <div class="row g-2 mb-3">
                    <div class="col-6">
                        <label class="form-label fw-medium">Kategori <span class="text-danger">*</span></label>
                        <select name="kategori" class="form-select" required>
                            <option value="Ringan">Ringan</option>
                            <option value="Sedang">Sedang</option>
                            <option value="Berat">Berat</option>
                        </select>
                    </div>
                    <div class="col-6">
                        <label class="form-label fw-medium">Bobot Poin <span class="text-danger">*</span></label>
                        <input type="number" name="bobot_poin" class="form-control" min="1" max="100" value="5" required>
                    </div>
                </div>
                <div class="mb-0">
                    <label class="form-label fw-medium">Keterangan</label>
                    <textarea name="keterangan" class="form-control" rows="2"></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-primary btn-sm">Simpan Kategori</button>
            </div>
        </form>
    </div>
</div>
@endsection
