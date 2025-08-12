<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Http\Request;

class Authenticate extends Middleware
{
    /**
     * Get the path the user should be redirected to when they are not authenticated.
     */
    protected function redirectTo(Request $request): ?string
    {
        if ($request->expectsJson()) {
            return null;
        }

        // If this is an API request, return null to let the frontend handle the redirect
        if ($request->is('api/*')) {
            return null;
        }

        // For web routes, redirect to the home page or login page if it exists
        return '/login';
    }
}
