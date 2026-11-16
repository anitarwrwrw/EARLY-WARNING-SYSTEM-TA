@extends('layouts.app')
@section('page-title', 'Pencatatan Poin Pelanggaran')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold mb-0">Catatan Pelanggaran Tata Tertib</h4>
        <small class="text-muted">Akumulasi poin kedisiplinan sebagai fitur input prediksi risiko EWS</small>
    </div>
    <button class="btn btn-danger btn-sm" data-bs-toggle="modal" data-bs-target="#modalTambahPelanggaran">
        <i class="bi bi-exclamation-octagon me-1"></i>Catat Pelanggaran Siswa
    </button>
</div>

{{-- Filter Siswa --}}
<div class="card shadow-sm mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('bk.master.pelanggaran.index') }}" class="row g-2 align-items-center">
            <div class="col-md-6">
                <select name="siswa_id" class="form-select form-select-sm" onchange="this.form.submit()">
                    <option value="">Semua Siswa</option>
                    @foreach($siswa as $s)
                        <option value="{{ $s->id }}" {{ request('siswa_id') == $s->id ? 'selected' : '' }}>
                            {{ $s->nama_lengkap }} ({{ $s->nis }}) - {{ $s->kelas->tingkat ?? '' }} {{ $s->kelas->nama_kelas ?? '' }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-secondary btn-sm w-100">
                    <i class="bi bi-filter me-1"></i>Filter
                </button>
            </div>
            @if(request()->filled('siswa_id'))
            <div class="col-md-2">
                <a href="{{ route('bk.master.pelanggaran.index') }}" class="btn btn-outline-secondary btn-sm w-100">Reset</a>
            </div>
            @endif
        </form>
    </div>
</div>

<div class="card shadow-sm">
    <div class="card-body p-0">
        @if($pelanggaran->isEmpty())
            <div class="text-center py-5 text-muted">
                <i class="bi bi-shield-check text-success fs-1 d-block mb-2"></i>
                Tidak ada riwayat pelanggaran tata tertib tercatat.
            </div>
        @else
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th style="width: 50px;">#</th>
                        <th>Tanggal</th>
                        <th>Siswa</th>
                        <th>Kelas</th>
                        <th>Jenis Pelanggaran</th>
                        <th>Poin</th>
                        <th>Dicatat Oleh</th>
                        <th class="text-end" style="width: 80px;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                @foreach($pelanggaran as $i => $p)
                    <tr>
                        <td class="text-muted small">{{ $pelanggaran->firstItem() + $i }}</td>
                        <td>{{ \Carbon\Carbon::parse($p->tanggal)->isoFormat('D MMM Y') }}</td>
                        <td>
                            <div class="fw-semibold">{{ $p->siswa->nama_lengkap }}</div>
                            <small class="text-muted">{{ $p->siswa->nis }}</small>
                        </td>
                        <td><small>{{ $p->siswa->kelas->tingkat ?? '' }} {{ $p->siswa->kelas->nama_kelas ?? '-' }}</small></td>
                        <td>
                            <div>{{ $p->jenis_pelanggaran }}</div>
                            @if($p->keterangan)
                                <small class="text-muted fst-italic">{{ $p->keterangan }}</small>
                            @endif
                        </td>
                        <td>
                            <span class="badge bg-danger bg-opacity-10 text-danger border border-danger">
                                +{{ $p->poin }} Poin
                            </span>
                        </td>
                        <td><small class="text-muted">{{ $p->dicatatOleh->name ?? '-' }}</small></td>
                        <td class="text-end">
                            <form method="POST" action="{{ route('bk.master.pelanggaran.destroy', $p->id) }}"
                                  onsubmit="return confirm('Hapus catatan pelanggaran ini?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger" title="Hapus">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
        @if($pelanggaran->hasPages())
            <div class="card-footer bg-transparent py-3">
                {{ $pelanggaran->links() }}
            </div>
        @endif
        @endif
    </div>
</div>

{{-- Modal Tambah Pelanggaran --}}
<div class="modal fade" id="modalTambahPelanggaran" tabindex="-1">
    <div class="modal-dialog">
        <form method="POST" action="{{ route('bk.master.pelanggaran.store') }}" class="modal-content">
            @csrf
            <div class="modal-header">
                <h6 class="modal-title fw-bold text-danger"><i class="bi bi-exclamation-diamond me-2"></i>Catat Pelanggaran Siswa</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label fw-medium">Nama Siswa <span class="text-danger">*</span></label>
                    <select name="siswa_id" class="form-select" required>
                        <option value="">-- Pilih Siswa --</option>
                        @foreach($siswa as $s)
                            <option value="{{ $s->id }}" {{ old('siswa_id', request('siswa_id')) == $s->id ? 'selected' : '' }}>
                                {{ $s->nama_lengkap }} ({{ $s->nis }}) - {{ $s->kelas->tingkat ?? '' }} {{ $s->kelas->nama_kelas ?? '' }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="row g-2 mb-3">
                    <div class="col-7">
                        <label class="form-label fw-medium">Tanggal Kejadian <span class="text-danger">*</span></label>
                        <input type="date" name="tanggal" class="form-control" value="{{ date('Y-m-d') }}" max="{{ date('Y-m-d') }}" required>
                    </div>
                    <div class="col-5">
                        <label class="form-label fw-medium">Bobot Poin <span class="text-danger">*</span></label>
                        <input type="number" name="poin" class="form-control" placeholder="Contoh: 10" min="1" max="100" required>
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-medium">Jenis Pelanggaran <span class="text-danger">*</span></label>
                    <input type="text" name="jenis_pelanggaran" class="form-control"
                           placeholder="Contoh: Terlambat, Merokok di area sekolah, Membolos jam ke-4" required>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-medium">Keterangan / Kronologi Singkat</label>
                    <textarea name="keterangan" class="form-control" rows="2" placeholder="Catatan tambahan kejadian..."></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-danger btn-sm">
                    <i class="bi bi-save me-1"></i>Simpan Pelanggaran
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

