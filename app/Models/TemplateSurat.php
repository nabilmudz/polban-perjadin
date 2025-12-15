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
        'status',
    ];

    protected $casts = [
    'status' => 'boolean',
    ];
}
