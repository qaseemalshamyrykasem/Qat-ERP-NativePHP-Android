<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Requests\Api\V1\StoreReceiptVoucherRequest;
use App\Http\Requests\Api\V1\StorePaymentVoucherRequest;
use App\Models\ReceiptVoucher;
use App\Models\PaymentVoucher;
use App\Services\VoucherService;
use App\Http\Resources\ReceiptVoucherResource;
use App\Http\Resources\PaymentVoucherResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class VoucherController extends BaseCrudController
{
    protected string $model = ReceiptVoucher::class;
    protected string $storeRequest = StoreReceiptVoucherRequest::class;
    protected string $updateRequest = StoreReceiptVoucherRequest::class;
    protected string $resource = ReceiptVoucherResource::class;
    protected array $with = ['account', 'customer', 'creator'];

    public function __construct(protected VoucherService $service) {}

    // ===== Receipt Vouchers =====

    public function receiptsIndex(Request $request)
    {
        $this->authorize('viewAny', ReceiptVoucher::class);
        $q = ReceiptVoucher::with($this->with);
        if ($request->filled('from')) $q->where('voucher_date', '>=', $request->input('from'));
        if ($request->filled('to'))   $q->where('voucher_date', '<=', $request->input('to'));
        return ReceiptVoucherResource::collection($q->orderByDesc('id')->paginate(min(100, $request->integer('per_page', 25))));
    }

    public function receiptsStore(StoreReceiptVoucherRequest $request): JsonResponse
    {
        $this->authorize('create', ReceiptVoucher::class);
        $data = $request->validated();
        $data['created_by'] = $request->user()->id;
        $result = $this->service->addReceipt($data);
        return response()->json($result, $result['success'] ? 201 : 400);
    }

    public function receiptsDestroy(int $id): JsonResponse
    {
        $result = $this->service->deleteReceipt($id);
        return response()->json($result, $result['success'] ? 200 : 400);
    }

    // ===== Payment Vouchers =====

    public function paymentsIndex(Request $request)
    {
        $this->authorize('viewAny', PaymentVoucher::class);
        $q = PaymentVoucher::with(['account', 'supplier', 'creator']);
        if ($request->filled('from')) $q->where('voucher_date', '>=', $request->input('from'));
        if ($request->filled('to'))   $q->where('voucher_date', '<=', $request->input('to'));
        return PaymentVoucherResource::collection($q->orderByDesc('id')->paginate(min(100, $request->integer('per_page', 25))));
    }

    public function paymentsStore(StorePaymentVoucherRequest $request): JsonResponse
    {
        $this->authorize('create', PaymentVoucher::class);
        $data = $request->validated();
        $data['created_by'] = $request->user()->id;
        $result = $this->service->addPayment($data);
        return response()->json($result, $result['success'] ? 201 : 400);
    }

    public function paymentsDestroy(int $id): JsonResponse
    {
        $result = $this->service->deletePayment($id);
        return response()->json($result, $result['success'] ? 200 : 400);
    }
}
