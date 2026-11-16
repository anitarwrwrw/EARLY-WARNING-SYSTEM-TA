<?php

namespace App\Imports;

use App\Models\Siswa;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Illuminate\Support\Collection;

/**
 * Import data induk siswa dari file Excel / CSV oleh Admin.
 *
 * Header kolom yang didukung:
 * nisn | nis | nama_lengkap | jenis_kelamin | nama_ortu | no_wa_ortu | alamat | tanggal_lahir
 */
class SiswaImport implements ToCollection, WithHeadingRow
{
    private int $importedCount = 0;
    private int $updatedCount  = 0;

    public function __construct(
        private readonly int $kelasId
    ) {}

    public function collection(Collection $rows): void
    {
        foreach ($rows as $row) {
            $nisn = !empty($row['nisn']) ? trim((string)$row['nisn']) : null;
            $nis  = !empty($row['nis']) ? trim((string)$row['nis']) : null;
            $nama = !empty($row['nama_lengkap']) ? trim((string)$row['nama_lengkap']) : null;

            if (!$nis || !$nama) {
                continue; // Skip jika nis atau nama kosong
            }

            $jk = strtoupper(trim((string)($row['jenis_kelamin'] ?? 'L')));
            if (!in_array($jk, ['L', 'P'])) {
                $jk = 'L';
            }

            $tanggalLahir = null;
            if (!empty($row['tanggal_lahir'])) {
                try {
                    $tanggalLahir = date('Y-m-d', strtotime($row['tanggal_lahir']));
                } catch (\Throwable) {
                    $tanggalLahir = null;
                }
            }

            // Cari apakah siswa sudah ada berdasarkan NIS atau NISN
            $siswa = Siswa::where('nis', $nis);
            if ($nisn) {
                $siswa->orWhere('nisn', $nisn);
            }
            $existing = $siswa->first();

            $data = [
                'kelas_id'      => $this->kelasId,
                'nisn'          => $nisn,
                'nis'           => $nis,
                'nama_lengkap'  => $nama,
                'jenis_kelamin' => $jk,
                'nama_ortu'     => !empty($row['nama_ortu']) ? trim((string)$row['nama_ortu']) : null,
                'no_wa_ortu'    => !empty($row['no_wa_ortu']) ? trim((string)$row['no_wa_ortu']) : null,
                'alamat'        => !empty($row['alamat']) ? trim((string)$row['alamat']) : null,
                'tanggal_lahir' => $tanggalLahir,
                'is_active'     => true,
            ];

            if ($existing) {
                $existing->update($data);
                $this->updatedCount++;
            } else {
                Siswa::create($data);
                $this->importedCount++;
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
