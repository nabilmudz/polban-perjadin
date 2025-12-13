<?php

namespace App\Services;

use App\Models\TemplateSurat;

class TemplateSuratService
{
    public function getAll()
    {
        $query = TemplateSurat::query()
            ->orderByDesc('status')   // aktif di atas
            ->latest()                // terbaru dulu
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
        return TemplateSurat::create($data);
    }

    public function update(int $id, array $data): TemplateSurat
    {
        $template = TemplateSurat::findOrFail($id);

        $template->update($data);

        return $template;
    }

    public function toggleStatus(int $id): TemplateSurat
    {
        $template = TemplateSurat::findOrFail($id);

        $template->update([
            'status' => !$template->status,
        ]);

        return $template;
    }
}
