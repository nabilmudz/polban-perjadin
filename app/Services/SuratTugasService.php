<?php

namespace App\Services;

use App\Models\SuratTugas;
use App\Models\User;
use App\Models\DetailPelaksanaTugas;
use App\Models\Pegawai;
use App\Models\Mahasiswa;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;

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
        if (!$item) {
            throw new ModelNotFoundException('Surat Tugas not found');
        }

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

    public function createWithPersonel(array $suratData, array $personel)
    {
        return DB::transaction(function () use ($suratData, $personel) {
            $surat = SuratTugas::create($suratData);

            foreach ($personel as $p) {
                $personableType = $p['type'] === 'pegawai'
                    ? Pegawai::class
                    : Mahasiswa::class;

                DetailPelaksanaTugas::create([
                    'surat_tugas_id'  => $surat->surat_tugas_id,
                    'personable_type' => $personableType,
                    'personable_id'   => $p['id'],
                    'status_sebagai'  => 'Peserta',
                ]);
            }

            return $surat;
        });
    }

    public function updateStatus(SuratTugas $surat, string $status, ?string $catatan, string $role)
    {
        $allowedTransitions = [
            'pengusul' => [
                'draft' => ['submitted_wadir_review'],
                'revision_requested' => ['submitted_wadir_review'],
                'returned_for_correction' => ['submitted_wadir_review'],
            ],

            //WADIR
            'wadir1' => [
                'submitted_wadir_review' => ['approved_wadir', 'revision_requested', 'rejected'],
            ],
            'wadir2' => [
                'submitted_wadir_review' => ['approved_wadir', 'revision_requested', 'rejected'],
            ],
            'wadir3' => [
                'submitted_wadir_review' => ['approved_wadir', 'revision_requested', 'rejected'],
            ],
            'wadir4' => [
                'submitted_wadir_review' => ['approved_wadir', 'revision_requested', 'rejected'],
            ],

            // SEKDIR
            'sekdir' => [
                'approved_wadir' => ['pending_sekdir_numbering'],
                'pending_sekdir_numbering' => ['pending_direktur_signature', 'returned_for_correction'],
            ],

            // DIREKTUR
            'direktur' => [
                'pending_direktur_signature' => ['published', 'returned_for_correction', 'rejected'],
            ],

            // BKU
            'bku' => [
                'under_bku_review' => ['completed', 'returned_for_correction'],
            ],
        ];

        $current = $surat->status_surat;

        if (!isset($allowedTransitions[$role])) {
            throw new \DomainException("Role '{$role}' tidak memiliki akses perubahan status.");
        }

        if (!isset($allowedTransitions[$role][$current])) {
            throw new \DomainException("Role '{$role}' tidak boleh mengubah status dari '{$current}'.");
        }

        if (!in_array($status, $allowedTransitions[$role][$current], true)) {
            throw new \DomainException("Transisi dari '{$current}' ke '{$status}' tidak diizinkan untuk role '{$role}'.");
        }

        $requiresCatatan = ['revision_requested', 'returned_for_correction', 'rejected'];

        if (in_array($status, $requiresCatatan, true) && (empty($catatan))) {
            throw new \DomainException("Status '{$status}' membutuhkan catatan revisi.");
        }

        return DB::transaction(function () use ($surat, $status, $catatan) {
            $data = [
                'status_surat' => $status,
            ];

            if (!empty($catatan)) {
                $data['catatan_revisi'] = $catatan;
            }

            $surat->update($data);

            return $surat->fresh();
        });
    }
}

