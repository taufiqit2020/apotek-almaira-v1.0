<?php
namespace App\Http\Middleware;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;
class RoleMiddleware {
    public function handle(Request $request, Closure $next, string ...$roles): Response {
        if (!Auth::check()) {
            return redirect()->route('login')->with('toast_error', 'Silakan login terlebih dahulu.');
        }
        $user = Auth::user();
        if (!$user->is_active) {
            Auth::logout();
            return redirect()->route('login')->with('toast_error', 'Akun Anda telah dinonaktifkan.');
        }
        if (empty($roles)) return $next($request);
        $userRole = $user->role?->slug;
        if (!in_array($userRole, $roles)) abort(403, 'Anda tidak memiliki akses ke halaman ini.');
        return $next($request);
    }
}
