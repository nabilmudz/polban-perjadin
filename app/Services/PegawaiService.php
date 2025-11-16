<?php

namespace App\Services;

use App\Models\Pegawai;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class PegawaiService
{
    public function getAll(array $filters = [])
    {
        $query = Pegawai::query();

        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function($q) use ($search) {
                $q->where('nama', 'like', "%{$search}%")
                  ->orWhere('nip', 'like', "%{$search}%")
                  ->orWhere('pangkat', 'like', "%{$search}%")
                  ->orWhere('golongan', 'like', "%{$search}%")
                  ->orWhere('jabatan', 'like', "%{$search}%");
            });
        }

        if (isset($filters['status']) && $filters['status'] !== '') {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['from']) && !empty($filters['to'])) {
            $query->whereBetween('created_at', [$filters['from'], $filters['to']]);
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
        $pegawai = Pegawai::find($id);
        if (!$pegawai) throw new ModelNotFoundException('Pegawai not found');
        return $pegawai;
    }

    public function create(array $data)
    {
        return Pegawai::create($data);
    }

    public function update($id, array $data)
    {
        $pegawai = Pegawai::findOrFail($id);
        $pegawai->update($data);
        return $pegawai;
    }

    public function delete($id)
    {
        $pegawai = Pegawai::findOrFail($id);
        $pegawai->delete();
        return true;
    }

    public function toggleStatus($id)
    {
        $pegawai = Pegawai::findOrFail($id);
        $pegawai->status = $pegawai->status == 1 ? 0 : 1;
        $pegawai->save();

        return $pegawai;
    }
}
