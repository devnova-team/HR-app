<?php

namespace App\Http\Controllers;

use App\Http\Resources\PayrollDetailResource;
use App\Http\Resources\PayrollItemResource;
use App\Models\PayrollRecord;
use App\Support\ApiPagination;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

/**
 * Read-only. Every query is scoped to the signed-in employee, so someone else's
 * record id returns 404 (never 403) and nothing is calculated here.
 */
class PayrollController extends Controller
{
    /** GET /me/payroll */
    public function index(Request $request): JsonResponse
    {
        $paginator = PayrollRecord::query()
            ->where('employee_id', $request->user()->id)
            ->orderByDesc('period_start')
            ->paginate(ApiPagination::limit($request));

        return ApiPagination::respond($paginator, PayrollItemResource::class);
    }

    /** GET /me/payroll/{id} */
    public function show(Request $request, string $id): JsonResponse
    {
        return response()->json([
            'data' => (new PayrollDetailResource($this->ownRecord($request, $id)))->resolve(),
        ]);
    }

    /** GET /me/payroll/{id}/pdf */
    public function pdf(Request $request, string $id): Response
    {
        $record = $this->ownRecord($request, $id);
        $employee = $request->user()->loadMissing(['level', 'jobTitle']);

        return Pdf::loadView('pdf.payroll', ['record' => $record, 'employee' => $employee])
            ->download('payroll-'.$record->period_start->format('Y-m').'.pdf');
    }

    private function ownRecord(Request $request, string $id): PayrollRecord
    {
        return PayrollRecord::query()
            ->where('employee_id', $request->user()->id)
            ->findOrFail($id);
    }
}
