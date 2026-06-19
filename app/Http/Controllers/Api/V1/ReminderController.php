<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Requests\Api\V1\StoreReminderRequest;
use App\Models\Reminder;
use App\Http\Resources\ReminderResource;

class ReminderController extends BaseCrudController
{
    protected string $model = Reminder::class;
    protected string $storeRequest = StoreReminderRequest::class;
    protected string $updateRequest = StoreReminderRequest::class;
    protected string $resource = ReminderResource::class;
    protected array $with = ['creator'];

    protected function applyFilters($q, $request): void
    {
        if ($request->filled('status')) $q->where('status', $request->input('status'));
        if ($request->filled('entity_type')) $q->where('entity_type', $request->input('entity_type'));
        if ($request->boolean('today')) $q->whereDate('due_date', today());
    }

    public function dismiss(int $id): \Illuminate\Http\JsonResponse
    {
        $r = Reminder::find($id);
        if (! $r) return $this->notFound();
        $r->update(['status' => 'dismissed']);
        return response()->json(['success' => true, 'message' => 'تم تجاهل التذكير']);
    }
}
