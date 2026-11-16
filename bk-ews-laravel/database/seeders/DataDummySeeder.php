<?php

namespace Database\Seeders;

use App\Models\Siswa;
use App\Models\AkademikAbsensi;
use App\Models\KuesionerPsikososial;
use App\Models\Pelanggaran;
use App\Models\KategoriPelanggaran;
use App\Models\PeriodeSemester;
use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * Seeder data dummy untuk keperluan demo & pengujian EWS.
 * Menambahkan variasi data agar hasil prediksi beragam.
 */
class DataDummySeeder extends Seeder
{
    public function run(): void
    {
        $siswa = Siswa::all();
        $semAktif = PeriodeSemester::where('is_aktif', true)->first();
        $semLalu = PeriodeSemester::where('is_aktif', false)->latest('id')->first();

        if (!$semAktif) {
            $this->command->error('Periode semester aktif tidak ditemukan!');
            return;
        }

        $profil = [
            // [rata_nilai_lalu, rata_nilai_skrg, alpha, izin, sakit, poin_pelanggaran, mot, kel, aman]
            // Siswa 1: Risiko Tinggi + Anomali
            [78.0, 65.0, 8, 2, 1, 30, 9,  11, 13],
            // Siswa 2: Risiko Tinggi Normal
            [75.0, 62.0, 6, 1, 0, 20, 10, 14, 16],
            // Siswa 3: Risiko Sedang + Anomali (penurunan mendadak)
            [82.0, 70.0, 5, 0, 2, 10, 13, 18, 17],
            // Siswa 4: Risiko Sedang
            [80.0, 74.0, 3, 1, 1,  8, 14, 17, 18],
            // Siswa 5: Risiko Rendah
            [88.0, 87.5, 1, 0, 0,  0, 22, 21, 22],
            // Siswa 6: Risiko Rendah
            [85.0, 88.0, 0, 1, 0,  0, 23, 22, 24],
            // Siswa 7: Risiko Tinggi + Anomali (kasus ekstrem)
            [80.0, 55.0, 10, 0, 0, 40,  8, 10, 12],
            // Siswa 8: Risiko Sedang
            [76.0, 71.0, 2, 2, 1,  5, 15, 16, 19],
            // Siswa 9: Risiko Rendah
            [90.0, 91.0, 0, 0, 1,  0, 24, 23, 25],
            // Siswa 10: Risiko Rendah
            [87.0, 86.0, 1, 0, 0,  0, 21, 20, 23],
        ];

        $guruBk = User::where('role', 'guru_bk')->first();
        $kategoriList = KategoriPelanggaran::all();

        foreach ($siswa as $index => $s) {
            $p = $profil[$index % count($profil)];

            [$nilaiLalu, $nilaiSkrg, $alpha, $izin, $sakit, $poinPelanggaran, $mot, $kel, $aman] = $p;

            // Akademik semester lalu (jika ada semester lalu)
            if ($semLalu) {
                AkademikAbsensi::firstOrCreate(
                    ['siswa_id' => $s->id, 'semester_id' => $semLalu->id],
                    [
                        'input_oleh'   => null,
                        'rata_nilai'   => $nilaiLalu,
                        'tren_nilai'   => 0,
                        'jumlah_alpha' => rand(0, 2),
                        'jumlah_izin'  => rand(0, 2),
                        'jumlah_sakit' => rand(0, 1),
                    ]
                );
            }

            // Akademik semester aktif berjalan
            AkademikAbsensi::firstOrCreate(
                ['siswa_id' => $s->id, 'semester_id' => $semAktif->id],
                [
                    'input_oleh'   => null,
                    'rata_nilai'   => $nilaiSkrg,
                    'tren_nilai'   => round($nilaiSkrg - $nilaiLalu, 2),
                    'jumlah_alpha' => $alpha,
                    'jumlah_izin'  => $izin,
                    'jumlah_sakit' => $sakit,
                ]
            );

            // Kuesioner psikososial semester aktif
            KuesionerPsikososial::firstOrCreate(
                ['siswa_id' => $s->id, 'semester_id' => $semAktif->id],
                [
                    'skor_motivasi' => $mot,
                    'skor_keluarga' => $kel,
                    'skor_keamanan' => $aman,
                    'tanggal_isi'   => now()->subDays(rand(1, 14)),
                ]
            );

            // Pelanggaran (hanya untuk yang punya poin)
            if ($poinPelanggaran > 0 && $guruBk) {
                // Pilih kategori yang mendekati poin atau acak
                $kat = $kategoriList->where('bobot_poin', '<=', $poinPelanggaran)->first() ?? $kategoriList->first();

                Pelanggaran::create([
                    'siswa_id'                => $s->id,
                    'dicatat_oleh'            => $guruBk->id,
                    'kategori_pelanggaran_id' => $kat?->id,
                    'tanggal'                 => now()->subDays(rand(5, 30)),
                    'jenis_pelanggaran'       => $kat ? $kat->nama : 'Terlambat / Alpha',
                    'poin'                    => $poinPelanggaran,
                    'keterangan'              => 'Data demo untuk pengujian EWS',
                ]);
            }
        }

        $this->command->info('✅ DataDummySeeder: Data akademik, kuesioner, dan pelanggaran dummy berhasil di-seed.');
    }
}
