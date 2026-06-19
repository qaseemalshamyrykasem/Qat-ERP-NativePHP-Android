<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Requests\Api\V1\CloseDailySessionRequest;
use App\Http\Requests\Api\V1\StoreDailySessionRequest;
use App\Models\DailySession;
use App\Services\DailySessionService;
use App\Http\Resources\DailySessionResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class DailySessionController extends Controller
{
    public function __construct(protected DailySessionService $service) {}

    public function index(Request $request)
    {
        $this->authorize('viewAny', DailySession::class);
        $q = DailySession::with(['opener', 'closer']);
        if ($request->filled('from')) $q->where('session_date', '>=', $request->input('from'));
        if ($request->filled('to'))   $q->where('session_date', '<=', $request->input('to'));
        $perPage = min(100, $request->integer('per_page', 25));
        return DailySessionResource::collection($q->orderByDesc('session_date')->paginate($perPage));
    }

    public function open(StoreDailySessionRequest $request): JsonResponse
    {
        $this->authorize('create', DailySession::class);
        $result = $this->service->open(
            (float) $request->input('opening_balance'),
            $request->input('session_date'),
            $request->user()->id,
        );
        return response()->json($result, $result['success'] ? 201 : 400);
    }

    public function close(CloseDailySessionRequest $request, int $id): JsonResponse
    {
        $this->authorize('update', DailySession::find($id) ?? DailySession::class);
        $result = $this->service->close(
            $id,
            (float) $request->input('actual_balance'),
            $request->input('notes'),
            $request->user()->id,
        );
        return response()->json($result, $result['success'] ? 200 : 400);
    }

    public function stats(Request $request): JsonResponse
    {
        $date = $request->input('date', now()->format('Y-m-d'));
        return response()->json(['success' => true, 'stats' => $this->service->computeStats($date)]);
    }
}
