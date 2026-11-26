@extends('layouts.app')
@section('page-title', 'Instrumen Kuesioner & Monitoring')

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
    <div>
        <h4 class="fw-bold mb-0">Kuesioner Psikososial & Pantauan Pengisian</h4>
        <small class="text-muted">Kelola daftar pernyataan survei siswa dan pantau progres pengisian per kelas</small>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('kuesioner.publik') }}" target="_blank" class="btn btn-outline-primary btn-sm">
            <i class="bi bi-box-arrow-up-right me-1"></i>Lihat Form Publik
        </a>
        <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#modalTambahPertanyaan">
            <i class="bi bi-plus-lg me-1"></i>Tambah Pertanyaan
        </button>
    </div>
</div>

{{-- Section 1: Monitoring Pengisian Kuesioner Semua Kelas --}}
<div class="card shadow-sm mb-4">
    <div class="card-header bg-transparent border-0 d-flex flex-wrap justify-content-between align-items-center gap-2">
        <span class="fw-semibold">
            <i class="bi bi-bar-chart-steps text-primary me-2"></i>Progres Pengisian Kuesioner ({{ $periode?->nama_semester ?? '-' }})
        </span>
        <div class="d-flex align-items-center gap-2">
            <span class="badge bg-primary bg-opacity-10 text-primary border border-primary">
                Total: {{ $sudahIsiAll }} / {{ $totalSiswaAll }} Siswa
            </span>
            <form method="GET">
                <select name="semester_id" class="form-select form-select-sm" onchange="this.form.submit()">
                    @foreach($semuaPeriode as $sem)
                        <option value="{{ $sem->id }}" {{ $periode && $sem->id === $periode->id ? 'selected' : '' }}>
                            {{ $sem->nama_semester }}
                        </option>
                    @endforeach
                </select>
            </form>
        </div>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Kelas</th>
                        <th>Wali Kelas</th>
                        <th>Sudah Mengisi</th>
                        <th>Belum Mengisi</th>
                        <th style="width:220px">Persentase</th>
                    </tr>
                </thead>
                <tbody>
                @forelse($monitoringKelas as $mk)
                    <tr>
                        <td class="fw-semibold">{{ $mk->tingkat }} {{ $mk->nama_kelas }}</td>
                        <td><small class="text-muted">{{ $mk->waliKelas->name ?? '-' }}</small></td>
                        <td><span class="badge bg-success">{{ $mk->sudah_isi }}</span></td>
                        <td><span class="badge {{ $mk->belum_isi > 0 ? 'bg-warning text-dark' : 'bg-light text-muted border' }}">{{ $mk->belum_isi }}</span></td>
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                <div class="progress flex-grow-1" style="height: 8px;">
                                    <div class="progress-bar {{ $mk->persentase >= 100 ? 'bg-success' : 'bg-primary' }}"
                                         style="width: {{ $mk->persentase }}%"></div>
                                </div>
                                <small class="fw-semibold">{{ $mk->persentase }}%</small>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center py-4 text-muted">Belum ada data kelas.</td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- Section 2: Daftar Pertanyaan Kuesioner --}}
