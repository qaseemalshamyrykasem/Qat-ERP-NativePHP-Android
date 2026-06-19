<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Scope queries to the agent of the authenticated user.
 * Used for agents who should only see their own customers / sales / distributions.
 */
class ScopeByAgent
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        if ($user && $user->role === 'agent' && $user->agent_id) {
            app()->instance('agent.scoped_id', $user->agent_id);
        }
        return $next($request);
    }
}
