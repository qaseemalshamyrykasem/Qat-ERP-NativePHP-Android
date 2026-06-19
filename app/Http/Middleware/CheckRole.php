<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Check that the authenticated user has one of the allowed roles.
 * Usage: ->middleware('role:admin,manager')
 */
class CheckRole
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();
        if (! $user) {
            return $this->unauthorized($request, 'يجب تسجيل الدخول');
        }

        if (! in_array($user->role, $roles, true)) {
            return $this->unauthorized($request, 'ليس لديك صلاحية الوصول لهذا القسم');
        }

        return $next($request);
    }

    private function unauthorized(Request $request, string $message): Response
    {
        if ($request->expectsJson()) {
            return response()->json(['success' => false, 'message' => $message], 403);
        }
        return redirect()->back()->with('error', $message);
    }
}
