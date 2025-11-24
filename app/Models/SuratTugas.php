<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SuratTugas extends Model
{
    use HasFactory;

    protected $primaryKey = 'surat_tugas_id';

    protected $fillable = [
        'nomor_urutan_surat',
        'kode_unit_kerja',
        'kode_perihal',
        'tahun_nomor_surat',
        'user_id',
        'diusulkan_kepada',
        'nama_penyelenggara',
        'lokasi_kegiatan',
        'nomor_surat_usulan_jurusan',
        'nomor_surat_tugas_resmi',
        'perihal_tugas',
        'ditugaskan_sebagai',
        'kota_tujuan',
        'tanggal_berangkat',
        'tanggal_kembali',
        'status_surat',
        'catatan_revisi',
        'path_file_surat_usulan',
        'path_file_surat_tugas_final',
        'sumber_dana',
        'pagu_desentralisasi',
        'template_nama_kementerian',
        'template_nama_direktur',
        'template_nip_direktur',
        'template_tembusan',
        'rendered_html',
        'tanggal_paraf_wadir',
        'tanggal_persetujuan_direktur',
        'tanggal_penomoran_sekdir',
        'wadir_signature_data',
        'direktur_signature_data',
        'wadir_signature_position',
        'direktur_signature_position',
        'is_surat_perintah_langsung',
        'wadir_approver_id',
        'direktur_approver_id',
        'sekdir_processor_id',
    ];

    protected $casts = [
        'lokasi_kegiatan' => 'array',
        'template_tembusan' => 'array',
        'wadir_signature_position' => 'array',
        'direktur_signature_position' => 'array',
        'tanggal_pengajuan' => 'datetime',
        'tanggal_berangkat' => 'datetime',
        'tanggal_kembali' => 'datetime',
        'tanggal_paraf_wadir' => 'datetime',
        'tanggal_persetujuan_direktur' => 'datetime',
        'tanggal_penomoran_sekdir' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function pengusul()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function wadir()
    {
        return $this->belongsTo(User::class, 'wadir_approver_id');
    }

    public function direktur()
    {
        return $this->belongsTo(User::class, 'direktur_approver_id');
    }

    public function sekdir()
    {
        return $this->belongsTo(User::class, 'sekdir_processor_id');
    }

    public function laporan()
    {
        return $this->hasOne(
            Laporan::class,
            'surat_tugas_id',
            'surat_tugas_id'
        );
    }

    public function detailPelaksanaTugas()
    {
        return $this->hasMany(
            DetailPelaksanaTugas::class,
            'surat_tugas_id',
            'surat_tugas_id'
        );
    }
}