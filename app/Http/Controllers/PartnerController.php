<?php

namespace App\Http\Controllers;

use App\Models\Partner;
use App\Models\PartnerOrder;
use App\Models\Role;
use App\Models\User;
use App\Services\ActivityLogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class PartnerController extends Controller
{
    public function index(Request $request)
    {
        $query = Partner::with(['user', 'approver'])->latest();

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('name', 'like', "%{$s}%")
                  ->orWhere('phone', 'like', "%{$s}%")
                  ->orWhere('email', 'like', "%{$s}%")
                  ->orWhere('code', 'like', "%{$s}%")
                  ->orWhere('pic_name', 'like', "%{$s}%");
            });
        }

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $partners = $query->paginate(15)->withQueryString();
        $pendingCount = Partner::pending()->count();
        $maxPartnerId = (int) Partner::max('id');

        return view('partners.index', compact('partners', 'pendingCount', 'maxPartnerId'));
    }

    public function pendingUpdates(Request $request)
    {
        $sinceId = max(0, (int) $request->query('since_id', 0));

        $newPending = Partner::pending()
            ->where('registration_source', Partner::SOURCE_SELF)
            ->when($sinceId > 0, fn ($q) => $q->where('id', '>', $sinceId))
            ->latest()
            ->get();

        return response()->json([
            'success'       => true,
            'pending_count' => Partner::pending()->count(),
            'max_id'        => (int) Partner::max('id'),
            'new_registrations' => $newPending->map(fn (Partner $p) => [
                'id'         => $p->id,
                'code'       => $p->code,
                'name'       => $p->name,
                'type_label' => $p->type_label,
                'pic_name'   => $p->pic_name,
                'phone'      => $p->phone,
                'city'       => $p->city,
                'created_at' => $p->created_at?->format('d/m/Y H:i'),
                'show_url'   => route('partners.show', $p),
            ])->values(),
        ]);
    }

    public function create()
    {
        return view('partners.create', [
            'types'      => Partner::typeOptions(),
            'priceModes' => Partner::priceModeOptions(),
            'defaults'   => Partner::defaultsForType(Partner::TYPE_UMKM),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $this->validatePartner($request);

        $defaults = Partner::defaultsForType($validated['type']);

        $payload = [
            'name'     => $validated['name'],
            'type'     => $validated['type'],
            'npwp'     => $validated['npwp'] ?? null,
            'nib'      => $validated['nib'] ?? null,
            'address'  => $validated['address'] ?? null,
            'city'     => $validated['city'] ?? null,
            'pic_name' => $validated['pic_name'] ?? null,
            'phone'    => $validated['phone'],
            'email'    => $validated['email'] ?? null,
            'notes'    => $validated['notes'] ?? null,
            'price_mode'      => $request->input('price_mode', $defaults['price_mode']),
            'allow_transfer'  => $request->boolean('allow_transfer'),
            'allow_cod'       => $request->boolean('allow_cod'),
            'invoice_enabled' => $request->boolean('invoice_enabled'),
            'credit_days'     => (int) $request->input('credit_days', 30),
            'ppn_enabled'     => $request->boolean('ppn_enabled'),
            'ppn_percent'     => $request->boolean('ppn_enabled') ? (float) $request->input('ppn_percent', 11) : null,
            'ppn_bearer'      => $request->boolean('ppn_enabled') ? $request->input('ppn_bearer', 'buyer') : null,
            'registration_source' => Partner::SOURCE_ADMIN,
            'status' => $request->boolean('activate_now', true)
                ? Partner::STATUS_APPROVED
                : Partner::STATUS_PENDING,
        ];

        if ($payload['status'] === Partner::STATUS_APPROVED) {
            $payload['approved_at'] = now();
            $payload['approved_by'] = auth()->id();
        }

        $partner = DB::transaction(function () use ($request, $payload) {
            $partner = Partner::create($payload);
            $partner->update([
                'code' => 'MIT-' . str_pad((string) $partner->id, 4, '0', STR_PAD_LEFT),
            ]);

            if ($request->boolean('create_login')) {
                $this->createOrAttachUser($partner, $request);
            }

            return $partner->fresh('user');
        });

        ActivityLogService::log(
            'CREATE',
            'Mitra Katalog',
            "Mendaftarkan mitra: {$partner->name} ({$partner->type_label}) — status {$partner->status}"
        );

        return redirect()
            ->route('partners.show', $partner)
            ->with('toast_success', 'Mitra berhasil ditambahkan.');
    }

    public function show(Partner $partner)
    {
        $partner->load(['user', 'approver']);
        return view('partners.show', compact('partner'));
    }

    public function edit(Partner $partner)
    {
        $partner->load('user');
        return view('partners.edit', [
            'partner'    => $partner,
            'types'      => Partner::typeOptions(),
            'priceModes' => Partner::priceModeOptions(),
            'statuses'   => Partner::statusOptions(),
        ]);
    }

    public function update(Request $request, Partner $partner)
    {
        $validated = $this->validatePartner($request, $partner);

        $payload = [
            'name'     => $validated['name'],
            'type'     => $validated['type'],
            'npwp'     => $validated['npwp'] ?? null,
            'nib'      => $validated['nib'] ?? null,
            'address'  => $validated['address'] ?? null,
            'city'     => $validated['city'] ?? null,
            'pic_name' => $validated['pic_name'] ?? null,
            'phone'    => $validated['phone'],
            'email'    => $validated['email'] ?? null,
            'notes'    => $validated['notes'] ?? null,
            'price_mode'      => $request->input('price_mode', $partner->price_mode),
            'allow_transfer'  => $request->boolean('allow_transfer'),
            'allow_cod'       => $request->boolean('allow_cod'),
            'invoice_enabled' => $request->boolean('invoice_enabled'),
            'credit_days'     => (int) $request->input('credit_days', 30),
            'ppn_enabled'     => $request->boolean('ppn_enabled'),
            'ppn_percent'     => $request->boolean('ppn_enabled') ? (float) $request->input('ppn_percent', 11) : null,
            'ppn_bearer'      => $request->boolean('ppn_enabled') ? $request->input('ppn_bearer', 'buyer') : null,
        ];

        if ($request->filled('status') && in_array($request->status, array_keys(Partner::statusOptions()), true)) {
            $payload['status'] = $request->status;
            if ($request->status === Partner::STATUS_APPROVED && $partner->status !== Partner::STATUS_APPROVED) {
                $payload['approved_at'] = now();
                $payload['approved_by'] = auth()->id();
                $payload['rejection_reason'] = null;
            }
            if ($request->status === Partner::STATUS_REJECTED) {
                $payload['rejection_reason'] = $request->input('rejection_reason');
            }
        }

        $old = $partner->toArray();

        DB::transaction(function () use ($request, $partner, $payload) {
            $partner->update($payload);

            if ($request->boolean('create_login') && !$partner->user_id) {
                $this->createOrAttachUser($partner->fresh(), $request);
            } elseif ($partner->user && $request->filled('password')) {
                $partner->user->update([
                    'password'  => Hash::make($request->password),
                    'is_active' => $partner->fresh()->status === Partner::STATUS_APPROVED,
                ]);
            } elseif ($partner->user) {
                $partner->user->update([
                    'is_active' => $partner->fresh()->status === Partner::STATUS_APPROVED,
                ]);
            }
        });

        ActivityLogService::updated(
            'Mitra Katalog',
            "Memperbarui mitra {$partner->name}",
            $old,
            $partner->fresh()->toArray()
        );

        return redirect()
            ->route('partners.show', $partner)
            ->with('toast_success', 'Data mitra berhasil diperbarui.');
    }

    public function approve(Request $request, Partner $partner)
    {
        if ($partner->status === Partner::STATUS_APPROVED) {
            return back()->with('toast_info', 'Mitra sudah aktif.');
        }

        $defaults = Partner::defaultsForType($partner->type);

        $partner->update([
            'status'          => Partner::STATUS_APPROVED,
            'approved_at'     => now(),
            'approved_by'     => auth()->id(),
            'rejection_reason'=> null,
            'price_mode'      => $partner->price_mode ?: $defaults['price_mode'],
            'invoice_enabled' => $request->has('invoice_enabled')
                ? $request->boolean('invoice_enabled')
                : $partner->invoice_enabled,
        ]);

        if ($partner->user) {
            $partner->user->update(['is_active' => true]);
        }

        ActivityLogService::log(
            'APPROVE',
            'Mitra Katalog',
            "Menyetujui mitra {$partner->name} ({$partner->code})"
        );

        return back()->with('toast_success', "Mitra {$partner->name} disetujui.");
    }

    public function reject(Request $request, Partner $partner)
    {
        $request->validate([
            'rejection_reason' => 'required|string|max:500',
        ], [
            'rejection_reason.required' => 'Alasan penolakan wajib diisi.',
        ]);

        $partner->update([
            'status'           => Partner::STATUS_REJECTED,
            'rejection_reason' => $request->rejection_reason,
        ]);

        if ($partner->user) {
            $partner->user->update(['is_active' => false]);
        }

        ActivityLogService::log(
            'REJECT',
            'Mitra Katalog',
            "Menolak mitra {$partner->name}: {$request->rejection_reason}"
        );

        return back()->with('toast_warning', "Mitra {$partner->name} ditolak.");
    }

    public function deactivate(Partner $partner)
    {
        $partner->update(['status' => Partner::STATUS_INACTIVE]);
        if ($partner->user) {
            $partner->user->update(['is_active' => false]);
        }

        ActivityLogService::log(
            'UPDATE',
            'Mitra Katalog',
            "Menonaktifkan mitra {$partner->name}"
        );

        return back()->with('toast_success', 'Mitra dinonaktifkan.');
    }

    public function destroy(Partner $partner)
    {
        // Blokir hapus jika masih ada PO aktif (belum selesai/dibatalkan)
        $activeOrders = $partner->orders()
            ->whereNotIn('status', [
                PartnerOrder::STATUS_CANCELLED,
                PartnerOrder::STATUS_FULFILLED,
            ])
            ->count();

        if ($activeOrders > 0) {
            return back()->with(
                'toast_error',
                "Mitra {$partner->name} masih punya {$activeOrders} PO aktif. Selesaikan/batalkan PO dulu, atau nonaktifkan mitra saja."
            );
        }

        $old = $partner->loadMissing('user')->toArray();
        $name = $partner->name;
        $code = $partner->code;

        DB::transaction(function () use ($partner) {
            if ($partner->user) {
                $partner->user->update(['is_active' => false]);
            }
            // Soft delete — riwayat PO lama tetap terhubung
            $partner->delete();
        });

        ActivityLogService::deleted('Mitra Katalog', "{$name} ({$code})", $old);

        return redirect()
            ->route('partners.index')
            ->with('toast_success', "Mitra {$name} berhasil dihapus.");
    }

    private function validatePartner(Request $request, ?Partner $partner = null): array
    {
        return $request->validate([
            'name'     => 'required|string|max:200',
            'type'     => ['required', Rule::in(array_keys(Partner::typeOptions()))],
            'npwp'     => 'nullable|string|max:40',
            'nib'      => 'nullable|string|max:50',
            'address'  => 'nullable|string',
            'city'     => 'nullable|string|max:100',
            'pic_name' => 'nullable|string|max:150',
            'phone'    => [
                'required', 'string', 'max:30',
                Rule::unique('partners', 'phone')->ignore($partner?->id)->whereNull('deleted_at'),
            ],
            'email'    => 'nullable|email|max:150',
            'notes'    => 'nullable|string',
            'price_mode' => ['nullable', Rule::in(array_keys(Partner::priceModeOptions()))],
            'credit_days' => 'nullable|integer|min:1|max:90',
            'ppn_enabled' => 'nullable|boolean',
            'ppn_percent' => 'nullable|required_if:ppn_enabled,1|numeric|min:0|max:100',
            'ppn_bearer'  => ['nullable', 'required_if:ppn_enabled,1', Rule::in(['buyer', 'seller'])],
            'username' => [
                Rule::requiredIf(fn () => $request->boolean('create_login') && !$partner?->user_id),
                'nullable', 'string', 'max:50',
                Rule::unique('users', 'username')->ignore($partner?->user_id),
            ],
            'login_email' => [
                Rule::requiredIf(fn () => $request->boolean('create_login') && !$partner?->user_id),
                'nullable', 'email', 'max:150',
                Rule::unique('users', 'email')->ignore($partner?->user_id),
            ],
            'password' => [
                Rule::requiredIf(fn () => $request->boolean('create_login') && !$partner?->user_id),
                'nullable', 'string', 'min:6',
            ],
        ], [
            'name.required'  => 'Nama usaha / institusi wajib diisi.',
            'type.required'  => 'Tipe mitra wajib dipilih.',
            'phone.required' => 'Nomor telepon wajib diisi.',
            'phone.unique'   => 'Nomor telepon sudah terdaftar sebagai mitra.',
            'username.required' => 'Username login wajib diisi jika membuat akun.',
            'login_email.required' => 'Email login wajib diisi jika membuat akun.',
            'password.required' => 'Password wajib diisi jika membuat akun.',
        ]);
    }

    /** Tambah mitra cepat dari kasir (JSON) — langsung approved + default komersial. */
    public function quickStore(Request $request)
    {
        $validated = $request->validate([
            'name'     => 'required|string|max:200',
            'phone'    => 'required|string|max:30|unique:partners,phone',
            'type'     => ['required', Rule::in(array_keys(Partner::typeOptions()))],
            'pic_name' => 'nullable|string|max:150',
            'address'  => 'nullable|string|max:2000',
            'city'     => 'nullable|string|max:100',
        ], [
            'name.required'  => 'Nama mitra wajib diisi.',
            'phone.required' => 'Nomor HP wajib diisi.',
            'phone.unique'   => 'Nomor HP sudah terdaftar sebagai mitra.',
            'type.required'  => 'Tipe mitra wajib dipilih.',
            'type.in'        => 'Tipe mitra tidak valid.',
        ]);

        $defaults = Partner::defaultsForType($validated['type']);

        $partner = DB::transaction(function () use ($validated, $defaults) {
            $partner = Partner::create([
                'name'                => $validated['name'],
                'type'                => $validated['type'],
                'phone'               => $validated['phone'],
                'pic_name'            => $validated['pic_name'] ?? $validated['name'],
                'address'             => $validated['address'] ?? null,
                'city'                => $validated['city'] ?? null,
                'price_mode'          => $defaults['price_mode'],
                'allow_transfer'      => $defaults['allow_transfer'],
                'allow_cod'           => $defaults['allow_cod'],
                'invoice_enabled'     => $defaults['invoice_enabled'],
                'credit_days'         => $defaults['credit_days'],
                'ppn_enabled'         => false,
                'registration_source' => Partner::SOURCE_ADMIN,
                'status'              => Partner::STATUS_APPROVED,
                'approved_at'         => now(),
                'approved_by'         => auth()->id(),
            ]);

            $partner->update([
                'code' => 'MIT-' . str_pad((string) $partner->id, 4, '0', STR_PAD_LEFT),
            ]);

            return $partner->fresh();
        });

        ActivityLogService::log(
            'CREATE',
            'Mitra Katalog',
            "Tambah cepat dari kasir: {$partner->name} ({$partner->type_label})"
        );

        return response()->json([
            'success' => true,
            'message' => 'Mitra berhasil ditambahkan dan langsung aktif.',
            'partner' => [
                'id'                  => $partner->id,
                'code'                => $partner->code,
                'name'                => $partner->name,
                'phone'               => $partner->phone,
                'pic_name'            => $partner->pic_name,
                'type'                => $partner->type,
                'type_label'          => $partner->type_label,
                'price_mode'          => $partner->price_mode,
                'price_mode_label'    => $partner->price_mode_label,
                'invoice_enabled'     => (bool) $partner->invoice_enabled,
                'credit_days'         => (int) ($partner->credit_days ?: 30),
                'ppn_enabled'         => (bool) $partner->ppn_enabled,
                'ppn_percent'         => (float) ($partner->ppn_percent ?: 0),
                'ppn_bearer'          => $partner->ppn_bearer,
                'has_overdue_invoice' => false,
                'address'             => $partner->address,
                'allow_transfer'      => (bool) $partner->allow_transfer,
                'allow_cod'           => (bool) $partner->allow_cod,
            ],
        ]);
    }

    /** Daftar / pencarian mitra aktif untuk kasir POS (JSON). */
    public function search(Request $request)
    {
        $query = trim((string) $request->get('q', ''));
        $limit = min(50, max(10, (int) $request->get('limit', 30)));

        $partners = Partner::approved()
            ->when($query !== '', function ($q) use ($query) {
                $q->where(function ($inner) use ($query) {
                    $inner->where('name', 'like', "%{$query}%")
                        ->orWhere('phone', 'like', "%{$query}%")
                        ->orWhere('code', 'like', "%{$query}%")
                        ->orWhere('pic_name', 'like', "%{$query}%");
                });
            })
            ->orderBy('name')
            ->limit($limit)
            ->get([
                'id', 'code', 'name', 'phone', 'type', 'pic_name', 'address',
                'price_mode', 'invoice_enabled', 'credit_days',
                'allow_transfer', 'allow_cod',
                'ppn_enabled', 'ppn_percent', 'ppn_bearer',
            ]);

        $partners->transform(function (Partner $partner) {
            return [
                'id'                   => $partner->id,
                'code'                 => $partner->code,
                'name'                 => $partner->name,
                'phone'                => $partner->phone,
                'pic_name'             => $partner->pic_name,
                'address'              => $partner->address,
                'type'                 => $partner->type,
                'type_label'           => $partner->type_label,
                'price_mode'           => $partner->price_mode,
                'price_mode_label'     => $partner->price_mode_label,
                'invoice_enabled'      => (bool) $partner->invoice_enabled,
                'credit_days'          => (int) ($partner->credit_days ?: 30),
                'allow_transfer'       => (bool) $partner->allow_transfer,
                'allow_cod'            => (bool) $partner->allow_cod,
                'ppn_enabled'          => (bool) $partner->ppn_enabled,
                'ppn_percent'          => (float) ($partner->ppn_percent ?: 0),
                'ppn_bearer'           => $partner->ppn_bearer,
                'has_overdue_invoice'  => $partner->hasOverdueInvoice(),
            ];
        });

        return response()->json($partners);
    }

    private function createOrAttachUser(Partner $partner, Request $request): User
    {
        $roleId = Role::where('slug', Role::MITRA)->value('id');
        if (!$roleId) {
            abort(500, 'Role mitra belum tersedia. Jalankan migrasi langkah 1.');
        }

        $user = User::create([
            'name'     => $partner->pic_name ?: $partner->name,
            'username' => $request->username,
            'email'    => $request->login_email ?: $partner->email,
            'password' => Hash::make($request->password),
            'role_id'  => $roleId,
            'is_active'=> $partner->status === Partner::STATUS_APPROVED,
        ]);

        $partner->update(['user_id' => $user->id]);

        return $user;
    }
}
