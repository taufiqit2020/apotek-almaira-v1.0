<!DOCTYPE html>
<html lang="id" class="h-full min-h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Katalog produk {{ $apotekName ?? 'Apotek Almaira' }} — cek ketersediaan &amp; harga obat terkini.">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'E-Catalog') · Almaira</title>
    @yield('meta')
    <link rel="icon" type="image/jpeg" href="{{ asset('assets/images/logodashboard.jpeg') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@600;700;800;900&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
    <style>
        .font-banner { font-family: 'Montserrat', 'Plus Jakarta Sans', sans-serif; letter-spacing: 0.02em; }
        .font-body  { font-family: 'Plus Jakarta Sans', 'Inter', sans-serif; }
        [x-cloak] { display: none !important; }

        /* Footer selalu tampil di bawah layar */
        html, body {
            min-height: 100%;
            min-height: 100dvh;
        }
        body.catalog-portal {
            display: flex;
            flex-direction: column;
            min-height: 100vh;
            min-height: 100dvh;
            padding-bottom: 5.25rem;
        }
        body.catalog-portal main {
            flex: 1 0 auto;
            width: 100%;
        }
        body.catalog-portal .catalog-site-footer {
            position: fixed;
            left: 0;
            right: 0;
            bottom: 0;
            z-index: 40;
            box-shadow: 0 -4px 24px rgba(15, 23, 42, 0.06);
        }
    </style>
</head>
<body class="catalog-portal min-h-screen bg-slate-50 font-body" x-data="{ mobileFilterOpen: false }">

{{-- ═══ TOAST CONTAINER ═══════════════════════════════════════════ --}}
<div class="fixed top-4 right-4 z-[100] flex flex-col gap-2 pointer-events-none">
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
        x-init="setTimeout(() => show = false, 4500)"
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0 translate-x-8"
        x-transition:enter-end="opacity-100 translate-x-0"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-end="opacity-0 translate-x-8"
        class="toast toast-{{ $flashType }} pointer-events-auto"
    >
        <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            @if($flashType === 'success')
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
            @elseif($flashType === 'error')
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            @else
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M12 2a10 10 0 100 20A10 10 0 0012 2z"/>
            @endif
        </svg>
        <span class="flex-1 text-sm">{{ session($flashKey) }}</span>
        <button type="button" @click="show = false" class="opacity-70 hover:opacity-100 flex-shrink-0">×</button>
    </div>
    @endif
    @endforeach
</div>

