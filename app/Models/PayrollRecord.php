<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PayrollRecord extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'employee_id', 'period_start', 'period_end',
        'total_hours_minutes', 'overtime_hours_minutes', 'pay_period_hours_minutes',
        'basic_salary', 'tax', 'reimbursement', 'bonus', 'overtime_pay', 'total_salary',
        'currency', 'paid_on',
    ];

    protected function casts(): array
    {
        return [
            'period_start' => 'date',
            'period_end' => 'date',
            'paid_on' => 'datetime',
            'total_hours_minutes' => 'integer',
            'overtime_hours_minutes' => 'integer',
            'pay_period_hours_minutes' => 'integer',
            'basic_salary' => 'decimal:2',
            'tax' => 'decimal:2',
            'reimbursement' => 'decimal:2',
            'bonus' => 'decimal:2',
            'overtime_pay' => 'decimal:2',
            'total_salary' => 'decimal:2',
        ];
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }
}
