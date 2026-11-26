@extends('layouts.app')
@section('page-title', 'Input Nilai & Absensi')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold mb-0">Input Nilai & Absensi</h4>
        <small class="text-muted">Kelas {{ $kelas->tingkat }} {{ $kelas->nama_kelas }} — {{ $kelas->jurusan }}</small>
    </div>

    {{-- Import Excel --}}
    <button class="btn btn-outline-success btn-sm" data-bs-toggle="modal" data-bs-target="#modalImport">
        <i class="bi bi-file-earmark-excel me-1"></i>Import Excel
    </button>
</div>

{{-- Pilih Semester --}}
<div class="card shadow-sm mb-4">
    <div class="card-body py-2">
        <form method="GET" class="d-flex align-items-center gap-3">
            <label class="fw-medium mb-0">Periode Semester:</label>
            <select name="semester_id" class="form-select form-select-sm w-auto" onchange="this.form.submit()">
                @foreach($semuaPeriode as $sem)
                    <option value="{{ $sem->id }}" {{ $periode && $sem->id === $periode->id ? 'selected' : '' }}>
                        {{ $sem->nama_semester }} {{ $sem->is_aktif ? '(Aktif)' : '' }}
                    </option>
                @endforeach
            </select>
        </form>
    </div>
</div>

{{-- Tabel Input per Siswa --}}
<div class="card shadow-sm">
    <div class="card-header bg-transparent border-0 fw-semibold">
        <i class="bi bi-pencil-square me-2 text-primary"></i>Input Nilai & Absensi Per Siswa ({{ $periode?->nama_semester ?? '-' }})
    </div>
    <div class="card-body p-0">
        @if(!$periode)
            <div class="text-center py-4 text-muted">Belum ada periode semester aktif. Hubungi Admin.</div>
        @else
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Siswa</th>
                        <th style="width:130px">Rata Nilai</th>
                        <th style="width:100px">Alpha</th>
                        <th style="width:100px">Izin</th>
                        <th style="width:100px">Sakit</th>
                        <th style="width:100px">Tren Nilai</th>
                        <th style="width:80px">Simpan</th>
                    </tr>
                </thead>
                <tbody>
                @foreach($siswaList as $s)
                @php $ak = $s->akademikAbsensi->first(); @endphp
                <tr>
                    <td>
                        <div class="fw-semibold">{{ $s->nama_lengkap }}</div>
                        <small class="text-muted">{{ $s->nis }}</small>
                    </td>
                    <form method="POST" action="{{ route('wali.simpan-nilai') }}" class="contents">
                        @csrf
                        <input type="hidden" name="siswa_id" value="{{ $s->id }}">
                        <input type="hidden" name="semester_id" value="{{ $periode->id }}">
                        <td><input type="number" name="rata_nilai" step="0.1" min="0" max="100"
                                   class="form-control form-control-sm" value="{{ $ak->rata_nilai ?? '' }}" required></td>
                        <td><input type="number" name="jumlah_alpha" min="0"
                                   class="form-control form-control-sm" value="{{ $ak->jumlah_alpha ?? 0 }}" required></td>
                        <td><input type="number" name="jumlah_izin" min="0"
                                   class="form-control form-control-sm" value="{{ $ak->jumlah_izin ?? 0 }}" required></td>
                        <td><input type="number" name="jumlah_sakit" min="0"
                                   class="form-control form-control-sm" value="{{ $ak->jumlah_sakit ?? 0 }}" required></td>
                        <td>
                            @if($ak)
                                <span class="badge {{ $ak->tren_nilai >= 0 ? 'bg-success' : 'bg-danger' }}">
                                    {{ $ak->tren_nilai >= 0 ? '+' : '' }}{{ $ak->tren_nilai }}
                                </span>
                            @else
                                <span class="text-muted small">—</span>
                            @endif
                        </td>
                        <td>
                            <button type="submit" class="btn btn-primary btn-sm" title="Simpan">
                                <i class="bi bi-save2"></i>
                            </button>
                        </td>
                    </form>
                </tr>
                @endforeach
                </tbody>
            </table>
        </div>
        @endif
    </div>
</div>

{{-- Modal Import Excel --}}
@if($periode)
<div class="modal fade" id="modalImport" tabindex="-1">
    <div class="modal-dialog">
        <form method="POST" action="{{ route('wali.import-excel') }}" enctype="multipart/form-data" class="modal-content">
            @csrf
            <div class="modal-header">
                <h6 class="modal-title"><i class="bi bi-file-earmark-excel text-success me-2"></i>Import dari Excel/CSV</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" name="semester_id" value="{{ $periode->id }}">
                <div class="mb-3">
                    <label class="form-label fw-medium">Pilih File</label>
                    <input type="file" name="file_excel" class="form-control" accept=".xlsx,.xls,.csv" required>
                    <div class="form-text">Format: .xlsx / .xls / .csv, maks 5MB</div>
                </div>
                <div class="alert alert-info py-2 small">
                    <strong>Format kolom yang diharapkan:</strong><br>
                    <code>nis | rata_nilai | jumlah_alpha | jumlah_izin | jumlah_sakit</code><br>
                    Baris pertama adalah header.
                </div>
                <a href="{{ route('wali.template-excel') }}" class="btn btn-outline-secondary btn-sm">
                    <i class="bi bi-download me-1"></i>Unduh Template Excel / CSV
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
@endif
@endsection
