<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("
            ALTER TABLE surat_tugas
            MODIFY status_surat ENUM(
                'draft',
                'submitted_wadir_review',
                'revision_requested',
                'rejected',
                'approved_wadir',
                'pending_sekdir_numbering',
                'sekdir_revision_requested',
                'pending_direktur_signature',
                'published',
                'awaiting_proof_upload',
                'under_bku_review',
                'returned_for_correction',
                'completed'
            ) NOT NULL DEFAULT 'draft'
        ");
    }

    public function down(): void
    {
        // IMPORTANT: only safe if there are NO rows currently using 'sekdir_revision_requested'
        // If there might be, update them first before rollback.
        DB::statement("
            ALTER TABLE surat_tugas
            MODIFY status_surat ENUM(
                'draft',
                'submitted_wadir_review',
                'revision_requested',
                'rejected',
                'approved_wadir',
                'pending_sekdir_numbering',
                'pending_direktur_signature',
                'published',
                'awaiting_proof_upload',
                'under_bku_review',
                'returned_for_correction',
                'completed'
            ) NOT NULL DEFAULT 'draft'
        ");
    }
};
