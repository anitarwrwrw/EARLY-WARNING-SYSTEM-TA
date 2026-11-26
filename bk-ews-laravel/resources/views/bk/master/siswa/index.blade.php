@extends('layouts.app')
@section('page-title', 'Data Master Siswa')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold mb-0">Daftar Data Siswa</h4>
        <small class="text-muted">Data induk siswa SMK Sunan Ampel (Read-Only — dikelola Admin)</small>
    </div>
</div>

{{-- Filter & Pencarian --}}
<div class="card shadow-sm mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('bk.master.siswa.index') }}" class="row g-2 align-items-center">
            <div class="col-md-4">
                <input type="text" name="search" class="form-control form-control-sm"
                       placeholder="Cari NIS atau Nama Siswa..." value="{{ request('search') }}">
            </div>
            <div class="col-md-3">
                <select name="kelas_id" class="form-select form-select-sm" onchange="this.form.submit()">
                    <option value="">Semua Kelas</option>
                    @foreach($kelas as $k)
                        <option value="{{ $k->id }}" {{ request('kelas_id') == $k->id ? 'selected' : '' }}>
                            {{ $k->tingkat }} {{ $k->nama_kelas }} ({{ $k->jurusan }})
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-secondary btn-sm w-100">
                    <i class="bi bi-search me-1"></i>Filter
                </button>
            </div>
            @if(request()->hasAny(['search', 'kelas_id']))
            <div class="col-md-2">
                <a href="{{ route('bk.master.siswa.index') }}" class="btn btn-outline-secondary btn-sm w-100">
                    Reset
                </a>
            </div>
            @endif
        </form>
    </div>
</div>

{{-- Tabel Data Siswa --}}
<div class="card shadow-sm">
    <div class="card-body p-0">
        @if($siswa->isEmpty())
            <div class="text-center py-5 text-muted">
                <i class="bi bi-people fs-1 d-block mb-2"></i>
                Tidak ada data siswa ditemukan.
            </div>
        @else
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th style="width: 50px;">#</th>
                        <th>NIS</th>
                        <th>Nama Lengkap</th>
                        <th>L/P</th>
                        <th>Kelas</th>
                        <th>Nama Orang Tua</th>
                        <th>No. WA Ortu</th>
                        <th class="text-end" style="width: 140px;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                @foreach($siswa as $i => $s)
                    <tr>
                        <td class="text-muted small">{{ $siswa->firstItem() + $i }}</td>
                        <td><span class="badge bg-light text-dark border">{{ $s->nis }}</span></td>
                        <td class="fw-medium">{{ $s->nama_lengkap }}</td>
                        <td>{{ $s->jenis_kelamin }}</td>
                        <td>{{ $s->kelas->tingkat ?? '' }} {{ $s->kelas->nama_kelas ?? '-' }}</td>
                        <td>{{ $s->nama_ortu ?? '-' }}</td>
                        <td>
                            @if($s->no_wa_ortu)
                                <a href="https://wa.me/{{ preg_replace('/^0/', '62', preg_replace('/[^0-9]/', '', $s->no_wa_ortu)) }}"
                                   target="_blank" class="text-success text-decoration-none">
                                    <i class="bi bi-whatsapp me-1"></i>{{ $s->no_wa_ortu }}
                                </a>
                            @else
                                <span class="text-muted small">-</span>
                            @endif
                        </td>
                        <td class="text-end">
                            <a href="{{ route('bk.ews.detail-siswa', $s->id) }}" class="btn btn-sm btn-outline-info" title="Detail & Riwayat EWS">
                                <i class="bi bi-eye me-1"></i>Detail EWS
                            </a>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
        @if($siswa->hasPages())
            <div class="card-footer bg-transparent py-3">
                {{ $siswa->links() }}
            </div>
        @endif
        @endif
    </div>
</div>
@endsection

