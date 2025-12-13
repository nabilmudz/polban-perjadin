<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BuktiLaporan extends Model
{
    protected $table = 'bukti_laporan';

    protected $fillable = [
        'surat_tugas_id',
        'kategori',
        'file_path',
        'nominal',
        'keterangan',
    ];

    public function suratTugas()
    {
        return $this->belongsTo(SuratTugas::class, 'surat_tugas_id', 'surat_tugas_id');
    }
}
