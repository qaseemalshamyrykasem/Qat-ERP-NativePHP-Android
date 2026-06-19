<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\Supplier;

class SupplierController extends BaseCrudController
{
    protected string $model = Supplier::class;
    protected string $storeRequest = \App\Http\Requests\Api\V1\StoreSupplierRequest::class;
    protected string $updateRequest = \App\Http\Requests\Api\V1\StoreSupplierRequest::class;
    protected string $resource = \App\Http\Resources\SupplierResource::class;

    protected function applyFilters($q, $request): void
    {
        if ($request->filled('name'))  $q->where('name', 'like', '%' . $request->input('name') . '%');
        if ($request->filled('phone')) $q->where('phone', 'like', '%' . $request->input('phone') . '%');
    }
}
