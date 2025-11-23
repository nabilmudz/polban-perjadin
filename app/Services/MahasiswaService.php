<?php

namespace App\Services;

use App\Models\Mahasiswa;
use Illuminate\Http\Request;
use App\Imports\MahasiswaImport;
use Maatwebsite\Excel\Facades\Excel;

class MahasiswaService
{
    public function getMahasiswa(Request $request)
    {
        $query = Mahasiswa::query();

        if ($request->has('search')) {
            $query->where('nama', 'like', '%' . $request->search . '%')
                ->orWhere('nim', 'like', '%' . $request->search . '%')
                ->orWhere('jurusan', 'like', '%' . $request->search . '%');
        }

        if ($request->has('sort_by') && $request->has('sort_direction')) {
            $query->orderBy($request->sort_by, $request->sort_direction);
        } else {
            $query->orderBy('nama', 'asc');
        }

        return $query->paginate($request->get('limit', 10));
    }

    public function createMahasiswa(Request $request)
    {
        $request->validate([
            'nim' => 'required|unique:mahasiswa,nim',
            'nama' => 'required|string|max:255',
            'jurusan' => 'required|string|max:255',
            'prodi' => 'required|string|max:255',
        ]);

        return Mahasiswa::create($request->all());
    }

    public function updateMahasiswa(Request $request, Mahasiswa $mahasiswa)
    {
        $request->validate([
            'nim' => 'required|unique:mahasiswa,nim,' . $mahasiswa->id,
            'nama' => 'required|string|max:255',
            'prodi' => 'required|string|max:255',
        ]);

        $mahasiswa->update($request->all());
        return $mahasiswa;
    }

    public function deleteMahasiswa(Mahasiswa $mahasiswa)
    {
        return $mahasiswa->delete();
    }

    public function importMahasiswa(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls',
        ]);

        Excel::import(new MahasiswaImport, $request->file('file'));
    }
}