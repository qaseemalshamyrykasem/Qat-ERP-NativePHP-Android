<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\Expense;
use App\Services\ExpenseService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ExpenseController extends BaseCrudController
{
    protected string $model = Expense::class;
    protected string $storeRequest = \App\Http\Requests\Api\V1\StoreExpenseRequest::class;
    protected string $updateRequest = \App\Http\Requests\Api\V1\StoreExpenseRequest::class;
    protected string $resource = \App\Http\Resources\ExpenseResource::class;
    protected array $with = ['creator'];

    public function __construct(protected ExpenseService $service) {}

    public function store(Request $request): JsonResponse
    {
        $this->authorize('create', Expense::class);
        $data = $this->validateStore($request);
        $data['created_by'] = $request->user()->id;
        $result = $this->service->create($data);
        return response()->json($result, $result['success'] ? 201 : 400);
    }

    public function destroy(int $id): JsonResponse
    {
        $result = $this->service->delete($id);
        return response()->json($result, $result['success'] ? 200 : 400);
    }

    protected function applyFilters($q, $request): void
    {
        if ($request->filled('from')) $q->where('expense_date', '>=', $request->input('from'));
        if ($request->filled('to'))   $q->where('expense_date', '<=', $request->input('to'));
        if ($request->filled('category')) $q->where('category', $request->input('category'));
    }

    private function validateStore(Request $request): array
    {
        return $request->validate((new $this->storeRequest)->rules());
    }
}
