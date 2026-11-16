View [bk.pelanggaran.index] not found.@extends('layouts.app')
@section('page-title', 'Detail & Tren Perkembangan Siswa')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold mb-0">Tren Perkembangan & Analitik Siswa</h4>
        <small class="text-muted">{{ $siswa->nama_lengkap }} (NIS: {{ $siswa->nis }}) — Kelas {{ $siswa->kelas->tingkat ?? '' }} {{ $siswa->kelas->nama_kelas ?? '-' }}</small>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('bk.dashboard') }}" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left me-1"></i>Kembali ke Dashboard
        </a>
        <a href="{{ route('bk.tindaklanjut.create', ['siswa_id' => $siswa->id]) }}" class="btn btn-primary btn-sm">
            <i class="bi bi-journal-plus me-1"></i>Catat Tindak Lanjut
        </a>
    </div>
</div>

<div class="row g-4 mb-4">
    {{-- Card Profil Siswa --}}
    <div class="col-lg-4">
        <div class="card shadow-sm h-100">
            <div class="card-header bg-transparent border-0 fw-semibold">
                <i class="bi bi-person-lines-fill text-primary me-2"></i>Informasi Siswa & Kontak
            </div>
            <div class="card-body">
                <div class="text-center mb-3">
                    <div class="bg-primary bg-opacity-10 rounded-circle d-inline-flex align-items-center justify-content-center mb-2" style="width: 70px; height: 70px;">
                        <span class="fs-2 text-primary fw-bold">{{ substr($siswa->nama_lengkap, 0, 1) }}</span>
                    </div>
                    <h5 class="fw-bold mb-0">{{ $siswa->nama_lengkap }}</h5>
                    <span class="text-muted small">NIS: {{ $siswa->nis }}</span>
                </div>

                <ul class="list-group list-group-flush small">
                    <li class="list-group-item d-flex justify-content-between px-0">
                        <span class="text-muted">Kelas:</span>
                        <span class="fw-medium">{{ $siswa->kelas->tingkat ?? '' }} {{ $siswa->kelas->nama_kelas ?? '-' }} ({{ $siswa->kelas->jurusan ?? '-' }})</span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between px-0">
                        <span class="text-muted">Jenis Kelamin:</span>
                        <span class="fw-medium">{{ $siswa->jenis_kelamin === 'L' ? 'Laki-laki' : 'Perempuan' }}</span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between px-0">
                        <span class="text-muted">Orang Tua / Wali:</span>
                        <span class="fw-medium">{{ $siswa->nama_ortu ?: '-' }}</span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between px-0">
                        <span class="text-muted">No. WhatsApp Ortu:</span>
                        @if($siswa->no_wa_ortu)
                            <a href="https://wa.me/{{ preg_replace('/^0/', '62', preg_replace('/[^0-9]/', '', $siswa->no_wa_ortu)) }}"
                               target="_blank" class="text-success text-decoration-none fw-medium">
                                <i class="bi bi-whatsapp me-1"></i>{{ $siswa->no_wa_ortu }}
                            </a>
                        @else
                            <span class="text-muted">-</span>
                        @endif
                    </li>
                    <li class="list-group-item d-flex justify-content-between px-0">
                        <span class="text-muted">Akumulasi Pelanggaran:</span>
                        <span class="badge bg-danger bg-opacity-10 text-danger border border-danger">
                            {{ $siswa->totalPoinPelanggaran() }} Poin
                        </span>
                    </li>
                </ul>

                @php $ewsTerbaru = $riwayatEws->last(); @endphp
                @if($ewsTerbaru)
                    <div class="mt-3 p-3 rounded border bg-light">
                        <div class="small text-muted mb-1">Status EWS Terakhir:</div>
                        <div class="d-flex align-items-center justify-content-between">
                            <span class="badge rounded-pill badge-p{{ $ewsTerbaru->level_prioritas }}">
                                {{ $ewsTerbaru->ikon_prioritas }} {{ $ewsTerbaru->label_prioritas }}
                            </span>
                            @if($ewsTerbaru->is_anomali)
                                <span class="badge bg-warning text-dark">⚠️ Anomali</span>
                            @endif
                        </div>
                        <div class="small text-muted mt-2">
                            <strong>Pemicu:</strong> {{ $ewsTerbaru->pemicu_utama ?: 'Tidak ada' }}
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- Line Chart Tren Siswa --}}
    <div class="col-lg-8">
        <div class="card shadow-sm h-100">
            <div class="card-header bg-transparent border-0 d-flex justify-content-between align-items-center">
                <span class="fw-semibold"><i class="bi bi-graph-up text-primary me-2"></i>Grafik Tren Perkembangan Lintas Semester</span>
                <small class="text-muted">Pergerakan Nilai & Level Prioritas Risiko</small>
            </div>
            <div class="card-body">
                <div style="height: 300px;">
                    <canvas id="chartTrenSiswa"></canvas>
                </div>
                <div class="small text-muted text-center mt-2">
                    <span class="me-3"><span style="display:inline-block;width:12px;height:12px;background:#3b82f6;border-radius:2px;" class="me-1"></span> Rata-rata Nilai Rapor</span>
                    <span><span style="display:inline-block;width:12px;height:12px;background:#ef4444;border-radius:2px;" class="me-1"></span> Level Prioritas EWS (1 = Kritis, 5 = Aman)</span>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Riwayat Analisis EWS --}}
