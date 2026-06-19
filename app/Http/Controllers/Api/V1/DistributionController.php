<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\StoreDistributionRequest;
use App\Http\Resources\DistributionResource;
use App\Services\DistributionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use App\Models\Distribution;

class DistributionController extends Controller
{
    public function __construct(protected DistributionService $service) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        $q = Distribution::with(['items', 'agent', 'creator']);
        if ($from = $request->input('from'))  $q->where('distribution_date', '>=', $from);
        if ($to = $request->input('to'))      $q->where('distribution_date', '<=', $to);
        if ($aid = $request->input('agent_id')) $q->where('agent_id', $aid);
        return DistributionResource::collection($q->orderByDesc('id')->paginate(min(100, $request->integer('per_page', 25))));
    }

    public function store(StoreDistributionRequest $request): JsonResponse
    {
        $this->authorize('create', Distribution::class);
        $data = $request->validated();
        $data['created_by'] = $request->user()->id;
        $result = $this->service->addDistribution($data);
        return response()->json($result, $result['success'] ? 201 : 400);
    }

    public function show(int $id): JsonResponse
    {
        $d = Distribution::with(['items.product', 'agent', 'creator'])->find($id);
        if (! $d) return response()->json(['success' => false, 'message' => 'غير موجود'], 404);
        $this->authorize('view', $d);
        return response()->json(['success' => true, 'distribution' => new DistributionResource($d)]);
    }

    public function destroy(int $id): JsonResponse
    {
        $d = Distribution::find($id);
        if (! $d) return response()->json(['success' => false, 'message' => 'غير موجود'], 404);
        $this->authorize('delete', $d);
        $result = $this->service->deleteDistribution($id);
        return response()->json($result, $result['success'] ? 200 : 400);
    }
}
