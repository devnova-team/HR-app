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
        Schema::create('employees', function (Blueprint $table) {
            $table->uuid('id')->primary();

            // Foreign Keys (Organization)
            $table->foreignUuid('level_id')->constrained('levels');
            $table->foreignUuid('department_id')->constrained('departments');
            $table->foreignUuid('job_title_id')->constrained('job_titles');

            // Foreign Keys (Location)
            $table->foreignUuid('country_id')->nullable()->constrained('countries')->nullOnDelete();
            $table->foreignUuid('governorate_id')->nullable()->constrained('governorates')->nullOnDelete();
            $table->foreignUuid('city_id')->nullable()->constrained('cities')->nullOnDelete();

            // Basic Info
            $table->string('employee_id')->unique();
            $table->string('first_name');
            $table->string('last_name');
            $table->string('email')->unique();
            $table->string('phone_number');
            $table->string('password');
            $table->date('date_of_birth')->nullable();
            $table->string('avatar_url')->nullable();
            $table->string('full_address');

            // Status
            $table->boolean('is_verified')->default(false);
            $table->boolean('is_first_login')->default(true);
            $table->boolean('is_active')->default(true);

            $table->timestamp('email_verified_at')->nullable();
            $table->timestamps();

            // Indexes
            $table->index(['department_id', 'level_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employees');
    }
};