<div class="card shadow-sm">
    <div class="card-header bg-transparent border-0 d-flex justify-content-between align-items-center">
        <span class="fw-semibold"><i class="bi bi-ui-checks text-primary me-2"></i>Daftar Pertanyaan Kuesioner Psikososial</span>
        <span class="badge bg-light text-dark border">{{ $pertanyaanList->where('is_active', true)->count() }} aktif</span>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th style="width:65px">No</th>
                        <th style="width:180px">Aspek</th>
                        <th>Pernyataan</th>
                        <th>Skoring</th>
                        <th>Status</th>
                        <th class="text-end" style="width:110px">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                @forelse($pertanyaanList as $p)
                    <tr>
                        <td class="fw-bold">{{ $p->no_urut }}</td>
                        <td>
                            @if($p->aspek === 'motivasi')
                                <span class="badge bg-primary bg-opacity-10 text-primary border border-primary">Motivasi Belajar</span>
                            @elseif($p->aspek === 'keluarga')
                                <span class="badge bg-success bg-opacity-10 text-success border border-success">Dukungan Keluarga</span>
                            @else
                                <span class="badge bg-info bg-opacity-10 text-dark border border-info">Perasaan Aman</span>
                            @endif
                        </td>
                        <td>{{ $p->pernyataan }}</td>
                        <td>
                            @if($p->is_reverse)
                                <span class="badge bg-warning text-dark">Reverse (6 - N)</span>
                            @else
                                <span class="badge bg-light text-muted border">Normal</span>
                            @endif
                        </td>
                        <td>
                            @if($p->is_active)
                                <span class="badge bg-success">Aktif</span>
                            @else
                                <span class="badge bg-secondary">Nonaktif</span>
                            @endif
                        </td>
                        <td class="text-end">
                            <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal"
                                    data-bs-target="#modalEditPertanyaan{{ $p->id }}" title="Edit">
                                <i class="bi bi-pencil"></i>
                            </button>
                            <form method="POST" action="{{ route('bk.kuesioner.destroy', $p->id) }}" class="d-inline"
                                  onsubmit="return confirm('Hapus pertanyaan nomor {{ $p->no_urut }}?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger" title="Hapus">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>

                    {{-- Modal Edit Pertanyaan --}}
                    <div class="modal fade" id="modalEditPertanyaan{{ $p->id }}" tabindex="-1">
                        <div class="modal-dialog">
                            <form method="POST" action="{{ route('bk.kuesioner.update', $p->id) }}" class="modal-content">
                                @csrf
                                @method('PUT')
                                <div class="modal-header">
                                    <h6 class="modal-title fw-bold">Edit Pertanyaan No. {{ $p->no_urut }}</h6>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>
                                <div class="modal-body">
                                    <div class="row g-2 mb-3">
                                        <div class="col-4">
                                            <label class="form-label fw-medium">No. Urut <span class="text-danger">*</span></label>
                                            <input type="number" name="no_urut" class="form-control" min="1" max="100" value="{{ $p->no_urut }}" required>
                                        </div>
                                        <div class="col-8">
                                            <label class="form-label fw-medium">Aspek <span class="text-danger">*</span></label>
                                            <select name="aspek" class="form-select" required>
                                                <option value="motivasi" {{ $p->aspek === 'motivasi' ? 'selected' : '' }}>Motivasi Belajar</option>
                                                <option value="keluarga" {{ $p->aspek === 'keluarga' ? 'selected' : '' }}>Dukungan Keluarga</option>
                                                <option value="keamanan" {{ $p->aspek === 'keamanan' ? 'selected' : '' }}>Perasaan Aman di Sekolah</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label fw-medium">Teks Pernyataan <span class="text-danger">*</span></label>
                                        <textarea name="pernyataan" class="form-control" rows="3" required>{{ $p->pernyataan }}</textarea>
                                    </div>
                                    <div class="form-check mb-2">
                                        <input class="form-check-input" type="checkbox" name="is_reverse" value="1"
                                               id="revEdit{{ $p->id }}" {{ $p->is_reverse ? 'checked' : '' }}>
                                        <label class="form-check-label" for="revEdit{{ $p->id }}">
                                            Pertanyaan Negatif (Gunakan Reverse Scoring: 6 - Skor)
                                        </label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="is_active" value="1"
                                               id="actEdit{{ $p->id }}" {{ $p->is_active ? 'checked' : '' }}>
                                        <label class="form-check-label" for="actEdit{{ $p->id }}">Tampilkan di Form Publik Siswa</label>
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
                        <td colspan="6" class="text-center py-4 text-muted">Belum ada pertanyaan kuesioner.</td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- Modal Tambah Pertanyaan --}}
<div class="modal fade" id="modalTambahPertanyaan" tabindex="-1">
    <div class="modal-dialog">
        <form method="POST" action="{{ route('bk.kuesioner.store') }}" class="modal-content">
            @csrf
            <div class="modal-header">
                <h6 class="modal-title fw-bold">Tambah Pertanyaan Kuesioner</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row g-2 mb-3">
                    <div class="col-4">
                        <label class="form-label fw-medium">No. Urut <span class="text-danger">*</span></label>
                        <input type="number" name="no_urut" class="form-control" min="1" max="100"
                               value="{{ ($pertanyaanList->max('no_urut') ?? 0) + 1 }}" required>
                    </div>
                    <div class="col-8">
                        <label class="form-label fw-medium">Aspek <span class="text-danger">*</span></label>
                        <select name="aspek" class="form-select" required>
                            <option value="motivasi">Motivasi Belajar</option>
                            <option value="keluarga">Dukungan Keluarga</option>
                            <option value="keamanan">Perasaan Aman di Sekolah</option>
                        </select>
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-medium">Teks Pernyataan <span class="text-danger">*</span></label>
                    <textarea name="pernyataan" class="form-control" rows="3"
                              placeholder="Tuliskan pernyataan survei dengan bahasa ramah siswa..." required></textarea>
                </div>
                <div class="form-check mb-2">
                    <input class="form-check-input" type="checkbox" name="is_reverse" value="1" id="revTambah">
                    <label class="form-check-label" for="revTambah">
                        Pertanyaan Negatif (Reverse Scoring: 6 - Skor)
                    </label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="is_active" value="1" id="actTambah" checked>
                    <label class="form-check-label" for="actTambah">Aktif di Form Publik</label>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-primary btn-sm">Simpan Pertanyaan</button>
            </div>
        </form>
    </div>
</div>
@endsection
