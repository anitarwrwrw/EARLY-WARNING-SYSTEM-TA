<?php

namespace App\Http\Controllers;

use App\Models\Kelas;
use App\Models\KuesionerPsikososial;
use App\Models\PeriodeSemester;
use App\Models\Siswa;
use Illuminate\Http\Request;

class KuesionerPublikController extends Controller
{
    /**
     * Definisi instrumen kuesioner psikososial 15 butir (Skala Likert 1-5).
     * Butir ke-3 tiap aspek (index 2, 7, 12) adalah reverse scoring.
     */
    public const BUTIR_KUESIONER = [
        // ─── Aspek 1: Motivasi Belajar (5 butir) ───────────────────────
        ['aspek' => 'motivasi', 'label_aspek' => 'Motivasi Belajar', 'no' => 1,  'pernyataan' => 'Saya selalu bersemangat hadir dan mengikuti pelajaran di sekolah.',               'reverse' => false],
        ['aspek' => 'motivasi', 'label_aspek' => 'Motivasi Belajar', 'no' => 2,  'pernyataan' => 'Saya tertarik dan menikmati materi kejuruan yang diajarkan di sekolah.',          'reverse' => false],
        ['aspek' => 'motivasi', 'label_aspek' => 'Motivasi Belajar', 'no' => 3,  'pernyataan' => 'Saya sering merasa malas untuk menyelesaikan tugas sekolah yang diberikan.', 'reverse' => true],
        ['aspek' => 'motivasi', 'label_aspek' => 'Motivasi Belajar', 'no' => 4,  'pernyataan' => 'Saya berusaha memahami materi pelajaran yang belum saya mengerti.',             'reverse' => false],
        ['aspek' => 'motivasi', 'label_aspek' => 'Motivasi Belajar', 'no' => 5,  'pernyataan' => 'Saya memiliki target dan semangat untuk meraih prestasi yang baik di sekolah.', 'reverse' => false],
        // ─── Aspek 2: Dukungan Keluarga (5 butir) ──────────────────────
        ['aspek' => 'keluarga', 'label_aspek' => 'Dukungan Keluarga', 'no' => 6,  'pernyataan' => 'Orang tua saya menunjukkan perhatian dan peduli terhadap pelajaran saya.',       'reverse' => false],
        ['aspek' => 'keluarga', 'label_aspek' => 'Dukungan Keluarga', 'no' => 7,  'pernyataan' => 'Di rumah, tersedia tempat dan waktu yang cukup bagi saya untuk belajar.',        'reverse' => false],
        ['aspek' => 'keluarga', 'label_aspek' => 'Dukungan Keluarga', 'no' => 8,  'pernyataan' => 'Orang tua saya jarang menanyakan perkembangan belajar saya di sekolah.', 'reverse' => true],
        ['aspek' => 'keluarga', 'label_aspek' => 'Dukungan Keluarga', 'no' => 9,  'pernyataan' => 'Saya merasa mendapat dukungan penuh dari keluarga untuk terus bersekolah.',     'reverse' => false],
        ['aspek' => 'keluarga', 'label_aspek' => 'Dukungan Keluarga', 'no' => 10, 'pernyataan' => 'Keluarga saya berkomunikasi dengan baik dan saling mendukung satu sama lain.',   'reverse' => false],
        // ─── Aspek 3: Perasaan Aman di Sekolah (5 butir) ───────────────
        ['aspek' => 'keamanan', 'label_aspek' => 'Perasaan Aman di Sekolah', 'no' => 11, 'pernyataan' => 'Saya merasa nyaman dan aman bergaul dengan teman-teman di sekolah.',             'reverse' => false],
        ['aspek' => 'keamanan', 'label_aspek' => 'Perasaan Aman di Sekolah', 'no' => 12, 'pernyataan' => 'Saya bebas dari gangguan atau perundungan (bullying) dari siapapun di sekolah.', 'reverse' => false],
        ['aspek' => 'keamanan', 'label_aspek' => 'Perasaan Aman di Sekolah', 'no' => 13, 'pernyataan' => 'Saya merasa tidak nyaman dan ingin menghindari berada di lingkungan sekolah.', 'reverse' => true],
        ['aspek' => 'keamanan', 'label_aspek' => 'Perasaan Aman di Sekolah', 'no' => 14, 'pernyataan' => 'Saya memiliki teman yang bisa dipercaya dan diajak berbagi cerita di sekolah.',  'reverse' => false],
        ['aspek' => 'keamanan', 'label_aspek' => 'Perasaan Aman di Sekolah', 'no' => 15, 'pernyataan' => 'Secara keseluruhan, saya merasa aman dan nyaman berada di SMK Sunan Ampel.',     'reverse' => false],
    ];

    /**
     * Tampilkan form kuesioner publik tanpa login.
     */
    public function index(Request $request)
    {
        $semesterAktif = PeriodeSemester::aktif();
        $isBuka        = $semesterAktif && $semesterAktif->buka_kuesioner;

        $kelasList = Kelas::where('is_active', true)
            ->orderBy('tingkat')
            ->orderBy('nama_kelas')
            ->get();

        // Parameter pra-pilih kelas dari link share WhatsApp (opsional)
        $preselectedKelasId = $request->query('kelas_id');

        return view('kuesioner.publik', [
            'semesterAktif'      => $semesterAktif,
            'isBuka'             => $isBuka,
            'kelasList'          => $kelasList,
            'preselectedKelasId' => $preselectedKelasId,
            'butirList'          => self::BUTIR_KUESIONER,
        ]);
    }

