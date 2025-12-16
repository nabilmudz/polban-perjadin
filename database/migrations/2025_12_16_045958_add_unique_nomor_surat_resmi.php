<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('surat_tugas', function (Blueprint $table) {
            $table->unique(
                ['tahun_nomor_surat', 'kode_unit_kerja', 'kode_perihal', 'nomor_urutan_surat'],
                'uniq_nomor_surat_resmi'
            );
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('surat_tugas', function (Blueprint $table) {
            $table->dropUnique('uniq_nomor_surat_resmi');
        });
    }
};
