<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\StoreSaleRequest;
use App\Http\Resources\SaleResource;
use App\Services\SaleService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use App\Models\Sale;

class SaleController extends Controller
{
    public function __construct(protected SaleService $service) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        $user = $request->user();
        $q = Sale::with(['items', 'agent', 'customer', 'creator']);

        if ($user->role === 'agent' && $user->agent_id) {
            $q->where('agent_id', $user->agent_id);
        }

        if ($from = $request->input('from'))      $q->where('sale_date', '>=', $from);
        if ($to = $request->input('to'))          $q->where('sale_date', '<=', $to);
        if ($method = $request->input('payment_method')) $q->where('payment_method', $method);
        if ($agentId = $request->input('agent_id'))     $q->where('agent_id', $agentId);

        $perPage = (int) min(100, $request->integer('per_page', 25));
        return SaleResource::collection($q->orderByDesc('id')->paginate($perPage));
    }

    public function store(StoreSaleRequest $request): JsonResponse
    {
        $this->authorize('create', Sale::class);
        $data = $request->validated();
        $data['created_by'] = $request->user()->id;

        if ($request->user()->role === 'agent' && $request->user()->agent_id) {
            $data['agent_id'] = $request->user()->agent_id;
        }

        $result = $this->service->addSale($data);
        if (! $result['success']) {
            return response()->json($result, 400);
        }
        return response()->json($result, 201);
    }

    public function show(Request $request, int $id): JsonResponse
    {
        $sale = Sale::with(['items.product', 'agent', 'customer', 'creator'])->find($id);
        if (! $sale) return response()->json(['success' => false, 'message' => 'غير موجود'], 404);

        $this->authorize('view', $sale);

        if ($request->user()->role === 'agent' && $sale->agent_id !== $request->user()->agent_id) {
            return response()->json(['success' => false, 'message' => 'غير مصرح'], 403);
        }
        return response()->json(['success' => true, 'sale' => new SaleResource($sale)]);
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        $sale = Sale::find($id);
        if (! $sale) return response()->json(['success' => false, 'message' => 'غير موجود'], 404);

        $this->authorize('delete', $sale);

        $user = $request->user();
        $result = $this->service->deleteSale($id, $user->role === 'agent' ? $user->agent_id : null, $user->isAdmin());
        return response()->json($result, $result['success'] ? 200 : 400);
    }
}
