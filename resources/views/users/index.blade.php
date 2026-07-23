@extends('layouts.app')

@section('title', 'User')
@section('page-title', 'Manajemen User')

@section('breadcrumb')
<svg class="w-3 h-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
<span class="text-gray-600 font-medium">Manajemen User</span>
@endsection

@section('content')
<div class="animate-in users-page">
    {{-- Header --}}
    <div class="page-header">
        <div>
            <h2 class="page-title">Manajemen Pengguna</h2>
            <p class="page-subtitle">Kelola akun, foto profil, dan hak akses pengguna sistem</p>
        </div>
        <a wire:navigate href="{{ route('users.create') }}" class="btn btn-primary shadow-sm flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Tambah User
        </a>
    </div>

    {{-- Stats --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 sm:gap-4 mb-5">
        <div class="card p-4 flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center shadow-sm">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
            </div>
            <div>
                <p class="text-2xl font-bold text-gray-900">{{ $users->total() }}</p>
                <p class="text-xs text-gray-500">Total User</p>
            </div>
        </div>
        <div class="card p-4 flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-green-50 text-green-600 flex items-center justify-center shadow-sm">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
            <div>
                <p class="text-2xl font-bold text-gray-900">{{ $users->where('is_active', true)->count() }}</p>
                <p class="text-xs text-gray-500">User Aktif</p>
            </div>
        </div>
        <div class="card p-4 flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-red-50 text-red-600 flex items-center justify-center shadow-sm">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/></svg>
            </div>
            <div>
                <p class="text-2xl font-bold text-gray-900">{{ $users->where('is_active', false)->count() }}</p>
                <p class="text-xs text-gray-500">User Nonaktif</p>
            </div>
        </div>
    </div>

    {{-- Filters --}}
    <form method="GET" class="card p-4 mb-5 flex flex-wrap items-center gap-3 sm:gap-4 bg-white border border-gray-100 rounded-2xl shadow-sm">
        <div class="flex-1 min-w-[200px] relative">
            <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama, email, username..." class="form-input pl-9 text-sm">
        </div>
        <div class="w-full sm:w-52">
            <select name="role_id" class="form-input text-sm">
                <option value="">— Semua Role —</option>
                @foreach($roles as $role)
                <option value="{{ $role->id }}" {{ request('role_id') == $role->id ? 'selected' : '' }}>{{ $role->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="w-full sm:w-40">
            <select name="status" class="form-input text-sm">
                <option value="">— Semua Status —</option>
                <option value="1" {{ request('status') === '1' ? 'selected' : '' }}>Aktif</option>
                <option value="0" {{ request('status') === '0' ? 'selected' : '' }}>Non-aktif</option>
            </select>
        </div>
        <button type="submit" class="btn btn-primary btn-sm flex items-center gap-1.5 shadow-sm">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2a1 1 0 01-.293.707L13 13.414V19a1 1 0 01-.553.894l-4 2A1 1 0 017 21v-7.586L3.293 6.707A1 1 0 013 6V4z"/></svg>
            Filter
        </button>
        @if(request()->hasAny(['search', 'role_id', 'status']))
        <a wire:navigate href="{{ route('users.index') }}" class="btn btn-secondary btn-sm">Reset</a>
        @endif
    </form>

    {{-- Table --}}
    <div class="card overflow-hidden mb-2">
        <div class="overflow-x-auto">
            <table class="data-table w-full">
                <thead>
                    <tr>
                        <th class="w-12">#</th>
                        <th>Nama</th>
                        <th>Username</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Status</th>
                        <th>Login Terakhir</th>
                        <th class="text-center w-36">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($users as $index => $user)
                    <tr x-data="{ active: {{ $user->is_active ? 'true' : 'false' }} }" class="align-middle">
                        <td class="text-gray-400 text-sm">{{ $users->firstItem() + $index }}</td>
                        <td>
                            <div class="flex items-center gap-3 min-w-[200px]">
                                <div class="w-10 h-10 rounded-full bg-gradient-to-br from-emerald-500 to-teal-700 flex items-center justify-center flex-shrink-0 shadow-sm overflow-hidden ring-2 ring-emerald-50">
                                    @if($user->avatarUrl())
                                        <img src="{{ $user->avatarUrl() }}" alt="{{ $user->name }}" class="w-full h-full object-cover">
                                    @else
                                        <span class="text-white text-xs font-bold tracking-wide">{{ $user->initials() }}</span>
                                    @endif
                                </div>
                                <div class="min-w-0">
                                    <p class="font-bold text-gray-800 leading-snug truncate" title="{{ $user->name }}">{{ $user->name }}</p>
                                    @if($user->id === auth()->id())
                                    <span class="badge bg-blue-50 text-blue-700 text-[10px] font-bold border border-blue-200 mt-0.5">Anda</span>
                                    @endif
                                </div>
                            </div>
                        </td>
                        <td class="text-gray-600 font-mono text-sm whitespace-nowrap">{{ $user->username ?? '-' }}</td>
                        <td class="text-gray-600 text-sm">
                            <span class="block max-w-[200px] truncate" title="{{ $user->email }}">{{ $user->email }}</span>
                        </td>
                        <td>
                            @php
                            $roleColors = [
                                'super_admin' => 'bg-rose-50 text-rose-700 border-rose-200',
                                'staff_it' => 'bg-violet-50 text-violet-700 border-violet-200',
                                'kepala_operasional' => 'bg-orange-50 text-orange-700 border-orange-200',
                                'staff_operasional' => 'bg-amber-50 text-amber-700 border-amber-200',
                                'admin_keuangan' => 'bg-blue-50 text-blue-700 border-blue-200',
                                'kasir' => 'bg-green-50 text-green-700 border-green-200',
                                'mitra' => 'bg-slate-50 text-slate-700 border-slate-200',
                            ];
                            $roleColor = $roleColors[$user->role?->slug] ?? 'bg-gray-50 text-gray-700 border-gray-200';
                            @endphp
                            <span class="inline-flex max-w-[180px] whitespace-normal text-left leading-tight badge {{ $roleColor }} border text-[11px] font-semibold py-1">
                                {{ $user->role?->name ?? 'No Role' }}
                            </span>
                        </td>
                        <td>
                            <template x-if="active">
                                <span class="badge bg-green-50 text-green-700 border border-green-200 text-[11px] font-semibold inline-flex items-center">
                                    <span class="w-1.5 h-1.5 rounded-full bg-green-500 mr-1.5 animate-pulse"></span>
                                    Aktif
                                </span>
                            </template>
                            <template x-if="!active">
                                <span class="badge bg-red-50 text-red-700 border border-red-200 text-[11px] font-semibold inline-flex items-center" x-cloak>
                                    <span class="w-1.5 h-1.5 rounded-full bg-red-500 mr-1.5"></span>
                                    Nonaktif
                                </span>
                            </template>
                        </td>
                        <td class="text-gray-400 text-sm whitespace-nowrap">
                            {{ $user->last_login ? $user->last_login->locale('id')->diffForHumans() : 'Belum pernah' }}
                        </td>
                        <td>
                            <div class="flex items-center justify-center gap-1.5">
                                <a wire:navigate href="{{ route('users.edit', $user) }}"
                                   class="btn btn-secondary btn-icon btn-sm"
                                   title="Edit User">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                </a>

                                @if($user->id !== auth()->id())
                                <button
                                    type="button"
                                    @click="
                                        if (confirm('Yakin ingin ' + (active ? 'menonaktifkan' : 'mengaktifkan') + ' user {{ $user->name }}?')) {
                                            fetch('{{ route('users.toggle-status', $user) }}', {
                                                method: 'PATCH',
                                                headers: {
                                                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                                    'Accept': 'application/json'
                                                }
                                            })
                                            .then(res => res.json())
                                            .then(data => {
                                                if (data.success) {
                                                    active = data.is_active;
                                                    window.dispatchEvent(new CustomEvent('toast', { detail: { type: 'success', message: data.message } }));
                                                } else {
                                                    window.dispatchEvent(new CustomEvent('toast', { detail: { type: 'error', message: data.message } }));
                                                }
                                            })
                                            .catch(err => {
                                                window.dispatchEvent(new CustomEvent('toast', { detail: { type: 'error', message: 'Terjadi kesalahan!' } }));
                                            });
                                        }
                                    "
                                    class="btn btn-icon btn-sm"
                                    :class="active ? 'bg-amber-50 text-amber-600 hover:bg-amber-100 border border-amber-200' : 'bg-green-50 text-green-600 hover:bg-green-100 border border-green-200'"
                                    :title="active ? 'Nonaktifkan' : 'Aktifkan'">
                                    <svg x-show="active" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/></svg>
                                    <svg x-show="!active" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" x-cloak><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                </button>

                                <form id="del-{{ $user->id }}" method="POST" action="{{ route('users.destroy', $user) }}">
                                    @csrf @method('DELETE')
                                </form>
                                <button
                                    type="button"
                                    @click="confirm('del-{{ $user->id }}', 'Hapus User', 'Yakin ingin menghapus user {{ $user->name }}?')"
                                    class="btn btn-icon btn-sm bg-red-50 text-red-500 hover:bg-red-100 border border-red-200"
                                    title="Hapus User">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                </button>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center py-12 text-gray-400">
                            <svg class="w-12 h-12 mx-auto mb-3 text-gray-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                            </svg>
                            <p class="font-medium">Belum ada user yang sesuai</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($users->hasPages())
        <div class="px-5 py-4 border-t border-gray-100 bg-gray-50/50">
            {{ $users->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
