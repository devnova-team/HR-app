<?php

namespace App\Models;

use App\Enums\ExpenseStatus;
use Database\Factories\ExpenseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Expense extends BaseModel
{
    /** @use HasFactory<ExpenseFactory> */
    use HasFactory;

    protected $attributes = [
        'status' => ExpenseStatus::Review->value,
    ];

    protected $fillable = [
        'employee_id',
        'expense_category_id',
        'reviewed_by_id',
        'payroll_record_id',
        'amount',
        'currency',
        'transaction_date',
        'description',
        'document_url',
        'status',
        'reviewed_at',
        'rejection_reason',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'transaction_date' => 'datetime',
            'status' => ExpenseStatus::class,
            'reviewed_at' => 'datetime',
        ];
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function expenseCategory(): BelongsTo
    {
        return $this->belongsTo(ExpenseCategory::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(ExpenseCategory::class, 'expense_category_id');
    }

    public function getCategoryIdAttribute(): ?string
    {
        return $this->expense_category_id;
    }

    public function setCategoryIdAttribute(?string $value): void
    {
        $this->attributes['expense_category_id'] = $value;
    }

    public function reviewedBy(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'reviewed_by_id');
    }

    public function payrollRecord(): BelongsTo
    {
        return $this->belongsTo(PayrollRecord::class);
    }
}
