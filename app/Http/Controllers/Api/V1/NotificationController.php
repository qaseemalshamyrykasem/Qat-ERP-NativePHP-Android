<?php

namespace App\Http\Controllers\Api\V1;

use App\Services\NotificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class NotificationController extends Controller
{
    public function __construct(protected NotificationService $service) {}

    public function index(Request $request)
    {
        $perPage = min(100, $request->integer('per_page', 25));
        return \App\Models\AppNotification::where('user_id', $request->user()->id)
            ->orderByDesc('created_at')
            ->paginate($perPage);
    }

    public function unreadCount(Request $request): JsonResponse
    {
        return response()->json(['success' => true, 'count' => $this->service->unreadCount($request->user()->id)]);
    }

    public function markRead(int $id): JsonResponse
    {
        $this->service->markRead($id);
        return response()->json(['success' => true]);
    }

    public function markAllRead(Request $request): JsonResponse
    {
        $this->service->markAllRead($request->user()->id);
        return response()->json(['success' => true]);
    }
}
