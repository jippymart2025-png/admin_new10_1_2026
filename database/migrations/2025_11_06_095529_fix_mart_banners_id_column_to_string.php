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
        Schema::table('mart_banners', function (Blueprint $table) {
            // Drop primary key constraint first
            $table->dropPrimary('PRIMARY');
            
            // Change id column to string (UUID)
            $table->string('id', 36)->change();
            
            // Re-add primary key
            $table->primary('id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('mart_banners', function (Blueprint $table) {
            // Drop primary key constraint
            $table->dropPrimary('PRIMARY');
            
            // Change back to integer
            $table->bigIncrements('id')->change();
        });
    }
};
