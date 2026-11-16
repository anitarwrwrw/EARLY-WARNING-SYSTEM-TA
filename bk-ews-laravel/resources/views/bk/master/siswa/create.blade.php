@extends('layouts.app')
@section('page-title', 'Tambah Data Siswa')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card shadow-sm">
            <div class="card-header bg-transparent d-flex justify-content-between align-items-center">
                <span class="fw-semibold"><i class="bi bi-person-plus text-primary me-2"></i>Form Tambah Siswa Baru</span>
                <a href="{{ route('bk.master.siswa.index') }}" class="btn btn-outline-secondary btn-sm">
                    <i class="bi bi-arrow-left me-1"></i>Kembali
                </a>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('bk.master.siswa.store') }}">
                    @csrf

                    <h6 class="fw-bold text-primary mb-3">Informasi Pokok Siswa</h6>
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-medium">Nomor Induk Siswa (NIS) <span class="text-danger">*</span></label>
                            <input type="text" name="nis" class="form-control @error('nis') is-invalid @enderror"
                                   value="{{ old('nis') }}" placeholder="Contoh: 20240101" required>
                            @error('nis')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-medium">Nama Lengkap <span class="text-danger">*</span></label>
                            <input type="text" name="nama_lengkap" class="form-control @error('nama_lengkap') is-invalid @enderror"
                                   value="{{ old('nama_lengkap') }}" placeholder="Nama sesuai ijazah/akta" required>
                            @error('nama_lengkap')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-medium">Kelas <span class="text-danger">*</span></label>
                            <select name="kelas_id" class="form-select @error('kelas_id') is-invalid @enderror" required>
                                <option value="">-- Pilih Kelas --</option>
                                @foreach($kelas as $k)
                                    <option value="{{ $k->id }}" {{ old('kelas_id') == $k->id ? 'selected' : '' }}>
                                        {{ $k->tingkat }} {{ $k->nama_kelas }} ({{ $k->jurusan }})
                                    </option>
                                @endforeach
                            </select>
                            @error('kelas_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-medium">Jenis Kelamin <span class="text-danger">*</span></label>
                            <select name="jenis_kelamin" class="form-select @error('jenis_kelamin') is-invalid @enderror" required>
                                <option value="">-- Pilih Jenis Kelamin --</option>
                                <option value="L" {{ old('jenis_kelamin') == 'L' ? 'selected' : '' }}>Laki-laki (L)</option>
                                <option value="P" {{ old('jenis_kelamin') == 'P' ? 'selected' : '' }}>Perempuan (P)</option>
                            </select>
                            @error('jenis_kelamin')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-medium">Tanggal Lahir</label>
                            <input type="date" name="tanggal_lahir" class="form-control @error('tanggal_lahir') is-invalid @enderror"
                                   value="{{ old('tanggal_lahir') }}">
                            @error('tanggal_lahir')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-medium">Alamat</label>
                            <input type="text" name="alamat" class="form-control @error('alamat') is-invalid @enderror"
                                   value="{{ old('alamat') }}" placeholder="Alamat domisili">
                            @error('alamat')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <h6 class="fw-bold text-primary mb-3 mt-4">Data Orang Tua / Wali Murid</h6>
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-medium">Nama Orang Tua / Wali</label>
                            <input type="text" name="nama_ortu" class="form-control @error('nama_ortu') is-invalid @enderror"
                                   value="{{ old('nama_ortu') }}" placeholder="Nama Ayah/Ibu/Wali">
                            @error('nama_ortu')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-medium">Nomor WhatsApp Orang Tua</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-whatsapp text-success"></i></span>
                                <input type="text" name="no_wa_ortu" class="form-control @error('no_wa_ortu') is-invalid @enderror"
                                       value="{{ old('no_wa_ortu') }}" placeholder="Contoh: 081234567890">
                            </div>
                            <small class="text-muted">Digunakan untuk integrasi Early Warning System via WhatsApp Gateway</small>
                            @error('no_wa_ortu')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <h6 class="fw-bold text-primary mb-3 mt-4">Akun Login Siswa (Portal Survei Kesejahteraan)</h6>
                    <div class="form-check form-switch mb-3">
                        <input class="form-check-input" type="checkbox" role="switch" name="buat_akun" id="buat_akun" value="1"
                               {{ old('buat_akun', '1') == '1' ? 'checked' : '' }} onchange="toggleAkunFields()">
                        <label class="form-check-label fw-medium" for="buat_akun">
                            Buat Akun Pengguna untuk Siswa ini (Username = NIS)
                        </label>
                    </div>

                    <div id="akun_fields" class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label class="form-label fw-medium">Password Akun</label>
                            <input type="password" name="password" id="password_input"
                                   class="form-control @error('password') is-invalid @enderror"
                                   placeholder="Minimal 6 karakter (Default: sama dengan NIS)">
                            <small class="text-muted">Biarkan kosong atau isi sesuai keinginan. Default password adalah NIS siswa.</small>
                            @error('password')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="d-flex justify-content-end gap-2 border-top pt-3">
                        <a href="{{ route('bk.master.siswa.index') }}" class="btn btn-secondary btn-sm">Batal</a>
                        <button type="submit" class="btn btn-primary btn-sm">
                            <i class="bi bi-save me-1"></i>Simpan Siswa
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function toggleAkunFields() {
        const checked = document.getElementById('buat_akun').checked;
        document.getElementById('akun_fields').style.display = checked ? 'flex' : 'none';
    }
</script>
@endpush