{{-- ═══ HEADER / KOP APOTEK ═══════════════════════════════════════ --}}
<header class="relative z-20 bg-white border-b border-slate-100 shadow-sm">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 py-3 flex items-center gap-3">
        <a href="{{ route('home') }}" class="flex items-center gap-2 shrink-0 hover:opacity-90 transition-opacity">
            <div class="w-10 h-10 rounded-xl bg-white border border-emerald-100 shadow-sm p-1 flex items-center justify-center">
                <img src="{{ asset('assets/images/logo-ptnmf.png') }}" alt="Logo PT" class="w-full h-full object-contain">
            </div>
            <div class="w-10 h-10 rounded-xl bg-white border border-emerald-100 shadow-sm p-1 flex items-center justify-center">
                <img src="{{ asset('assets/images/logo-apotek.png') }}" alt="Logo Apotek" class="w-full h-full object-contain">
            </div>
        </a>
        <a href="{{ route('home') }}" class="min-w-0 flex-1 hover:opacity-90 transition-opacity">
            <h1 class="font-banner text-sm sm:text-base font-extrabold text-slate-800 leading-tight truncate uppercase tracking-wide">
                PT Nur Madani Farma <span class="text-slate-300 font-normal normal-case">—</span> Apotek Almaira Banjarbaru
            </h1>
            <p class="text-[10px] sm:text-[11px] text-slate-500 truncate mt-0.5">{{ $apotekAddress ?? 'Jl. Panglima Batur No. 16, Kel. Komet, Kec. Banjarbaru Utara, Kota Banjarbaru, Kalsel 70714' }}</p>
        </a>
        <div class="flex items-center gap-2 shrink-0">
            @auth
                @if(auth()->user()->isMitra())
                @php $cartCount = \App\Services\PartnerCartService::count(); @endphp
                <a href="{{ route('mitra.cart') }}"
                   class="relative inline-flex items-center px-2.5 sm:px-3 py-2 rounded-xl border border-slate-200 bg-white text-xs font-bold text-slate-700 hover:bg-slate-50 transition-colors">
                    Keranjang
                    <span id="mitraCartBadge"
                          class="ml-1 inline-flex items-center justify-center min-w-[18px] h-[18px] px-1 rounded-full bg-emerald-600 text-white text-[10px] {{ $cartCount > 0 ? '' : 'hidden' }}">{{ $cartCount > 0 ? $cartCount : '' }}</span>
                </a>
                <a href="{{ route('mitra.orders.index') }}"
                   class="inline-flex items-center px-2.5 sm:px-3 py-2 rounded-xl border border-slate-200 bg-white text-xs font-bold text-slate-700 hover:bg-slate-50 transition-colors">
                    PO
                </a>
                <a href="{{ route('mitra.account') }}"
                   class="inline-flex items-center px-2.5 sm:px-3 py-2 rounded-xl text-xs font-bold transition-colors {{ request()->routeIs('mitra.account') ? 'bg-emerald-600 text-white border border-emerald-600 shadow-sm shadow-emerald-600/20' : 'border border-slate-200 bg-white text-slate-700 hover:bg-slate-50' }}">
                    Akun
                </a>
                @endif
            @else
                <a href="{{ route('home') }}"
                   class="hidden sm:inline-flex items-center px-3 py-2 rounded-xl border border-slate-200 bg-white text-xs font-bold text-slate-700 hover:bg-slate-50 transition-colors">
                    Beranda
                </a>
                <a href="{{ route('mitra.login') }}"
                   class="inline-flex items-center px-3 py-2 rounded-xl border border-slate-200 bg-white text-xs font-bold text-slate-700 hover:bg-slate-50 transition-colors">
                    Login
                </a>
                <a href="{{ route('mitra.register') }}"
                   class="inline-flex items-center px-3 py-2 rounded-xl bg-slate-800 hover:bg-slate-900 text-white text-xs font-bold transition-colors">
                    Daftar
                </a>
            @endauth
            @if(!empty($apotekPhone))
            <a href="https://wa.me/{{ preg_replace('/^0/', '62', preg_replace('/\D/', '', $apotekPhone)) }}"
               target="_blank" rel="noopener"
               class="hidden sm:inline-flex items-center gap-2 px-3.5 py-2 rounded-xl bg-emerald-500 hover:bg-emerald-600 text-white text-xs font-bold shadow-md shadow-emerald-500/20 transition-colors shrink-0">
                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.149-.15.35-.4.5-.6.15-.2.2-.35.3-.6.1-.24.05-.45-.05-.6-.1-.15-.628-1.517-.859-2.076-.229-.559-.462-.483-.639-.492-.15-.007-.35-.007-.55-.007-.198 0-.5.075-.762.375-.283.325-1.14 1.116-1.14 2.716 0 1.6 1.164 3.14 1.32 3.36.16.222 2.084 3.176 5.1 4.325.717.257 1.28.412 1.72.526.72.19 1.38.163 1.9.1.58-.075 1.758-.719 2.006-1.413.25-.694.25-1.29.174-1.415-.077-.124-.297-.198-.594-.347z"/><path d="M12.002 2C6.478 2 2 6.478 2 12c0 1.85.5 3.583 1.373 5.083L2 22l5.084-1.334A9.94 9.94 0 0012.002 22C17.523 22 22 17.522 22 12S17.523 2 12.002 2zm0 18.19a8.17 8.17 0 01-4.166-1.14l-.299-.177-3.02.793.807-2.943-.194-.303A8.17 8.17 0 013.81 12c0-4.517 3.674-8.19 8.192-8.19 4.516 0 8.19 3.673 8.19 8.19 0 4.518-3.674 8.19-8.19 8.19z"/></svg>
                Hubungi Kami
            </a>
            @endif
        </div>
    </div>
</header>

{{-- ═══ CONTENT ═══════════════════════════════════════════════════ --}}
<main class="w-full flex-1">
@yield('content')
</main>

