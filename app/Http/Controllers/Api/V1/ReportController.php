<?php

namespace App\Http\Controllers\Api\V1;

use App\Services\ReportService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class ReportController extends Controller
{
    public function __construct(protected ReportService $service) {}

    public function daily(Request $request): JsonResponse
    {
        $date = $request->input('date', now()->format('Y-m-d'));
        return response()->json(['success' => true, 'data' => $this->service->daily($date)]);
    }

    public function monthly(Request $request): JsonResponse
    {
        $month = $request->input('month', now()->format('Y-m'));
        return response()->json(['success' => true, 'data' => $this->service->monthly($month)]);
    }

    public function supplierStatement(Request $request, int $supplierId): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data'    => $this->service->supplierStatement($supplierId, $request->input('from'), $request->input('to')),
        ]);
    }

    public function agentStatement(Request $request, int $agentId): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data'    => $this->service->agentStatement($agentId, $request->input('from'), $request->input('to')),
        ]);
    }

    public function debts(): JsonResponse
    {
        return response()->json(['success' => true, 'data' => $this->service->debtsOverview()]);
    }
}
