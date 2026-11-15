<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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
        $user = $request->user();

        if (!$user) {
            if ($request->is('api/*')) {
                return response()->json(['message' => 'Unauthorized'], 401);
            }

            return redirect()->to('/panel/login');
        }

        if ($user->role !== $role) {
            if ($request->is('api/*')) {
                return response()->json(['message' => 'Forbidden'], 403);
            }

            abort(403, 'Unauthorized access');
        }

        if (!$user->isActive()) {
            if ($request->is('api/*')) {
                return response()->json([
                    'message' => 'Akun Anda tidak aktif atau telah dinonaktifkan.',
                ], 403);
            }

            Auth::logout();
            if ($request->hasSession()) {
                $request->session()->invalidate();
                $request->session()->regenerateToken();
            }

            return redirect()->to('/panel/login')->withErrors([
                'email' => 'Akun Anda tidak aktif atau telah dinonaktifkan.',
            ]);
        }

        return $next($request);
    }
}
