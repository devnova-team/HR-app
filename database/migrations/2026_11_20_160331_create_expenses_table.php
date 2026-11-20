<?php

use App\Enums\ExpenseStatus;
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
        Schema::create('expenses', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('employee_id')->constrained('employees')->cascadeOnDelete();
            $table->foreignUuid('expense_category_id')->constrained('expense_categories');
            $table->foreignUuid('reviewed_by_id')->nullable()->constrained('employees')->nullOnDelete();
            $table->foreignUuid('payroll_record_id')->nullable()->constrained('payroll_records')->nullOnDelete();
            $table->decimal('amount', 12, 2);
            $table->string('currency', 3)->default('USD');
            $table->dateTime('transaction_date');
            $table->text('description')->nullable();
            $table->string('document_url')->nullable();
            $table->string('status')->default(ExpenseStatus::Review->value);
            $table->timestamp('reviewed_at')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->timestamps();

            $table->index(['employee_id', 'status']);
            $table->index(['employee_id', 'transaction_date']);
            $table->index('payroll_record_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('expenses');
    }
};
