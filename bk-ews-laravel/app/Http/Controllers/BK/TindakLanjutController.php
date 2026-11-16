<?php

namespace App\Http\Controllers\BK;

use App\Http\Controllers\Controller;
use App\Models\Siswa;
use App\Models\TindakLanjutBk;
use App\Models\HasilPrediksiEws;
use App\Models\LogWhatsapp;
use App\Models\PengaturanSistem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;

class TindakLanjutController extends Controller
{
    public function index()
    {
        $tindakLanjut = TindakLanjutBk::with(['siswa.kelas', 'hasilEws', 'guruBk'])
            ->orderByDesc('tanggal_konseling')
            ->paginate(15);

        return view('bk.tindaklanjut.index', compact('tindakLanjut'));
    }

    public function create(Request $request)
    {
        $siswa = Siswa::with(['kelas', 'hasilPrediksiEws' => fn($q) => $q->latest()->limit(1)])->get();
        $siswaId = $request->input('siswa_id');
        $hasilEwsId = $request->input('hasil_ews_id');

        return view('bk.tindaklanjut.create', compact('siswa', 'siswaId', 'hasilEwsId'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'siswa_id'          => 'required|exists:siswa,id',
            'hasil_ews_id'      => 'nullable|exists:hasil_prediksi_ews,id',
            'tanggal_konseling' => 'required|date',
            'catatan_pembinaan' => 'nullable|string|max:5000',
            'status_penanganan' => 'required|in:Proses,Selesai',
            'tipe_konseling'    => 'nullable|string|max:100',
        ]);

        TindakLanjutBk::create([
            ...$validated,
            'guru_bk_id' => Auth::id(),
        ]);

        return redirect()->route('bk.tindaklanjut.index')
            ->with('success', 'Catatan tindak lanjut berhasil disimpan.');
    }

    public function edit(TindakLanjutBk $tindakLanjut)
    {
        $siswa = Siswa::with('kelas')->get();
        return view('bk.tindaklanjut.edit', compact('tindakLanjut', 'siswa'));
    }

    public function update(Request $request, TindakLanjutBk $tindakLanjut)
    {
        $validated = $request->validate([
            'tanggal_konseling' => 'required|date',
            'catatan_pembinaan' => 'nullable|string|max:5000',
            'status_penanganan' => 'required|in:Proses,Selesai',
            'tipe_konseling'    => 'nullable|string|max:100',
        ]);

        $tindakLanjut->update($validated);

        return redirect()->route('bk.tindaklanjut.index')
            ->with('success', 'Catatan tindak lanjut berhasil diperbarui.');
    }

    /**
     * Kirim notifikasi WhatsApp ke orang tua siswa via Fonnte.
     */
    public function kirimWhatsapp(Request $request)
    {
        $validated = $request->validate([
            'siswa_id'       => 'required|exists:siswa,id',
            'jenis_template' => 'required|in:koordinasi_preventif,undangan_konseling,apresiasi_positif',
            'tanggal_konseling' => 'nullable|date',  // Untuk template undangan
            'jam_konseling'     => 'nullable|string',
        ]);

        $siswa = Siswa::with(['kelas', 'hasilPrediksiEws' => fn($q) => $q->latest()->limit(1)])->findOrFail($validated['siswa_id']);

        if (! $siswa->no_wa_ortu) {
            return back()->with('error', 'Nomor WhatsApp orang tua siswa ' . $siswa->nama_lengkap . ' belum terdaftar.');
        }

        $hasilEwsTerakhir = $siswa->hasilEwsTerakhir();
        $pemicu = $hasilEwsTerakhir?->pemicu_utama ?? 'beberapa indikator yang perlu perhatian';

        $pesan = $this->buildPesanWhatsapp(
            $validated['jenis_template'],
            $siswa->nama_lengkap,
            $siswa->kelas->nama_kelas ?? '-',
            $pemicu,
            $validated['tanggal_konseling'] ?? null,
            $validated['jam_konseling'] ?? null,
        );

        // Kirim ke Fonnte API
        $statusKirim = 'Gagal';
        $responseApi = null;

        // Ambil token dari Pengaturan Sistem (fallback ke config)
        $waToken = PengaturanSistem::get('wa_gateway_token', config('services.fonnte.token', ''));

        try {
            $response = Http::timeout(10)
                ->withHeaders(['Authorization' => $waToken])
                ->post(config('services.fonnte.url', 'https://api.fonnte.com/send'), [
                    'target'  => $siswa->no_wa_ortu,
                    'message' => $pesan,
                    'delay'   => 2,
                ]);

            $responseApi = $response->body();
            $statusKirim = $response->successful() ? 'Berhasil' : 'Gagal';

        } catch (\Exception $e) {
            $responseApi = 'Error: ' . $e->getMessage();
        }

        // Simpan log
        LogWhatsapp::create([
            'siswa_id'       => $siswa->id,
            'dikirim_oleh'   => Auth::id(),
            'no_tujuan'      => $siswa->no_wa_ortu,
            'jenis_template' => $validated['jenis_template'],
            'isi_pesan'      => $pesan,
            'status_kirim'   => $statusKirim,
            'response_api'   => $responseApi,
            'waktu_kirim'    => now(),
        ]);

        return back()->with(
            $statusKirim === 'Berhasil' ? 'success' : 'error',
            $statusKirim === 'Berhasil'
                ? "Pesan WhatsApp berhasil dikirim ke orang tua {$siswa->nama_lengkap}."
                : "Pesan WhatsApp gagal dikirim. Periksa token Fonnte dan nomor tujuan."
        );
    }

