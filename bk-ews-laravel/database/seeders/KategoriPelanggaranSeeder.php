<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\KategoriPelanggaran;

class KategoriPelanggaranSeeder extends Seeder
{
    /**
     * Seed data master kategori pelanggaran beserta bobot poin.
     * Dikategorikan: Ringan, Sedang, Berat.
     */
    public function run(): void
    {
        $kategoris = [
            // ── Pelanggaran Ringan ───────────────────────────────────────────────
            [
                'nama'       => 'Terlambat masuk kelas',
                'kategori'   => 'Ringan',
                'bobot_poin' => 5,
                'keterangan' => 'Siswa datang terlambat tanpa surat izin dari pihak sekolah.',
            ],
            [
                'nama'       => 'Tidak berseragam lengkap',
                'kategori'   => 'Ringan',
                'bobot_poin' => 5,
                'keterangan' => 'Siswa tidak memakai seragam lengkap sesuai ketentuan.',
            ],
            [
                'nama'       => 'Membuang sampah sembarangan',
                'kategori'   => 'Ringan',
                'bobot_poin' => 5,
                'keterangan' => 'Siswa tidak menjaga kebersihan lingkungan sekolah.',
            ],
            [
                'nama'       => 'Tidak mengerjakan tugas/PR',
                'kategori'   => 'Ringan',
                'bobot_poin' => 10,
                'keterangan' => 'Siswa tidak mengumpulkan tugas tepat waktu tanpa alasan jelas.',
            ],

            // ── Pelanggaran Sedang ───────────────────────────────────────────────
            [
                'nama'       => 'Membolos / Meninggalkan kelas tanpa izin',
                'kategori'   => 'Sedang',
                'bobot_poin' => 20,
                'keterangan' => 'Siswa meninggalkan kelas atau sekolah tanpa izin guru.',
            ],
            [
                'nama'       => 'Bermain HP saat pelajaran',
                'kategori'   => 'Sedang',
                'bobot_poin' => 15,
                'keterangan' => 'Siswa menggunakan HP untuk keperluan non-akademik saat KBM.',
            ],
            [
                'nama'       => 'Berbicara tidak sopan kepada guru',
                'kategori'   => 'Sedang',
                'bobot_poin' => 25,
                'keterangan' => 'Siswa berkata tidak sopan atau kurang menghormati guru/staf.',
            ],
            [
                'nama'       => 'Merokok di lingkungan sekolah',
                'kategori'   => 'Sedang',
                'bobot_poin' => 30,
                'keterangan' => 'Siswa kedapatan merokok di area sekolah.',
            ],

            // ── Pelanggaran Berat ────────────────────────────────────────────────
            [
                'nama'       => 'Perkelahian / Tawuran',
                'kategori'   => 'Berat',
                'bobot_poin' => 50,
                'keterangan' => 'Siswa terlibat perkelahian fisik di dalam atau luar sekolah.',
            ],
            [
                'nama'       => 'Bullying / Perundungan',
                'kategori'   => 'Berat',
                'bobot_poin' => 50,
                'keterangan' => 'Siswa melakukan intimidasi, penghinaan, atau kekerasan terhadap siswa lain.',
            ],
            [
                'nama'       => 'Membawa/menggunakan narkoba',
                'kategori'   => 'Berat',
                'bobot_poin' => 100,
                'keterangan' => 'Siswa terbukti membawa, menyimpan, atau menggunakan narkoba.',
            ],
            [
                'nama'       => 'Pencurian',
                'kategori'   => 'Berat',
                'bobot_poin' => 75,
                'keterangan' => 'Siswa terbukti mencuri barang milik orang lain di lingkungan sekolah.',
            ],
        ];

        foreach ($kategoris as $data) {
            KategoriPelanggaran::create($data);
        }

        $this->command->info('✅ KategoriPelanggaranSeeder: ' . count($kategoris) . ' kategori berhasil dibuat.');
    }
}
