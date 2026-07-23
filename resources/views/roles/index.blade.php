@extends('layouts.app')
@section('title', 'Master Role / Hak Akses')
@section('page-title', 'Master Role')

@section('breadcrumb')
<svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"/></svg>
<span class="text-gray-600 font-medium">Role / Hak Akses</span>
@endsection

@section('content')
<div class="animate-in">
    <div class="page-header">
        <div>
            <h2 class="page-title">Master Role / Hak Akses</h2>
            <p class="page-subtitle">Kelola role dan modul yang boleh diakses — dipakai di form Tambah/Edit User</p>
        </div>
        <button type="button" onclick="openAddRoleModal()" class="btn btn-primary">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Tambah Role
        </button>
    </div>

    <form method="GET" class="card p-4 mb-5 flex flex-wrap items-center gap-3">
        <div class="flex-1 min-w-[220px] relative">
            <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama / deskripsi role..." class="form-input pl-9">
        </div>
        <button type="submit" class="btn btn-primary btn-sm">Filter</button>
        @if(request('search'))
        <a wire:navigate href="{{ route('roles.index') }}" class="btn btn-secondary btn-sm">Reset</a>
        @endif
    </form>

    <div class="card overflow-hidden">
        <div class="overflow-x-auto">
            <table class="data-table">
                <thead>
                    <tr>
                        <th class="w-12">#</th>
                        <th>Role</th>
                        <th>Hak Akses</th>
                        <th class="text-center">User</th>
                        <th class="text-center">Status</th>
                        <th class="text-center w-36">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($roles as $i => $role)
                    <tr>
                        <td class="text-gray-400">{{ $roles->firstItem() + $i }}</td>
                        <td>
                            <p class="font-semibold text-gray-800">{{ $role->name }}</p>
                            <p class="text-xs text-gray-400 font-mono mt-0.5">{{ $role->slug }}</p>
                            @if($role->description)
                            <p class="text-xs text-gray-500 mt-1 max-w-xs">{{ $role->description }}</p>
                            @endif
                            @if($role->is_system)
                            <span class="inline-flex mt-1 text-[10px] font-semibold uppercase tracking-wide text-slate-500 bg-slate-100 px-1.5 py-0.5 rounded">Sistem</span>
                            @endif
                        </td>
                        <td>
                            <div class="flex flex-wrap gap-1 max-w-md">
                                @forelse($role->permissionLabels() as $label)
                                <span class="inline-flex text-[11px] px-2 py-0.5 rounded-full bg-emerald-50 text-emerald-700 border border-emerald-100">{{ $label }}</span>
                                @empty
                                <span class="text-xs text-gray-400">Tidak ada akses modul internal</span>
                                @endforelse
                            </div>
                        </td>
                        <td class="text-center">
                            <span class="badge badge-info">{{ $role->users_count }}</span>
                        </td>
                        <td class="text-center">
                            @if($role->is_active)
                            <span class="badge badge-success">Aktif</span>
                            @else
                            <span class="badge badge-gray">Nonaktif</span>
                            @endif
                        </td>
                        <td>
                            <div class="flex items-center justify-center gap-2">
                                @php
                                    $rolePayload = [
                                        'id' => $role->id,
                                        'name' => $role->name,
                                        'description' => $role->description,
                                        'permissions' => $role->isFullAccess() ? array_keys($permissionLabels) : ($role->permissions ?? []),
                                        'full_access' => $role->isFullAccess(),
                                        'is_system' => $role->is_system,
                                    ];
                                @endphp
                                <button type="button"
                                    data-role="{{ e(json_encode($rolePayload, JSON_UNESCAPED_UNICODE)) }}"
                                    onclick="openEditRoleModal(JSON.parse(this.dataset.role))"
                                    class="btn btn-icon btn-sm btn-secondary" title="Edit">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                </button>

                                @if($role->slug !== 'super_admin')
                                <form id="toggle-role-{{ $role->id }}" method="POST" action="{{ route('roles.toggle-status', $role) }}">
                                    @csrf @method('PATCH')
                                </form>
                                <button type="button"
                                    @click="confirm('toggle-role-{{ $role->id }}', '{{ $role->is_active ? 'Nonaktifkan' : 'Aktifkan' }} Role', 'Yakin ingin {{ $role->is_active ? 'menonaktifkan' : 'mengaktifkan' }} role ini?')"
                                    class="btn btn-icon btn-sm {{ $role->is_active ? 'bg-amber-50 text-amber-600 hover:bg-amber-100' : 'bg-green-50 text-green-600 hover:bg-green-100' }}"
                                    title="{{ $role->is_active ? 'Nonaktifkan' : 'Aktifkan' }}">
                                    @if($role->is_active)
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/></svg>
                                    @else
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    @endif
                                </button>
                                @endif

                                @unless($role->is_system)
                                <form id="del-role-{{ $role->id }}" method="POST" action="{{ route('roles.destroy', $role) }}">
                                    @csrf @method('DELETE')
                                </form>
                                <button type="button"
                                    @click="confirm('del-role-{{ $role->id }}', 'Hapus Role', 'Hapus role {{ addslashes($role->name) }}? Pastikan tidak ada user terkait.')"
                                    class="btn btn-icon btn-sm bg-red-50 text-red-500 hover:bg-red-100"
                                    title="Hapus">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                </button>
                                @endunless
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center text-gray-400 py-10">Belum ada role.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($roles->hasPages())
        <div class="p-4 border-t border-gray-100">{{ $roles->links() }}</div>
        @endif
    </div>
