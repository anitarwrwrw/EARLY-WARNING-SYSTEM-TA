<?php

namespace App\Http\Controllers\Siswa;

use App\Http\Controllers\Controller;
use App\Models\KuesionerPsikososial;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class KuesionerController extends Controller
{
    /**
     * Definisi lengkap instrumen kuesioner psikososial (15 butir).
     * Reverse scoring ditandai 'reverse' => true.
     */
    private const BUTIR_KUESIONER = [
        // ─── Aspek 1: Motivasi Belajar (5 butir) ───────────────────────
        ['aspek' => 'motivasi', 'no' => 1,  'pernyataan' => 'Saya selalu bersemangat hadir dan mengikuti pelajaran di sekolah.',               'reverse' => false],
        ['aspek' => 'motivasi', 'no' => 2,  'pernyataan' => 'Saya tertarik dan menikmati materi kejuruan yang diajarkan di sekolah.',          'reverse' => false],
        ['aspek' => 'motivasi', 'no' => 3,  'pernyataan' => 'Saya sering merasa malas untuk menyelesaikan tugas sekolah yang diberikan.', 'reverse' => true],
        ['aspek' => 'motivasi', 'no' => 4,  'pernyataan' => 'Saya berusaha memahami materi pelajaran yang belum saya mengerti.',             'reverse' => false],
        ['aspek' => 'motivasi', 'no' => 5,  'pernyataan' => 'Saya memiliki target dan semangat untuk meraih prestasi yang baik di sekolah.', 'reverse' => false],
        // ─── Aspek 2: Dukungan Keluarga (5 butir) ──────────────────────
        ['aspek' => 'keluarga', 'no' => 6,  'pernyataan' => 'Orang tua saya menunjukkan perhatian dan peduli terhadap pelajaran saya.',       'reverse' => false],
        ['aspek' => 'keluarga', 'no' => 7,  'pernyataan' => 'Di rumah, tersedia tempat dan waktu yang cukup bagi saya untuk belajar.',        'reverse' => false],
        ['aspek' => 'keluarga', 'no' => 8,  'pernyataan' => 'Orang tua saya jarang menanyakan perkembangan belajar saya di sekolah.', 'reverse' => true],
        ['aspek' => 'keluarga', 'no' => 9,  'pernyataan' => 'Saya merasa mendapat dukungan penuh dari keluarga untuk terus bersekolah.',     'reverse' => false],
        ['aspek' => 'keluarga', 'no' => 10, 'pernyataan' => 'Keluarga saya berkomunikasi dengan baik dan saling mendukung satu sama lain.',   'reverse' => false],
        // ─── Aspek 3: Perasaan Aman di Sekolah (5 butir) ───────────────
        ['aspek' => 'keamanan', 'no' => 11, 'pernyataan' => 'Saya merasa nyaman dan aman bergaul dengan teman-teman di sekolah.',             'reverse' => false],
        ['aspek' => 'keamanan', 'no' => 12, 'pernyataan' => 'Saya bebas dari gangguan atau perundungan (bullying) dari siapapun di sekolah.', 'reverse' => false],
        ['aspek' => 'keamanan', 'no' => 13, 'pernyataan' => 'Saya merasa tidak nyaman dan ingin menghindari berada di lingkungan sekolah.', 'reverse' => true],
        ['aspek' => 'keamanan', 'no' => 14, 'pernyataan' => 'Saya memiliki teman yang bisa dipercaya dan diajak berbagi cerita di sekolah.',  'reverse' => false],
        ['aspek' => 'keamanan', 'no' => 15, 'pernyataan' => 'Secara keseluruhan, saya merasa aman dan nyaman berada di SMK Sunan Ampel.',     'reverse' => false],
    ];

    public function index(Request $request)
    {
        $user       = Auth::user();
        $siswa      = $user->siswaProfil;
        $semester   = $this->semesterAktif();

        if (! $siswa) {
            abort(403, 'Profil siswa tidak ditemukan. Hubungi Guru BK.');
        }

        // Cek apakah sudah mengisi kuesioner semester ini
        $sudahIsi = KuesionerPsikososial::where('siswa_id', $siswa->id)
            ->where('semester', $semester)
            ->exists();

        return view('siswa.kuesioner', [
            'siswa'    => $siswa,
            'semester' => $semester,
            'sudahIsi' => $sudahIsi,
            'butir'    => self::BUTIR_KUESIONER,
        ]);
    }

    public function store(Request $request)
    {
        $user   = Auth::user();
        $siswa  = $user->siswaProfil;
        $semester = $this->semesterAktif();

        if (! $siswa) {
            abort(403);
        }

        // Validasi: semua 15 butir harus diisi skala 1-5
        $rules = [];
        for ($i = 1; $i <= 15; $i++) {
            $rules["jawaban.{$i}"] = 'required|integer|min:1|max:5';
        }
        $validated = $request->validate($rules);
        $jawaban   = $validated['jawaban']; // [1..15 => nilai]

        // Cek sudah isi atau belum
        $existing = KuesionerPsikososial::where('siswa_id', $siswa->id)
            ->where('semester', $semester)
            ->first();

        if ($existing) {
            return redirect()->route('siswa.kuesioner')
                ->with('warning', 'Kamu sudah mengisi survei ini untuk semester ' . $semester . '.');
        }

        // Susun array jawaban berurutan (1..15) → indeks 0-based
        $jawabanArray = array_map(fn($i) => (int) ($jawaban[$i] ?? 3), range(1, 15));

        // Hitung skor dengan reverse scoring
        $skor = KuesionerPsikososial::hitungSkor($jawabanArray);

        KuesionerPsikososial::create([
            'siswa_id'      => $siswa->id,
            'semester'      => $semester,
            'skor_motivasi' => $skor['skor_motivasi'],
            'skor_keluarga' => $skor['skor_keluarga'],
            'skor_keamanan' => $skor['skor_keamanan'],
            'jawaban_raw'   => $jawabanArray,
            'tanggal_isi'   => now(),
        ]);

        return redirect()->route('siswa.kuesioner')
            ->with('success', 'Terima kasih! Survei kesejahteraanmu berhasil disimpan. 😊');
    }

    private function semesterAktif(): string
    {
        $bulan  = now()->month;
        $tahun  = now()->year;
        $jenis  = ($bulan >= 7) ? 'Ganjil' : 'Genap';
        $ta     = ($bulan >= 7) ? "{$tahun}/" . ($tahun + 1) : ($tahun - 1) . "/{$tahun}";
        return "{$jenis} {$ta}";
    }
}

