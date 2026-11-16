@extends('layouts.app')
@section('page-title', 'Survei Kesejahteraan & Kenyamanan Belajar Siswa')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card shadow-sm">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0"><i class="bi bi-clipboard-heart me-2"></i>Survei Kesejahteraan & Kenyamanan Belajar</h5>
                <small class="opacity-75">SMK Sunan Ampel — Semester {{ $semester }}</small>
            </div>
            <div class="card-body">

                @if($sudahIsi)
                    <div class="text-center py-5">
                        <div class="display-4 mb-3">🎉</div>
                        <h5 class="fw-bold text-success">Terima Kasih!</h5>
                        <p class="text-muted">Kamu sudah mengisi survei ini untuk semester <strong>{{ $semester }}</strong>.<br>
                        Jawaban kamu membantu kami memberikan dukungan yang lebih baik.</p>
                    </div>
                @else
                    <p class="text-muted mb-4">
                        Halo, <strong>{{ $siswa->nama_lengkap }}</strong>! 👋<br>
                        Survei ini membantu kami memahami kondisi belajarmu. Jawablah dengan <strong>jujur</strong>
                        sesuai perasaanmu. Tidak ada jawaban benar/salah. Data kamu <strong>dijaga kerahasiaannya</strong>.
                    </p>

                    <form method="POST" action="{{ route('siswa.kuesioner.store') }}">
                        @csrf

                        @php
                            $aspekSaatIni = '';
                            $aspekLabel   = ['motivasi' => 'Motivasi Belajar', 'keluarga' => 'Dukungan Keluarga', 'keamanan' => 'Perasaan Aman di Sekolah'];
                            $aspekIcon    = ['motivasi' => 'bi-lightning-fill', 'keluarga' => 'bi-house-heart-fill', 'keamanan' => 'bi-shield-fill-check'];
                        @endphp

                        @foreach($butir as $butirItem)
                            @if($aspekSaatIni !== $butirItem['aspek'])
                                @if($aspekSaatIni !== '') </div> @endif
                                @php $aspekSaatIni = $butirItem['aspek']; @endphp
                                <div class="mb-4">
                                <h6 class="fw-bold text-primary border-bottom pb-2 mb-3">
                                    <i class="bi {{ $aspekIcon[$butirItem['aspek']] }} me-2"></i>
                                    Aspek {{ $aspekLabel[$butirItem['aspek']] }}
                                </h6>
                            @endif

                            <div class="mb-3 p-3 rounded border @error('jawaban.'.$butirItem['no']) border-danger @else border-light @enderror"
                                 style="background: #f8fafc">
                                <p class="mb-2 fw-medium">
                                    {{ $butirItem['no'] }}. {{ $butirItem['pernyataan'] }}
                                    @if($butirItem['reverse'])
                                        <span class="badge bg-secondary ms-1" style="font-size:.65rem" title="Pernyataan negatif">❌</span>
                                    @endif
                                </p>
                                <div class="d-flex gap-2 flex-wrap">
                                    @foreach([1 => 'Sangat Tidak Setuju', 2 => 'Tidak Setuju', 3 => 'Netral', 4 => 'Setuju', 5 => 'Sangat Setuju'] as $val => $label)
                                    <div class="form-check me-1">
                                        <input class="form-check-input" type="radio"
                                               name="jawaban[{{ $butirItem['no'] }}]"
                                               id="q{{ $butirItem['no'] }}_{{ $val }}"
                                               value="{{ $val }}"
                                               {{ old("jawaban.{$butirItem['no']}") == $val ? 'checked' : '' }}
                                               required>
                                        <label class="form-check-label small" for="q{{ $butirItem['no'] }}_{{ $val }}">
                                            <strong>{{ $val }}</strong><br>
                                            <span class="text-muted" style="font-size:.7rem">{{ $label }}</span>
                                        </label>
                                    </div>
                                    @endforeach
                                </div>
                                @error("jawaban.{$butirItem['no']}")
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>

                            @if($loop->last) </div> @endif
                        @endforeach

                        <div class="d-grid mt-4">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="bi bi-send-check me-2"></i>Kirim Jawaban Saya
                            </button>
                        </div>
                    </form>
                @endif

            </div>
        </div>
    </div>
</div>
@endsection

