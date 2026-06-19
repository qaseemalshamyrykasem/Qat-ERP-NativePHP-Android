<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Requests\Api\V1\StoreDebtPaymentRequest;
use App\Models\Debt;
use App\Services\DebtService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Http\Resources\DebtResource;

class DebtController extends BaseCrudController
{
    protected string $model = Debt::class;
    protected string $storeRequest = \App\Http\Requests\Api\V1\StoreDebtPaymentRequest::class;
    protected string $updateRequest = \App\Http\Requests\Api\V1\StoreDebtPaymentRequest::class;
    protected string $resource = DebtResource::class;
    protected array $with = ['customer', 'agent', 'payments'];

    public function __construct(protected DebtService $service) {}

    protected function applyFilters($q, $request): void
    {
        if ($request->filled('customer_id')) $q->where('customer_id', $request->input('customer_id'));
        if ($request->filled('agent_id'))    $q->where('agent_id', $request->input('agent_id'));
        if ($request->filled('status'))      $q->where('status', $request->input('status'));
    }

    public function pay(StoreDebtPaymentRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $result = $this->service->registerPayment(
            $validated['debt_id'],
            $validated['amount'],
            $validated['payment_method'] ?? 'cash',
            $validated['wallet_type'] ?? null,
            $validated['payment_date'] ?? null,
            $request->user()->id,
        );
        return response()->json($result, $result['success'] ? 200 : 400);
    }

    public function history(int $id): JsonResponse
    {
        $result = $this->service->getPaymentHistory($id);
        return response()->json($result);
    }

    public function markOverdue(): JsonResponse
    {
        $count = $this->service->markOverdue();
        return response()->json(['success' => true, 'marked_overdue' => $count]);
    }
}
