@extends('layouts.app')
@section('title', 'Tambah User')
@section('page-title', 'Tambah User')

@section('breadcrumb')
<svg class="w-3 h-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
<a wire:navigate href="{{ route('users.index') }}" class="hover:text-primary-600 transition-colors">Manajemen User</a>
<svg class="w-3 h-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
<span class="text-gray-600 font-medium">Tambah</span>
@endsection

@section('content')
<div class="animate-in max-w-2xl mx-auto">
    <div class="page-header mb-6">
        <div>
            <h2 class="page-title text-2xl font-bold">Tambah User Baru</h2>
            <p class="page-subtitle text-gray-500">Buat akun pengguna baru dengan hak akses yang ditentukan</p>
        </div>
        <a wire:navigate href="{{ route('users.index') }}" class="btn btn-secondary flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            Kembali
        </a>
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

    <div class="card p-6 bg-white border border-gray-100 rounded-2xl shadow-sm">
        <form method="POST" action="{{ route('users.store') }}">
            @csrf

            <div class="space-y-5">
                {{-- Nama Lengkap --}}
                <div>
                    <label class="form-label font-bold text-gray-700">Nama Lengkap <span class="text-red-500">*</span></label>
                    <input type="text" name="name" value="{{ old('name') }}" class="form-input {{ $errors->has('name') ? 'error' : '' }}" placeholder="Contoh: Alyaiqlima, S.Farm." required>
                    @error('name')<p class="form-error">{{ $message }}</p>@enderror
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    {{-- Username --}}
                    <div>
                        <label class="form-label font-bold text-gray-700">Username <span class="text-red-500">*</span></label>
                        <input type="text" name="username" value="{{ old('username') }}" class="form-input {{ $errors->has('username') ? 'error' : '' }}" placeholder="Contoh: alya" required>
                        @error('username')<p class="form-error">{{ $message }}</p>@enderror
                    </div>

                    {{-- Email --}}
                    <div>
                        <label class="form-label font-bold text-gray-700">Email <span class="text-red-500">*</span></label>
                        <input type="email" name="email" value="{{ old('email') }}" class="form-input {{ $errors->has('email') ? 'error' : '' }}" placeholder="Contoh: alya@apotekalmaira.com" required>
                        @error('email')<p class="form-error">{{ $message }}</p>@enderror
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4" x-data="{ showPass: false }">
                    {{-- Password --}}
                    <div>
                        <label class="form-label font-bold text-gray-700">Password <span class="text-red-500">*</span></label>
                        <div class="relative">
                            <input :type="showPass ? 'text' : 'password'" name="password" class="form-input pr-10 {{ $errors->has('password') ? 'error' : '' }}" placeholder="Minimal 8 karakter" required>
                            <button type="button" @click="showPass = !showPass" class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600">
                                <svg x-show="!showPass" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                <svg x-show="showPass" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" x-cloak><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/></svg>
                            </button>
                        </div>
                        @error('password')<p class="form-error">{{ $message }}</p>@enderror
                    </div>

                    {{-- Konfirmasi Password --}}
                    <div>
                        <label class="form-label font-bold text-gray-700">Konfirmasi Password <span class="text-red-500">*</span></label>
                        <input :type="showPass ? 'text' : 'password'" name="password_confirmation" class="form-input" placeholder="Ulangi password" required>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    {{-- Role --}}
                    <div>
                        <label class="form-label font-bold text-gray-700">Role / Hak Akses <span class="text-red-500">*</span></label>
                        <select name="role_id" class="form-input" required>
                            <option value="">— Pilih Role —</option>
                            @foreach($roles as $role)
                            <option value="{{ $role->id }}" {{ old('role_id') == $role->id ? 'selected' : '' }}>{{ $role->name }}</option>
                            @endforeach
                        </select>
                        @error('role_id')<p class="form-error">{{ $message }}</p>@enderror
                    </div>

                    {{-- Status Aktif --}}
                    <div>
                        <label class="form-label font-bold text-gray-700">Status Akun</label>
                        <div class="flex items-center gap-6 mt-3">
                            <label class="flex items-center gap-2 cursor-pointer text-sm text-gray-700">
                                <input type="radio" name="is_active" value="1" class="text-emerald-600 focus:ring-emerald-500" checked>
                                <span>Aktif</span>
                            </label>
                            <label class="flex items-center gap-2 cursor-pointer text-sm text-gray-700">
                                <input type="radio" name="is_active" value="0" class="text-emerald-600 focus:ring-emerald-500">
                                <span>Non-aktif</span>
                            </label>
                        </div>
                        @error('is_active')<p class="form-error">{{ $message }}</p>@enderror
                    </div>
                </div>

                {{-- Action Buttons --}}
                <div class="flex justify-end gap-3 border-t border-gray-100 pt-5 mt-6">
                    <a wire:navigate href="{{ route('users.index') }}" class="btn btn-secondary">Batal</a>
                    <button type="submit" class="btn btn-primary shadow-md flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"/></svg>
                        Simpan User
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
