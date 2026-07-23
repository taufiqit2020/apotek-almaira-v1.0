<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class PermissionMiddleware
{
    public function handle(Request $request, Closure $next, string ...$permissions): Response
    {
        if (! Auth::check()) {
            return redirect()->route('login')->with('toast_error', 'Silakan login terlebih dahulu.');
        }

        $user = Auth::user();
        $user->loadMissing('role');

        if (! $user->is_active) {
            Auth::logout();

            return redirect()->route('login')->with('toast_error', 'Akun Anda telah dinonaktifkan.');
        }

        if (empty($permissions)) {
            return $next($request);
        }

        if (! $user->role || ! $user->role->allowsAny($permissions)) {
            abort(403, 'Anda tidak memiliki akses ke halaman ini.');
        }

        return $next($request);
    }
}
