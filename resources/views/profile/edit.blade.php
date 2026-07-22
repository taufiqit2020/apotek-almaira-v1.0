@extends('layouts.app')
@section('title', 'Profil')
@section('page-title', 'Profil Saya')

@section('breadcrumb')
<svg class="w-3 h-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
<span class="text-gray-600 font-medium">Profil Saya</span>
@endsection

@section('content')
<script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.1/cropper.min.js"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.1/cropper.min.css">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@600;700;800;900&display=swap" rel="stylesheet">
<script>
    window.avatarUpload = () => ({
        imageUrl: null,
        rawImageUrl: null,
        cropper: null,
        showModal: false,
        _initTimer: null,
        fileChosen(event) {
            const file = event.target.files[0];
            if (!file) return;
            if (!file.type.startsWith('image/')) {
                alert('File harus berupa gambar (JPG, PNG, atau WEBP).');
                event.target.value = '';
                return;
            }
            const reader = new FileReader();
            reader.onload = (e) => {
                this.destroyCropper();
                this.rawImageUrl = e.target.result;
                this.showModal = true;
                this.$nextTick(() => this.initCropper());
            };
            reader.readAsDataURL(file);
        },
        initCropper() {
            clearTimeout(this._initTimer);
            // Tunggu modal selesai tampil agar ukuran container benar (tidak terpotong)
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

                if (image.complete) {
                    start();
                } else {
                    image.onload = () => start();
                }
            }, 80);
        },
        destroyCropper() {
            clearTimeout(this._initTimer);
            if (this.cropper) {
                this.cropper.destroy();
                this.cropper = null;
            }
        },
        zoomIn() { if (this.cropper) this.cropper.zoom(0.1); },
        zoomOut() { if (this.cropper) this.cropper.zoom(-0.1); },
        rotate() { if (this.cropper) this.cropper.rotate(90); },
        cancelCrop() {
            this.showModal = false;
            this.destroyCropper();
            const fileInput = document.getElementById('avatar-input');
            if (fileInput) fileInput.value = '';
            this.rawImageUrl = null;
        },
        applyCrop() {
            if (!this.cropper) return;
            const canvas = this.cropper.getCroppedCanvas({
                width: 400,
                height: 400,
                imageSmoothingEnabled: true,
                imageSmoothingQuality: 'high',
            });
            if (!canvas) return;
            this.imageUrl = canvas.toDataURL('image/jpeg', 0.92);
            document.getElementById('cropped-avatar-input').value = this.imageUrl;
            const fileInput = document.getElementById('avatar-input');
            if (fileInput) fileInput.value = '';
            this.showModal = false;
            this.destroyCropper();
            this.rawImageUrl = null;
        }
    });
    window.alpineComponents = window.alpineComponents || {};
    window.alpineComponents.avatarUpload = window.avatarUpload;
    if (window.Alpine && typeof window.Alpine.data === 'function') {
        window.Alpine.data('avatarUpload', window.avatarUpload);
    }
</script>

