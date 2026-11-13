<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payroll_records', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('employee_id')->constrained('employees')->cascadeOnDelete();
            $table->date('period_start');
            $table->date('period_end');
            $table->unsignedInteger('total_hours_minutes')->default(0);
            $table->unsignedInteger('overtime_hours_minutes')->default(0);
            $table->unsignedInteger('pay_period_hours_minutes')->default(0);
            $table->decimal('basic_salary', 12, 2)->default(0);
            $table->decimal('tax', 12, 2)->default(0);
            $table->decimal('reimbursement', 12, 2)->default(0);
            $table->decimal('bonus', 12, 2)->default(0);
            $table->decimal('overtime_pay', 12, 2)->default(0);
            $table->decimal('total_salary', 12, 2)->default(0);
            $table->string('currency', 3)->default('USD');
            $table->timestamp('paid_on');
            $table->timestamps();

            $table->unique(['employee_id', 'period_start']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payroll_records');
    }
};
