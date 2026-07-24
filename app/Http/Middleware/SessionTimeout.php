<?php
namespace App\Http\Middleware;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SessionTimeout {
    public function handle(Request $request, Closure $next): Response {
        // Auto-logout dinonaktifkan: user akan selalu tetap login kecuali menekan tombol Logout sendiri
        return $next($request);
    }
}