    // ─── Private Helpers ───────────────────────────────────────────────────────

    private function buildPesanWhatsapp(
        string $template,
        string $namaSiswa,
        string $kelas,
        string $pemicu,
        ?string $tanggal,
        ?string $jam
    ): string {
        return match ($template) {
            'koordinasi_preventif' =>
                "Assalamualaikum Wr. Wb.\n\n" .
                "Yth. Bapak/Ibu Wali Murid dari *{$namaSiswa}* (Kelas *{$kelas}*).\n\n" .
                "Melalui Sistem Pemantauan Dini Bimbingan Konseling SMK Sunan Ampel, kami menginformasikan adanya perubahan tren pada *{$pemicu}* ananda akhir-akhir ini.\n\n" .
                "Sebagai langkah pencegahan dini dan pendampingan bersama, kami mohon kerja sama Bapak/Ibu untuk ikut memantau motivasi dan kegiatan belajar ananda di rumah.\n\n" .
                "Terima kasih atas kerja samanya.\n— Tim BK SMK Sunan Ampel",

            'undangan_konseling' =>
                "Assalamualaikum Wr. Wb.\n\n" .
                "Yth. Bapak/Ibu Wali Murid dari *{$namaSiswa}*.\n\n" .
                "Sehubungan dengan evaluasi perkembangan akademik dan kedisiplinan ananda di sekolah, kami mengharap kehadiran Bapak/Ibu untuk berdiskusi bersama Guru BK pada:\n\n" .
                "📅 Hari/Tanggal : *" . ($tanggal ? \Carbon\Carbon::parse($tanggal)->isoFormat('dddd, D MMMM Y') : '-') . "*\n" .
                "🕐 Pukul        : *" . ($jam ?? '-') . "*\n" .
                "📍 Tempat       : Ruang BK SMK Sunan Ampel\n\n" .
                "Kehadiran Bapak/Ibu sangat berarti bagi kebaikan perkembangan ananda. Terima kasih.",

            'apresiasi_positif' =>
                "Assalamualaikum Wr. Wb.\n\n" .
                "Yth. Bapak/Ibu Wali Murid dari *{$namaSiswa}*.\n\n" .
                "Kami dari Bimbingan Konseling SMK Sunan Ampel ingin menyampaikan apresiasi atas perkembangan positif ananda pada periode ini. 🎉\n\n" .
                "Berdasarkan evaluasi sistem, tingkat kehadiran, nilai, dan kenyamanan belajar ananda telah berada pada kategori *sangat baik*.\n\n" .
                "Terima kasih atas dukungan dan pendampingan Bapak/Ibu di rumah. Mari terus pertahankan semangat belajar ananda!\n— Tim BK SMK Sunan Ampel",

            default => 'Pesan tidak tersedia.',
        };
    }
}

