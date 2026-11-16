<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kuesioner Siswa — SMK Sunan Ampel</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <!-- Google Font -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f1f5f9;
            color: #1e293b;
        }
        .header-box {
            background: linear-gradient(135deg, #1e3a8a 0%, #2563eb 100%);
            color: white;
            border-radius: 1rem 1rem 0 0;
            padding: 2rem 1.5rem;
        }
        .form-card {
            border: none;
            border-radius: 1rem;
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1), 0 8px 10px -6px rgba(0, 0, 0, 0.1);
        }
        .likert-option {
            flex: 1;
            min-width: 60px;
            text-align: center;
            border: 1px solid #cbd5e1;
            border-radius: 0.5rem;
            padding: 0.5rem 0.25rem;
            cursor: pointer;
            transition: all 0.2s ease;
            background-color: #fff;
        }
        .likert-option:hover {
            background-color: #f8fafc;
            border-color: #94a3b8;
        }
        .likert-input:checked + .likert-label {
            background-color: #2563eb;
            color: white;
            border-color: #2563eb;
        }
        .likert-input {
            display: none;
        }
        .likert-label {
            display: block;
            margin: 0;
            width: 100%;
            height: 100%;
            border-radius: 0.5rem;
            cursor: pointer;
        }
        .question-card {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 0.75rem;
            padding: 1.25rem;
            margin-bottom: 1rem;
            transition: border-color 0.2s ease;
        }
        .question-card:focus-within {
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.15);
        }
        .aspek-title {
            color: #1e3a8a;
            border-left: 4px solid #2563eb;
            padding-left: 0.75rem;
            font-weight: 700;
            margin-top: 2rem;
            margin-bottom: 1.25rem;
        }
    </style>
</head>
<body>

