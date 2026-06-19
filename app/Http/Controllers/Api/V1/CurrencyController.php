<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Requests\Api\V1\StoreCurrencyRequest;
use App\Models\Currency;
use App\Services\CurrencyService;
use App\Http\Resources\CurrencyResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CurrencyController extends BaseCrudController
{
    protected string $model = Currency::class;
    protected string $storeRequest = StoreCurrencyRequest::class;
    protected string $updateRequest = StoreCurrencyRequest::class;
    protected string $resource = CurrencyResource::class;

    public function __construct(protected CurrencyService $service) {}

    public function setDefault(int $id): JsonResponse
    {
        $this->service->setDefault($id);
        return response()->json(['success' => true, 'message' => 'تم تعيين العملة الافتراضية']);
    }
}
