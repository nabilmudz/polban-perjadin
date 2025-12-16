<?php

namespace App\Services;

use App\Models\TemplateSurat;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class TemplateSuratService
{
    public function getAll()
    {
        $query = TemplateSurat::query()
            ->orderByDesc('status')
            ->orderByDesc('id')
            ->paginate(10);

        return [
            'data' => $query->items(),
            'meta' => [
                'current_page' => $query->currentPage(),
                'last_page'    => $query->lastPage(),
                'per_page'     => $query->perPage(),
                'from'         => $query->firstItem(),
                'to'           => $query->lastItem(),
                'total'        => $query->total(),
            ],
            'links' => [
                'prev' => $query->previousPageUrl(),
                'next' => $query->nextPageUrl(),
            ],
        ];
    }

    public function create(array $data): TemplateSurat
    {
        return DB::transaction(function () use ($data) {
            $status = (int)($data['status'] ?? 0);

            if ($status === 1) {
                TemplateSurat::where('status', 1)->update(['status' => 0]);
            }

            return TemplateSurat::create($data);
        });
    }

    public function update(int $id, array $data): TemplateSurat
    {
        return DB::transaction(function () use ($id, $data) {
            $template = TemplateSurat::findOrFail($id);

            $status = array_key_exists('status', $data)
                ? (int)$data['status']
                : (int)$template->status;

            if ($status === 1) {
                TemplateSurat::where('status', 1)
                    ->where('id', '!=', $id)
                    ->update(['status' => 0]);
            }

            $template->update($data);

            return $template->refresh();
        });
    }

    public function toggleStatus(int $id): TemplateSurat
    {
        return DB::transaction(function () use ($id) {
            $template = TemplateSurat::findOrFail($id);

            if (!$template->status) {
                TemplateSurat::where('status', 1)
                    ->where('id', '!=', $id)
                    ->update(['status' => 0]);

                $template->update(['status' => 1]);
            } else {
                $template->update(['status' => 0]);
            }

            return $template->refresh();
        });
    }
}

Cache::forget('active_template_surat');