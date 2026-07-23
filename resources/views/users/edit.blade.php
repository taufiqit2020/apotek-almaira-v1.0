@extends('layouts.app')
@section('title', 'Edit User')
@section('page-title', 'Edit User')

@section('breadcrumb')
<svg class="w-3 h-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
<a wire:navigate href="{{ route('users.index') }}" class="hover:text-primary-600 transition-colors">Manajemen User</a>
<svg class="w-3 h-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
<span class="text-gray-600 font-medium">Edit</span>
@endsection

@section('content')
<div class="animate-in max-w-2xl mx-auto" x-data="{ showResetModal: false }">
    <div class="page-header mb-6">
        <div>
            <h2 class="page-title text-2xl font-bold">Edit Pengguna: {{ $user->name }}</h2>
            <p class="page-subtitle text-gray-500">Perbarui profil, hak akses, atau status akun pengguna</p>
        </div>
        <div class="flex items-center gap-3">
            <button type="button" @click="showResetModal = true" class="btn btn-secondary !bg-amber-50 !text-amber-700 hover:!bg-amber-100 flex items-center gap-2 border border-amber-200">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m-5 4a5 5 0 01-10 0 5 5 0 0110 0zM19 9V7m0 2v2m0-2h2m-2 0h-2"/></svg>
                Reset Password
            </button>
            <a wire:navigate href="{{ route('users.index') }}" class="btn btn-secondary flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                Kembali
            </a>
        </div>
    </div>

    @if($errors->any())
    <div class="mb-6 p-4 bg-red-50 border border-red-200 rounded-xl text-red-700 text-sm">
        <ul class="list-disc pl-5">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    @php
        $roleOptions = $roles->map(fn ($r) => [
            'id' => $r->id,
            'name' => $r->name,
            'description' => $r->description,
            'labels' => $r->permissionLabels(),
        ])->values();
    @endphp
    <div class="card p-6 bg-white border border-gray-100 rounded-2xl shadow-sm"
         x-data="{
            roleId: @js(old('role_id', $user->role_id)),
            roles: @js($roleOptions),
            get selected() { return this.roles.find(r => String(r.id) === String(this.roleId)) || null }
         }">
        <form method="POST" action="{{ route('users.update', $user) }}">
            @csrf
            @method('PUT')

            <div class="space-y-5">
                {{-- Nama Lengkap --}}
                <div>
                    <label class="form-label font-bold text-gray-700">Nama Lengkap <span class="text-red-500">*</span></label>
                    <input type="text" name="name" value="{{ old('name', $user->name) }}" class="form-input {{ $errors->has('name') ? 'error' : '' }}" required>
                    @error('name')<p class="form-error">{{ $message }}</p>@enderror
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    {{-- Username --}}
                    <div>
                        <label class="form-label font-bold text-gray-700">Username <span class="text-red-500">*</span></label>
                        <input type="text" name="username" value="{{ old('username', $user->username) }}" class="form-input {{ $errors->has('username') ? 'error' : '' }}" required>
                        @error('username')<p class="form-error">{{ $message }}</p>@enderror
                    </div>

                    {{-- Email --}}
                    <div>
                        <label class="form-label font-bold text-gray-700">Email <span class="text-red-500">*</span></label>
                        <input type="email" name="email" value="{{ old('email', $user->email) }}" class="form-input {{ $errors->has('email') ? 'error' : '' }}" required>
                        @error('email')<p class="form-error">{{ $message }}</p>@enderror
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    {{-- Role --}}
                    <div>
                        <label class="form-label font-bold text-gray-700">Role / Hak Akses <span class="text-red-500">*</span></label>
                        <select name="role_id" x-model="roleId" class="form-input" required>
                            <option value="">— Pilih Role —</option>
                            @foreach($roles as $role)
                            <option value="{{ $role->id }}" {{ old('role_id', $user->role_id) == $role->id ? 'selected' : '' }}>{{ $role->name }}</option>
                            @endforeach
                        </select>
                        @error('role_id')<p class="form-error">{{ $message }}</p>@enderror
                        <p class="text-xs text-gray-400 mt-1">Kelola daftar role di <a href="{{ route('roles.index') }}" class="text-emerald-600 hover:underline" wire:navigate>Master Role / Hak Akses</a></p>
                    </div>

                    {{-- Status Aktif --}}
                    <div>
                        <label class="form-label font-bold text-gray-700">Status Akun</label>
                        @if($user->id === auth()->id())
                        <div class="mt-3 text-sm text-amber-600 bg-amber-50 p-2 rounded-lg border border-amber-100 flex items-center gap-2">
                            <svg class="w-4 h-4 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
                            <span>Anda tidak dapat menonaktifkan akun sendiri yang sedang aktif.</span>
                        </div>
                        <input type="hidden" name="is_active" value="1">
                        @else
                        <div class="flex items-center gap-6 mt-3">
                            <label class="flex items-center gap-2 cursor-pointer text-sm text-gray-700">
                                <input type="radio" name="is_active" value="1" class="text-emerald-600 focus:ring-emerald-500" {{ old('is_active', $user->is_active) ? 'checked' : '' }}>
                                <span>Aktif</span>
                            </label>
                            <label class="flex items-center gap-2 cursor-pointer text-sm text-gray-700">
                                <input type="radio" name="is_active" value="0" class="text-emerald-600 focus:ring-emerald-500" {{ !old('is_active', $user->is_active) ? 'checked' : '' }}>
                                <span>Non-aktif</span>
                            </label>
                        </div>
                        @endif
                        @error('is_active')<p class="form-error">{{ $message }}</p>@enderror
                    </div>
                </div>

                <div x-show="selected" x-cloak class="rounded-xl border border-emerald-100 bg-emerald-50/60 p-4">
                    <p class="text-sm font-semibold text-emerald-800" x-text="selected?.name"></p>
                    <p class="text-xs text-emerald-700/80 mt-1" x-text="selected?.description || 'Hak akses sesuai konfigurasi role'"></p>
                    <div class="flex flex-wrap gap-1.5 mt-3">
                        <template x-for="label in (selected?.labels || [])" :key="label">
                            <span class="inline-flex text-[11px] px-2 py-0.5 rounded-full bg-white text-emerald-700 border border-emerald-200" x-text="label"></span>
                        </template>
                    </div>
                </div>

                {{-- Action Buttons --}}
                <div class="flex justify-end gap-3 border-t border-gray-100 pt-5 mt-6">
                    <a wire:navigate href="{{ route('users.index') }}" class="btn btn-secondary">Batal</a>
                    <button type="submit" class="btn btn-primary shadow-md flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"/></svg>
                        Perbarui User
                    </button>
                </div>
            </div>
        </form>
    </div>

    {{-- Reset Password Modal --}}
    <div class="modal-backdrop" x-show="showResetModal" x-cloak>
        <div class="modal-box max-w-md p-6" @click.away="showResetModal = false">
            <h3 class="text-lg font-bold text-gray-900 mb-3 flex items-center gap-2">
                <svg class="w-5 h-5 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m-5 4a5 5 0 01-10 0 5 5 0 0110 0zM19 9V7m0 2v2m0-2h2m-2 0h-2"/></svg>
                Reset Password User
            </h3>
            <p class="text-sm text-gray-500 mb-5">
                Masukkan password baru untuk user <strong>{{ $user->name }}</strong>. User akan login menggunakan password baru ini setelah disimpan.
            </p>

            <form method="POST" action="{{ route('users.update', $user) }}" x-data="{ showPassModal: false }">
                @csrf
                @method('PUT')
                
                {{-- Keep other values unchanged --}}
                <input type="hidden" name="name" value="{{ $user->name }}">
                <input type="hidden" name="username" value="{{ $user->username }}">
                <input type="hidden" name="email" value="{{ $user->email }}">
                <input type="hidden" name="role_id" value="{{ $user->role_id }}">
                <input type="hidden" name="is_active" value="{{ $user->is_active ? 1 : 0 }}">

                <div class="space-y-4">
                    <div>
                        <label class="form-label text-xs font-semibold">Password Baru</label>
                        <div class="relative">
                            <input :type="showPassModal ? 'text' : 'password'" name="password" class="form-input text-sm" placeholder="Minimal 8 karakter" required>
                            <button type="button" @click="showPassModal = !showPassModal" class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600">
                                <svg x-show="!showPassModal" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                <svg x-show="showPassModal" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" x-cloak><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/></svg>
                            </button>
                        </div>
                    </div>

                    <div>
                        <label class="form-label text-xs font-semibold">Konfirmasi Password Baru</label>
                        <input :type="showPassModal ? 'text' : 'password'" name="password_confirmation" class="form-input text-sm" placeholder="Ulangi password" required>
                    </div>
                </div>

                <div class="flex justify-end gap-3 mt-6 border-t border-gray-100 pt-4">
                    <button type="button" @click="showResetModal = false" class="btn btn-secondary text-xs">Batal</button>
                    <button type="submit" class="btn btn-primary !bg-amber-600 hover:!bg-amber-700 text-xs shadow-md">
                        Simpan Password Baru
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
