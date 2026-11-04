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
        Schema::create('password_reset_otps', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('employee_id')->constrained('employees')->onDelete('cascade');
            $table->string('otp_hash');
            $table->timestamp('expires_at');
            $table->timestamp('verified_at')->nullable();
            $table->timestamp('used_at')->nullable();
            $table->unsignedTinyInteger('attempts')->default(0);  // 5 attempts limit
            $table->string('reset_token_hash')->nullable(); // Optional: to link OTP with a reset token
            $table->index('reset_token_hash'); // Index for quick lookup
            $table->timestamp('reset_token_expires_at')->nullable(); // Optional: expiration for the reset token
            $table->timestamps();
            $table->index(['employee_id', 'created_at']); // to get the latest OTP for an employee quickly

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('password_reset_otps');
    }
};
