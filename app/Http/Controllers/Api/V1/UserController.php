<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Requests\Api\V1\StoreUserRequest;
use App\Http\Requests\Api\V1\UpdateUserRequest;
use App\Models\User;
use App\Http\Resources\UserResource;

class UserController extends BaseCrudController
{
    protected string $model = User::class;
    protected string $storeRequest = StoreUserRequest::class;
    protected string $updateRequest = UpdateUserRequest::class;
    protected string $resource = UserResource::class;
    protected array $with = ['agent'];

    public function store($request): \Illuminate\Http\JsonResponse
    {
        $req = app($this->storeRequest);
        $data = $req->validated();
        if (! empty($data['password'])) {
            $data['password'] = bcrypt($data['password']);
        }
        $user = User::create($data);
        return response()->json([
            'success' => true,
            'message' => 'تم إنشاء المستخدم',
            'user'    => new UserResource($user->load($this->with)),
        ], 201);
    }

    public function update($request, int $id): \Illuminate\Http\JsonResponse
    {
        $user = User::find($id);
        if (! $user) return $this->notFound();
        $this->authorize('update', $user);

        $req = app($this->updateRequest);
        $data = $req->validated();
        if (! empty($data['password'])) {
            $data['password'] = bcrypt($data['password']);
        } else {
            unset($data['password']);
        }
        $user->update($data);
        return response()->json([
            'success' => true,
            'message' => 'تم التحديث',
            'user'    => new UserResource($user->fresh($this->with)),
        ]);
    }

    protected function applyFilters($q, $request): void
    {
        if ($request->filled('role'))   $q->where('role', $request->input('role'));
        if ($request->filled('search')) $q->where(function($q2) use ($request) {
            $q2->where('username', 'like', '%' . $request->input('search') . '%')
               ->orWhere('full_name', 'like', '%' . $request->input('search') . '%');
        });
        if ($request->filled('status')) $q->where('status', filter_var($request->input('status'), FILTER_VALIDATE_BOOLEAN));
    }
}
