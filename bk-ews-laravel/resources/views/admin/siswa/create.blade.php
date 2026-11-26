@extends('layouts.app')
@section('page-title', 'Tambah Data Siswa')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold mb-0">Tambah Siswa Baru</h4>
        <small class="text-muted">Lengkapi biodata siswa dan kontak WhatsApp orang tua</small>
    </div>
    <a href="{{ route('admin.siswa.index') }}" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-arrow-left me-1"></i>Kembali
    </a>
</div>

<div class="card shadow-sm">
    <div class="card-body">
        <form method="POST" action="{{ route('admin.siswa.store') }}">
            @csrf
            <div class="row g-3 mb-3">
                <div class="col-md-4">
                    <label class="form-label fw-medium">NISN</label>
                    <input type="text" name="nisn" class="form-control @error('nisn') is-invalid @enderror"
                           value="{{ old('nisn') }}" placeholder="Contoh: 0051234567">
                    @error('nisn')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-medium">NIS <span class="text-danger">*</span></label>
                    <input type="text" name="nis" class="form-control @error('nis') is-invalid @enderror"
                           value="{{ old('nis') }}" placeholder="Contoh: 2025001" required>
                    @error('nis')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-medium">Kelas <span class="text-danger">*</span></label>
                    <select name="kelas_id" class="form-select @error('kelas_id') is-invalid @enderror" required>
                        <option value="">-- Pilih Kelas --</option>
                        @foreach($kelasList as $k)
                            <option value="{{ $k->id }}" {{ old('kelas_id') == $k->id ? 'selected' : '' }}>
                                {{ $k->tingkat }} {{ $k->nama_kelas }}
                            </option>
                        @endforeach
                    </select>
                    @error('kelas_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>

            <div class="row g-3 mb-3">
                <div class="col-md-6">
                    <label class="form-label fw-medium">Nama Lengkap <span class="text-danger">*</span></label>
                    <input type="text" name="nama_lengkap" class="form-control @error('nama_lengkap') is-invalid @enderror"
                           value="{{ old('nama_lengkap') }}" required>
                    @error('nama_lengkap')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-medium">Jenis Kelamin <span class="text-danger">*</span></label>
                    <select name="jenis_kelamin" class="form-select @error('jenis_kelamin') is-invalid @enderror" required>
                        <option value="L" {{ old('jenis_kelamin') === 'L' ? 'selected' : '' }}>Laki-laki (L)</option>
                        <option value="P" {{ old('jenis_kelamin') === 'P' ? 'selected' : '' }}>Perempuan (P)</option>
                    </select>
                    @error('jenis_kelamin')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-medium">Tanggal Lahir</label>
                    <input type="date" name="tanggal_lahir" class="form-control @error('tanggal_lahir') is-invalid @enderror"
                           value="{{ old('tanggal_lahir') }}">
                    @error('tanggal_lahir')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>

            <div class="row g-3 mb-3">
                <div class="col-md-6">
                    <label class="form-label fw-medium">Nama Orang Tua / Wali</label>
                    <input type="text" name="nama_ortu" class="form-control @error('nama_ortu') is-invalid @enderror"
                           value="{{ old('nama_ortu') }}">
                    @error('nama_ortu')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-medium">Nomor WhatsApp Orang Tua</label>
                    <input type="text" name="no_wa_ortu" class="form-control @error('no_wa_ortu') is-invalid @enderror"
                           value="{{ old('no_wa_ortu') }}" placeholder="081234567890">
                    @error('no_wa_ortu')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>

            <div class="mb-4">
                <label class="form-label fw-medium">Alamat</label>
                <textarea name="alamat" class="form-control @error('alamat') is-invalid @enderror" rows="2">{{ old('alamat') }}</textarea>
                @error('alamat')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="d-flex justify-content-end gap-2 border-top pt-3">
                <a href="{{ route('admin.siswa.index') }}" class="btn btn-secondary btn-sm">Batal</a>
                <button type="submit" class="btn btn-primary btn-sm">
                    <i class="bi bi-save me-1"></i>Simpan Siswa
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
