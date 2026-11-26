@extends('layouts.app')
@section('page-title', 'Riwayat Log WhatsApp')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold mb-0">Riwayat Notifikasi WhatsApp Orang Tua</h4>
        <small class="text-muted">Log pengiriman pesan peringatan dini, undangan konseling, dan apresiasi</small>
    </div>
    <a href="{{ route('bk.tindaklanjut.index') }}" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-arrow-left me-1"></i>Kembali ke Tindak Lanjut
    </a>
</div>

<div class="card shadow-sm">
    <div class="card-body p-0">
        @if($logs->isEmpty())
            <div class="text-center py-5 text-muted">
                <i class="bi bi-whatsapp fs-1 d-block mb-2 text-success"></i>
                Belum ada riwayat pengiriman pesan WhatsApp.
            </div>
        @else
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th style="width:50px">#</th>
                        <th>Waktu Kirim</th>
                        <th>Siswa & Kelas</th>
                        <th>No. WA Tujuan</th>
                        <th>Template</th>
                        <th>Ringkasan Pesan</th>
                        <th>Status</th>
                        <th>Pengirim</th>
                    </tr>
                </thead>
                <tbody>
                @foreach($logs as $i => $log)
                    <tr>
                        <td class="text-muted small">{{ $logs->firstItem() + $i }}</td>
                        <td><small>{{ $log->waktu_kirim?->isoFormat('D MMM Y, HH:mm') ?? '-' }}</small></td>
                        <td>
                            <div class="fw-semibold">{{ $log->siswa->nama_lengkap ?? '-' }}</div>
                            <small class="text-muted">{{ $log->siswa->kelas->tingkat ?? '' }} {{ $log->siswa->kelas->nama_kelas ?? '-' }}</small>
                        </td>
                        <td><code>{{ $log->no_wa_tujuan }}</code></td>
                        <td><span class="badge bg-light text-dark border">{{ str_replace('_', ' ', ucfirst($log->jenis_template)) }}</span></td>
                        <td>
                            <div style="max-width:260px" class="text-truncate small text-muted" title="{{ $log->isi_pesan }}">
                                {{ $log->isi_pesan }}
                            </div>
                        </td>
                        <td>
                            @if($log->status_kirim === 'Berhasil')
                                <span class="badge bg-success">Berhasil</span>
                            @else
                                <span class="badge bg-danger">Gagal</span>
                            @endif
                        </td>
                        <td><small class="text-muted">{{ $log->guruBk->name ?? '-' }}</small></td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
        @if($logs->hasPages())
            <div class="card-footer bg-transparent py-3">
                {{ $logs->links() }}
            </div>
        @endif
        @endif
    </div>
</div>
@endsection