{{-- ═══ FOOTER (fixed — selalu terlihat di bawah layar) ═════════════════ --}}
<footer class="catalog-site-footer border-t border-slate-200 bg-white/95 backdrop-blur-md">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 py-3.5">
        <div class="flex flex-col sm:flex-row items-center justify-between gap-2.5">
            <div class="flex items-center gap-2.5">
                <div class="w-8 h-8 rounded-lg bg-white border border-emerald-100 shadow-sm p-1 flex items-center justify-center shrink-0">
                    <img src="{{ asset('assets/images/logo-ptnmf.png') }}" alt="PT Nur Madani Farma" class="w-full h-full object-contain">
                </div>
                <div class="text-center sm:text-left leading-tight">
                    <p class="text-[11px] sm:text-xs font-bold text-slate-700">
                        © {{ date('Y') }} <span class="text-emerald-700">PT Nur Madani Farma</span> · {{ $apotekName ?? 'Apotek Almaira' }} Banjarbaru
                    </p>
                    <p class="text-[10px] text-slate-400">Apotek Almaira v1.0 — Sistem Manajemen Apotek Terintegrasi</p>
                </div>
            </div>
            <div class="flex items-center gap-3 text-[10px] sm:text-xs text-slate-400">
                <span class="hidden sm:inline">Banjarbaru, Kalimantan Selatan</span>
                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-emerald-50 text-emerald-700 font-semibold border border-emerald-100 whitespace-nowrap">
                    <svg class="w-3 h-3 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                    Hak Cipta Dilindungi
                </span>
            </div>
        </div>
    </div>
</footer>

@stack('scripts')
<script>
/**
 * Pesan WA katalog — konsisten per platform:
 * - Windows / PC / macOS desktop → langsung buka WhatsApp (wa.me) + teks & tautan foto
 *   (Dialog Share Windows dihindari — itu yang terasa seperti bug)
 * - iOS / Android → Web Share + file gambar ke WhatsApp
 */
window.CatalogWA = {
    isMobile() {
        const ua = navigator.userAgent || '';
        const touchIpad = navigator.maxTouchPoints > 1 && /MacIntel/i.test(navigator.platform || '');
        return /Android|webOS|iPhone|iPad|iPod|Mobile/i.test(ua) || touchIpad;
    },

    isWindowsDesktop() {
        const ua = navigator.userAgent || '';
        return /Windows/i.test(ua) && !/Mobile/i.test(ua);
    },

    /** Desktop (termasuk Windows): jangan pakai navigator.share. */
    shouldOpenWhatsAppDirect() {
        return !this.isMobile() || this.isWindowsDesktop();
    },

    openWhatsApp(href) {
        const url = href || '#';
        const win = window.open(url, '_blank', 'noopener,noreferrer');
        if (!win) window.location.href = url;
    },

    resolveImageUrl(imageUrl) {
        if (!imageUrl) return '';
        try {
            const parsed = new URL(imageUrl, window.location.origin);
            return window.location.origin + parsed.pathname + parsed.search;
        } catch (_) {
            return imageUrl;
        }
    },

    slugFileName(name, ext) {
        const base = String(name || 'produk')
            .toLowerCase()
            .replace(/[^a-z0-9]+/g, '-')
            .replace(/^-+|-+$/g, '')
            .slice(0, 40) || 'produk-almaira';
        return base + '.' + ext;
    },

    async buildImageFile(imageUrl, productName) {
        const url = this.resolveImageUrl(imageUrl);
        if (!url) return null;

        const res = await fetch(url, { credentials: 'same-origin', cache: 'force-cache' });
        if (!res.ok) return null;

        let blob = await res.blob();
        let type = blob.type || 'image/png';
        if (!type.startsWith('image/')) {
            type = 'image/png';
            blob = new Blob([blob], { type });
        }
        let ext = (type.split('/')[1] || 'png').toLowerCase();
        if (ext === 'jpeg') ext = 'jpg';
        if (!['png', 'jpg', 'webp', 'gif'].includes(ext)) ext = 'png';

        return new File([blob], this.slugFileName(productName, ext), { type });
    },

    canSharePayload(payload) {
        try {
            return typeof navigator.canShare === 'function' ? navigator.canShare(payload) : false;
        } catch (_) {
            return false;
        }
    },

    async share(anchor) {
        if (anchor.dataset.waBusy === '1') return;
        anchor.dataset.waBusy = '1';

        const href = anchor.getAttribute('data-wa-href') || anchor.getAttribute('href') || '';
        const text = anchor.getAttribute('data-wa-text') || '';
        const imageUrl = anchor.getAttribute('data-wa-image') || '';
        const detailUrl = anchor.getAttribute('data-wa-detail') || '';
        const name = anchor.getAttribute('data-wa-name') || 'Produk Apotek Almaira';

        try {
            // PC / Windows / desktop: langsung ke WhatsApp — jangan buka dialog Share OS.
            if (this.shouldOpenWhatsAppDirect()) {
                this.openWhatsApp(href);
                return;
            }

            // iOS / Android: kirim file gambar + teks lewat share sheet → WhatsApp.
            if (imageUrl && typeof navigator.share === 'function') {
                try {
                    const file = await this.buildImageFile(imageUrl, name);
                    if (file) {
                        const withFiles = { files: [file], text, title: name };
                        if (this.canSharePayload(withFiles)) {
                            await navigator.share(withFiles);
                            return;
                        }
                        const filesOnly = { files: [file], text, title: name };
                        if (this.canSharePayload(filesOnly)) {
                            await navigator.share(filesOnly);
                            return;
                        }
                    }
                } catch (err) {
                    if (err && (err.name === 'AbortError' || err.name === 'NotAllowedError')) {
                        return;
                    }
                }
            }

            // Fallback mobile tanpa file share
            if (typeof navigator.share === 'function') {
                try {
                    const textPayload = { title: name, text };
                    if (detailUrl) textPayload.url = detailUrl;
                    if (this.canSharePayload(textPayload)) {
                        await navigator.share(textPayload);
                        return;
                    }
                } catch (err) {
                    if (err && (err.name === 'AbortError' || err.name === 'NotAllowedError')) {
                        return;
                    }
                }
            }

            this.openWhatsApp(href);
        } finally {
            delete anchor.dataset.waBusy;
        }
    }
};

