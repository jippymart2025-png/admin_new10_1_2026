<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Alter the enum column to include all required status values
        DB::statement("ALTER TABLE `settlement_weeks` MODIFY COLUMN `status` ENUM('open', 'under_review', 'approved', 'processing', 'settled', 'failed', 'on_hold') NOT NULL DEFAULT 'open'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert to original enum values (if needed)
        // Note: This assumes the original enum had: 'open', 'under_review', 'approved', 'processing'
        DB::statement("ALTER TABLE `settlement_weeks` MODIFY COLUMN `status` ENUM('open', 'under_review', 'approved', 'processing') NOT NULL DEFAULT 'open'");
    }
};
