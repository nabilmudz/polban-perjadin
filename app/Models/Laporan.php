<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Laporan extends Model
{
    use HasFactory;

    protected $table = 'laporan_perjalanan_dinas'; 
    
    protected $guarded = [];

    public function suratTugas()
    {
        return $this->belongsTo(SuratTugas::class, 'surat_tugas_id', 'surat_tugas_id');
    }
}