<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('laporan_perjalanan_dinas', function (Blueprint $table) {
            $table->date('tanggal_pengumpulan_laporan')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('laporan_perjalanan_dinas', function (Blueprint $table) {
            $table->date('tanggal_pengumpulan_laporan')->nullable(false)->change();
        });
    }

};
