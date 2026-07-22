<?php
namespace App\Http\Middleware;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;
class SessionTimeout {
    public function handle(Request $request, Closure $next): Response {
        if (Auth::check()) {
            $timeoutMinutes = (int) config('apotek.session_timeout_minutes', 30);
            $lastActivity = session('last_activity_time', now()->timestamp);
            if (now()->timestamp - $lastActivity > ($timeoutMinutes * 60)) {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();
                if ($request->expectsJson()) return response()->json(['message' => 'Session expired'], 401);
                return redirect()->route('login')->with('toast_warning', "Sesi Anda telah habis karena tidak aktif selama {$timeoutMinutes} menit.");
            }
            session(['last_activity_time' => now()->timestamp]);
        }
        return $next($request);
    }
}
