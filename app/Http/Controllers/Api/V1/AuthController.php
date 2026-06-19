<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\LoginRequest;
use App\Http\Resources\UserResource;
use App\Services\AuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function __construct(protected AuthService $auth) {}

    /**
     * POST /api/v1/auth/login
     * Stateful token via Sanctum.
     */
    public function login(LoginRequest $request): JsonResponse
    {
        $result = $this->auth->login($request->input('username'), $request->input('password'));

        if (! $result['success']) {
            return response()->json($result, 401);
        }

        /** @var \App\Models\User $user */
        $user = $result['user'];
        $tokenResult = $this->auth->issueApiToken($user, $request->header('User-Agent', 'api-client'));

        return response()->json([
            'success' => true,
            'message' => 'تم تسجيل الدخول',
            'token'   => $tokenResult['token'],
            'user'    => new UserResource($user),
        ]);
    }

    /**
     * POST /api/v1/auth/logout
     */
    public function logout(Request $request): JsonResponse
    {
        $request->user()?->currentAccessToken()?->delete();
        return response()->json(['success' => true, 'message' => 'تم تسجيل الخروج']);
    }

    /**
     * GET /api/v1/auth/me
     */
    public function me(Request $request): JsonResponse
    {
        return response()->json([
            'success' => true,
            'user'    => new UserResource($request->user()),
        ]);
    }
}
