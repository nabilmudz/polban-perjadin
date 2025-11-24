<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\TemplateSurat;
use Inertia\Inertia;

class TemplateSuratController extends Controller
{
    public function edit()
    {
        $template = TemplateSurat::first() ?? new TemplateSurat([
            'nama_kementerian' => '',
            'nama_direktur' => '',
            'nip_direktur' => '',
            'tembusan_default' => [],
        ]);

        return Inertia::render('Admin/TemplateSurat', [
            'template' => TemplateSurat::first()
        ]);
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'nama_kementerian' => 'nullable|string',
            'nama_direktur'    => 'nullable|string',
            'nip_direktur'     => 'nullable|string',
            'tembusan_default' => 'nullable|array',
        ]);

        $template = TemplateSurat::first();

        if ($template) {
            $template->update($data);
        } else {
            TemplateSurat::create($data);
        }

        return back()->with('success', 'Template berhasil disimpan!');
    }
}