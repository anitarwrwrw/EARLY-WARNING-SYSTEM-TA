<?php

namespace App\Imports;

use App\Models\AkademikAbsensi;
use App\Models\Siswa;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Illuminate\Support\Collection;

/**
 * Import data nilai & absensi dari Excel/CSV.
 *
 * Format kolom Excel yang diharapkan:
 *   nis | rata_rata_nilai | jumlah_alpha | jumlah_izin | jumlah_sakit
 *
 * Baris pertama adalah header (WithHeadingRow).
 */
class AkademikAbsensiImport implements ToCollection, WithHeadingRow, WithValidation
{
    public function __construct(
        private readonly string $semester,
        private readonly int $inputOlehId,
    ) {}

    public function collection(Collection $rows): void
    {
        foreach ($rows as $row) {
            $nis    = trim($row['nis'] ?? '');
            $siswa  = Siswa::where('nis', $nis)->where('is_active', true)->first();

            if (! $siswa) continue; // Skip baris dengan NIS tidak ditemukan

            $nilaiSekarang  = (float) ($row['rata_rata_nilai'] ?? 0);

            // Hitung tren nilai
            $nilaiSebelumnya = AkademikAbsensi::where('siswa_id', $siswa->id)
                ->where('semester', $this->semesterSebelumnya())
                ->value('rata_rata_nilai');

            $trenNilai = $nilaiSebelumnya !== null
                ? round($nilaiSekarang - $nilaiSebelumnya, 2)
                : 0;

            AkademikAbsensi::updateOrCreate(
                ['siswa_id' => $siswa->id, 'semester' => $this->semester],
                [
                    'input_oleh'      => $this->inputOlehId,
                    'rata_rata_nilai' => $nilaiSekarang,
                    'tren_nilai'      => $trenNilai,
                    'jumlah_alpha'    => (int) ($row['jumlah_alpha'] ?? 0),
                    'jumlah_izin'     => (int) ($row['jumlah_izin'] ?? 0),
                    'jumlah_sakit'    => (int) ($row['jumlah_sakit'] ?? 0),
                ]
            );
        }
    }

    public function rules(): array
    {
        return [
            'nis'             => 'required',
            'rata_rata_nilai' => 'required|numeric|min:0|max:100',
            'jumlah_alpha'    => 'required|integer|min:0',
        ];
    }

    private function semesterSebelumnya(): string
    {
        [$jenis, $ta] = explode(' ', $this->semester, 2);
        [$tahunMulai] = explode('/', $ta);
        if ($jenis === 'Ganjil') {
            $tahunSebelumnya = $tahunMulai - 1;
            return "Genap {$tahunSebelumnya}/{$tahunMulai}";
        } else {
            return "Ganjil {$ta}";
        }
    }
}

