<?php

namespace App\Imports;

use App\Models\Mahasiswa;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;

class MahasiswaImport implements ToModel, WithHeadingRow, WithValidation
{
    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public function model(array $row)
    {
        return new Mahasiswa([
            'nim'  => $row['nim'],
            'nama' => $row['nama'],
            'jurusan' => $row['jurusan'],
            'prodi' => $row['prodi'],
        ]);
    }

    public function rules(): array
    {
        return [
            'nim' => 'required|unique:mahasiswa,nim',
            'nama' => 'required',
            'jurusan' => 'required',
            'prodi' => 'required',
        ];
    }
}
