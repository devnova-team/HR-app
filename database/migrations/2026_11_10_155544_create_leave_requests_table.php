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
        Schema::create('leave_requests', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('employee_id')->constrained('employees')->cascadeOnDelete();
            $table->foreignUuid('leave_category_id')->constrained('leave_categories');
            $table->foreignUuid('task_delegate_id')->nullable()->constrained('employees')->nullOnDelete();
            $table->dateTime('start_date');
            $table->dateTime('end_date');
            $table->decimal('days_count', 5, 2);
            $table->text('description');
            $table->string('emergency_contact_name');
            $table->string('emergency_contact_phone');
            $table->string('status')->default('review');
            $table->foreignUuid('reviewed_by_id')->nullable()->constrained('employees')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->timestamps();

            $table->index(['employee_id', 'status']);
            $table->index(['employee_id', 'start_date', 'end_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('leave_requests');
    }
};