    /**
     * Endpoint API JSON untuk mengambil daftar siswa berdasarkan kelas (AJAX dropdown).
     */
    public function getSiswaByKelas(Kelas $kelas)
    {
        $semesterAktif = PeriodeSemester::aktif();

        $siswa = Siswa::where('kelas_id', $kelas->id)
            ->where('is_active', true)
            ->orderBy('nama_lengkap')
            ->get(['id', 'nama_lengkap', 'nis'])
            ->map(function ($s) use ($semesterAktif) {
                // Beri penanda apakah siswa sudah mengisi kuesioner semester ini
                $sudahIsi = false;
                if ($semesterAktif) {
                    $sudahIsi = KuesionerPsikososial::where('siswa_id', $s->id)
                        ->where('semester_id', $semesterAktif->id)
                        ->exists();
                }

                return [
                    'id'           => $s->id,
                    'nama_lengkap' => $s->nama_lengkap,
                    'nis'          => $s->nis,
                    'sudah_isi'    => $sudahIsi,
                ];
            });

        return response()->json($siswa);
    }

    /**
     * Proses penyimpanan jawaban kuesioner publik.
     */
    public function store(Request $request)
    {
        $semesterAktif = PeriodeSemester::aktif();

        if (! $semesterAktif || ! $semesterAktif->buka_kuesioner) {
            return back()->with('error', 'Pengisian kuesioner saat ini sedang ditutup oleh pihak sekolah.');
        }

        // 1. Validasi identitas siswa
        $request->validate([
            'kelas_id' => 'required|exists:kelas,id',
            'siswa_id' => 'required|exists:siswa,id',
            'nisn'     => 'required|string',
        ], [
            'kelas_id.required' => 'Pilih kelas Anda.',
            'siswa_id.required' => 'Pilih nama Anda dari daftar siswa.',
            'nisn.required'     => 'Masukkan NISN untuk verifikasi identitas.',
        ]);

        $siswa = Siswa::where('id', $request->siswa_id)
            ->where('kelas_id', $request->kelas_id)
            ->where('is_active', true)
            ->first();

        if (! $siswa) {
            return back()->withInput()->withErrors([
                'siswa_id' => 'Data siswa tidak cocok dengan kelas yang dipilih.',
            ]);
        }

        // Verifikasi NISN (cegah orang lain mengisi atas nama siswa)
        // Jika di database NISN belum diisi, kita cocokan dengan NIS sebagai fallback
        $nisnInput = trim($request->nisn);
        $nisnValid = false;

        if (!empty($siswa->nisn)) {
            $nisnValid = (strcasecmp(trim($siswa->nisn), $nisnInput) === 0);
        } else {
            // Fallback jika admin belum menginputkan NISN siswa
            $nisnValid = (strcasecmp(trim($siswa->nis), $nisnInput) === 0);
        }

        if (! $nisnValid) {
            return back()->withInput()->withErrors([
                'nisn' => 'Verifikasi NISN gagal. NISN yang Anda masukkan tidak sesuai dengan data siswa.',
            ]);
        }

        // 2. Cegah pengisian ganda di semester yang sama
        $sudahIsi = KuesionerPsikososial::where('siswa_id', $siswa->id)
            ->where('semester_id', $semesterAktif->id)
            ->exists();

        if ($sudahIsi) {
            return back()->withInput()->with('warning', "Halo {$siswa->nama_lengkap}, Anda sudah mengisi kuesioner untuk semester {$semesterAktif->nama_semester}. Terima kasih atas partisipasi Anda!");
        }

        // 3. Validasi 15 butir kuesioner (Skala 1 - 5)
        $rules = [];
        $messages = [];
        for ($i = 1; $i <= 15; $i++) {
            $rules["jawaban.{$i}"] = 'required|integer|between:1,5';
            $messages["jawaban.{$i}.required"] = "Pernyataan nomor {$i} belum dijawab.";
        }

        $validated = $request->validate($rules, $messages);
        $jawaban   = $validated['jawaban']; // [1..15 => skor]

        // Susun array jawaban 0-based
        $jawabanArray = array_map(fn($i) => (int) ($jawaban[$i] ?? 3), range(1, 15));

        // Hitung skor aspek dengan reverse scoring otomatis
        $skor = KuesionerPsikososial::hitungSkor($jawabanArray);

        // 4. Simpan ke database
        KuesionerPsikososial::create([
            'siswa_id'      => $siswa->id,
            'semester_id'   => $semesterAktif->id,
            'skor_motivasi' => $skor['skor_motivasi'],
            'skor_keluarga' => $skor['skor_keluarga'],
            'skor_keamanan' => $skor['skor_keamanan'],
            'jawaban_raw'   => $jawabanArray,
            'tanggal_isi'   => now(),
        ]);

        return redirect()->route('kuesioner.sukses')
            ->with('nama_siswa', $siswa->nama_lengkap)
            ->with('nama_semester', $semesterAktif->nama_semester);
    }

    /**
     * Halaman ucapan terima kasih / konfirmasi sukses pengisian.
     */
    public function sukses()
    {
        return view('kuesioner.sukses');
    }
}
