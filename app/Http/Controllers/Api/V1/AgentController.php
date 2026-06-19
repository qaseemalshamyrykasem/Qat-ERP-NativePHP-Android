<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\Agent;

class AgentController extends BaseCrudController
{
    protected string $model = Agent::class;
    protected string $storeRequest = \App\Http\Requests\Api\V1\StoreAgentRequest::class;
    protected string $updateRequest = \App\Http\Requests\Api\V1\StoreAgentRequest::class;
    protected string $resource = \App\Http\Resources\AgentResource::class;

    protected function applyFilters($q, $request): void
    {
        if ($request->filled('name'))  $q->where('name', 'like', '%' . $request->input('name') . '%');
        if ($request->filled('status')) $q->where('status', $request->input('status'));
        if ($request->filled('area'))  $q->where('area', 'like', '%' . $request->input('area') . '%');
    }
}
