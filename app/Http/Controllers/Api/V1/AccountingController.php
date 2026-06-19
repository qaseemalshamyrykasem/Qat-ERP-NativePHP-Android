<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Requests\Api\V1\StoreChartOfAccountRequest;
use App\Http\Requests\Api\V1\StoreJournalEntryRequest;
use App\Models\ChartOfAccount;
use App\Models\JournalEntry;
use App\Services\AccountingService;
use App\Http\Resources\ChartOfAccountResource;
use App\Http\Resources\JournalEntryResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AccountingController extends BaseCrudController
{
    protected string $model = ChartOfAccount::class;
    protected string $storeRequest = StoreChartOfAccountRequest::class;
    protected string $updateRequest = StoreChartOfAccountRequest::class;
    protected string $resource = ChartOfAccountResource::class;
    protected array $with = ['parent'];

    public function __construct(protected AccountingService $service) {}

    // ===== Journal Entries =====

    public function journalIndex(Request $request)
    {
        $this->authorize('viewAny', JournalEntry::class);
        $q = JournalEntry::with(['lines.account', 'creator']);
        if ($request->filled('from')) $q->where('entry_date', '>=', $request->input('from'));
        if ($request->filled('to'))   $q->where('entry_date', '<=', $request->input('to'));
        if ($request->filled('status')) $q->where('status', $request->input('status'));
        return JournalEntryResource::collection($q->orderByDesc('id')->paginate(min(100, $request->integer('per_page', 25))));
    }

    public function journalStore(StoreJournalEntryRequest $request): JsonResponse
    {
        $this->authorize('create', JournalEntry::class);
        try {
            $entry = $this->service->postJournalEntry([
                'entry_date'     => $request->input('entry_date'),
                'description'    => $request->input('description'),
                'reference_type' => $request->input('reference_type'),
                'reference_id'   => $request->input('reference_id'),
                'created_by'     => $request->user()->id,
            ], $request->input('lines'));
            return response()->json(['success' => true, 'message' => 'تم حفظ القيد', 'entry' => new JournalEntryResource($entry->load('lines.account'))], 201);
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 400);
        }
    }

    public function journalShow(int $id): JsonResponse
    {
        $entry = JournalEntry::with(['lines.account', 'creator'])->find($id);
        if (! $entry) return $this->notFound();
        $this->authorize('view', $entry);
        return response()->json(['success' => true, 'entry' => new JournalEntryResource($entry)]);
    }

    public function journalDestroy(int $id): JsonResponse
    {
        $entry = JournalEntry::find($id);
        if (! $entry) return $this->notFound();
        $this->authorize('delete', $entry);
        $entry = $this->service->voidEntry($entry, 'حذف يدوي');
        return response()->json(['success' => true, 'message' => 'تم إلغاء القيد', 'entry' => new JournalEntryResource($entry)]);
    }

    // ===== Reports =====

    public function trialBalance(Request $request): JsonResponse
    {
        $asOf = $request->input('as_of') ? new \DateTime($request->input('as_of')) : null;
        return response()->json(['success' => true, 'data' => $this->service->trialBalance($asOf)]);
    }

    public function incomeStatement(Request $request): JsonResponse
    {
        $from = $request->input('from') ? new \DateTime($request->input('from')) : null;
        $to   = $request->input('to')   ? new \DateTime($request->input('to'))   : null;
        return response()->json(['success' => true, 'data' => $this->service->incomeStatement($from, $to)]);
    }

    public function balanceSheet(Request $request): JsonResponse
    {
        $asOf = $request->input('as_of') ? new \DateTime($request->input('as_of')) : null;
        return response()->json(['success' => true, 'data' => $this->service->balanceSheet($asOf)]);
    }

    public function generalLedger(Request $request): JsonResponse
    {
        $data = $this->service->generalLedger(
            $request->input('from'),
            $request->input('to'),
            $request->integer('account_id') ?: null,
        );
        return response()->json(['success' => true, 'rows' => $data]);
    }
}
