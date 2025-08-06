<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class RoleMiddleware
{
    public function handle(Request $request, Closure $next, ...$roles)
    {
        if (!$request->user()) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }

        $userType = $request->user()->user_type;
        
        if (!in_array($userType, $roles)) {
            return response()->json(['success' => false, 'message' => 'Insufficient permissions'], 403);
        }

        return $next($request);
    }
}