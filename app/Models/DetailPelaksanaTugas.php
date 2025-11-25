<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DetailPelaksanaTugas extends Model
{
    use HasFactory;

    protected $table = 'detail_pelaksana_tugas';
    protected $primaryKey = 'detail_pelaksana_id';

    protected $fillable = [
        'surat_tugas_id',
        'personable_type',
        'personable_id',
        'status_sebagai',
    ];

    public function suratTugas()
    {
        return $this->belongsTo(SuratTugas::class, 'surat_tugas_id', 'surat_tugas_id');
    }

    public function personable()
    {
        return $this->morphTo();
    }
}