<div class="card shadow-sm mb-4">
    <div class="card-header bg-transparent border-0 fw-semibold">
        <i class="bi bi-clock-history text-primary me-2"></i>Riwayat Deteksi Early Warning System (EWS)
    </div>
    <div class="card-body p-0">
        @if($riwayatEws->isEmpty())
            <div class="text-center py-4 text-muted">Belum ada riwayat analisis EWS untuk siswa ini.</div>
        @else
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Semester</th>
                        <th>Waktu Analisis</th>
                        <th>Status Risiko (Random Forest)</th>
                        <th>Deteksi Anomali (Isolation Forest)</th>
                        <th>Level Prioritas</th>
                        <th>Pemicu Utama</th>
                    </tr>
                </thead>
                <tbody>
                @foreach($riwayatEws as $rews)
                    <tr>
                        <td class="fw-medium">{{ $rews->semester }}</td>
                        <td><small>{{ $rews->tanggal_analisis?->isoFormat('D MMM Y, HH:mm') ?? '-' }}</small></td>
                        <td>
                            <span class="badge rounded-pill
                                @if($rews->status_risiko === 'Risiko Tinggi') bg-danger
                                @elseif($rews->status_risiko === 'Risiko Sedang') bg-warning text-dark
                                @else bg-success @endif">
                                {{ $rews->status_risiko }}
                            </span>
                        </td>
                        <td>
                            @if($rews->is_anomali)
                                <span class="badge bg-warning text-dark">⚠️ Anomali ({{ $rews->anomaly_score }})</span>
                            @else
                                <span class="badge bg-light text-muted border">Normal</span>
                            @endif
                        </td>
                        <td>
                            <span class="badge badge-p{{ $rews->level_prioritas }}">
                                {{ $rews->ikon_prioritas }} {{ $rews->label_prioritas }}
                            </span>
                        </td>
                        <td><small class="text-muted">{{ $rews->pemicu_utama ?: '-' }}</small></td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
        @endif
    </div>
</div>

