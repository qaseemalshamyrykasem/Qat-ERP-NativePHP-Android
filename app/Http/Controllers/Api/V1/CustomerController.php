<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\Customer;

class CustomerController extends BaseCrudController
{
    protected string $model = Customer::class;
    protected string $storeRequest = \App\Http\Requests\Api\V1\StoreCustomerRequest::class;
    protected string $updateRequest = \App\Http\Requests\Api\V1\StoreCustomerRequest::class;
    protected string $resource = \App\Http\Resources\CustomerResource::class;
    protected array $with = ['agent'];

    protected function applyFilters($q, $request): void
    {
        if ($request->filled('name'))  $q->where('name', 'like', '%' . $request->input('name') . '%');
        if ($request->filled('phone')) $q->where('phone', 'like', '%' . $request->input('phone') . '%');
        if ($request->filled('agent_id')) $q->where('agent_id', $request->input('agent_id'));
        if ($request->filled('status')) $q->where('status', $request->input('status'));

        $user = $request->user();
        if ($user->role === 'agent' && $user->agent_id) {
            $q->where('agent_id', $user->agent_id);
        }
    }
}
