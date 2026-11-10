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
        Schema::create('employee_leave_balances', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('employee_id')->constrained('employees')->cascadeOnDelete();
            $table->foreignUuid('leave_category_id')->constrained('leave_categories')->cascadeOnDelete();
            $table->unsignedSmallInteger('year');
            $table->decimal('total_days', 5, 2);
            $table->decimal('used_days', 5, 2)->default(0);
            $table->timestamps();

            $table->unique(
                ['employee_id', 'leave_category_id', 'year'],
                'emp_leave_balances_emp_cat_year_unique'
            );
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employee_leave_balances');
    }
};
