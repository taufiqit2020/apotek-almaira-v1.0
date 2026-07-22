<?php

namespace App\Http\Controllers;

use App\Models\Partner;
use App\Models\Role;
use App\Models\Setting;
use App\Models\User;
use App\Services\ActivityLogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class PartnerPortalController extends Controller
{
    private function apotekData(): array
    {
        return [
            'apotekName'    => Setting::get('apotek_name', 'Apotek Almaira'),
            'apotekAddress' => Setting::get('apotek_address', 'Jl. Panglima Batur No. 16, Kel. Komet, Kec. Banjarbaru Utara, Kota Banjarbaru, Kalsel 70714'),
            'apotekPhone'   => Setting::get('apotek_phone', '0851-6665-7070'),
        ];
    }

    public function registerForm()
    {
        if (Auth::check() && Auth::user()->isMitra()) {
            return redirect()->route('mitra.account');
        }

        return view('partners.portal.register', array_merge($this->apotekData(), [
            'types' => Partner::typeOptions(),
        ]));
    }

    public function register(Request $request)
    {
        $request->validate([
            'name'     => 'required|string|max:200',
            'type'     => ['required', Rule::in(array_keys(Partner::typeOptions()))],
            'npwp'     => 'nullable|string|max:40',
            'nib'      => 'nullable|string|max:50',
            'address'  => 'nullable|string',
            'city'     => 'nullable|string|max:100',
            'pic_name' => 'required|string|max:150',
            'phone'    => [
                'required', 'string', 'max:30',
                Rule::unique('partners', 'phone')->whereNull('deleted_at'),
            ],
            'email'    => 'required|email|max:150|unique:users,email',
            'username' => 'required|string|max:50|unique:users,username',
            'password' => 'required|string|min:6|confirmed',
        ], [
            'name.required'     => 'Nama usaha / institusi wajib diisi.',
            'type.required'     => 'Tipe mitra wajib dipilih.',
            'pic_name.required' => 'Nama PIC wajib diisi.',
            'phone.required'    => 'Nomor telepon wajib diisi.',
            'phone.unique'      => 'Nomor telepon sudah terdaftar sebagai mitra.',
            'email.required'    => 'Email wajib diisi.',
            'email.unique'      => 'Email sudah terdaftar.',
            'username.required' => 'Username wajib diisi.',
            'username.unique'   => 'Username sudah dipakai.',
            'password.required' => 'Password wajib diisi.',
            'password.confirmed'=> 'Konfirmasi password tidak cocok.',
        ]);

        $roleId = Role::where('slug', Role::MITRA)->value('id');
        if (!$roleId) {
            return back()->with('toast_error', 'Sistem mitra belum siap. Hubungi apotek.')->withInput();
        }

        $defaults = Partner::defaultsForType($request->type);

        $partner = DB::transaction(function () use ($request, $roleId, $defaults) {
            $user = User::create([
                'name'     => $request->pic_name,
                'username' => $request->username,
                'email'    => $request->email,
                'password' => Hash::make($request->password),
                'role_id'  => $roleId,
                'is_active'=> false, // aktif setelah admin approve
            ]);

            $partner = Partner::create([
                'name'     => $request->name,
                'type'     => $request->type,
                'npwp'     => $request->npwp,
                'nib'      => $request->nib,
                'address'  => $request->address,
                'city'     => $request->city,
                'pic_name' => $request->pic_name,
                'phone'    => $request->phone,
                'email'    => $request->email,
                'user_id'  => $user->id,
                'status'   => Partner::STATUS_PENDING,
                'price_mode'      => $defaults['price_mode'],
                'allow_transfer'  => true,
                'allow_cod'       => true,
                'invoice_enabled' => $defaults['invoice_enabled'],
                'credit_days'     => 30,
                'registration_source' => Partner::SOURCE_SELF,
            ]);

            $partner->update([
                'code' => 'MIT-' . str_pad((string) $partner->id, 4, '0', STR_PAD_LEFT),
            ]);

            return $partner;
        });

        ActivityLogService::log(
            'REGISTER',
            'Mitra Katalog',
            "Pendaftaran mitra mandiri: {$partner->name} ({$partner->type}) — menunggu approval"
        );

        $partner->refresh();
        $summary = $this->buildRegistrationSummary($partner, $request->username);

        session()->put('mitra_registration_id', $partner->id);

        return redirect()
            ->route('mitra.register.success', ['code' => $partner->code])
            ->with('registered_partner_summary', $summary);
    }

    public function registerSuccess(Request $request, ?string $code = null)
    {
        $summary = $this->resolveRegistrationSummary(
            $code ?: $request->query('code'),
            session('registered_partner_summary'),
            session('mitra_registration_id')
        );

        return view('partners.portal.register-success', array_merge($this->apotekData(), [
            'summary'      => $summary,
            'summaryFields'=> $this->formatSummaryFields($summary),
            'waUrl'        => $this->buildWhatsappConfirmationUrl($summary),
        ]));
    }

    private function resolveRegistrationSummary(?string $code, mixed $sessionSummary, mixed $partnerId): array
    {
        if (filled($code)) {
            $partner = Partner::with('user')->where('code', $code)->first();
            if ($partner) {
                return $this->buildRegistrationSummary($partner, $partner->user?->username);
            }
        }

        if (filled($partnerId)) {
            $partner = Partner::with('user')->find($partnerId);
            if ($partner) {
                return $this->buildRegistrationSummary($partner, $partner->user?->username);
            }
        }

        if (is_array($sessionSummary) && filled($sessionSummary['code'] ?? null)) {
            return $sessionSummary;
        }

        return is_array($sessionSummary) ? $sessionSummary : [];
    }

    private function formatSummaryFields(array $summary): array
    {
        $display = fn ($value) => filled($value) ? (string) $value : '-';

        return [
            'Kode Mitra'   => $display($summary['code'] ?? null),
            'Nama Usaha'   => $display($summary['name'] ?? null),
            'Tipe Mitra'   => $display($summary['type_label'] ?? null),
            'Kota'         => $display($summary['city'] ?? null),
            'NPWP'         => $display($summary['npwp'] ?? null),
            'NIB/Izin'     => $display($summary['nib'] ?? null),
            'Alamat'       => $display($summary['address'] ?? null),
            'Nama PIC'     => $display($summary['pic_name'] ?? null),
            'Telepon/WA'   => $display($summary['phone'] ?? null),
            'Email'        => $display($summary['email'] ?? null),
            'Username'     => $display($summary['username'] ?? null),
        ];
    }

    private function buildWhatsappConfirmationUrl(array $summary): string
    {
        $fields = $this->formatSummaryFields($summary);

        $lines = [
            'Halo Admin Apotek Almaira,',
            '',
            'Saya telah mendaftar sebagai Mitra B2B dan mohon bantu proses persetujuan akun saya.',
            '',
            'DATA PENDAFTARAN:',
        ];

        foreach ($fields as $label => $value) {
            $lines[] = '* ' . $label . ': ' . $value;
        }

        $lines[] = '';
        $lines[] = 'Mohon data di atas diverifikasi dan akun saya disetujui agar dapat mengakses Portal Mitra & E-Catalog.';
        $lines[] = '';
        $lines[] = 'Terima kasih.';

        return 'https://wa.me/6285166657070?text=' . rawurlencode(implode("\n", $lines));
    }

    private function buildRegistrationSummary(Partner $partner, ?string $username = null): array
    {
        $username = $username ?? $partner->user?->username;

        return [
            'code'       => $partner->code,
            'name'       => $partner->name,
            'type'       => $partner->type,
            'type_label' => Partner::typeOptions()[$partner->type] ?? $partner->type,
            'npwp'       => $partner->npwp,
            'nib'        => $partner->nib,
            'address'    => $partner->address,
            'city'       => $partner->city,
            'pic_name'   => $partner->pic_name,
            'phone'      => $partner->phone,
            'email'      => $partner->email,
            'username'   => $username,
        ];
    }

    public function loginForm()
    {
        if (Auth::check() && Auth::user()->isMitra()) {
            return redirect()->route('mitra.account');
        }
        if (Auth::check() && Auth::user()->isStaff()) {
            return redirect()->route('dashboard');
        }

        return view('partners.portal.login', $this->apotekData());
    }

    public function login(Request $request)
    {
        $request->validate([
            'login'    => 'required|string',
            'password' => 'required|string',
        ], [
            'login.required'    => 'Username atau email wajib diisi.',
            'password.required' => 'Password wajib diisi.',
        ]);

        $user = User::with(['role', 'partner'])
            ->where('email', $request->login)
            ->orWhere('username', $request->login)
            ->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return back()->withErrors(['login' => 'Username atau password salah.'])->withInput($request->only('login'));
        }

        if (!$user->isMitra()) {
            return back()->withErrors([
                'login' => 'Akun ini bukan akun mitra. Staff apotek silakan login di halaman staff.',
            ])->withInput($request->only('login'));
        }

        $partner = $user->partner;

        if ($partner && $partner->status === Partner::STATUS_PENDING) {
            return back()->withErrors([
                'login' => 'Pendaftaran mitra Anda masih menunggu approval admin. Anda akan dapat login setelah disetujui.',
            ])->withInput($request->only('login'));
        }

        if ($partner && in_array($partner->status, [Partner::STATUS_REJECTED, Partner::STATUS_INACTIVE], true)) {
            return back()->withErrors([
                'login' => 'Akun mitra Anda ' . ($partner->status === Partner::STATUS_REJECTED ? 'ditolak' : 'nonaktif') . '. Hubungi apotek.',
            ])->withInput($request->only('login'));
        }

        if (!$user->is_active) {
            return back()->withErrors(['login' => 'Akun mitra belum aktif. Hubungi apotek.'])->withInput($request->only('login'));
        }

        Auth::login($user, $request->boolean('remember'));
        $request->session()->regenerate();
        $user->update(['last_login' => now()]);

        ActivityLogService::login($user->name . ' (Mitra)');

        return redirect()
            ->route('mitra.account')
            ->with('toast_success', 'Selamat datang, ' . ($partner?->name ?? $user->name) . '!');
    }

    public function account()
    {
        $user = Auth::user();
        abort_unless($user && $user->isMitra(), 403);

        $partner = $user->partner;
        if (!$partner) {
            Auth::logout();
            return redirect()->route('mitra.login')->with('toast_error', 'Data mitra tidak ditemukan.');
        }

        $orderStats = [
            'total' => $partner->orders()->count(),
            'open'  => $partner->orders()->whereIn('status', ['submitted', 'confirmed'])->count(),
            'done'  => $partner->orders()->where('status', 'fulfilled')->count(),
        ];

        return view('partners.portal.account', array_merge($this->apotekData(), [
            'partner'   => $partner,
            'user'      => $user,
            'cartCount' => \App\Services\PartnerCartService::count(),
            'orderStats'=> $orderStats,
        ]));
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('catalog.index')->with('toast_info', 'Anda telah logout dari portal mitra.');
    }
}
