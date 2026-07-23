<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Services\ActivityLogService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class RoleController extends Controller
{
    public function index(Request $request)
    {
        $query = Role::withCount('users')->orderByRaw('is_system DESC')->orderBy('name');

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', '%'.$request->search.'%')
                    ->orWhere('slug', 'like', '%'.$request->search.'%')
                    ->orWhere('description', 'like', '%'.$request->search.'%');
            });
        }

        $roles = $query->paginate(20)->withQueryString();
        $permissionLabels = Role::PERMISSION_LABELS;

        return view('roles.index', compact('roles', 'permissionLabels'));
    }

    public function store(Request $request)
    {
        $v = $request->validate([
            'name' => ['required', 'string', 'max:100', 'unique:roles,name'],
            'description' => ['nullable', 'string', 'max:255'],
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['string', Rule::in(array_keys(Role::PERMISSION_LABELS))],
        ]);

        $role = Role::create([
            'name' => $v['name'],
            'slug' => Role::makeSlug($v['name']),
            'description' => $v['description'] ?? null,
            'permissions' => array_values($v['permissions'] ?? []),
            'is_system' => false,
            'is_active' => true,
        ]);

        ActivityLogService::created('Role', $role->name);

        return back()->with('toast_success', "Role {$role->name} berhasil ditambahkan!");
    }

    public function update(Request $request, Role $role)
    {
        $v = $request->validate([
            'name' => ['required', 'string', 'max:100', Rule::unique('roles', 'name')->ignore($role->id)],
            'description' => ['nullable', 'string', 'max:255'],
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['string', Rule::in(array_keys(Role::PERMISSION_LABELS))],
        ]);

        $permissions = array_values($v['permissions'] ?? []);

        // Kepala IT selalu akses penuh
        if ($role->slug === Role::SUPER_ADMIN) {
            $permissions = ['*'];
        }

        $role->update([
            'name' => $v['name'],
            'description' => $v['description'] ?? null,
            'permissions' => $permissions,
        ]);

        ActivityLogService::updated('Role', $role->name);

        return back()->with('toast_success', "Role {$role->name} berhasil diperbarui!");
    }

    public function destroy(Role $role)
    {
        if ($role->is_system) {
            return back()->with('toast_error', 'Role sistem tidak dapat dihapus.');
        }

        $count = $role->users()->count();
        if ($count > 0) {
            return back()->with('toast_error', "Role tidak bisa dihapus karena masih dipakai {$count} user!");
        }

        $name = $role->name;
        $role->delete();
        ActivityLogService::deleted('Role', $name);

        return back()->with('toast_success', "Role {$name} berhasil dihapus!");
    }

    public function toggleStatus(Role $role)
    {
        if ($role->slug === Role::SUPER_ADMIN) {
            return back()->with('toast_error', 'Role Kepala IT tidak dapat dinonaktifkan.');
        }

        $role->update(['is_active' => ! $role->is_active]);

        return back()->with('toast_success', 'Status role diperbarui!');
    }
}
