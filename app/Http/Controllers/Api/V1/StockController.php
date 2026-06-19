<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\StockService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class StockController extends Controller
{
    public function __construct(protected StockService $service) {}

    public function adjust(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'product_id'    => 'required|integer|exists:products,id',
            'new_quantity'  => 'required|numeric|min:0',
            'reason'        => 'nullable|string',
        ]);
        $result = $this->service->adjustStock($validated['product_id'], (float) $validated['new_quantity'], $validated['reason'] ?? null);
        return response()->json($result, $result['success'] ? 200 : 400);
    }

    public function restock(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'product_id' => 'required|integer|exists:products,id',
            'quantity'   => 'required|numeric|min:0.01',
            'unit_cost'  => 'nullable|numeric|min:0',
            'reason'     => 'nullable|string',
        ]);
        $result = $this->service->restock($validated['product_id'], (float) $validated['quantity'], $validated['unit_cost'] ?? null, $validated['reason'] ?? null);
        return response()->json($result, $result['success'] ? 200 : 400);
    }

    public function lowStock(): JsonResponse
    {
        return response()->json($this->service->lowStockReport());
    }

    public function agentStock(int $agentId): JsonResponse
    {
        return response()->json($this->service->agentStockSummary($agentId));
    }
}
