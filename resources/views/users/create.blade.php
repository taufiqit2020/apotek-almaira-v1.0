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
<script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.1/cropper.min.js"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.1/cropper.min.css">
<style>
    .user-cropper-box {
        width: 100%;
        height: min(58vh, 360px);
        min-height: 260px;
        background: #0f172a;
        border-radius: 0.9rem;
        overflow: hidden;
        position: relative;
    }
    .user-cropper-box img { display: block; max-width: 100%; }
    .user-cropper-box .cropper-container { max-width: 100% !important; }
    .user-cropper-modal { max-height: min(92vh, 640px); }
</style>
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
            roleId: @js(old('role_id', '')),
            roles: @js($roleOptions),
            preview: null,
            showCropModal: false,
            rawImageUrl: null,
            cropper: null,
            _initTimer: null,
            get selected() { return this.roles.find(r => String(r.id) === String(this.roleId)) || null },
            fileChosen(e) {
                const file = e.target.files?.[0];
                if (!file) return;
                if (!file.type.startsWith('image/')) {
                    alert('File harus berupa gambar (JPG, PNG, atau WEBP).');
                    e.target.value = '';
                    return;
                }
                const reader = new FileReader();
                reader.onload = (ev) => {
                    this.destroyCropper();
                    this.rawImageUrl = ev.target.result;
                    this.showCropModal = true;
                    this.$nextTick(() => this.initCropper());
                };
                reader.readAsDataURL(file);
            },
            initCropper() {
                clearTimeout(this._initTimer);
                this._initTimer = setTimeout(() => {
                    const image = this.$refs.cropperImage;
                    if (!image || !this.rawImageUrl) return;
                    const start = () => {
                        this.destroyCropper();
                        this.cropper = new Cropper(image, {
                            aspectRatio: 1,
                            viewMode: 1,
                            dragMode: 'move',
                            autoCropArea: 0.85,
                            responsive: true,
                            restore: false,
                            checkOrientation: true,
                            background: false,
                            modal: true,
                            guides: true,
                            center: true,
                            highlight: true,
                            cropBoxMovable: true,
                            cropBoxResizable: true,
                            toggleDragModeOnDblclick: false,
                        });
                    };
                    if (image.complete) start();
                    else image.onload = () => start();
                }, 80);
            },
            destroyCropper() {
                clearTimeout(this._initTimer);
                if (this.cropper) { this.cropper.destroy(); this.cropper = null; }
            },
            zoomIn() { this.cropper?.zoom(0.1); },
            zoomOut() { this.cropper?.zoom(-0.1); },
            rotate() { this.cropper?.rotate(90); },
            cancelCrop() {
                this.showCropModal = false;
                this.destroyCropper();
                this.rawImageUrl = null;
                if (this.$refs.avatarInput) this.$refs.avatarInput.value = '';
            },
            applyCrop() {
                if (!this.cropper) return;
                const canvas = this.cropper.getCroppedCanvas({ width: 400, height: 400, imageSmoothingEnabled: true, imageSmoothingQuality: 'high' });
                if (!canvas) return;
                this.preview = canvas.toDataURL('image/jpeg', 0.92);
                this.$refs.croppedAvatar.value = this.preview;
                if (this.$refs.avatarInput) this.$refs.avatarInput.value = '';
                this.showCropModal = false;
                this.destroyCropper();
                this.rawImageUrl = null;
            }
         }">
        <form method="POST" action="{{ route('users.store') }}" enctype="multipart/form-data">
            @csrf

            <div class="space-y-5">
                {{-- Foto Profil --}}
                <div class="flex flex-col sm:flex-row items-start sm:items-center gap-4 p-4 rounded-xl border border-gray-100 bg-slate-50/70">
                    <div class="w-24 h-24 rounded-2xl overflow-hidden border-2 border-white shadow-md bg-emerald-100 flex items-center justify-center text-emerald-700 text-sm font-semibold shrink-0 ring-2 ring-emerald-100">
                        <template x-if="preview">
                            <img :src="preview" alt="Preview" class="w-full h-full object-cover">
                        </template>
                        <template x-if="!preview">
                            <span>Foto</span>
                        </template>
                    </div>
                    <div class="flex-1 min-w-0">
                        <label class="form-label font-bold text-gray-700 mb-1">Foto Profil <span class="text-gray-400 font-normal">(opsional)</span></label>
                        <p class="text-xs text-gray-500 mb-3">Pilih foto, lalu atur crop & posisi. JPG/PNG/WEBP maks. 2 MB.</p>
                        <button type="button" class="btn btn-secondary btn-sm" @click="$refs.avatarInput.click()">
                            Pilih Foto
                        </button>
                        <input type="file" x-ref="avatarInput" class="hidden" accept="image/jpeg,image/png,image/jpg,image/webp" @change="fileChosen($event)">
                        <input type="hidden" name="cropped_avatar" x-ref="croppedAvatar" value="">
                        @error('avatar')<p class="form-error mt-2">{{ $message }}</p>@enderror
                    </div>
                </div>

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
                        <select name="role_id" x-model="roleId" class="form-input" required>
                            <option value="">— Pilih Role —</option>
                            @foreach($roles as $role)
                            <option value="{{ $role->id }}" {{ old('role_id') == $role->id ? 'selected' : '' }}>{{ $role->name }}</option>
                            @endforeach
                        </select>
                        @error('role_id')<p class="form-error">{{ $message }}</p>@enderror
                        <p class="text-xs text-gray-400 mt-1">Kelola daftar role di <a href="{{ route('roles.index') }}" class="text-emerald-600 hover:underline" wire:navigate>Master Role / Hak Akses</a></p>
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
                        Simpan User
                    </button>
                </div>
            </div>
        </form>

        <template x-teleport="body">
            <div x-show="showCropModal" x-cloak class="fixed inset-0 z-[200] flex items-center justify-center p-3 sm:p-6 bg-slate-900/60 backdrop-blur-sm"
                 @keydown.escape.window="if (showCropModal) cancelCrop()" @click.self="cancelCrop()">
                <div @click.stop class="user-cropper-modal bg-white rounded-2xl w-full max-w-lg shadow-2xl border border-slate-100 flex flex-col overflow-hidden"
                     x-show="showCropModal"
                     x-transition>
                    <div class="px-5 py-4 border-b border-slate-100 flex justify-between items-center bg-slate-50/90">
                        <h3 class="text-sm font-bold text-slate-800">Atur Crop & Posisi Foto</h3>
                        <button type="button" @click="cancelCrop()" class="text-slate-400 hover:text-slate-600 p-1.5 rounded-lg hover:bg-slate-100">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>
                    <div class="p-4 sm:p-5">
                        <div class="user-cropper-box ring-1 ring-slate-800">
                            <img x-ref="cropperImage" :src="rawImageUrl" alt="Crop">
                        </div>
                        <p class="text-[11px] text-slate-400 font-medium text-center mt-3">Seret, zoom, atau putar agar posisi wajah tepat.</p>
                    </div>
                    <div class="px-4 sm:px-5 py-3.5 border-t border-slate-100 flex flex-wrap justify-between items-center bg-slate-50/90 gap-3">
                        <div class="flex gap-1.5">
                            <button type="button" @click="zoomIn()" class="p-2 bg-white border border-slate-200 rounded-lg hover:bg-emerald-50" title="Zoom +"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0zM10 7v3m0 0v3m0-3h3m-3 0H7"/></svg></button>
                            <button type="button" @click="zoomOut()" class="p-2 bg-white border border-slate-200 rounded-lg hover:bg-emerald-50" title="Zoom -"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0zM13 10H7"/></svg></button>
                            <button type="button" @click="rotate()" class="p-2 bg-white border border-slate-200 rounded-lg hover:bg-emerald-50" title="Putar"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg></button>
                        </div>
                        <div class="flex gap-2">
                            <button type="button" @click="cancelCrop()" class="py-2 px-3.5 text-xs font-bold rounded-lg border border-slate-200 bg-white">Batal</button>
                            <button type="button" @click="applyCrop()" class="py-2 px-4 text-xs font-bold rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white">Terapkan</button>
                        </div>
                    </div>
                </div>
            </div>
        </template>
    </div>
</div>
@endsection
