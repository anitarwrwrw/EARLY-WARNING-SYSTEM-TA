@extends('layouts.app')
@section('page-title', 'Edit Catatan Tindak Lanjut')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card shadow-sm">
            <div class="card-header bg-transparent d-flex justify-content-between align-items-center">
                <span class="fw-semibold"><i class="bi bi-pencil-square text-primary me-2"></i>Edit Tindak Lanjut: {{ $tindakLanjut->siswa->nama_lengkap }}</span>
                <a href="{{ route('bk.tindaklanjut.index') }}" class="btn btn-outline-secondary btn-sm">
                    <i class="bi bi-arrow-left me-1"></i>Kembali
                </a>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('bk.tindaklanjut.update', $tindakLanjut->id) }}">
                    @csrf
                    @method('PUT')

                    <div class="alert alert-light border d-flex align-items-center justify-content-between mb-3">
                        <div>
                            <strong>{{ $tindakLanjut->siswa->nama_lengkap }}</strong> (NIS: {{ $tindakLanjut->siswa->nis }})
                            <span class="text-muted d-block small">Kelas: {{ $tindakLanjut->siswa->kelas->tingkat ?? '' }} {{ $tindakLanjut->siswa->kelas->nama_kelas ?? '-' }}</span>
                        </div>
                        @if($tindakLanjut->hasilEws)
                            <span class="badge badge-p{{ $tindakLanjut->hasilEws->level_prioritas }}">
                                {{ $tindakLanjut->hasilEws->ikon_prioritas }} {{ $tindakLanjut->hasilEws->label_prioritas }}
                            </span>
                        @endif
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-medium">Tanggal Konseling <span class="text-danger">*</span></label>
                            <input type="date" name="tanggal_konseling" class="form-control @error('tanggal_konseling') is-invalid @enderror"
                                   value="{{ old('tanggal_konseling', $tindakLanjut->tanggal_konseling?->format('Y-m-d')) }}" required>
                            @error('tanggal_konseling')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-medium">Bentuk Penanganan / Konseling</label>
                            <select name="bentuk_penanganan" class="form-select @error('bentuk_penanganan') is-invalid @enderror">
                                <option value="Konseling Individual" {{ old('bentuk_penanganan', $tindakLanjut->bentuk_penanganan) == 'Konseling Individual' ? 'selected' : '' }}>Konseling Individual</option>
                                <option value="Konseling Kelompok" {{ old('bentuk_penanganan', $tindakLanjut->bentuk_penanganan) == 'Konseling Kelompok' ? 'selected' : '' }}>Konseling Kelompok</option>
                                <option value="Panggilan Orang Tua" {{ old('bentuk_penanganan', $tindakLanjut->bentuk_penanganan) == 'Panggilan Orang Tua' ? 'selected' : '' }}>Panggilan Orang Tua ke Sekolah</option>
                                <option value="Kunjungan Rumah (Home Visit)" {{ old('bentuk_penanganan', $tindakLanjut->bentuk_penanganan) == 'Kunjungan Rumah (Home Visit)' ? 'selected' : '' }}>Kunjungan Rumah (Home Visit)</option>
                                <option value="Alih Tangan Kasus (Referral)" {{ old('bentuk_penanganan', $tindakLanjut->bentuk_penanganan) == 'Alih Tangan Kasus (Referral)' ? 'selected' : '' }}>Alih Tangan Kasus (Referral)</option>
                            </select>
                            @error('bentuk_penanganan')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-medium">Status Kasus <span class="text-danger">*</span></label>
                        <div class="d-flex gap-4">
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="status_kasus" id="status_proses" value="Proses"
                                       {{ old('status_kasus', $tindakLanjut->status_kasus) === 'Proses' ? 'checked' : '' }} required>
                                <label class="form-check-label" for="status_proses">
                                    <span class="badge bg-warning text-dark"><i class="bi bi-arrow-repeat me-1"></i>Dalam Proses</span>
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="status_kasus" id="status_selesai" value="Selesai"
                                       {{ old('status_kasus', $tindakLanjut->status_kasus) === 'Selesai' ? 'checked' : '' }}>
                                <label class="form-check-label" for="status_selesai">
                                    <span class="badge bg-success"><i class="bi bi-check2 me-1"></i>Selesai / Teratasi</span>
                                </label>
                            </div>
                        </div>
                        @error('status_kasus')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-medium">Catatan Konseling & Hasil Pembinaan</label>
                        <textarea name="catatan_konseling" class="form-control @error('catatan_konseling') is-invalid @enderror"
                                  rows="5">{{ old('catatan_konseling', $tindakLanjut->catatan_konseling) }}</textarea>
                        @error('catatan_konseling')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="d-flex justify-content-end gap-2 border-top pt-3">
                        <a href="{{ route('bk.tindaklanjut.index') }}" class="btn btn-secondary btn-sm">Batal</a>
                        <button type="submit" class="btn btn-primary btn-sm">
                            <i class="bi bi-check2-circle me-1"></i>Perbarui Catatan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

