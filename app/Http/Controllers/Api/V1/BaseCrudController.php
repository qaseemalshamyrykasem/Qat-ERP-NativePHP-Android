<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

/**
 * Generic CRUD controller scaffold.
 * Subclasses define $model, $storeRequest, $updateRequest, $resource.
 */
abstract class BaseCrudController extends Controller
{
    protected string $model;          // Eloquent model class
    protected string $storeRequest;   // FormRequest for store
    protected string $updateRequest;  // FormRequest for update
    protected string $resource;       // API Resource class
    protected array $with = [];       // eager-load relations

    public function index(Request $request)
    {
        $this->authorize('viewAny', $this->model);

        $q = $this->model::query()->with($this->with);
        $this->applyFilters($q, $request);

        $perPage = (int) min(100, $request->integer('per_page', 25));
        $paginator = $q->orderByDesc('id')->paginate($perPage);
        return $this->resource::collection($paginator);
    }

    public function store(Request $request)
    {
        $this->authorize('create', $this->model);
        $req = app($this->storeRequest);
        $data = $req->validated();
        if (auth()->check() && in_array('created_by', $this->fillable())) {
            $data['created_by'] = $request->user()->id;
        }
        $record = $this->model::create($data);
        return response()->json([
            'success' => true,
            'message' => 'تم الإنشاء بنجاح',
            'record'  => new $this->resource($record->load($this->with)),
        ], 201);
    }

    public function show(int $id)
    {
        $record = $this->model::with($this->with)->find($id);
        if (! $record) return $this->notFound();
        $this->authorize('view', $record);
        return response()->json(['success' => true, 'record' => new $this->resource($record)]);
    }

    public function update(Request $request, int $id)
    {
        $record = $this->model::find($id);
        if (! $record) return $this->notFound();
        $this->authorize('update', $record);

        $req = app($this->updateRequest ?: $this->storeRequest);
        $data = $req->validated();
        if (isset($data['password']) && $data['password'] === '') {
            unset($data['password']);
        }
        $record->update($data);
        return response()->json([
            'success' => true,
            'message' => 'تم التحديث بنجاح',
            'record'  => new $this->resource($record->fresh($this->with)),
        ]);
    }

    public function destroy(int $id)
    {
        $record = $this->model::find($id);
        if (! $record) return $this->notFound();
        $this->authorize('delete', $record);
        $record->delete();
        return response()->json(['success' => true, 'message' => 'تم الحذف']);
    }

    protected function applyFilters($q, Request $request): void
    {
        // Override in subclass to add domain-specific filters
    }

    protected function fillable(): array
    {
        return (new $this->model)->getFillable();
    }

    protected function notFound()
    {
        return response()->json(['success' => false, 'message' => 'السجل غير موجود'], 404);
    }
}