</div>

{{-- Modal Tambah / Edit --}}
<div id="modalRole" class="fixed inset-0 z-50 hidden">
    <div class="absolute inset-0 bg-black/40" onclick="closeRoleModal()"></div>
    <div class="relative mx-auto mt-10 w-full max-w-2xl px-4">
        <div class="bg-white rounded-2xl shadow-xl border border-gray-100 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
                <h3 id="modalRoleTitle" class="font-bold text-lg text-gray-800">Tambah Role</h3>
                <button type="button" onclick="closeRoleModal()" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <form id="formRole" method="POST" action="{{ route('roles.store') }}" class="p-6 space-y-4">
                @csrf
                <input type="hidden" name="_method" id="roleMethod" value="POST">

                <div>
                    <label class="form-label font-bold text-gray-700">Nama Role <span class="text-red-500">*</span></label>
                    <input type="text" name="name" id="roleName" class="form-input" required placeholder="Contoh: Supervisor Gudang">
                </div>
                <div>
                    <label class="form-label font-bold text-gray-700">Deskripsi</label>
                    <textarea name="description" id="roleDescription" rows="2" class="form-input" placeholder="Ringkas fungsi role ini"></textarea>
                </div>

                <div>
                    <div class="flex items-center justify-between mb-2">
                        <label class="form-label font-bold text-gray-700 mb-0">Hak Akses Modul</label>
                        <p id="roleFullAccessHint" class="hidden text-xs text-rose-600 font-medium">Kepala IT selalu punya akses penuh</p>
                    </div>
                    <div id="rolePermissionsWrap" class="grid grid-cols-1 sm:grid-cols-2 gap-2 p-3 rounded-xl border border-gray-100 bg-gray-50/80">
                        @foreach($permissionLabels as $key => $label)
                        <label class="flex items-start gap-2 text-sm text-gray-700 cursor-pointer p-2 rounded-lg hover:bg-white">
                            <input type="checkbox" name="permissions[]" value="{{ $key }}" class="role-perm mt-0.5 text-emerald-600 focus:ring-emerald-500 rounded">
                            <span>{{ $label }}</span>
                        </label>
                        @endforeach
                    </div>
                </div>

                <div class="flex justify-end gap-3 pt-2 border-t border-gray-100">
                    <button type="button" onclick="closeRoleModal()" class="btn btn-secondary">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan Role</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function openAddRoleModal() {
    document.getElementById('modalRoleTitle').textContent = 'Tambah Role';
    document.getElementById('formRole').action = @json(route('roles.store'));
    document.getElementById('roleMethod').value = 'POST';
    document.getElementById('roleName').value = '';
    document.getElementById('roleDescription').value = '';
    document.getElementById('roleFullAccessHint').classList.add('hidden');
    document.querySelectorAll('.role-perm').forEach(el => {
        el.checked = false;
        el.disabled = false;
    });
    document.getElementById('modalRole').classList.remove('hidden');
}

function openEditRoleModal(role) {
    document.getElementById('modalRoleTitle').textContent = 'Edit Role';
    document.getElementById('formRole').action = `/roles/${role.id}`;
    document.getElementById('roleMethod').value = 'PUT';
    document.getElementById('roleName').value = role.name || '';
    document.getElementById('roleDescription').value = role.description || '';
    const hint = document.getElementById('roleFullAccessHint');
    const perms = role.permissions || [];
    document.querySelectorAll('.role-perm').forEach(el => {
        el.checked = role.full_access || perms.includes(el.value);
        el.disabled = !!role.full_access;
    });
    if (role.full_access) hint.classList.remove('hidden');
    else hint.classList.add('hidden');
    document.getElementById('modalRole').classList.remove('hidden');
}

function closeRoleModal() {
    document.getElementById('modalRole').classList.add('hidden');
}
</script>
@endsection
