<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\StorePurchaseRequest;
use App\Http\Resources\PurchaseResource;
use App\Services\PurchaseService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use App\Models\Purchase;

class PurchaseController extends Controller
{
    public function __construct(protected PurchaseService $service) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        $q = Purchase::with(['items', 'supplier', 'creator']);
        if ($from = $request->input('from'))  $q->where('purchase_date', '>=', $from);
        if ($to = $request->input('to'))      $q->where('purchase_date', '<=', $to);
        if ($sid = $request->input('supplier_id')) $q->where('supplier_id', $sid);
        $perPage = (int) min(100, $request->integer('per_page', 25));
        return PurchaseResource::collection($q->orderByDesc('id')->paginate($perPage));
    }

    public function store(StorePurchaseRequest $request): JsonResponse
    {
        $this->authorize('create', Purchase::class);
        $data = $request->validated();
        $data['created_by'] = $request->user()->id;
        $result = $this->service->addPurchase($data);
        return response()->json($result, $result['success'] ? 201 : 400);
    }

    public function show(int $id): JsonResponse
    {
        $purchase = Purchase::with(['items.product', 'supplier', 'creator'])->find($id);
        if (! $purchase) return response()->json(['success' => false, 'message' => 'غير موجود'], 404);
        $this->authorize('view', $purchase);
        return response()->json(['success' => true, 'purchase' => new PurchaseResource($purchase)]);
    }

    public function destroy(int $id): JsonResponse
    {
        $purchase = Purchase::find($id);
        if (! $purchase) return response()->json(['success' => false, 'message' => 'غير موجود'], 404);
        $this->authorize('delete', $purchase);
        $result = $this->service->deletePurchase($id);
        return response()->json($result, $result['success'] ? 200 : 400);
    }

    public function settleSupplier(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'supplier_id'   => 'required|integer|exists:suppliers,id',
            'amount'        => 'required|numeric|min:0.01',
            'method'        => 'nullable|in:cash,transfer,other',
            'wallet_type'   => 'nullable|string|max:50',
            'date'          => 'nullable|date',
        ]);
        $result = $this->service->settleSupplier(
            $validated['supplier_id'],
            $validated['amount'],
            $validated['method'] ?? 'cash',
            $validated['wallet_type'] ?? null,
            $validated['date'] ?? null,
        );
        return response()->json($result, $result['success'] ? 200 : 400);
    }
}
