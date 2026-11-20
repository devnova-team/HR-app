<?php

namespace App\Services;

use App\Enums\ExpenseStatus;
use App\Models\Employee;
use App\Models\Expense;
use App\Repositories\Interfaces\ExpenseRepositoryInterface;
use App\Services\Interfaces\ExpenseServiceInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

class ExpenseService implements ExpenseServiceInterface
{
    public function __construct(
        protected ExpenseRepositoryInterface $repository
    ) {}

    public function listEmployeeExpenses(
        Employee $employee,
        array $filters = [],
        int $limit = 20
    ): LengthAwarePaginator {
        return $this->repository->getForEmployee($employee, $filters, $limit);
    }

    public function getExpenseDetails(Employee $employee, string $id): Expense
    {
        $expense = $this->repository->findForEmployee($employee, $id);

        if (! $expense) {
            throw new NotFoundHttpException('Expense record not found.');
        }

        return $expense;
    }

    public function submitExpense(Employee $employee, array $data): Expense
    {
        $documentUrl = $data['document_url'] ?? null;

        if (isset($data['document']) && $data['document'] instanceof UploadedFile) {
            $path = Storage::disk('public')->putFile('expenses', $data['document']);
            $documentUrl = Storage::disk('public')->url($path);
        }

        $expenseData = [
            'employee_id' => $employee->id,
            'expense_category_id' => $data['expense_category_id'] ?? $data['category_id'],
            'amount' => $data['amount'],
            'currency' => $data['currency'] ?? 'USD',
            'transaction_date' => $data['transaction_date'],
            'description' => $data['description'] ?? null,
            'document_url' => $documentUrl,
            'status' => ExpenseStatus::Review->value,
        ];

        return $this->repository->create($expenseData)->load('expenseCategory');
    }

    public function reviewExpense(
        Employee $reviewer,
        string $id,
        ExpenseStatus $status,
        ?string $rejectionReason = null
    ): Expense {
        $expense = $this->repository->findById($id);

        if (! $expense) {
            throw new NotFoundHttpException('Expense record not found.');
        }

        if ($expense->status !== ExpenseStatus::Review) {
            throw new UnprocessableEntityHttpException('Expense has already been processed.');
        }

        $updateData = [
            'status' => $status->value,
            'reviewed_by_id' => $reviewer->id,
            'reviewed_at' => now(),
            'rejection_reason' => $status === ExpenseStatus::Rejected ? $rejectionReason : null,
        ];

        return $this->repository->update($expense, $updateData);
    }

    public function listActiveCategories(): Collection
    {
        return $this->repository->getActiveCategories();
    }
}
