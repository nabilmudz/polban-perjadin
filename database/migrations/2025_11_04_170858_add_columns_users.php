<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Add role column
            if (!Schema::hasColumn('users', 'role')) {
                $table->enum('role', [
                    'pengusul', 'wadir1', 'wadir2', 'wadir3', 'wadir4',
                    'sekdir', 'direktur', 'pelaksana', 'bku', 'admin'
                ])->default('pengusul')->after('email');
            }

            // Add foreign key to pegawai if it doesn't exist
            if (!Schema::hasColumn('users', 'pegawai_id')) {
                $table->foreignId('pegawai_id')->nullable()->after('role')
                    ->constrained('pegawai')->onDelete('set null');
            }

            // Add extra fields
            if (!Schema::hasColumn('users', 'para_file_path')) {
                $table->string('para_file_path')->nullable()->after('pegawai_id');
            }
            if (!Schema::hasColumn('users', 'signature_file_path')) {
                $table->string('signature_file_path')->nullable()->after('para_file_path');
            }
            if (!Schema::hasColumn('users', 'kode_pengusul')) {
                $table->string('kode_pengusul')->nullable()->after('signature_file_path');
            }
            if (!Schema::hasColumn('users', 'nama_unit_kerja')) {
                $table->string('nama_unit_kerja')->nullable()->after('kode_pengusul');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'role',
                'pegawai_id',
                'para_file_path',
                'signature_file_path',
                'kode_pengusul',
                'nama_unit_kerja'
            ]);
        });
    }
};
