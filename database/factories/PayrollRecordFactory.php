<?php

namespace Database\Factories;

use App\Models\PayrollRecord;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * Demo / test data only. Payroll is stored data, so the app never computes it;
 * the total here is just made consistent so seeded records look sensible.
 * Always pass employee_id: PayrollRecord::factory()->create(['employee_id' => $id]).
 *
 * @extends Factory<PayrollRecord>
 */
class PayrollRecordFactory extends Factory
{
    protected $model = PayrollRecord::class;

    public function definition(): array
    {
        $start = now()->subMonthNoOverflow()->startOfMonth();

        $basic = fake()->randomFloat(2, 2000, 6000);
        $bonus = fake()->randomElement([0, 100, 250]);
        $overtimePay = fake()->randomFloat(2, 0, 300);
        $reimbursement = fake()->randomElement([0, 40, 75.5]);
        $tax = round($basic * 0.1, 2);
        $overtimeMinutes = fake()->numberBetween(0, 900);
        $payPeriodMinutes = 22 * 8 * 60;

        return [
            'period_start' => $start->toDateString(),
            'period_end' => $start->copy()->endOfMonth()->toDateString(),
            'total_hours_minutes' => $payPeriodMinutes + $overtimeMinutes,
            'overtime_hours_minutes' => $overtimeMinutes,
            'pay_period_hours_minutes' => $payPeriodMinutes,
            'basic_salary' => $basic,
            'tax' => $tax,
            'reimbursement' => $reimbursement,
            'bonus' => $bonus,
            'overtime_pay' => $overtimePay,
            'total_salary' => round($basic + $bonus + $overtimePay + $reimbursement - $tax, 2),
            'currency' => 'USD',
            'paid_on' => $start->copy()->endOfMonth()->addDay()->setTime(10, 0)->toDateTimeString(),
        ];
    }

    /** A record for the calendar month containing $month. */
    public function forMonth(CarbonInterface $month): static
    {
        $start = $month->copy()->startOfMonth();

        return $this->state(fn () => [
            'period_start' => $start->toDateString(),
            'period_end' => $start->copy()->endOfMonth()->toDateString(),
            'paid_on' => $start->copy()->endOfMonth()->addDay()->setTime(10, 0)->toDateTimeString(),
        ]);
    }
}
