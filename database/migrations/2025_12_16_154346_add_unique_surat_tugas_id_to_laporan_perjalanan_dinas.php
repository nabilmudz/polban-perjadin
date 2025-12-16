<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('laporan_perjalanan_dinas', function (Blueprint $table) {
            $table->unique('surat_tugas_id');
        });
    }

    public function down(): void
    {
        Schema::table('laporan_perjalanan_dinas', function (Blueprint $table) {
            $table->dropUnique(['surat_tugas_id']);
        });
    }

};