<div class="container py-4 py-md-5">
    <div class="row justify-content-center">
        <div class="col-lg-9 col-xl-8">

            <div class="card form-card">
                <!-- Header Card -->
                <div class="header-box text-center">
                    <div class="mb-2">
                        <span class="badge bg-white text-primary px-3 py-2 rounded-pill fw-semibold shadow-sm">
                            <i class="bi bi-shield-check me-1"></i> Bimbingan Konseling & EWS
                        </span>
                    </div>
                    <h3 class="fw-bold mb-1">Kuesioner Kesejahteraan Siswa</h3>
                    <p class="mb-0 text-light opacity-90">SMK Sunan Ampel Jombang</p>
                    @if($semesterAktif)
                        <div class="mt-2 text-warning small fw-semibold">
                            <i class="bi bi-calendar-event me-1"></i> Periode: {{ $semesterAktif->nama_semester }}
                        </div>
                    @endif
                </div>

                <div class="card-body p-4 p-md-5">

                    {{-- Notifikasi Error / Sukses --}}
                    @if(session('error'))
                        <div class="alert alert-danger d-flex align-items-center mb-4" role="alert">
                            <i class="bi bi-exclamation-triangle-fill fs-4 me-3"></i>
                            <div>{{ session('error') }}</div>
                        </div>
                    @endif

                    @if(session('warning'))
                        <div class="alert alert-warning d-flex align-items-center mb-4" role="alert">
                            <i class="bi bi-info-circle-fill fs-4 me-3"></i>
                            <div>{{ session('warning') }}</div>
                        </div>
                    @endif

                    @if(! $isBuka)
                        {{-- Kuesioner Sedang Ditutup --}}
                        <div class="text-center py-5">
                            <i class="bi bi-lock-fill text-muted display-1 mb-3"></i>
                            <h4 class="fw-bold text-secondary">Kuesioner Saat Ini Ditutup</h4>
                            <p class="text-muted mb-0">
                                Form survei kesejahteraan belum dibuka oleh pihak sekolah.<br>
                                Silakan hubungi Guru BK atau Wali Kelas Anda untuk informasi lebih lanjut.
                            </p>
                        </div>
                    @else
                        {{-- Petunjuk Pengisian --}}
                        <div class="p-3 bg-light rounded-3 mb-4 border border-light-subtle">
                            <h6 class="fw-bold text-primary mb-1"><i class="bi bi-info-circle me-1"></i> Petunjuk Pengisian:</h6>
                            <p class="small text-muted mb-0">
                                Kuesioner ini bertujuan untuk memahami kenyamanan belajar dan kondisi psikososial Anda.
                                <strong>Tidak ada jawaban benar atau salah.</strong> Jawablah dengan jujur sesuai apa yang Anda rasakan. Kerahasiaan jawaban Anda dijamin sepenuhnya.
                            </p>
                        </div>

                        <form method="POST" action="{{ route('kuesioner.store') }}" id="kuesionerForm">
                            @csrf

                            <!-- SECTION 1: IDENTITAS SISWA -->
                            <div class="card bg-light border-0 p-3 p-md-4 rounded-3 mb-4">
                                <h6 class="fw-bold text-dark mb-3">
                                    <i class="bi bi-person-badge text-primary me-2"></i>1. Verifikasi Identitas Siswa
                                </h6>

                                <div class="row g-3">
                                    {{-- Dropdown Kelas --}}
                                    <div class="col-md-6">
                                        <label for="kelas_id" class="form-label small fw-semibold">Pilih Kelas <span class="text-danger">*</span></label>
                                        <select class="form-select @error('kelas_id') is-invalid @enderror" id="kelas_id" name="kelas_id" required>
                                            <option value="">-- Pilih Kelas Anda --</option>
                                            @foreach($kelasList as $kelas)
                                                <option value="{{ $kelas->id }}"
                                                    {{ (old('kelas_id', $preselectedKelasId) == $kelas->id) ? 'selected' : '' }}>
                                                    {{ $kelas->tingkat }} {{ $kelas->nama_kelas }} ({{ $kelas->jurusan }})
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('kelas_id')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    {{-- Dropdown Siswa (Populated via AJAX) --}}
                                    <div class="col-md-6">
                                        <label for="siswa_id" class="form-label small fw-semibold">Nama Siswa <span class="text-danger">*</span></label>
                                        <select class="form-select @error('siswa_id') is-invalid @enderror" id="siswa_id" name="siswa_id" required disabled>
                                            <option value="">-- Pilih Kelas Terlebih Dahulu --</option>
                                        </select>
                                        <div id="siswaSpinner" class="spinner-border spinner-border-sm text-primary d-none mt-2" role="status">
                                            <span class="visually-hidden">Loading...</span>
                                        </div>
                                        @error('siswa_id')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    {{-- Input NISN untuk Verifikasi --}}
                                    <div class="col-12">
                                        <label for="nisn" class="form-label small fw-semibold">
                                            Nomor Induk Siswa Nasional (NISN) <span class="text-danger">*</span>
                                        </label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-white"><i class="bi bi-shield-lock text-muted"></i></span>
                                            <input type="text"
                                                   class="form-select-lg form-control @error('nisn') is-invalid @enderror"
                                                   id="nisn"
                                                   name="nisn"
                                                   placeholder="Masukkan NISN Anda untuk verifikasi identitas"
                                                   value="{{ old('nisn') }}"
                                                   required
                                                   autocomplete="off">
                                            @error('nisn')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                        <div class="form-text text-muted" style="font-size: 0.8rem;">
                                            <i class="bi bi-info-circle me-1"></i>NISN digunakan sebagai kata sandi verifikasi untuk memastikan hanya Anda yang mengisi kuesioner ini.
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- SECTION 2: BUTIR KUESIONER -->
                            <div class="mb-4">
                                <h6 class="fw-bold text-dark mb-1">
                                    <i class="bi bi-ui-checks text-primary me-2"></i>2. Pernyataan Survei (Skala 1 - 5)
                                </h6>
                                <p class="small text-muted mb-4">
                                    Keterangan: <strong>1</strong> = Sangat Tidak Setuju, <strong>2</strong> = Tidak Setuju, <strong>3</strong> = Netral, <strong>4</strong> = Setuju, <strong>5</strong> = Sangat Setuju
                                </p>

                                @php
                                    $aspekSaatIni = '';
                                    $aspekIcon = [
                                        'motivasi' => 'bi-lightning-charge-fill',
                                        'keluarga' => 'bi-house-heart-fill',
                                        'keamanan' => 'bi-shield-check',
                                    ];
                                @endphp

                                @foreach($butirList as $b)
                                    @if($aspekSaatIni !== $b['aspek'])
                                        @php $aspekSaatIni = $b['aspek']; @endphp
                                        <div class="aspek-title">
                                            <i class="bi {{ $aspekIcon[$b['aspek']] ?? 'bi-star' }} me-2"></i>
                                            Aspek {{ $b['label_aspek'] }}
                                        </div>
                                    @endif

                                    <div class="question-card @error('jawaban.'.$b['no']) border-danger @enderror">
                                        <div class="fw-medium mb-3">
                                            <span class="badge bg-primary-subtle text-primary border border-primary-subtle me-2">{{ $b['no'] }}</span>
                                            {{ $b['pernyataan'] }}
                                        </div>

                                        <div class="d-flex gap-2 justify-content-between">
                                            @foreach([
                                                1 => 'Sangat Tidak Setuju',
                                                2 => 'Tidak Setuju',
                                                3 => 'Netral',
                                                4 => 'Setuju',
                                                5 => 'Sangat Setuju'
                                            ] as $val => $label)
                                                <div class="likert-option p-0">
                                                    <input type="radio"
                                                           class="likert-input"
                                                           name="jawaban[{{ $b['no'] }}]"
                                                           id="q_{{ $b['no'] }}_{{ $val }}"
                                                           value="{{ $val }}"
                                                           {{ old('jawaban.'.$b['no']) == $val ? 'checked' : '' }}
                                                           required>
                                                    <label class="likert-label p-2" for="q_{{ $b['no'] }}_{{ $val }}">
                                                        <div class="fw-bold fs-5">{{ $val }}</div>
                                                        <div class="d-none d-md-block text-truncate" style="font-size: 0.65rem;">
                                                            {{ $label }}
                                                        </div>
                                                    </label>
                                                </div>
                                            @endforeach
                                        </div>

                                        @error('jawaban.'.$b['no'])
                                            <div class="text-danger small mt-2">
                                                <i class="bi bi-exclamation-circle me-1"></i>{{ $message }}
                                            </div>
                                        @enderror
                                    </div>
                                @endforeach
                            </div>

                            <!-- TOMBOL SUBMIT -->
                            <div class="d-grid gap-2 pt-3">
                                <button type="submit" class="btn btn-primary btn-lg fw-bold shadow-sm py-3" id="btnSubmit">
                                    <i class="bi bi-send-check me-2"></i> Kirim Jawaban Kuesioner
                                </button>
                            </div>
                        </form>
                    @endif

                </div>
                <div class="card-footer bg-light text-center py-3 text-muted small rounded-bottom">
                    &copy; {{ date('Y') }} Tim Bimbingan Konseling &bull; SMK Sunan Ampel Jombang
                </div>
            </div>

        </div>
    </div>
