@extends('layouts.app')
@section('page-title', 'Tindak Lanjut Bimbingan Konseling')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold mb-0">Tindak Lanjut & Konseling Siswa</h4>
        <small class="text-muted">Pencatatan sesi bimbingan, konseling individu/kelompok, dan panggilan orang tua</small>
    </div>
    <a href="{{ route('bk.tindaklanjut.create') }}" class="btn btn-primary btn-sm">
        <i class="bi bi-journal-plus me-1"></i>Catat Tindak Lanjut Baru
    </a>
</div>

<div class="card shadow-sm">
    <div class="card-body p-0">
        @if($tindakLanjut->isEmpty())
            <div class="text-center py-5 text-muted">
                <i class="bi bi-journal-text fs-1 d-block mb-2"></i>
                Belum ada riwayat catatan tindak lanjut konseling.
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
                        <th>Tipe Konseling</th>
                        <th>Terkait Hasil EWS</th>
                        <th>Catatan Pembinaan</th>
                        <th>Status</th>
                        <th>Konselor / Guru BK</th>
                        <th class="text-end" style="width: 90px;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                @foreach($tindakLanjut as $i => $tl)
                    <tr>
                        <td class="text-muted small">{{ $tindakLanjut->firstItem() + $i }}</td>
                        <td>{{ \Carbon\Carbon::parse($tl->tanggal_konseling)->isoFormat('D MMM Y') }}</td>
                        <td>
                            <div class="fw-semibold">{{ $tl->siswa->nama_lengkap }}</div>
                            <small class="text-muted">{{ $tl->siswa->nis }}</small>
                        </td>
                        <td><small>{{ $tl->siswa->kelas->tingkat ?? '' }} {{ $tl->siswa->kelas->nama_kelas ?? '-' }}</small></td>
                        <td>
                            <span class="badge bg-light text-dark border">{{ $tl->tipe_konseling ?? 'Individual' }}</span>
                        </td>
                        <td>
                            @if($tl->hasilEws)
                                <span class="badge badge-p{{ $tl->hasilEws->level_prioritas }}">
                                    {{ $tl->hasilEws->ikon_prioritas }} {{ $tl->hasilEws->label_prioritas }}
                                </span>
                            @else
                                <span class="text-muted small">Pembinaan Rutin</span>
                            @endif
                        </td>
                        <td>
                            <div style="max-width: 250px;" class="text-truncate" title="{{ $tl->catatan_pembinaan }}">
                                {{ $tl->catatan_pembinaan ?: '-' }}
                            </div>
                        </td>
                        <td>
                            @if($tl->status_penanganan === 'Selesai')
                                <span class="badge bg-success"><i class="bi bi-check2 me-1"></i>Selesai</span>
                            @else
                                <span class="badge bg-warning text-dark"><i class="bi bi-arrow-repeat me-1"></i>Dalam Proses</span>
                            @endif
                        </td>
                        <td><small class="text-muted">{{ $tl->guruBk->name ?? '-' }}</small></td>
                        <td class="text-end">
                            <a href="{{ route('bk.tindaklanjut.edit', $tl->id) }}" class="btn btn-sm btn-outline-primary" title="Edit Tindak Lanjut">
                                <i class="bi bi-pencil"></i>
                            </a>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
        @if($tindakLanjut->hasPages())
            <div class="card-footer bg-transparent py-3">
                {{ $tindakLanjut->links() }}
            </div>
        @endif
        @endif
    </div>
</div>
@endsection