<style>
    .profile-page {
        --profile-ink: #0f172a;
        --profile-muted: #64748b;
        --profile-accent: #059669;
        --profile-accent-soft: #ecfdf5;
    }
    .profile-hero {
        background:
            radial-gradient(ellipse 90% 120% at 100% -10%, rgba(52, 211, 153, 0.35), transparent 55%),
            radial-gradient(ellipse 70% 90% at 0% 110%, rgba(14, 165, 233, 0.22), transparent 50%),
            linear-gradient(135deg, #064e3b 0%, #047857 42%, #0f766e 78%, #065f46 100%);
    }
    .profile-hero-grid {
        background-image:
            linear-gradient(rgba(255,255,255,0.05) 1px, transparent 1px),
            linear-gradient(90deg, rgba(255,255,255,0.05) 1px, transparent 1px);
        background-size: 26px 26px;
        mask-image: linear-gradient(to bottom, black 15%, transparent 92%);
    }
    .profile-font-banner {
        font-family: 'Montserrat', 'Plus Jakarta Sans', system-ui, sans-serif;
    }
    .profile-hero-logo {
        width: 3rem;
        height: 3rem;
        border-radius: 0.8rem;
        background: #fff;
        padding: 0.3rem;
        box-shadow: 0 8px 24px rgba(0,0,0,0.22), 0 0 0 2px rgba(255,255,255,0.4);
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }
    @media (min-width: 768px) {
        .profile-hero-logo {
            width: 4rem;
            height: 4rem;
            border-radius: 0.95rem;
            padding: 0.4rem;
        }
    }
    .profile-hero-text {
        text-align: center;
        width: 100%;
        max-width: 36rem;
        margin: 0 auto;
        padding: 0 0.25rem;
    }
    .profile-hero-title {
        color: #ffffff;
        font-weight: 900;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        line-height: 1.25;
        text-shadow: 0 2px 8px rgba(0, 0, 0, 0.35);
        white-space: normal;
        word-break: keep-all;
        overflow-wrap: normal;
        hyphens: none;
    }
    .profile-hero-title-main {
        font-size: clamp(0.95rem, 2.8vw, 1.35rem);
    }
    .profile-hero-title-sub {
        font-size: clamp(1.05rem, 3.2vw, 1.55rem);
        color: #ecfeff;
        text-shadow: 0 2px 10px rgba(0, 0, 0, 0.4);
    }
    .profile-hero-title-city {
        font-size: clamp(0.72rem, 2vw, 0.95rem);
        font-weight: 800;
        letter-spacing: 0.18em;
        color: #fcd34d;
        text-shadow: 0 1px 6px rgba(0, 0, 0, 0.35);
        margin-top: 0.35rem;
    }
    .profile-hero-divider {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 0.55rem;
        margin: 0.4rem auto 0.45rem;
        max-width: 16rem;
    }
    .profile-hero-divider span {
        height: 1.5px;
        flex: 1;
        min-width: 1.5rem;
        background: linear-gradient(90deg, transparent, rgba(251, 191, 36, 0.95));
    }
    .profile-hero-divider span:last-child {
        background: linear-gradient(90deg, rgba(251, 191, 36, 0.95), transparent);
    }
    .profile-card {
        background: #fff;
        border: 1px solid #eef2f7;
        border-radius: 1.25rem;
        box-shadow: 0 1px 2px rgba(15, 23, 42, 0.04), 0 8px 24px -12px rgba(15, 23, 42, 0.12);
        transition: box-shadow 0.2s ease, transform 0.2s ease;
    }
    .profile-card:hover {
        box-shadow: 0 2px 4px rgba(15, 23, 42, 0.05), 0 14px 32px -14px rgba(15, 23, 42, 0.16);
    }
    .profile-card-hero {
        overflow: hidden;
        border-radius: 1.25rem 1.25rem 0 0;
    }
    .profile-avatar-wrap {
        position: relative;
        width: 5.25rem;
        height: 5.25rem;
        flex-shrink: 0;
    }
    @media (min-width: 768px) {
        .profile-avatar-wrap {
            width: 5.75rem;
            height: 5.75rem;
        }
    }
    .profile-avatar-ring {
        width: 4.25rem;
        height: 4.25rem;
        border-radius: 9999px;
        overflow: hidden;
        margin: 0 auto;
        border: 3px solid #fff;
        box-shadow: 0 0 0 2px #d1fae5, 0 4px 14px rgba(15, 23, 42, 0.12);
        background: linear-gradient(135deg, #10b981, #0d9488);
        color: #fff;
        font-size: 1.35rem;
        font-weight: 800;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    @media (min-width: 768px) {
        .profile-avatar-ring {
            width: 4.75rem;
            height: 4.75rem;
            font-size: 1.5rem;
        }
    }
    .profile-avatar-btn {
        position: absolute;
        right: 0;
        bottom: 0;
        width: 2.15rem;
        height: 2.15rem;
        border-radius: 9999px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: linear-gradient(135deg, #059669, #10b981);
        color: #fff;
        border: 2.5px solid #fff;
        box-shadow: 0 4px 12px rgba(5, 150, 105, 0.4), 0 0 0 1px rgba(5, 150, 105, 0.15);
        cursor: pointer;
        z-index: 5;
        transition: transform 0.15s ease, box-shadow 0.15s ease, background 0.15s ease;
    }
    .profile-avatar-btn:hover {
        transform: scale(1.08);
        background: linear-gradient(135deg, #047857, #059669);
        box-shadow: 0 6px 16px rgba(5, 150, 105, 0.5);
    }
    .profile-avatar-btn:focus {
        outline: none;
        box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.4), 0 4px 12px rgba(5, 150, 105, 0.4);
    }
    .profile-avatar-btn svg {
        width: 1.05rem;
        height: 1.05rem;
        flex-shrink: 0;
        stroke-width: 2.25;
    }
    .profile-stat {
        display: flex;
        align-items: flex-start;
        gap: 0.75rem;
        padding: 0.85rem 0.9rem;
        border-radius: 0.9rem;
        background: #f8fafc;
        border: 1px solid #f1f5f9;
    }
    .profile-stat-icon {
        width: 2.25rem;
        height: 2.25rem;
        border-radius: 0.7rem;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }
    .profile-field label {
        display: block;
        font-size: 0.75rem;
        font-weight: 700;
        color: #334155;
        letter-spacing: 0.01em;
        margin-bottom: 0.4rem;
    }
    .profile-field .form-input {
        border-radius: 0.85rem;
        border-color: #e2e8f0;
        background: #fbfdff;
        transition: border-color 0.15s, box-shadow 0.15s, background 0.15s;
    }
    .profile-field .form-input:focus {
        background: #fff;
        border-color: #34d399;
        box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.15);
    }
    .profile-section-icon {
        width: 2.5rem;
        height: 2.5rem;
        border-radius: 0.85rem;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }
    @keyframes profile-fade-up {
        from { opacity: 0; transform: translateY(10px); }
        to { opacity: 1; transform: translateY(0); }
    }
    .profile-anim { animation: profile-fade-up 0.45s ease both; }
    .profile-anim-delay-1 { animation-delay: 0.06s; }
    .profile-anim-delay-2 { animation-delay: 0.12s; }
    .profile-anim-delay-3 { animation-delay: 0.18s; }
    .profile-actions {
        position: relative;
        z-index: 5;
        pointer-events: auto;
    }
    .profile-actions .btn {
        min-width: 140px;
        pointer-events: auto;
    }
    [x-cloak] { display: none !important; }

    /* Area cropper — tinggi tetap agar Cropper.js tidak terpotong */
    .profile-cropper-box {
        width: 100%;
        height: min(58vh, 360px);
        min-height: 260px;
        background: #0f172a;
        border-radius: 0.9rem;
        overflow: hidden;
        position: relative;
    }
    .profile-cropper-box img {
        display: block;
        max-width: 100%;
    }
    .profile-cropper-box .cropper-container {
        max-width: 100% !important;
    }
    .profile-cropper-modal {
        max-height: min(92vh, 640px);
    }
</style>

<div class="profile-page animate-in max-w-5xl mx-auto">
    <div class="page-header mb-6 profile-anim">
        <div>
            <h2 class="page-title text-2xl font-bold text-slate-800 tracking-tight">Pengaturan Profil</h2>
            <p class="page-subtitle text-slate-500 font-medium mt-0.5">Kelola identitas, data akun, dan keamanan login Anda</p>
        </div>
    </div>

    @if(session('toast_success'))
    <div class="mb-6 p-4 bg-emerald-50 border border-emerald-200/80 rounded-2xl text-emerald-800 text-sm flex items-center gap-3 shadow-sm profile-anim">
        <span class="w-9 h-9 rounded-xl bg-emerald-100 text-emerald-600 flex items-center justify-center flex-shrink-0">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        </span>
        <span class="font-semibold">{{ session('toast_success') }}</span>
    </div>
    @endif

    @if($errors->any())
    <div class="mb-6 p-4 bg-rose-50 border border-rose-200/80 rounded-2xl text-rose-800 text-sm shadow-sm profile-anim">
        <div class="flex items-center gap-2 mb-2 font-bold">
            <svg class="w-5 h-5 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
            <span>Mohon perbaiki kesalahan berikut:</span>
        </div>
        <ul class="list-disc pl-5 font-medium space-y-1">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <form method="POST" action="{{ route('profile.update') }}" enctype="multipart/form-data" x-data="avatarUpload()">
        @csrf
        @method('PUT')

        {{-- Hero branding + profil ringkas --}}
        <div class="profile-card mb-6 profile-anim profile-anim-delay-1">
            <div class="profile-card-hero profile-hero relative">
                <div class="profile-hero-grid absolute inset-0 opacity-80"></div>
                <div class="absolute inset-0 bg-gradient-to-t from-black/25 via-transparent to-black/10"></div>

                <div class="relative z-10 px-4 sm:px-6 md:px-8 py-5 md:py-6">
                    {{-- Mobile: logo di atas agar teks tidak terjepit --}}
                    <div class="flex md:hidden items-center justify-center gap-4 mb-3">
                        <div class="profile-hero-logo">
                            @if(file_exists(public_path('assets/images/logo-ptnmf.png')))
                                <img src="{{ asset('assets/images/logo-ptnmf.png') }}" alt="Logo PT Nur Madani Farma" class="w-full h-full object-contain">
                            @else
                                <span class="text-[10px] font-black text-emerald-700">NMF</span>
                            @endif
                        </div>
                        <div class="profile-hero-logo">
                            @if(file_exists(public_path('assets/images/logo-apotek.png')))
                                <img src="{{ asset('assets/images/logo-apotek.png') }}" alt="Logo Apotek Almaira" class="w-full h-full object-contain">
                            @else
                                <span class="text-[9px] font-black text-sky-700">APO</span>
                            @endif
                        </div>
                    </div>

                    {{-- Desktop: logo kiri-kanan + teks tengah lebar penuh --}}
                    <div class="flex items-center justify-center gap-4 md:gap-6">
                        <div class="profile-hero-logo hidden md:flex">
                            @if(file_exists(public_path('assets/images/logo-ptnmf.png')))
                                <img src="{{ asset('assets/images/logo-ptnmf.png') }}" alt="Logo PT Nur Madani Farma" class="w-full h-full object-contain">
                            @else
                                <span class="text-[10px] font-black text-emerald-700">NMF</span>
                            @endif
                        </div>

                        <div class="profile-hero-text profile-font-banner">
                            <p class="profile-hero-title profile-hero-title-main">
                                PT Nur Madani Farma
                            </p>
                            <div class="profile-hero-divider" aria-hidden="true">
                                <span></span>
                                <svg class="w-2.5 h-2.5 text-amber-300 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path d="M10 2l2.4 5.8H19l-4.8 3.5 1.8 5.7L10 13.4l-6 3.6 1.8-5.7L1 7.8h6.6z"/></svg>
                                <span></span>
                            </div>
                            <p class="profile-hero-title profile-hero-title-sub">
                                Apotek Almaira
                            </p>
                            <p class="profile-hero-title profile-hero-title-city">
                                Banjarbaru
                            </p>
                        </div>

                        <div class="profile-hero-logo hidden md:flex">
                            @if(file_exists(public_path('assets/images/logo-apotek.png')))
                                <img src="{{ asset('assets/images/logo-apotek.png') }}" alt="Logo Apotek Almaira" class="w-full h-full object-contain">
                            @else
                                <span class="text-[9px] font-black text-sky-700">APO</span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            {{-- Foto + identitas --}}
            <div class="px-5 md:px-7 py-5 md:py-6">
                <div class="flex flex-col sm:flex-row items-center sm:items-center gap-4 sm:gap-5">
                    <div class="profile-avatar-wrap" title="Ubah foto profil">
                        <div class="profile-avatar-ring">
                            <template x-if="imageUrl">
                                <img :src="imageUrl" class="w-full h-full object-cover" alt="Foto Profil">
                            </template>
                            <template x-if="!imageUrl">
                                @if($user->avatar && file_exists(public_path($user->avatar)))
                                    <img src="{{ asset($user->avatar) }}" class="w-full h-full object-cover" alt="Foto Profil">
                                @else
                                    <span class="select-none">{{ mb_strtoupper(mb_substr($user->name, 0, 1)) }}</span>
                                @endif
                            </template>
                        </div>
                        <button type="button"
                                @click="document.getElementById('avatar-input').click()"
                                class="profile-avatar-btn"
                                aria-label="Ubah foto profil"
                                title="Ubah foto profil">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/>
                            </svg>
                        </button>
                        <input type="file" id="avatar-input" class="hidden" accept="image/jpeg,image/png,image/jpg,image/webp" @change="fileChosen($event)">
                        <input type="hidden" id="cropped-avatar-input" name="cropped_avatar" value="">
                    </div>

                    <div class="flex-1 text-center sm:text-left min-w-0">
                        <div class="flex flex-col sm:flex-row sm:items-center gap-2 sm:gap-2.5">
                            <h3 class="text-lg md:text-xl font-bold text-slate-800 tracking-tight truncate" title="{{ $user->name }}">
                                {{ $user->name }}
                            </h3>
                            <span class="self-center sm:self-auto inline-flex items-center gap-1 text-[10px] font-bold text-emerald-800 bg-emerald-50 border border-emerald-100 px-2 py-0.5 rounded-full uppercase tracking-wider">
                                {{ $user->role?->name ?? 'User' }}
                            </span>
                        </div>
                        <div class="mt-1.5 flex flex-col sm:flex-row sm:flex-wrap items-center sm:items-start justify-center sm:justify-start gap-x-3 gap-y-1 text-xs text-slate-500">
                            <span class="inline-flex items-center gap-1 font-medium truncate max-w-full">
                                <svg class="w-3.5 h-3.5 text-slate-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207"/></svg>
                                {{ $user->email }}
                            </span>
                            <span class="inline-flex items-center gap-1 font-medium">
                                <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                Terdaftar {{ $user->created_at->format('d M Y') }}
                            </span>
                        </div>
                        <p class="mt-1.5 text-[10px] text-slate-400 font-medium">Ubah foto lewat ikon kamera · JPG/PNG/WEBP maks. 2MB</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
            {{-- System info --}}
            <aside class="lg:col-span-4 space-y-6 profile-anim profile-anim-delay-2">
                <div class="profile-card p-5 md:p-6">
                    <div class="flex items-center gap-3 mb-5">
                        <div class="profile-section-icon bg-slate-900 text-white">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                        </div>
                        <div>
                            <h4 class="text-sm font-bold text-slate-800">Informasi Sistem</h4>
                            <p class="text-[11px] text-slate-400 font-medium">Ringkasan akun login</p>
                        </div>
                    </div>

                    <div class="space-y-2.5">
                        <div class="profile-stat">
                            <div class="profile-stat-icon bg-emerald-100 text-emerald-700">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            </div>
                            <div class="min-w-0 flex-1">
                                <p class="text-[11px] font-semibold text-slate-400 uppercase tracking-wide">Status Akun</p>
                                <p class="text-sm font-bold text-emerald-700 mt-0.5">Aktif</p>
                            </div>
                        </div>
                        <div class="profile-stat">
                            <div class="profile-stat-icon bg-sky-100 text-sky-700">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                            </div>
                            <div class="min-w-0 flex-1">
                                <p class="text-[11px] font-semibold text-slate-400 uppercase tracking-wide">Username</p>
                                <p class="text-sm font-bold text-slate-800 mt-0.5 font-mono truncate">{{ $user->username }}</p>
                            </div>
                        </div>
                        <div class="profile-stat">
                            <div class="profile-stat-icon bg-violet-100 text-violet-700">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            </div>
                            <div class="min-w-0 flex-1">
                                <p class="text-[11px] font-semibold text-slate-400 uppercase tracking-wide">Login Terakhir</p>
                                <p class="text-sm font-bold text-slate-800 mt-0.5">
                                    {{ $user->last_login ? $user->last_login->translatedFormat('d M Y, H:i') : '—' }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="rounded-2xl border border-emerald-100 bg-gradient-to-br from-emerald-50 to-teal-50/60 p-5">
                    <div class="flex items-start gap-3">
                        <div class="w-9 h-9 rounded-xl bg-white text-emerald-600 shadow-sm flex items-center justify-center flex-shrink-0">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        </div>
                        <div>
                            <p class="text-sm font-bold text-emerald-900">Tips keamanan</p>
                            <p class="text-xs text-emerald-800/80 mt-1 leading-relaxed font-medium">
                                Gunakan password unik minimal 8 karakter dan perbarui foto profil agar akun mudah dikenali di sistem.
                            </p>
                        </div>
                    </div>
                </div>
            </aside>

            {{-- Forms --}}
            <div class="lg:col-span-8 space-y-6 profile-anim profile-anim-delay-3">
                <div class="profile-card p-5 md:p-6">
                    <div class="flex items-center gap-3 pb-4 mb-5 border-b border-slate-100">
                        <div class="profile-section-icon bg-emerald-50 text-emerald-600">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                        </div>
                        <div>
                            <h3 class="text-base font-bold text-slate-800">Detail Profil</h3>
                            <p class="text-[11px] text-slate-400 font-medium">Data yang tampil di seluruh aplikasi</p>
                        </div>
                    </div>

                    <div class="space-y-4">
                        <div class="profile-field">
                            <label>Nama Lengkap <span class="text-rose-500">*</span></label>
                            <input type="text" name="name" value="{{ old('name', $user->name) }}" class="form-input {{ $errors->has('name') ? 'error' : '' }}" required>
                            @error('name')<p class="form-error mt-1 text-xs text-rose-500 font-medium">{{ $message }}</p>@enderror
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="profile-field">
                                <label>Username <span class="text-rose-500">*</span></label>
                                <input type="text" name="username" value="{{ old('username', $user->username) }}" class="form-input {{ $errors->has('username') ? 'error' : '' }}" required>
                                @error('username')<p class="form-error mt-1 text-xs text-rose-500 font-medium">{{ $message }}</p>@enderror
                            </div>
                            <div class="profile-field">
                                <label>Email <span class="text-rose-500">*</span></label>
                                <input type="email" name="email" value="{{ old('email', $user->email) }}" class="form-input {{ $errors->has('email') ? 'error' : '' }}" required>
                                @error('email')<p class="form-error mt-1 text-xs text-rose-500 font-medium">{{ $message }}</p>@enderror
                            </div>
                        </div>
                    </div>
                </div>

                <div class="profile-card p-5 md:p-6">
                    <div class="flex items-center gap-3 pb-4 mb-5 border-b border-slate-100">
                        <div class="profile-section-icon bg-amber-50 text-amber-600">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                        </div>
                        <div>
                            <h3 class="text-base font-bold text-slate-800">Ubah Password</h3>
                            <p class="text-[11px] text-slate-400 font-medium">Kosongkan jika tidak ingin mengubah password</p>
                        </div>
                    </div>

                    <div class="space-y-4">
                        <div class="profile-field">
                            <label>Password Lama</label>
                            <input type="password" name="old_password" class="form-input {{ $errors->has('old_password') ? 'error' : '' }}" placeholder="Masukkan password lama untuk verifikasi" autocomplete="current-password">
                            @error('old_password')<p class="form-error mt-1 text-xs text-rose-500 font-medium">{{ $message }}</p>@enderror
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="profile-field">
                                <label>Password Baru</label>
                                <input type="password" name="password" class="form-input {{ $errors->has('password') ? 'error' : '' }}" placeholder="Min. 8 karakter" autocomplete="new-password">
                                @error('password')<p class="form-error mt-1 text-xs text-rose-500 font-medium">{{ $message }}</p>@enderror
                            </div>
                            <div class="profile-field">
                                <label>Konfirmasi Password Baru</label>
                                <input type="password" name="password_confirmation" class="form-input" placeholder="Ulangi password baru" autocomplete="new-password">
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>

        {{-- Action bar: di luar kartu agar tidak terpotong / tertutup --}}
        <div class="profile-actions mt-2 mb-8 flex flex-col-reverse sm:flex-row sm:items-center sm:justify-end gap-3 p-4 md:p-5 rounded-2xl border border-slate-200 bg-white shadow-sm">
            <a href="{{ route('dashboard') }}"
               class="btn btn-secondary inline-flex items-center justify-center gap-2 py-2.5 px-6 text-sm font-semibold rounded-xl no-underline">
                Batal
            </a>
            <button type="submit"
                    class="btn btn-primary inline-flex items-center justify-center gap-2 py-2.5 px-7 text-sm font-semibold rounded-xl cursor-pointer">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                Simpan Perubahan
            </button>
        </div>

        {{-- Modal crop di-teleport ke body agar tidak terpotong oleh transform/overflow parent --}}
        <template x-teleport="body">
            <div x-show="showModal"
                 x-cloak
                 class="fixed inset-0 z-[200] flex items-center justify-center p-3 sm:p-6 bg-slate-900/60 backdrop-blur-sm"
                 @keydown.escape.window="if (showModal) cancelCrop()"
                 @click.self="cancelCrop()">
                <div @click.stop
                     class="profile-cropper-modal bg-white rounded-2xl w-full max-w-lg shadow-2xl border border-slate-100 flex flex-col overflow-hidden"
                     x-show="showModal"
                     x-transition:enter="transition ease-out duration-200"
                     x-transition:enter-start="opacity-0 translate-y-2 scale-[0.98]"
                     x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                     x-transition:leave="transition ease-in duration-150"
                     x-transition:leave-start="opacity-100"
                     x-transition:leave-end="opacity-0">
                    <div class="px-5 py-4 border-b border-slate-100 flex justify-between items-center bg-slate-50/90 flex-shrink-0">
                        <h3 class="text-sm font-bold text-slate-800 flex items-center gap-2">
                            <span class="w-8 h-8 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                            </span>
                            Atur Posisi Foto
                        </h3>
                        <button type="button" @click="cancelCrop()" class="text-slate-400 hover:text-slate-600 transition-colors p-1.5 rounded-lg hover:bg-slate-100 cursor-pointer">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>

                    <div class="p-4 sm:p-5 flex-1 min-h-0">
                        <div class="profile-cropper-box ring-1 ring-slate-800">
                            <img x-ref="cropperImage" :src="rawImageUrl" alt="Foto untuk dipotong">
                        </div>
                        <p class="text-[11px] text-slate-400 font-medium text-center mt-3 leading-relaxed">
                            Seret, perbesar, atau putar foto lalu sesuaikan kotak potong agar wajah berada di tengah.
                        </p>
                    </div>

                    <div class="px-4 sm:px-5 py-3.5 border-t border-slate-100 flex flex-wrap justify-between items-center bg-slate-50/90 gap-3 flex-shrink-0">
                        <div class="flex gap-1.5">
                            <button type="button" @click="zoomIn()" class="p-2 bg-white border border-slate-200 rounded-lg hover:bg-emerald-50 hover:text-emerald-700 hover:border-emerald-200 transition-colors cursor-pointer" title="Zoom masuk">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0zM10 7v3m0 0v3m0-3h3m-3 0H7"/></svg>
                            </button>
                            <button type="button" @click="zoomOut()" class="p-2 bg-white border border-slate-200 rounded-lg hover:bg-emerald-50 hover:text-emerald-700 hover:border-emerald-200 transition-colors cursor-pointer" title="Zoom keluar">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0zM13 10H7"/></svg>
                            </button>
                            <button type="button" @click="rotate()" class="p-2 bg-white border border-slate-200 rounded-lg hover:bg-emerald-50 hover:text-emerald-700 hover:border-emerald-200 transition-colors cursor-pointer" title="Putar 90°">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                            </button>
                        </div>
                        <div class="flex gap-2">
                            <button type="button" @click="cancelCrop()" class="py-2 px-3.5 text-xs font-bold rounded-lg border border-slate-200 bg-white text-slate-600 hover:bg-slate-50 cursor-pointer">
                                Batal
                            </button>
                            <button type="button" @click="applyCrop()" class="py-2 px-4 text-xs font-bold rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white shadow-md shadow-emerald-600/20 cursor-pointer">
                                Terapkan
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </template>
    </form>
</div>
@endsection