</div>

<!-- Bootstrap 5 Bundle JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const kelasSelect = document.getElementById('kelas_id');
    const siswaSelect = document.getElementById('siswa_id');
    const spinner = document.getElementById('siswaSpinner');
    const oldSiswaId = "{{ old('siswa_id') }}";

    function loadSiswa(kelasId, selectedId = null) {
        if (!kelasId) {
            siswaSelect.innerHTML = '<option value="">-- Pilih Kelas Terlebih Dahulu --</option>';
            siswaSelect.disabled = true;
            return;
        }

        siswaSelect.disabled = true;
        if (spinner) spinner.classList.remove('d-none');
        siswaSelect.innerHTML = '<option value="">Memuat data siswa...</option>';

        fetch(`/api/siswa-by-kelas/${kelasId}`)
            .then(res => {
                if (!res.ok) throw new Error('Gagal memuat data');
                return res.json();
            })
            .then(data => {
                let options = '<option value="">-- Pilih Nama Anda --</option>';
                data.forEach(siswa => {
                    const isSelected = (selectedId && selectedId == siswa.id) ? 'selected' : '';
                    const statusText = siswa.sudah_isi ? ' (Sudah Mengisi ✓)' : '';
                    options += `<option value="${siswa.id}" ${isSelected}>${siswa.nama_lengkap}${statusText}</option>`;
                });
                siswaSelect.innerHTML = options;
                siswaSelect.disabled = false;
            })
            .catch(err => {
                console.error(err);
                siswaSelect.innerHTML = '<option value="">Gagal memuat daftar siswa</option>';
            })
            .finally(() => {
                if (spinner) spinner.classList.add('d-none');
            });
    }

    if (kelasSelect) {
        kelasSelect.addEventListener('change', function () {
            loadSiswa(this.value);
        });

        // Trigger saat load pertama jika kelas sudah terpilih (misal old() atau via query param)
        if (kelasSelect.value) {
            loadSiswa(kelasSelect.value, oldSiswaId);
        }
    }
});
</script>

</body>
</html>
