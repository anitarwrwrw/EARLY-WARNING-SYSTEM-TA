@extends('layouts.app')
@section('page-title', 'Pengaturan Sistem & Integrasi API')

@section('content')
<div class="mb-4">
    <h4 class="fw-bold mb-0">Pengaturan Sistem</h4>
    <small class="text-muted">Konfigurasi koneksi layanan Machine Learning Flask & WhatsApp Gateway (Fonnte)</small>
</div>

<div class="row g-4">
    <div class="col-md-8">
        <div class="card shadow-sm">
            <div class="card-header bg-transparent border-0 fw-semibold">
                <i class="bi bi-sliders me-2 text-primary"></i>Konfigurasi Endpoint & Token
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('admin.pengaturan.update') }}">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label fw-medium">Nama Sekolah / Instansi <span class="text-danger">*</span></label>
                        <input type="text" name="nama_sekolah" class="form-control @error('nama_sekolah') is-invalid @enderror"
                               value="{{ old('nama_sekolah', $namaSekolah) }}" required>
                        @error('nama_sekolah')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-medium">URL Endpoint Flask ML API <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <input type="url" name="flask_api_url" id="flask_api_url"
                                   class="form-control @error('flask_api_url') is-invalid @enderror"
                                   value="{{ old('flask_api_url', $flaskUrl) }}" required>
                            <button type="button" class="btn btn-outline-primary" id="btnTestFlask">
                                <i class="bi bi-plug me-1"></i>Test Koneksi
                            </button>
                        </div>
                        <div id="flaskTestResult" class="small mt-1"></div>
                        @error('flask_api_url')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-medium">Token WhatsApp Gateway (Fonnte)</label>
                        <div class="input-group">
                            <input type="text" name="wa_gateway_token" id="wa_gateway_token"
                                   class="form-control @error('wa_gateway_token') is-invalid @enderror"
                                   value="{{ old('wa_gateway_token', $waToken) }}" placeholder="Masukkan API Token Fonnte">
                            <button type="button" class="btn btn-outline-success" id="btnTestWa">
                                <i class="bi bi-whatsapp me-1"></i>Cek Token WA
                            </button>
                        </div>
                        <div id="waTestResult" class="small mt-1"></div>
                        @error('wa_gateway_token')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                    </div>

                    <div class="d-flex justify-content-end border-top pt-3">
                        <button type="submit" class="btn btn-primary btn-sm">
                            <i class="bi bi-save me-1"></i>Simpan Pengaturan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card shadow-sm">
            <div class="card-header bg-transparent border-0 fw-semibold">
                <i class="bi bi-info-circle me-2 text-primary"></i>Catatan Integrasi
            </div>
            <div class="card-body small text-muted">
                <p><strong>1. Flask ML API:</strong> Pastikan server Python Flask berjalan di port yang dikonfigurasi (default <code>http://127.0.0.1:5000</code>).</p>
                <p class="mb-0"><strong>2. WhatsApp Gateway:</strong> Token Fonnte digunakan oleh Guru BK untuk mengirim notifikasi otomatis ke WhatsApp orang tua siswa.</p>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.getElementById('btnTestFlask').addEventListener('click', async function () {
    const resultEl = document.getElementById('flaskTestResult');
    const url = document.getElementById('flask_api_url').value;
    resultEl.innerHTML = '<span class="text-muted">Menguji koneksi...</span>';

    try {
        const res = await fetch("{{ route('admin.pengaturan.test-flask') }}", {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({ url })
        });
        const data = await res.json();
        resultEl.innerHTML = `<span class="${data.status === 'success' ? 'text-success' : 'text-danger'}">${data.message}</span>`;
    } catch (err) {
        resultEl.innerHTML = '<span class="text-danger">Terjadi kesalahan jaringan saat menguji koneksi.</span>';
    }
});

document.getElementById('btnTestWa').addEventListener('click', async function () {
    const resultEl = document.getElementById('waTestResult');
    const token = document.getElementById('wa_gateway_token').value;
    resultEl.innerHTML = '<span class="text-muted">Memeriksa token...</span>';

    try {
        const res = await fetch("{{ route('admin.pengaturan.test-wa') }}", {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({ token })
        });
        const data = await res.json();
        resultEl.innerHTML = `<span class="${data.status === 'success' ? 'text-success' : 'text-danger'}">${data.message}</span>`;
    } catch (err) {
        resultEl.innerHTML = '<span class="text-danger">Terjadi kesalahan jaringan saat memeriksa token.</span>';
    }
});
</script>
@endpush
