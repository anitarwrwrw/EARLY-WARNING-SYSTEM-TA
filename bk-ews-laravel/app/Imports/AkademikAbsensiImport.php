<?php

namespace App\Imports;

use App\Models\AkademikAbsensi;
use App\Models\PeriodeSemester;
use App\Models\Siswa;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Illuminate\Support\Collection;

/**
 * Import data nilai & absensi dari Excel/CSV oleh Wali Kelas.
 *
 * Header kolom yang didukung:
 *   nis | rata_nilai (atau rata_rata_nilai) | jumlah_alpha | jumlah_izin | jumlah_sakit
 */
class AkademikAbsensiImport implements ToCollection, WithHeadingRow
{
    private int $importedCount = 0;
    private int $updatedCount  = 0;

    public function __construct(
        private readonly int $semesterId,
        private readonly int $inputOlehId,
    ) {}

    public function collection(Collection $rows): void
    {
        // Cari semester sebelumnya untuk perhitungan tren nilai otomatis
        $semSebelumnya = PeriodeSemester::where('id', '<', $this->semesterId)->latest('id')->first();

        foreach ($rows as $row) {
            $nis = trim((string)($row['nis'] ?? ''));
            if (empty($nis)) continue;

            $siswa = Siswa::where('nis', $nis)->where('is_active', true)->first();
            if (! $siswa) continue; // Skip jika siswa tidak ditemukan

            // Dukung 'rata_nilai' maupun 'rata_rata_nilai'
            $nilaiSekarang = (float) ($row['rata_nilai'] ?? $row['rata_rata_nilai'] ?? 0);

            // Hitung tren nilai dari selisih semester ini vs semester lalu
            $trenNilai = 0.0;
            if ($semSebelumnya) {
                $nilaiSebelumnya = AkademikAbsensi::where('siswa_id', $siswa->id)
                    ->where('semester_id', $semSebelumnya->id)
                    ->value('rata_nilai');

                if ($nilaiSebelumnya !== null) {
                    $trenNilai = round($nilaiSekarang - (float)$nilaiSebelumnya, 2);
                }
            }

            $record = AkademikAbsensi::updateOrCreate(
                ['siswa_id' => $siswa->id, 'semester_id' => $this->semesterId],
                [
                    'input_oleh'   => $this->inputOlehId,
                    'rata_nilai'   => $nilaiSekarang,
                    'tren_nilai'   => $trenNilai,
                    'jumlah_alpha' => (int) ($row['jumlah_alpha'] ?? 0),
                    'jumlah_izin'  => (int) ($row['jumlah_izin'] ?? 0),
                    'jumlah_sakit' => (int) ($row['jumlah_sakit'] ?? 0),
                ]
            );

            if ($record->wasRecentlyCreated) {
                $this->importedCount++;
            } else {
                $this->updatedCount++;
            }
        }
    }

    public function getImportedCount(): int
    {
        return $this->importedCount;
    }

    public function getUpdatedCount(): int
    {
        return $this->updatedCount;
    }
}
