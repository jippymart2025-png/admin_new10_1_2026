<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Note: Table already exists as 'dynamic_notification' (singular)
        // This migration is for documentation only
        if (!Schema::hasTable('dynamic_notification')) {
            Schema::create('dynamic_notification', function (Blueprint $table) {
                $table->string('id')->primary();
                $table->string('createdAt')->nullable();
                $table->string('subject')->nullable();
                $table->string('message')->nullable();
                $table->string('type')->nullable();
            });
        }
    }

    public function down(): void
    {
        // Don't drop - table already exists
        // Schema::dropIfExists('dynamic_notification');
    }
};
