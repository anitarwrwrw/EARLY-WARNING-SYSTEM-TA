@extends('layouts.app')
@section('page-title', 'Dashboard EWS — Pemetaan Prioritas Siswa')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold mb-0">Dashboard Early Warning System</h4>
        <small class="text-muted">Pemetaan Prioritas Penanganan Siswa — Semester: <strong>{{ $semester }}</strong></small>
    </div>
    <div class="d-flex gap-2 align-items-center">
        {{-- Filter Semester --}}
        <form method="GET" class="d-flex gap-2">
            <select name="semester" class="form-select form-select-sm" onchange="this.form.submit()">
                @foreach($semesterList as $sem)
                    <option value="{{ $sem }}" {{ $sem === $semester ? 'selected' : '' }}>{{ $sem }}</option>
                @endforeach
            </select>
        </form>

        {{-- Tombol Jalankan EWS --}}
        <form method="POST" action="{{ route('bk.ews.jalankan') }}">
            @csrf
            <input type="hidden" name="semester" value="{{ $semester }}">
            <button type="submit" class="btn btn-primary btn-sm"
                    onclick="return confirm('Jalankan Analisis EWS untuk semester {{ $semester }}?')">
                <i class="bi bi-cpu me-1"></i>Jalankan Analisis EWS
            </button>
        </form>
    </div>
</div>

{{-- ═══════════ KARTU STATISTIK ═══════════ --}}
<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="card stat-card shadow-sm">
            <div class="card-body py-3">
                <div class="d-flex align-items-center gap-3">
                    <div class="rounded-circle bg-primary bg-opacity-10 d-flex align-items-center justify-content-center"
                         style="width:48px;height:48px">
                        <i class="bi bi-people-fill text-primary fs-5"></i>
                    </div>
                    <div>
                        <div class="fs-4 fw-bold">{{ $stats['total_siswa'] }}</div>
                        <small class="text-muted">Total Siswa</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card stat-card shadow-sm border-danger border-opacity-25">
            <div class="card-body py-3">
                <div class="d-flex align-items-center gap-3">
                    <div class="rounded-circle bg-danger bg-opacity-10 d-flex align-items-center justify-content-center"
                         style="width:48px;height:48px">
                        <i class="bi bi-exclamation-triangle-fill text-danger fs-5"></i>
                    </div>
                    <div>
                        <div class="fs-4 fw-bold text-danger">{{ $stats['prioritas_1'] + $stats['prioritas_2'] }}</div>
                        <small class="text-muted">Risiko Tinggi</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card stat-card shadow-sm border-warning border-opacity-25">
            <div class="card-body py-3">
                <div class="d-flex align-items-center gap-3">
                    <div class="rounded-circle bg-warning bg-opacity-10 d-flex align-items-center justify-content-center"
                         style="width:48px;height:48px">
                        <i class="bi bi-eye-fill text-warning fs-5"></i>
                    </div>
                    <div>
                        <div class="fs-4 fw-bold text-warning">{{ $stats['prioritas_3'] }}</div>
                        <small class="text-muted">Pantau Ketat</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card stat-card shadow-sm border-success border-opacity-25">
            <div class="card-body py-3">
                <div class="d-flex align-items-center gap-3">
                    <div class="rounded-circle bg-success bg-opacity-10 d-flex align-items-center justify-content-center"
                         style="width:48px;height:48px">
                        <i class="bi bi-shield-check text-success fs-5"></i>
                    </div>
                    <div>
                        <div class="fs-4 fw-bold text-success">{{ $stats['risiko_rendah'] }}</div>
                        <small class="text-muted">Kondisi Aman</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- ═══════════ CHART + ANOMALI ═══════════ --}}
