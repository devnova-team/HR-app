<?php

namespace App\Http\Controllers\Api;

use App\Enums\ExpenseStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Expenses\ExpenseIndexRequest;
use App\Http\Requests\Expenses\ReviewExpenseRequest;
use App\Http\Requests\Expenses\StoreExpenseRequest;
use App\Http\Resources\ExpenseCategoryResource;
use App\Http\Resources\ExpenseResource;
use App\Services\Interfaces\ExpenseServiceInterface;
use App\Support\ApiPagination;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ExpenseController extends Controller
{
    public function __construct(
        protected ExpenseServiceInterface $service
    ) {}

    public function index(ExpenseIndexRequest $request): JsonResponse
    {
        $employee = $request->user();
        $limit = (int) $request->validated('limit', 20);

        $expenses = $this->service->listEmployeeExpenses(
            $employee,
            $request->validated(),
            $limit
        );

        return ApiPagination::respond($expenses, ExpenseResource::class);
    }

    public function categories(): JsonResponse
    {
        $categories = $this->service->listActiveCategories();

        return ApiResponse::success(
            ExpenseCategoryResource::collection($categories)
        );
    }

    public function show(Request $request, string $id): JsonResponse
    {
        $expense = $this->service->getExpenseDetails($request->user(), $id);

        return ApiResponse::success(new ExpenseResource($expense));
    }

    public function store(StoreExpenseRequest $request): JsonResponse
    {
        $expense = $this->service->submitExpense(
            $request->user(),
            $request->validated() + ['document' => $request->file('document')]
        );

        return ApiResponse::created(new ExpenseResource($expense));
    }

    public function review(ReviewExpenseRequest $request, string $id): JsonResponse
    {
        $status = ExpenseStatus::from($request->validated('status'));

        $expense = $this->service->reviewExpense(
            $request->user(),
            $id,
            $status,
            $request->validated('rejection_reason')
        );

        return ApiResponse::success(new ExpenseResource($expense));
    }
}
