<?php

namespace App\Imports;

use App\Models\Pegawai;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\ToCollection;
use Illuminate\Support\Collection;
class PegawaiImport implements ToCollection, WithHeadingRow
{
    public array $duplicates = [];

    public function collection(Collection $rows)
    {
        $toInsert = [];

        foreach ($rows as $index => $row) {
            $rowIndex = $index + 1;
            $nip = trim($row['nip'] ?? '');

            if (!$nip) continue;

            if (Pegawai::where('nip', $nip)->exists()) {
                $this->duplicates[] = "Baris {$rowIndex} — NIP {$nip} sudah terdaftar";
                continue;
            }

            $toInsert[] = [
                'nama' => $row['nama'] ?? '',
                'nip' => $nip,
                'pangkat' => $row['pangkat'] ?? null,
                'golongan' => $row['golongan'] ?? null,
                'jabatan' => $row['jabatan'] ?? null,
                'status' => $this->convertStatus($row['status'] ?? 'aktif'),
            ];
        }

        if (!empty($toInsert)) {
            Pegawai::insert($toInsert);
        }
    }

    private function convertStatus($value)
    {
        $map = [
            'aktif' => 1,
            'active' => 1,
            'non aktif' => 0,
            'non-aktif' => 0,
            'tidak aktif' => 0,
        ];
        return $map[strtolower(trim($value))] ?? 0;
    }
}
