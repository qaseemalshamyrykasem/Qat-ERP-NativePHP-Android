<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Requests\Api\V1\StoreAccountTransferRequest;
use App\Models\AccountTransfer;
use App\Services\TransferService;
use App\Http\Resources\AccountTransferResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TransferController extends BaseCrudController
{
    protected string $model = AccountTransfer::class;
    protected string $storeRequest = StoreAccountTransferRequest::class;
    protected string $updateRequest = StoreAccountTransferRequest::class;
    protected string $resource = AccountTransferResource::class;
    protected array $with = ['fromAccount', 'toAccount', 'fromCurrency', 'toCurrency', 'creator'];

    public function __construct(protected TransferService $service) {}

    public function store(Request $request): JsonResponse
    {
        $this->authorize('create', AccountTransfer::class);
        $req = app($this->storeRequest);
        $data = $req->validated();
        $data['created_by'] = $request->user()->id;
        $result = $this->service->create($data);
        return response()->json($result, $result['success'] ? 201 : 400);
    }

    public function destroy(int $id): JsonResponse
    {
        $result = $this->service->delete($id);
        return response()->json($result, $result['success'] ? 200 : 400);
    }
}
