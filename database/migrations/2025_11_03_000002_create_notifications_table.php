<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Note: The notifications table already exists in your database.
     * This migration ensures it has the correct structure.
     */
    public function up(): void
    {
        // Table already exists with structure:
        // - id (varchar/uuid)
        // - role (varchar)
        // - subject (varchar)
        // - message (varchar)
        // - createdAt (varchar - stores ISO date strings)
        
        // Only create if it doesn't exist (should already exist)
        if (!Schema::hasTable('notifications')) {
            Schema::create('notifications', function (Blueprint $table) {
                $table->string('id')->primary();
                $table->string('role')->nullable();
                $table->string('subject')->nullable();
                $table->string('message')->nullable();
                $table->string('createdAt')->nullable(); // Matches existing table structure
            });
        }
    }

    public function down(): void
    {
        // Don't drop - table was pre-existing
        // Schema::dropIfExists('notifications');
    }
};