{{-- Riwayat Akademik & Absensi --}}
<div class="card shadow-sm mb-4">
    <div class="card-header bg-transparent border-0 fw-semibold">
        <i class="bi bi-journal-bookmark text-primary me-2"></i>Rekap Nilai Rapor & Absensi per Semester
    </div>
    <div class="card-body p-0">
        @if($riwayatAkademik->isEmpty())
            <div class="text-center py-4 text-muted">Belum ada data akademik & absensi.</div>
        @else
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Semester</th>
                        <th>Rata-rata Nilai</th>
                        <th>Tren Nilai</th>
                        <th>Alpha (Hari)</th>
                        <th>Izin</th>
                        <th>Sakit</th>
                    </tr>
                </thead>
                <tbody>
                @foreach($riwayatAkademik as $ra)
                    <tr>
                        <td class="fw-medium">{{ $ra->semester }}</td>
                        <td><span class="fw-bold">{{ number_format($ra->rata_rata_nilai, 1) }}</span></td>
                        <td>
                            <span class="badge {{ $ra->tren_nilai >= 0 ? 'bg-success' : 'bg-danger' }}">
                                {{ $ra->tren_nilai >= 0 ? '+' : '' }}{{ number_format($ra->tren_nilai, 1) }}
                            </span>
                        </td>
                        <td>
                            <span class="badge {{ $ra->jumlah_alpha > 3 ? 'bg-danger' : 'bg-light text-dark border' }}">
                                {{ $ra->jumlah_alpha }}
                            </span>
                        </td>
                        <td>{{ $ra->jumlah_izin }}</td>
                        <td>{{ $ra->jumlah_sakit }}</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
        @endif
    </div>
</div>

{{-- Riwayat Tindak Lanjut Konseling --}}
<div class="card shadow-sm">
    <div class="card-header bg-transparent border-0 fw-semibold">
        <i class="bi bi-chat-heart text-primary me-2"></i>Riwayat Tindak Lanjut & Catatan Konseling BK
    </div>
    <div class="card-body p-0">
        @if($tindakLanjut->isEmpty())
            <div class="text-center py-4 text-muted">Belum ada catatan tindak lanjut bimbingan konseling.</div>
        @else
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Tanggal</th>
                        <th>Tipe Bimbingan</th>
                        <th>Status</th>
                        <th>Catatan Pembinaan</th>
                        <th>Konselor</th>
                    </tr>
                </thead>
                <tbody>
                @foreach($tindakLanjut as $tl)
                    <tr>
                        <td>{{ \Carbon\Carbon::parse($tl->tanggal_konseling)->isoFormat('D MMM Y') }}</td>
                        <td><span class="badge bg-light text-dark border">{{ $tl->tipe_konseling ?? 'Individual' }}</span></td>
                        <td>
                            @if($tl->status_penanganan === 'Selesai')
                                <span class="badge bg-success">Selesai</span>
                            @else
                                <span class="badge bg-warning text-dark">Proses</span>
                            @endif
                        </td>
                        <td>{{ $tl->catatan_pembinaan ?: '-' }}</td>
                        <td><small class="text-muted">{{ $tl->guruBk->name ?? '-' }}</small></td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
        @endif
    </div>
</div>
@endsection

@push('scripts')
<script>
    const labels = {!! json_encode($chartData['labels']) !!};
    const nilaiData = {!! json_encode($chartData['rata_rata_nilai']) !!};
    const prioritasData = {!! json_encode($chartData['level_prioritas']) !!};

    const ctx = document.getElementById('chartTrenSiswa').getContext('2d');
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: labels.length ? labels : ['Belum ada semester'],
            datasets: [
                {
                    label: 'Rata-rata Nilai Rapor',
                    data: nilaiData.length ? nilaiData : [0],
                    borderColor: '#3b82f6',
                    backgroundColor: 'rgba(59, 130, 246, 0.1)',
                    yAxisID: 'yNilai',
                    tension: 0.3,
                    fill: true,
                    pointRadius: 5
                },
                {
                    label: 'Level Prioritas (1-5)',
                    data: prioritasData.length ? prioritasData : [5],
                    borderColor: '#ef4444',
                    backgroundColor: 'transparent',
                    yAxisID: 'yPrioritas',
                    tension: 0.2,
                    borderDash: [5, 5],
                    pointRadius: 6,
                    pointBackgroundColor: '#ef4444'
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                yNilai: {
                    type: 'linear',
                    display: true,
                    position: 'left',
                    title: { display: true, text: 'Nilai Rapor (0-100)' },
                    min: 40,
                    max: 100
                },
                yPrioritas: {
                    type: 'linear',
                    display: true,
                    position: 'right',
                    title: { display: true, text: 'Level Prioritas (1 = Kritis)' },
                    min: 1,
                    max: 5,
                    ticks: { stepSize: 1, reverse: true },
                    grid: { drawOnChartArea: false }
                }
            }
        }
    });
</script>
@endpush

