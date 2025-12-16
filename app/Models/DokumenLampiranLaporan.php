<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DokumenLampiranLaporan extends Model
{
    protected $table = 'dokumen_lampiran_laporan';
    protected $primaryKey = 'dokumen_lampiran_id';

    protected $fillable = [
        'laporan_id',
        'jenis_dokumen',
        'nama_file',
        'path_file',
        'nominal',
        'tanggal_unggah',
    ];

    public $timestamps = true;

    public function laporan()
    {
        return $this->belongsTo(LaporanPerjalananDinas::class, 'laporan_id', 'laporan_id');
    }
}
