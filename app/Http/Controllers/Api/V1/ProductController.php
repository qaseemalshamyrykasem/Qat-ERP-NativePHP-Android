<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\Product;

class ProductController extends BaseCrudController
{
    protected string $model = Product::class;
    protected string $storeRequest = \App\Http\Requests\Api\V1\StoreProductRequest::class;
    protected string $updateRequest = \App\Http\Requests\Api\V1\StoreProductRequest::class;
    protected string $resource = \App\Http\Resources\ProductResource::class;
    protected array $with = ['supplier'];

    protected function applyFilters($q, $request): void
    {
        if ($request->filled('name'))      $q->where('name', 'like', '%' . $request->input('name') . '%');
        if ($request->filled('type'))      $q->where('type', $request->input('type'));
        if ($request->filled('supplier_id')) $q->where('supplier_id', $request->input('supplier_id'));
        if ($request->filled('status'))    $q->where('status', filter_var($request->input('status'), FILTER_VALIDATE_BOOLEAN));
        if ($request->boolean('low_stock'))$q->whereColumn('quantity', '<=', 'min_quantity');
    }
}
