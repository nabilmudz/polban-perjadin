<?php

namespace App\Services;

use App\Models\Mahasiswa;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Maatwebsite\Excel\Facades\Excel;

class MahasiswaService
{
    public function getAll(array $filters = [])
    {
        $query = Mahasiswa::query();

        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function($q) use ($search) {
                $q->where('nama', 'like', "%{$search}%")
                  ->orWhere('nim', 'like', "%{$search}%")
                  ->orWhere('jurusan', 'like', "%{$search}%")
                  ->orWhere('prodi', 'like', "%{$search}%");
            });
        }

        $paginate = $query->latest()->paginate(10)->withQueryString();

        return [
            'data' => $paginate->items(),
            'meta' => [
                'current_page' => $paginate->currentPage(),
                'last_page' => $paginate->lastPage(),
                'per_page' => $paginate->perPage(),
                'from' => $paginate->firstItem(),
                'to' => $paginate->lastItem(),
                'total' => $paginate->total(),
            ],
            'links' => [
                'prev' => $paginate->previousPageUrl(),
                'next' => $paginate->nextPageUrl(),
            ],
        ];
    }

    public function getById($id)
    {
        $mahasiswa = Mahasiswa::find($id);
        if (!$mahasiswa) throw new ModelNotFoundException('Mahasiswa not found');
        return $mahasiswa;
    }

    public function create(array $data)
    {
        return Mahasiswa::create($data);
    }

    public function update($id, array $data)
    {
        $mahasiswa = Mahasiswa::findOrFail($id);
        $mahasiswa->update($data);
        return $mahasiswa;
    }

    public function delete($id)
    {
        $mahasiswa = Mahasiswa::findOrFail($id);
        $mahasiswa->delete();
        return true;
    }

    public function toggleStatus($id)
    {
        $mahasiswa = Mahasiswa::findOrFail($id);
        $mahasiswa->status = $mahasiswa->status == 1 ? 0 : 1;
        $mahasiswa->save();

        return $mahasiswa;
    }
    
    // public function importExcel($file)
    // {
    //     try {
    //         Excel::import(new MahasiswaImport, $file);
    //     } catch (\Exception $e) {
    //         throw new \Exception("Gagal mengimpor file: " . $e->getMessage());
    //     }

    //     return true;
    // }

}
