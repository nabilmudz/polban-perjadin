<?php

namespace App\Services;

use App\Models\SuratTugas;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class SuratTugasService
{
    public function getAll(array $filters = [], $user = null)
    {
        $query = SuratTugas::query();

        if ($user && $user->role === 'pengusul') {
            $query->where('user_id', $user->id);
        }

        if (!empty($filters['search'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('perihal_tugas', 'like', "%{$filters['search']}%")
                ->orWhere('status_surat', 'like', "%{$filters['search']}%");
            });
        }

        if (!empty($filters['status'])) {
            $query->where('status_surat', $filters['status']);
        }

        if (!empty($filters['from']) && !empty($filters['to'])) {
            $query->whereBetween('created_at', [$filters['from'], $filters['to']]);
        }

        return $query->latest()->paginate(10)->withQueryString();
    }


    public function getById($id)
    {
        $item = SuratTugas::find($id);
        if (!$item) throw new ModelNotFoundException('Surat Tugas not found');
        return $item;
    }

    public function create(array $data)
    {
        return SuratTugas::create($data);
    }

    public function update($id, array $data)
    {
        $item = SuratTugas::findOrFail($id);
        $item->update($data);
        return $item;
    }

    public function delete($id)
    {
        $item = SuratTugas::findOrFail($id);
        $item->delete();
    }

    public function getForUser(User $user, array $filters = [])
    {
        $query = SuratTugas::query();

        if ($user->hasRole('pengusul')) {
            $query->where('user_id', $user->id);
        }

        if ($user->hasRole('wadir1')) {
            $query->whereIn('status_surat', ['submitted_wadir_review', 'approved_wadir']);
        }

        if ($user->hasRole('sekdir')) {
            $query->where('status_surat', 'pending_sekdir_numbering');
        }

        if (!empty($filters['status'])) {
            $query->where('status_surat', $filters['status']);
        }

        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function (Builder $q) use ($search) {
                $q->where('perihal_tugas', 'like', "%{$search}%")
                    ->orWhere('nama_penyelenggara', 'like', "%{$search}%");
            });
        }

        if (!empty($filters['from']) && !empty($filters['to'])) {
            $query->whereBetween('tanggal_berangkat', [$filters['from'], $filters['to']]);
        }

        return $query->latest()->paginate(10);
    }
}
