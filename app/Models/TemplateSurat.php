<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TemplateSurat extends Model
{
    protected $table = 'template_surat';

    protected $fillable = [
        'nama_kementerian',
        'nama_direktur',
        'nip_direktur',
        'tembusan_default',
    ];

    protected $casts = [
        'tembusan_default' => 'array',
    ];
}
