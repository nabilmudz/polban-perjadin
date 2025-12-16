<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LaporanPerjalananDinas extends Model
{
    protected $table = 'laporan_perjalanan_dinas';

    protected $primaryKey = 'laporan_id';
    public $incrementing = true;
    protected $keyType = 'int';

    protected $fillable = [
        'surat_tugas_id',
        'user_id',
        'tanggal_pengumpulan_laporan',
        'status_laporan',
        'catatan_verifikasi_bku',
        'tanggal_verifikasi_bku',
        'verifikator_bku_user_id',
    ];

    public function suratTugas()
    {
        return $this->belongsTo(SuratTugas::class, 'surat_tugas_id', 'surat_tugas_id');
    }

    public function dokumenLampiran()
    {
        return $this->hasMany(DokumenLampiranLaporan::class, 'laporan_id', 'laporan_id');
    }
}