<div class="row g-3 mb-4">
    <div class="col-md-7">
        <div class="card shadow-sm h-100">
            <div class="card-header bg-transparent border-0 fw-semibold">
                <i class="bi bi-bar-chart-fill me-2 text-primary"></i>Distribusi Risiko per Kelas
            </div>
            <div class="card-body">
                <canvas id="chartDistribusi" height="200"></canvas>
            </div>
        </div>
    </div>
    <div class="col-md-5">
        <div class="card shadow-sm h-100">
            <div class="card-header bg-transparent border-0 fw-semibold">
                <i class="bi bi-pie-chart-fill me-2 text-primary"></i>Ringkasan Status Risiko
            </div>
            <div class="card-body d-flex flex-column justify-content-center">
                <canvas id="chartDonut" height="200"></canvas>
            </div>
        </div>
    </div>
</div>

{{-- ═══════════ TABEL PEMETAAN PRIORITAS ═══════════ --}}
<div class="card shadow-sm">
    <div class="card-header bg-transparent border-0 d-flex justify-content-between align-items-center">
        <span class="fw-semibold"><i class="bi bi-table me-2 text-primary"></i>Pemetaan Prioritas Penanganan Siswa</span>
        <small class="text-muted">{{ $hasilEws->count() }} siswa teranalisis</small>
    </div>
    <div class="card-body p-0">
        @if($hasilEws->isEmpty())
            <div class="text-center py-5 text-muted">
                <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                Belum ada data hasil analisis EWS.<br>
                <small>Klik tombol <strong>"Jalankan Analisis EWS"</strong> untuk memulai.</small>
            </div>
        @else
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th style="width:50px">#</th>
                        <th>Siswa</th>
                        <th>Kelas</th>
                        <th>Status Risiko</th>
                        <th>Level Prioritas</th>
                        <th>Pemicu Utama</th>
                        <th>Anomali</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                @foreach($hasilEws as $i => $ews)
                <tr>
                    <td class="text-muted small">{{ $i + 1 }}</td>
                    <td>
                        <div class="fw-semibold">{{ $ews->siswa->nama_lengkap }}</div>
                        <small class="text-muted">{{ $ews->siswa->nis }}</small>
                    </td>
                    <td><small>{{ $ews->siswa->kelas->nama_kelas ?? '-' }}</small></td>
                    <td>
                        <span class="badge rounded-pill
                            @if($ews->status_risiko === 'Risiko Tinggi') bg-danger
                            @elseif($ews->status_risiko === 'Risiko Sedang') bg-warning text-dark
                            @else bg-success @endif">
                            {{ $ews->status_risiko }}
                        </span>
                    </td>
                    <td>
                        <span class="badge rounded-pill badge-p{{ $ews->level_prioritas }}">
                            {{ $ews->ikon_prioritas }} {{ $ews->label_prioritas }}
                        </span>
                    </td>
                    <td><small class="text-muted">{{ $ews->pemicu_utama }}</small></td>
                    <td class="text-center">
                        @if($ews->is_anomali)
                            <span class="badge bg-orange text-white" style="background:#f97316 !important">
                                ⚠️ Anomali
                            </span>
                        @else
                            <span class="text-muted small">—</span>
                        @endif
                    </td>
                    <td>
                        <div class="d-flex gap-1">
                            <a href="{{ route('bk.ews.detail-siswa', $ews->siswa) }}"
                               class="btn btn-sm btn-outline-primary" title="Lihat Detail">
                                <i class="bi bi-graph-up"></i>
                            </a>
                            <button type="button" class="btn btn-sm btn-outline-success"
                                    data-bs-toggle="modal" data-bs-target="#modalWA"
                                    data-siswa-id="{{ $ews->siswa->id }}"
                                    data-siswa-nama="{{ $ews->siswa->nama_lengkap }}"
                                    title="Kirim WhatsApp">
                                <i class="bi bi-whatsapp"></i>
                            </button>
                        </div>
                    </td>
                </tr>
                @endforeach
                </tbody>
            </table>
        </div>
        @endif
    </div>
</div>

