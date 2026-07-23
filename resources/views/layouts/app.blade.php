<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') · Almaira</title>
    <link rel="icon" type="image/png" href="{{ asset('assets/images/logo-apotek.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('assets/images/logo-apotek.png') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body
    class="h-full bg-background"
    x-data="{ ...toastManager(), ...sidebar(), ...confirmModal() }"
    x-on:sidebar-close-mobile.window="if (window.innerWidth < 1024) collapsed = true"
    data-authenticated="true"
>

{{-- ═══ TOAST CONTAINER ═══════════════════════════════════════════ --}}
<div class="fixed top-4 right-4 z-[100] flex flex-col gap-2 pointer-events-none">
    {{-- Server-side flash messages --}}
    @foreach([
        'toast_success' => 'success',
        'toast_error'   => 'error',
        'toast_warning' => 'warning',
        'toast_info'    => 'info',
    ] as $flashKey => $flashType)
    @if(session($flashKey))
    <div
        x-data="{ show: true }"
        x-show="show"
        x-init="setTimeout(() => show = false, 5000)"
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0 translate-x-8"
        x-transition:enter-end="opacity-100 translate-x-0"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-end="opacity-0 translate-x-8"
        class="toast toast-{{ $flashType }} pointer-events-auto animate-in"
    >
        <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            @if($flashType === 'success')
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
            @elseif($flashType === 'error')
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            @elseif($flashType === 'warning')
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
            @else
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M12 2a10 10 0 100 20A10 10 0 0012 2z"/>
            @endif
        </svg>
        <span class="flex-1 text-sm">{{ session($flashKey) }}</span>
        <button @click="show = false" class="opacity-70 hover:opacity-100 flex-shrink-0 pointer-events-auto">
            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z"/></svg>
        </button>
    </div>
    @endif
    @endforeach

    {{-- Alpine.js dynamic toasts --}}
    <template x-for="toast in toasts" :key="toast.id">
        <div
            x-show="true"
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0 translate-x-8"
            x-transition:enter-end="opacity-100 translate-x-0"
            x-transition:leave="transition ease-in duration-200"
            x-transition:leave-end="opacity-0 translate-x-8"
            :class="'toast toast-' + toast.type + ' pointer-events-auto'"
        >
            <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
            </svg>
            <span class="flex-1 text-sm" x-text="toast.message"></span>
            <button @click="remove(toast.id)" class="opacity-70 hover:opacity-100 pointer-events-auto">
                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z"/></svg>
            </button>
        </div>
    </template>
</div>

{{-- ═══ CONFIRM DELETE MODAL ═══════════════════════════════════════ --}}
<div x-show="open" class="modal-backdrop" x-cloak>
    <div
        class="modal-box max-w-md p-6"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 scale-95"
        x-transition:enter-end="opacity-100 scale-100"
    >
        <div class="flex items-start gap-4">
            <div class="flex-shrink-0 w-12 h-12 rounded-full bg-red-100 flex items-center justify-center">
                <svg class="w-6 h-6 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                </svg>
            </div>
            <div class="flex-1">
                <h3 class="text-lg font-bold text-gray-900" x-text="title"></h3>
                <p class="text-sm text-gray-500 mt-1" x-text="message"></p>
            </div>
        </div>
        <div class="flex gap-3 mt-6 justify-end">
            <button @click="open = false" class="btn btn-secondary">Batal</button>
            <button @click="execute()" class="btn btn-danger">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                Ya, Hapus
            </button>
        </div>
    </div>
</div>

{{-- Global Loading Progress Bar --}}
<div id="global-loading-bar" class="fixed top-0 left-0 h-[3px] bg-gradient-to-r from-emerald-500 via-teal-400 to-emerald-600 z-[99999] pointer-events-none" style="width: 0%; opacity: 0; box-shadow: 0 0 10px rgba(16, 185, 129, 0.5)"></div>

