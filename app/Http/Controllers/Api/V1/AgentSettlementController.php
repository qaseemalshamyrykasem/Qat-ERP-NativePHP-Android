<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Requests\Api\V1\StoreAgentSettlementRequest;
use App\Models\AgentSettlement;
use App\Services\AgentSettlementService;
use App\Http\Resources\AgentSettlementResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AgentSettlementController extends BaseCrudController
{
    protected string $model = AgentSettlement::class;
    protected string $storeRequest = StoreAgentSettlementRequest::class;
    protected string $updateRequest = StoreAgentSettlementRequest::class;
    protected string $resource = AgentSettlementResource::class;
    protected array $with = ['agent', 'creator'];

    public function __construct(protected AgentSettlementService $service) {}

    public function calculate(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'agent_id'         => 'required|integer|exists:agents,id',
            'settlement_date'  => 'required|date',
        ]);
        $calc = $this->service->calculate($validated['agent_id'], $validated['settlement_date']);
        return response()->json(['success' => true, 'calc' => $calc]);
    }

    public function save(StoreAgentSettlementRequest $request): JsonResponse
    {
        $data = $request->validated();
        $data['created_by'] = $request->user()->id;
        $result = $this->service->save($data);
        return response()->json($result, $result['success'] ? 201 : 400);
    }
}
