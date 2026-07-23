<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\User;
use App\Services\ActivityLogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $query = User::with(['role', 'employee'])->orderBy('name');

        if ($request->search) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', '%'.$request->search.'%')
                    ->orWhere('email', 'like', '%'.$request->search.'%')
                    ->orWhere('username', 'like', '%'.$request->search.'%');
            });
        }

        if ($request->role_id) {
            $query->where('role_id', $request->role_id);
        }

        if ($request->filled('status')) {
            $query->where('is_active', $request->status);
        }

        $users = $query->paginate(15)->withQueryString();
        $roles = Role::staffAssignable()->orderBy('name')->get();

        return view('users.index', compact('users', 'roles'));
    }

    public function create()
    {
        $roles = Role::staffAssignable()->orderBy('name')->get();

        return view('users.create', compact('roles'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'username' => 'required|string|max:50|unique:users',
            'email' => 'required|email|unique:users',
            'password' => ['required', Rules\Password::defaults()],
            'role_id' => 'required|exists:roles,id',
            'is_active' => 'boolean',
            'avatar' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
        ], [
            'avatar.image' => 'File foto profil harus berupa gambar.',
            'avatar.mimes' => 'Format foto: jpeg, png, jpg, atau webp.',
            'avatar.max' => 'Ukuran foto maksimal 2 MB.',
        ]);

        $role = Role::staffAssignable()->find($validated['role_id']);
        if (! $role) {
            return back()->withInput()->with('toast_error', 'Role tidak valid atau nonaktif.');
        }

        $validated['password'] = Hash::make($validated['password']);
        $validated['is_active'] = $request->boolean('is_active', true);
        unset($validated['avatar']);

        $user = User::create($validated);

        if ($request->hasFile('avatar')) {
            $user->avatar = $this->storeAvatar($request, $user->id);
            $user->save();
        }

        $logData = $user->toArray();
        unset($logData['password']);
        ActivityLogService::created('Users', $user->name, $logData);

        return redirect()->route('users.index')->with('toast_success', "User {$user->name} berhasil ditambahkan!");
    }

    public function edit(User $user)
    {
        $user->load('employee');
        $roles = Role::staffAssignable()->orderBy('name')->get();
        if ($user->role && ! $roles->contains('id', $user->role_id)) {
            $roles->prepend($user->role);
        }

        return view('users.edit', compact('user', 'roles'));
    }

    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'username' => 'required|string|max:50|unique:users,username,'.$user->id,
            'email' => 'required|email|unique:users,email,'.$user->id,
            'role_id' => 'required|exists:roles,id',
            'is_active' => 'boolean',
            'password' => ['nullable', Rules\Password::defaults()],
            'avatar' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'remove_avatar' => 'nullable|boolean',
        ], [
            'avatar.image' => 'File foto profil harus berupa gambar.',
            'avatar.mimes' => 'Format foto: jpeg, png, jpg, atau webp.',
            'avatar.max' => 'Ukuran foto maksimal 2 MB.',
        ]);

        $role = Role::query()
            ->where('id', $validated['role_id'])
            ->where(function ($q) use ($user) {
                $q->where(function ($q2) {
                    $q2->where('is_active', true)->where('slug', '!=', Role::MITRA);
                })->orWhere('id', $user->role_id);
            })
            ->first();

        if (! $role) {
            return back()->withInput()->with('toast_error', 'Role tidak valid atau nonaktif.');
        }

        $oldData = $user->toArray();
        unset($oldData['password']);

        if (empty($validated['password'])) {
            unset($validated['password']);
        } else {
            $validated['password'] = Hash::make($validated['password']);
        }

        $validated['is_active'] = $request->boolean('is_active');
        unset($validated['avatar'], $validated['remove_avatar']);

        if ($request->boolean('remove_avatar')) {
            $this->deleteOwnedAvatar($user);
            $validated['avatar'] = null;
        } elseif ($request->hasFile('avatar')) {
            $this->deleteOwnedAvatar($user);
            $validated['avatar'] = $this->storeAvatar($request, $user->id);
        }

        $user->update($validated);

        $newData = $user->fresh()->toArray();
        unset($newData['password']);
        ActivityLogService::updated('Users', $user->name, $oldData, $newData);

        return redirect()->route('users.edit', $user)->with('toast_success', "User {$user->name} berhasil diperbarui!");
    }

    public function destroy(User $user)
    {
        if ($user->id === auth()->id()) {
            return back()->with('toast_error', 'Anda tidak dapat menghapus akun sendiri!');
        }

        if (\App\Models\Sale::where('user_id', $user->id)->exists() || \App\Models\Purchase::where('user_id', $user->id)->exists()) {
            return back()->with('toast_error', "User {$user->name} tidak dapat dihapus karena memiliki riwayat transaksi!");
        }

        $name = $user->name;
        $oldData = $user->toArray();
        unset($oldData['password']);
        $this->deleteOwnedAvatar($user);

        $user->delete();
        ActivityLogService::deleted('Users', $name, $oldData);

        return redirect()->route('users.index')->with('toast_success', "User {$name} berhasil dihapus!");
    }

    public function toggleStatus(User $user)
    {
        if ($user->id === auth()->id()) {
            if (request()->expectsJson()) {
                return response()->json(['success' => false, 'message' => 'Anda tidak dapat menonaktifkan akun sendiri!'], 400);
            }

            return back()->with('toast_error', 'Anda tidak dapat menonaktifkan akun sendiri!');
        }

        $oldData = $user->toArray();
        unset($oldData['password']);

        $user->update(['is_active' => ! $user->is_active]);
        $status = $user->is_active ? 'diaktifkan' : 'dinonaktifkan';

        $newData = $user->toArray();
        unset($newData['password']);

        ActivityLogService::updated('Users', "{$user->name} {$status}", $oldData, $newData);

        if (request()->expectsJson()) {
            return response()->json([
                'success' => true,
                'is_active' => $user->is_active,
                'message' => "User {$user->name} berhasil {$status}!",
            ]);
        }

        return back()->with('toast_success', "User {$user->name} berhasil {$status}!");
    }

    private function storeAvatar(Request $request, int $userId): string
    {
        $dir = public_path('uploads/avatars');
        if (! File::isDirectory($dir)) {
            File::makeDirectory($dir, 0755, true);
        }

        $file = $request->file('avatar');
        $filename = 'avatar_'.$userId.'_'.time().'.'.$file->getClientOriginalExtension();
        $file->move($dir, $filename);

        return 'uploads/avatars/'.$filename;
    }

    /** Hapus file avatar milik user (jangan hapus foto karyawan yang hanya di-link). */
    private function deleteOwnedAvatar(User $user): void
    {
        $path = $user->avatar;
        if (! $path || ! str_starts_with($path, 'uploads/avatars/')) {
            return;
        }

        $full = public_path($path);
        if (File::exists($full)) {
            File::delete($full);
        }
    }
}
