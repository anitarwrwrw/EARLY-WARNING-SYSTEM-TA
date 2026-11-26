@extends('layouts.app')
@section('page-title', 'Catat Tindak Lanjut Konseling')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card shadow-sm">
            <div class="card-header bg-transparent d-flex justify-content-between align-items-center">
                <span class="fw-semibold"><i class="bi bi-journal-plus text-primary me-2"></i>Form Catatan Tindak Lanjut Konseling</span>
                <a href="{{ route('bk.tindaklanjut.index') }}" class="btn btn-outline-secondary btn-sm">
                    <i class="bi bi-arrow-left me-1"></i>Kembali
                </a>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('bk.tindaklanjut.store') }}">
                    @csrf
                    @if($hasilEwsId)
                        <input type="hidden" name="hasil_ews_id" value="{{ $hasilEwsId }}">
                    @endif

                    <div class="mb-3">
                        <label class="form-label fw-medium">Pilih Siswa <span class="text-danger">*</span></label>
                        <select name="siswa_id" class="form-select @error('siswa_id') is-invalid @enderror" required>
                            <option value="">-- Pilih Siswa yang Ditindaklanjuti --</option>
                            @foreach($siswa as $s)
                                @php $ewsTerakhir = $s->hasilPrediksiEws->first(); @endphp
                                <option value="{{ $s->id }}" {{ old('siswa_id', $siswaId) == $s->id ? 'selected' : '' }}>
                                    {{ $s->nama_lengkap }} ({{ $s->nis }}) - {{ $s->kelas->tingkat ?? '' }} {{ $s->kelas->nama_kelas ?? '' }}
                                    @if($ewsTerakhir)
                                        [{{ $ewsTerakhir->label_prioritas }}]
                                    @endif
                                </option>
                            @endforeach
                        </select>
                        @error('siswa_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-medium">Tanggal Konseling <span class="text-danger">*</span></label>
                            <input type="date" name="tanggal_konseling" class="form-control @error('tanggal_konseling') is-invalid @enderror"
                                   value="{{ old('tanggal_konseling', date('Y-m-d')) }}" required>
                            @error('tanggal_konseling')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-medium">Bentuk Penanganan / Konseling</label>
                            <select name="bentuk_penanganan" class="form-select @error('bentuk_penanganan') is-invalid @enderror">
                                <option value="Konseling Individual" {{ old('bentuk_penanganan') == 'Konseling Individual' ? 'selected' : '' }}>Konseling Individual</option>
                                <option value="Konseling Kelompok" {{ old('bentuk_penanganan') == 'Konseling Kelompok' ? 'selected' : '' }}>Konseling Kelompok</option>
                                <option value="Panggilan Orang Tua" {{ old('bentuk_penanganan') == 'Panggilan Orang Tua' ? 'selected' : '' }}>Panggilan Orang Tua ke Sekolah</option>
                                <option value="Kunjungan Rumah (Home Visit)" {{ old('bentuk_penanganan') == 'Kunjungan Rumah (Home Visit)' ? 'selected' : '' }}>Kunjungan Rumah (Home Visit)</option>
                                <option value="Alih Tangan Kasus (Referral)" {{ old('bentuk_penanganan') == 'Alih Tangan Kasus (Referral)' ? 'selected' : '' }}>Alih Tangan Kasus (Referral)</option>
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
                                       {{ old('status_kasus', 'Proses') === 'Proses' ? 'checked' : '' }} required>
                                <label class="form-check-label" for="status_proses">
                                    <span class="badge bg-warning text-dark"><i class="bi bi-arrow-repeat me-1"></i>Dalam Proses</span>
                                    <small class="text-muted d-block">Masih membutuhkan sesi bimbingan lanjutan atau observasi berkala</small>
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="status_kasus" id="status_selesai" value="Selesai"
                                       {{ old('status_kasus') === 'Selesai' ? 'checked' : '' }}>
                                <label class="form-check-label" for="status_selesai">
                                    <span class="badge bg-success"><i class="bi bi-check2 me-1"></i>Selesai / Teratasi</span>
                                    <small class="text-muted d-block">Masalah telah terselesaikan dan siswa menunjukkan progres stabil</small>
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
                                  rows="5" placeholder="Tuliskan rangkuman masalah, kesepakatan komitmen siswa, rencana intervensi, atau hasil diskusi bersama orang tua...">{{ old('catatan_konseling') }}</textarea>
                        @error('catatan_konseling')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="d-flex justify-content-end gap-2 border-top pt-3">
                        <a href="{{ route('bk.tindaklanjut.index') }}" class="btn btn-secondary btn-sm">Batal</a>
                        <button type="submit" class="btn btn-primary btn-sm">
                            <i class="bi bi-save me-1"></i>Simpan Catatan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

