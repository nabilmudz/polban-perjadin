<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('surat_tugas', function (Blueprint $table) {
            $table->enum('penyelenggara', ['polban', 'penyelenggara', 'kedua'])
                ->default('polban')
                ->after('diusulkan_kepada');
        });
    }

    public function down(): void
    {
        Schema::table('surat_tugas', function (Blueprint $table) {
            $table->dropColumn('penyelenggara');
        });
    }
};
