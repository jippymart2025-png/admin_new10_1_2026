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
        Schema::create('impersonation_tokens', function (Blueprint $table) {
            $table->id();

            $table->string('token', 255)->unique();

            // Restaurant owner user ID
            $table->string('user_id', 255);

            // Restaurant vendor ID
            $table->string('restaurant_id', 255);

            // Unix timestamp (INT) for expiration
            $table->unsignedBigInteger('expires_at');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('impersonation_tokens');
    }
};
