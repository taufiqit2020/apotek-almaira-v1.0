<?php
namespace App\Http\Controllers\Auth;
use App\Http\Controllers\Controller;
use App\Models\Partner;
use App\Models\Setting;
use App\Services\ActivityLogService;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;
class LoginController extends Controller {
    public function showLoginForm() {
        if (Auth::check()) return redirect()->route('dashboard');

        $phone = Setting::get('apotek_phone', '0851-6665-7070');
        $waNumber = preg_replace('/\D/', '', $phone);
        if (str_starts_with($waNumber, '0')) {
            $waNumber = '62' . substr($waNumber, 1);
        }

        return view('auth.login', [
            'apotekName'          => Setting::get('apotek_name', 'Apotek Almaira'),
            'apotekPhone'         => $phone,
            'waNumber'            => $waNumber,
            'pendingPartnerCount' => self::pendingSelfRegistrationCount(),
        ]);
    }

    public function pendingPartners()
    {
        return response()->json([
            'success'       => true,
            'pending_count' => self::pendingSelfRegistrationCount(),
        ]);
    }

    private static function pendingSelfRegistrationCount(): int
    {
        return Partner::pending()
            ->where('registration_source', Partner::SOURCE_SELF)
            ->count();
    }
    public function login(Request $request): RedirectResponse {
        $request->validate(['login' => 'required|string', 'password' => 'required|string'],
            ['login.required' => 'Username atau email wajib diisi.', 'password.required' => 'Password wajib diisi.']);
        $ip = $request->ip();
        $maxAttempts = (int) config('apotek.login_max_attempts', 5);
        $lockoutMins = (int) config('apotek.login_lockout_minutes', 15);
        $loginAttempt = DB::table('login_attempts')->where('ip_address', $ip)->first();
        if ($loginAttempt && $loginAttempt->locked_until && now()->lt($loginAttempt->locked_until)) {
            $seconds = now()->diffInSeconds($loginAttempt->locked_until, false);
            $minutes = ceil($seconds / 60);
            return back()->withErrors(['login' => "Terlalu banyak percobaan gagal. Silakan coba lagi nanti."])
                ->withInput($request->only('login'))
                ->with('lockout_seconds', $seconds);
        }
        $user = \App\Models\User::with('role')->where('email', $request->login)->orWhere('username', $request->login)->first();
        if (!$user || !Hash::check($request->password, $user->password)) {
            $existing = DB::table('login_attempts')->where('ip_address', $ip)->first();
            if ($existing) {
                DB::table('login_attempts')->where('ip_address', $ip)->update([
                    'email' => $request->login,
                    'attempts' => $existing->attempts + 1,
                    'last_attempt' => now(),
                ]);
            } else {
                DB::table('login_attempts')->insert([
                    'ip_address' => $ip,
                    'email' => $request->login,
                    'attempts' => 1,
                    'last_attempt' => now(),
                ]);
            }
            $attempt = DB::table('login_attempts')->where('ip_address', $ip)->value('attempts');
            if ($attempt >= $maxAttempts) {
                $lockedUntil = now()->addMinutes($lockoutMins);
                DB::table('login_attempts')->where('ip_address', $ip)->update(['locked_until' => $lockedUntil]);
                $seconds = now()->diffInSeconds($lockedUntil, false);
                ActivityLogService::log('LOCKOUT', 'Auth', "IP {$ip} terkunci selama {$lockoutMins} menit karena terlalu banyak percobaan login gagal.");
                return back()->withErrors(['login' => "Terlalu banyak percobaan gagal. Akun dikunci selama {$lockoutMins} menit."])
                    ->withInput($request->only('login'))
                    ->with('lockout_seconds', $seconds);
            }
            ActivityLogService::log('LOGIN_FAILED', 'Auth', "Percobaan login gagal dengan username/email: {$request->login}");
            return back()->withErrors(['login' => 'Username atau password salah! Sisa percobaan: ' . ($maxAttempts - $attempt)])->withInput($request->only('login'));
        }
        if (!$user->is_active) {
            ActivityLogService::log('LOGIN_FAILED', 'Auth', "Percobaan login dengan akun dinonaktifkan: {$user->username}");
            $user->loadMissing('partner');
            if ($user->isMitra() && $user->partner?->isPending()) {
                return back()->withErrors([
                    'login' => 'Pendaftaran mitra Anda masih menunggu approval admin. Gunakan login mitra di /mitra/login.',
                ])->withInput($request->only('login'));
            }
            return back()->withErrors(['login' => 'Akun Anda telah dinonaktifkan. Hubungi administrator.'])->withInput($request->only('login'));
        }
        Auth::login($user, $request->boolean('remember'));
        $request->session()->regenerate();
        DB::table('login_attempts')->where('ip_address', $ip)->delete();
        $user->update(['last_login' => now()]);
        ActivityLogService::login($user->name);
        session()->flash('toast_success', 'Selamat datang, ' . $user->name . '! 👋');

        // Mitra katalog: jangan masuk panel staff — arahkan ke portal mitra
        if ($user->isMitra()) {
            return redirect()->route('mitra.account');
        }

        return redirect()->route('dashboard');
    }
    public function logout(Request $request): RedirectResponse {
        $userName = Auth::user()?->name ?? 'Unknown';
        ActivityLogService::logout($userName);
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('login')->with('toast_info', 'Anda telah logout. Sampai jumpa!');
    }
}