document.addEventListener('click', function (e) {
    const link = e.target.closest('[data-wa-share]');
    if (!link) return;
    e.preventDefault();
    window.CatalogWA.share(link);
});

window.MitraCart = {
    csrf() {
        return document.querySelector('meta[name="csrf-token"]')?.content || '';
    },
    formatRp(amount) {
        return 'Rp ' + Math.round(amount).toLocaleString('id-ID');
    },
    updateBadge(count) {
        const badge = document.getElementById('mitraCartBadge');
        if (!badge) return;
        const n = parseInt(count, 10) || 0;
        if (n <= 0) {
            badge.classList.add('hidden');
            badge.textContent = '';
            return;
        }
        badge.textContent = n;
        badge.classList.remove('hidden');
        badge.classList.add('scale-110');
        setTimeout(() => badge.classList.remove('scale-110'), 200);
    },
    showToast(message, type = 'success') {
        const colors = {
            success: 'bg-emerald-600',
            error: 'bg-red-600',
            info: 'bg-blue-600',
        };
        let host = document.getElementById('mitraAjaxToastHost');
        if (!host) {
            host = document.createElement('div');
            host.id = 'mitraAjaxToastHost';
            host.className = 'fixed bottom-24 right-5 z-[120] flex flex-col gap-2 pointer-events-none';
            document.body.appendChild(host);
        }
        const el = document.createElement('div');
        el.className = 'pointer-events-auto flex items-center gap-2 px-4 py-3 rounded-xl text-white text-sm font-semibold shadow-lg transition-all duration-300 translate-y-2 opacity-0 ' + (colors[type] || colors.success);
        el.textContent = message;
        host.appendChild(el);
        requestAnimationFrame(() => {
            el.classList.remove('translate-y-2', 'opacity-0');
        });
        setTimeout(() => {
            el.classList.add('opacity-0', 'translate-y-2');
            setTimeout(() => el.remove(), 300);
        }, 2600);
    },
    async post(url, body) {
        const res = await fetch(url, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': this.csrf(),
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            },
            body,
        });
        const data = await res.json().catch(() => ({}));
        if (!res.ok) {
            throw new Error(data.message || 'Permintaan gagal.');
        }
        return data;
    },
};

document.addEventListener('submit', async function (e) {
    const form = e.target.closest('.mitra-add-cart-form');
    if (!form || !window.MitraCart) return;

    e.preventDefault();
    e.stopPropagation();

    const btn = form.querySelector('.mitra-add-cart-btn');
    if (!btn || btn.disabled) return;

    const original = btn.textContent.trim();
    btn.disabled = true;
    btn.textContent = 'Menambah...';

    try {
        const data = await window.MitraCart.post(form.action, new FormData(form));
        if (data.count !== undefined) window.MitraCart.updateBadge(data.count);
        window.MitraCart.showToast(data.message || 'Ditambahkan ke keranjang.');
        btn.textContent = '✓ Ditambahkan';
        setTimeout(() => {
            btn.textContent = original;
            btn.disabled = false;
        }, 1100);
    } catch (err) {
        btn.textContent = original;
        btn.disabled = false;
        window.MitraCart.showToast(err.message || 'Gagal menambah ke keranjang.', 'error');
    }
});
</script>
@livewireScripts
</body>
</html>
