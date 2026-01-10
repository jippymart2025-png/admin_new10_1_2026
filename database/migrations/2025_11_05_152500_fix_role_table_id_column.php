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
        // First, check if the table exists
        if (Schema::hasTable('role')) {
            // Modify the id column to be auto-increment
            DB::statement('ALTER TABLE `role` MODIFY `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY');
        } else {
            // If table doesn't exist, create it with proper structure
            Schema::create('role', function (Blueprint $table) {
                $table->id(); // This creates an auto-increment BIGINT UNSIGNED primary key
                $table->string('role_name');
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // We don't reverse this change as it's a fix for existing data
        // Removing auto-increment could cause issues
    }
};