{{-- Navigation Loading Overlay — dinonaktifkan (SPA tidak butuh overlay gelap) --}}
<div id="nav-loading-overlay" style="display:none !important;" aria-hidden="true"></div>
<style>
    @keyframes nav-spin {
        from { transform: rotate(0deg); }
        to   { transform: rotate(360deg); }
    }
</style>



{{-- ═══ APP LAYOUT ══════════════════════════════════════════════════ --}}
<div class="flex h-full min-h-screen">

    {{-- SIDEBAR: tidak di-persist (Alpine body scope). Highlight di-sync JS saat SPA navigate. --}}
    @include('layouts.sidebar')

    {{-- Mobile Sidebar Backdrop --}}
    <div 
        x-show="!collapsed" 
        @click="collapsed = true" 
        class="fixed inset-0 bg-black/40 backdrop-blur-sm z-30 lg:hidden"
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-end="opacity-0"
        x-cloak
    ></div>

    {{-- MAIN CONTENT — margin via CSS (.app-main), Alpine hanya toggle collapsed --}}
    <div class="app-main" :class="{ 'is-sidebar-collapsed': collapsed }">
        {{-- TOPBAR --}}
        @include('layouts.topbar')

        {{-- PAGE CONTENT --}}
        <main @class([
            'flex-1 min-w-0',
            'pos-main' => $__env->hasSection('dense-main'),
            'p-4 sm:p-6 pb-28 overflow-x-hidden' => ! $__env->hasSection('dense-main'),
        ])>
            {{-- Breadcrumb (disembunyikan di halaman dense seperti kasir) --}}
            @hasSection('breadcrumb')
                @unless($__env->hasSection('dense-main'))
                <nav class="flex items-center gap-2 text-sm text-gray-400 mb-4">
                    <a wire:navigate href="{{ route('dashboard') }}" class="hover:text-primary-600 transition-colors">Dashboard</a>
                    @yield('breadcrumb')
                </nav>
                @endunless
            @endif

            @yield('content')
        </main>

        {{-- FOOTER --}}
        <footer class="app-footer px-4 sm:px-6 py-3 border-t border-slate-100/80 bg-white/95 backdrop-blur-md shadow-[0_-2px_10px_rgba(0,0,0,0.04)]">
            <div class="flex flex-col sm:flex-row items-center justify-center sm:justify-between gap-1.5 text-center sm:text-left">
                <p class="text-[10px] sm:text-[11px] text-slate-500 leading-relaxed">
                    © {{ date('Y') }} <span class="font-bold text-slate-700">PT Nur Madani Farma</span>
                    · <span class="font-semibold text-slate-800">Apotek Almaira</span> Banjarbaru
                </p>
                <p class="text-[10px] text-slate-400 whitespace-nowrap">Apotek Almaira v1.0 · Kalimantan Selatan</p>
            </div>
        </footer>
    </div>
</div>

    <script>
    // Progress bar / sync dipindah ke resources/js/app.js (init sekali).
    // Script ini hanya fallback ringan jika app.js belum siap — juga guarded once.
    (function () {
        if (window.__almairaLayoutNavFallback) return;
        window.__almairaLayoutNavFallback = true;

        document.addEventListener('livewire:navigated', () => {
            if (typeof window.syncSidebarActive === 'function') window.syncSidebarActive();
            if (typeof window.restoreSidebarScroll === 'function') {
                requestAnimationFrame(() => window.restoreSidebarScroll());
            }
            if (typeof window.hideNavOverlay === 'function') window.hideNavOverlay();
            if (typeof window.finishNavProgress === 'function') window.finishNavProgress();
        });
    })();
    </script>

    @stack('scripts')
    <script>
        window.AlmairaLiveSyncConfig = { url: @json(route('catalog.live')) };
    </script>
    <style>
        .live-flash { border-radius: 0.35rem; transition: background-color .7s ease; background-color: rgba(167, 243, 208, 0.55) !important; }
    </style>
    <script src="{{ asset('js/product-live-sync.js') }}?v=1" defer></script>
    @livewireScripts
</body>
</html>

