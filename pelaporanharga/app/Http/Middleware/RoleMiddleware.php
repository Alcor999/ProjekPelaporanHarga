<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string $role): Response
    {
        if (!$request->user()) {
            // For API requests, return JSON
            if ($request->is('api/*')) {
                return response()->json(['message' => 'Unauthorized'], 401);
            }
            // For web requests, redirect to login
            return redirect()->to('/panel/login');
        }

        if ($request->user()->role !== $role) {
            // For API requests, return JSON
            if ($request->is('api/*')) {
                return response()->json(['message' => 'Forbidden'], 403);
            }
            // For web requests, redirect with error
            abort(403, 'Unauthorized access');
        }

        return $next($request);
    }
}