{{-- ═══════════ MODAL KIRIM WHATSAPP ═══════════ --}}
<div class="modal fade" id="modalWA" tabindex="-1">
    <div class="modal-dialog">
        <form method="POST" action="{{ route('bk.whatsapp.kirim') }}" class="modal-content">
            @csrf
            <div class="modal-header">
                <h6 class="modal-title"><i class="bi bi-whatsapp text-success me-2"></i>Kirim Notifikasi WhatsApp</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" name="siswa_id" id="wa_siswa_id">
                <p>Kepada orang tua: <strong id="wa_siswa_nama"></strong></p>
                <div class="mb-3">
                    <label class="form-label fw-medium">Pilih Template Pesan</label>
                    <select name="jenis_template" class="form-select" id="waTemplate">
                        <option value="koordinasi_preventif">📢 Koordinasi Preventif (Prioritas 1-3)</option>
                        <option value="undangan_konseling">📅 Undangan Konseling</option>
                        <option value="apresiasi_positif">🎉 Apresiasi Perkembangan Positif</option>
                    </select>
                </div>
                <div id="inputJadwal" style="display:none">
                    <div class="row g-2">
                        <div class="col-7">
                            <label class="form-label small">Tanggal Konseling</label>
                            <input type="date" name="tanggal_konseling" class="form-control form-control-sm">
                        </div>
                        <div class="col-5">
                            <label class="form-label small">Pukul</label>
                            <input type="text" name="jam_konseling" class="form-control form-control-sm"
                                   placeholder="09.00 WIB">
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-success btn-sm">
                    <i class="bi bi-send me-1"></i>Kirim Sekarang
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
// ─── Modal WhatsApp: isi data siswa ───────────────────────────────────────────
document.getElementById('modalWA').addEventListener('show.bs.modal', function (e) {
    const btn = e.relatedTarget;
    document.getElementById('wa_siswa_id').value   = btn.dataset.siswaId;
    document.getElementById('wa_siswa_nama').textContent = btn.dataset.siswaNama;
});

document.getElementById('waTemplate').addEventListener('change', function () {
    document.getElementById('inputJadwal').style.display =
        this.value === 'undangan_konseling' ? 'block' : 'none';
});

// ─── Chart Distribusi Per Kelas ───────────────────────────────────────────────
@php
    $labels  = $distribusiKelas->keys()->toArray();
    $rendah  = $distribusiKelas->pluck('rendah')->toArray();
    $sedang  = $distribusiKelas->pluck('sedang')->toArray();
    $tinggi  = $distribusiKelas->pluck('tinggi')->toArray();
@endphp

new Chart(document.getElementById('chartDistribusi'), {
    type: 'bar',
    data: {
        labels: {!! json_encode($labels) !!},
        datasets: [
            { label: 'Risiko Rendah', data: {!! json_encode($rendah) !!}, backgroundColor: '#22c55e' },
            { label: 'Risiko Sedang', data: {!! json_encode($sedang) !!}, backgroundColor: '#eab308' },
            { label: 'Risiko Tinggi', data: {!! json_encode($tinggi) !!}, backgroundColor: '#ef4444' },
        ]
    },
    options: {
        responsive: true,
        plugins: { legend: { position: 'top' } },
        scales: { x: { stacked: true }, y: { stacked: true, beginAtZero: true } }
    }
});

// ─── Chart Donut ──────────────────────────────────────────────────────────────
new Chart(document.getElementById('chartDonut'), {
    type: 'doughnut',
    data: {
        labels: ['Risiko Rendah', 'Risiko Sedang', 'Risiko Tinggi'],
        datasets: [{
            data: [{{ $stats['risiko_rendah'] }}, {{ $stats['risiko_sedang'] }}, {{ $stats['risiko_tinggi'] }}],
            backgroundColor: ['#22c55e', '#eab308', '#ef4444'],
            borderWidth: 2,
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: { position: 'bottom', labels: { boxWidth: 12 } }
        }
    }
});
</script>
@endpush

