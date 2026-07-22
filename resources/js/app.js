import axios from 'axios';
window.axios = axios;
window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

import focus from '@alpinejs/focus';

// Listen to Livewire's init event before it boots Alpine
document.addEventListener('livewire:init', () => {
    // Register focus plugin on Livewire's native Alpine instance
    window.Alpine.plugin(focus);

    // Register any inline components defined in blade views before app.js loaded
    window.alpineComponents = window.alpineComponents || {};
    Object.keys(window.alpineComponents).forEach(name => {
        window.Alpine.data(name, window.alpineComponents[name]);
    });
});

// ── Form gaji: format Rupiah ID (1.000.000) + hitung gaji bersih ──
window.salaryMoneyForm = (init = {}) => ({
    basic: Number(init.basic || 0),
    overtime: Number(init.overtime || 0),
    allowance: Number(init.allowance || 0),
    bpjs_kes: Number(init.bpjs_kes || 0),
    bpjs_ket: Number(init.bpjs_ket || 0),
    deduction: Number(init.deduction || 0),
    entities: Array.isArray(init.entities) ? init.entities : ['pt'],
    parseId(value) {
        const digits = String(value ?? '').replace(/\D/g, '');
        return digits === '' ? 0 : parseInt(digits, 10);
    },
    formatId(value) {
        const n = Number(value || 0);
        if (!n) return '';
        return new Intl.NumberFormat('id-ID').format(n);
    },
    get net() {
        return Math.max(
            0,
            Number(this.basic || 0) + Number(this.overtime || 0) + Number(this.allowance || 0)
            - (Number(this.bpjs_kes || 0) + Number(this.bpjs_ket || 0) + Number(this.deduction || 0))
        );
    },
    formatRupiah(val) {
        return 'Rp ' + new Intl.NumberFormat('id-ID').format(Number(val || 0));
    },
});

// ── Global Toast System ───────────────────────────────────────
window.toastManager = () => ({
    toasts: [],
    init() {
        // Allow any nested component to fire toasts via: window.dispatchEvent(new CustomEvent('toast', { detail: { type, message } }))
        window.addEventListener('toast', (e) => {
            const { type = 'info', message = '' } = e.detail || {};
            this.add(message, type);
        });

        // Bridge Livewire $this->dispatch('toast', ...) → Alpine toastManager
        const bridge = (payload) => {
            const data = Array.isArray(payload) ? (payload[0] || {}) : (payload || {});
            this.add(data.message || '', data.type || 'info');
        };
        if (window.Livewire) {
            window.Livewire.on('toast', bridge);
        } else {
            document.addEventListener('livewire:init', () => window.Livewire.on('toast', bridge));
        }
    },
    add(message, type = 'success', duration = 4000) {
        const id = Date.now();
        this.toasts.push({ id, message, type });
        setTimeout(() => this.remove(id), duration);
    },
    remove(id) {
        this.toasts = this.toasts.filter(t => t.id !== id);
    },
    success(msg) { this.add(msg, 'success'); },
    error(msg)   { this.add(msg, 'error', 6000); },
    warning(msg) { this.add(msg, 'warning', 5000); },
    info(msg)    { this.add(msg, 'info'); },
});

// ── Topbar clock (satu interval, dibersihkan saat komponen dihancurkan) ──
window.topbarClock = () => ({
    time: '',
    _timer: null,
    init() {
        const update = () => {
            this.time = new Date().toLocaleString('id-ID', {
                hour: '2-digit',
                minute: '2-digit',
                second: '2-digit',
                day: '2-digit',
                month: 'short',
                year: 'numeric',
            });
        };
        update();
        this._timer = setInterval(update, 1000);
    },
    destroy() {
        if (this._timer) clearInterval(this._timer);
    },
});

// ── Sidebar collapse state ────────────────────────────────────
window.sidebar = () => ({
    collapsed: (() => {
        if (typeof window === 'undefined') return false;
        if (window.innerWidth < 1024) return true;
        return localStorage.getItem('sidebar-collapsed') === 'true';
    })(),
    init() {
        const checkCollapse = () => {
            if (window.innerWidth < 1024) {
                this.collapsed = true;
            } else {
                const saved = localStorage.getItem('sidebar-collapsed');
                this.collapsed = saved === 'true';
            }
        };
        // Jangan paksa buka/tutup ulang tiap SPA navigate — hanya sync saat resize
        window.addEventListener('resize', checkCollapse);
    },
    toggle() {
        this.collapsed = !this.collapsed;
        if (window.innerWidth >= 1024) {
            localStorage.setItem('sidebar-collapsed', this.collapsed);
        }
    },
});

// ── Sidebar SPA + progress bar (INIT SEKALI SAJA) ──────────────
(() => {
    if (window.__almairaSpaNavReady) return;
    window.__almairaSpaNavReady = true;

    const NAV_MATCH = {
        dashboard:       (p) => p === '/' || p === '/dashboard',
        pos:             (p) => p === '/pos' || p.startsWith('/pos/'),
        sales:           (p) => p.startsWith('/sales'),
        invoices:        (p) => p.startsWith('/invoices'),
        credits:         (p) => p.startsWith('/credits'),
        prescriptions:   (p) => p.startsWith('/prescriptions'),
        products:        (p) => p.startsWith('/products'),
        purchases:       (p) => p.startsWith('/purchases'),
        'stock-outs':    (p) => p.startsWith('/stock-outs'),
        'stock-opnames': (p) => p.startsWith('/stock-opnames'),
        categories:      (p) => p.startsWith('/categories'),
        suppliers:       (p) => p.startsWith('/suppliers'),
        customers:       (p) => p.startsWith('/customers'),
        partners:        (p) => p.startsWith('/partners'),
        'partner-orders':(p) => p.startsWith('/partner-orders'),
        settings:        (p) => p.startsWith('/settings'),
        reports:         (p, u) => (p === '/reports' || p === '/reports/') && u.searchParams.get('type') !== 'log_aktivitas',
        salaries:        (p) => p.startsWith('/salaries'),
        users:           (p) => p.startsWith('/users'),
        'log-aktivitas': (p, u) => (p === '/reports' || p === '/reports/') && u.searchParams.get('type') === 'log_aktivitas',
        backup:          (p) => p.startsWith('/backup'),
    };

    const SCROLL_KEY = 'sidebar-scroll-position';
    let barTimer1, barTimer2, barTimer3;
    let navigating = false;

    function normalizePath(pathname) {
        if (!pathname || pathname === '/') return '/';
        return pathname.replace(/\/+$/, '') || '/';
    }

    function getSidebarNav() {
        return document.querySelector('#sidebar-nav') || document.querySelector('.sidebar nav');
    }

    function getBar() {
        return document.getElementById('global-loading-bar');
    }

    function getOverlay() {
        return document.getElementById('nav-loading-overlay');
    }

    window.saveSidebarScroll = function saveSidebarScroll() {
        const nav = getSidebarNav();
        if (!nav) return;
        localStorage.setItem(SCROLL_KEY, String(nav.scrollTop));
    };

    window.restoreSidebarScroll = function restoreSidebarScroll() {
        const nav = getSidebarNav();
        if (!nav) return;
        const saved = localStorage.getItem(SCROLL_KEY);
        if (saved === null) return;
        nav.scrollTop = parseInt(saved, 10) || 0;
    };

    window.syncSidebarActive = function syncSidebarActive() {
        const url  = new URL(window.location.href);
        const path = normalizePath(url.pathname);
        document.querySelectorAll('#sidebar-nav a.sidebar-nav-item[data-nav]').forEach((el) => {
            const fn = NAV_MATCH[el.dataset.nav];
            el.classList.toggle('active', fn ? !!fn(path, url) : false);
        });
    };

    window.startNavProgress = function startNavProgress() {
        const bar = getBar();
        if (!bar) return;
        clearTimeout(barTimer1); clearTimeout(barTimer2); clearTimeout(barTimer3);
        bar.style.transition = 'none';
        bar.style.width = '0%';
        bar.style.opacity = '1';
        bar.getBoundingClientRect();
        bar.style.transition = 'width 0.18s ease-out, opacity 0.3s ease';
        bar.style.width = '18%';
        barTimer1 = setTimeout(() => { bar.style.width = '50%'; }, 120);
        barTimer2 = setTimeout(() => { bar.style.width = '78%'; }, 350);
        barTimer3 = setTimeout(() => { bar.style.width = '92%'; }, 700);
    };

    window.finishNavProgress = function finishNavProgress() {
        const bar = getBar();
        if (!bar) return;
        clearTimeout(barTimer1); clearTimeout(barTimer2); clearTimeout(barTimer3);
        bar.style.transition = 'width 0.15s ease-out, opacity 0.35s ease';
        bar.style.width = '100%';
        setTimeout(() => {
            bar.style.opacity = '0';
            setTimeout(() => { bar.style.width = '0%'; }, 350);
        }, 180);
    };

    window.hideNavOverlay = function hideNavOverlay() {
        const overlay = getOverlay();
        document.body.classList.remove('navigating');
        if (!overlay) return;
        overlay.style.opacity = '0';
        overlay.style.pointerEvents = 'none';
        overlay.style.display = 'none';
    };

    function bindSidebarScroll() {
        const nav = getSidebarNav();
        if (!nav || nav.dataset.scrollBound === '1') return;
        nav.dataset.scrollBound = '1';
        nav.addEventListener('scroll', () => {
            localStorage.setItem(SCROLL_KEY, String(nav.scrollTop));
        }, { passive: true });
    }

    function afterNavigate() {
        navigating = false;
        window.hideNavOverlay();
        window.finishNavProgress();

        const main = document.querySelector('main');
        if (main) main.scrollTop = 0;

        const titleEl = document.querySelector('.topbar h1');
        if (titleEl && document.title) {
            const pageTitle = document.title.replace(/\s*—\s*Apotek Almaira\s*$/i, '').trim();
            if (pageTitle) titleEl.textContent = pageTitle;
        }

        window.syncSidebarActive();
        bindSidebarScroll();
        requestAnimationFrame(() => window.restoreSidebarScroll());
    }

    function bootSidebarNav() {
        window.syncSidebarActive();
        bindSidebarScroll();
        window.restoreSidebarScroll();
    }

    // Klik sidebar: highlight cepat + simpan scroll — JANGAN preventDefault
    document.addEventListener('click', (e) => {
        const link = e.target.closest('a[href]');
        if (link) {
            const href = link.getAttribute('href') || '';
            const isFileDownload = /\/export(?:\?|$)/.test(href)
                || link.hasAttribute('download')
                || /\.(xlsx|xls|csv|pdf|zip)(\?|$)/i.test(href);
            if (isFileDownload) {
                link.removeAttribute('wire:navigate');
            }
        }

        const navLink = e.target.closest('#sidebar-nav a.sidebar-nav-item[data-nav]');
        if (!navLink || !navLink.hasAttribute('href')) return;

        // Link eksternal / tab baru: biarkan browser
        if (navLink.target === '_blank' || e.metaKey || e.ctrlKey || e.shiftKey || e.altKey) return;

        window.saveSidebarScroll();

        // Highlight segera agar terasa instan (Livewire wire:navigate yang navigasi)
        document.querySelectorAll('#sidebar-nav a.sidebar-nav-item[data-nav]').forEach((el) => {
            el.classList.toggle('active', el === navLink);
        });

        // Mobile: tutup sidebar setelah pilih menu
        if (window.innerWidth < 1024) {
            const alpineRoot = document.body.__x || null;
            // Alpine v3: akses via $data tidak selalu ada; fallback set via event
            window.dispatchEvent(new CustomEvent('sidebar-close-mobile'));
        }
    }, true);

    document.addEventListener('livewire:navigate', () => {
        navigating = true;
        window.saveSidebarScroll();
        window.startNavProgress();
    });

    document.addEventListener('livewire:navigating', () => {
        navigating = true;
        window.startNavProgress();
    });

    document.addEventListener('livewire:navigated', () => {
        afterNavigate();
    });

    // Jika SPA gagal, pastikan UI tidak menggantung — tapi jangan history.back()
    document.addEventListener('livewire:navigate-error', () => {
        navigating = false;
        window.hideNavOverlay();
        window.finishNavProgress();
        window.syncSidebarActive();
    });

    document.addEventListener('DOMContentLoaded', () => {
        window.hideNavOverlay();
        window.finishNavProgress();
        bootSidebarNav();
    });

    // bfcache / pageshow: sync ulang, jangan reload paksa
    window.addEventListener('pageshow', (ev) => {
        window.hideNavOverlay();
        window.finishNavProgress();
        bootSidebarNav();
        // Jika halaman dari bfcache dan state aneh, cukup sync — jangan location.reload()
        if (ev.persisted) {
            window.syncSidebarActive();
        }
    });

    // Livewire sudah boot sebelum modul ini? sync segera
    if (document.readyState !== 'loading') {
        bootSidebarNav();
    }
})();

// ── Confirm Delete Modal ──────────────────────────────────────
window.confirmModal = () => ({
    open: false,
    title: 'Konfirmasi Hapus',
    message: 'Yakin ingin menghapus data ini? Tindakan ini tidak dapat dibatalkan.',
    action: null,
    confirm(formId, title = null, message = null) {
        this.title   = title   || 'Konfirmasi Hapus';
        this.message = message || 'Yakin ingin menghapus data ini? Tindakan ini tidak dapat dibatalkan.';
        this.action  = formId;
        this.open    = true;
    },
    execute() {
        if (this.action) {
            document.getElementById(this.action)?.submit();
        }
        this.open = false;
    }
});

// ── Format Currency (Rupiah) ──────────────────────────────────
window.formatRupiah = (angka) => {
    if (!angka && angka !== 0) return 'Rp 0';
    const num = parseFloat(angka) || 0;
    return 'Rp ' + num.toLocaleString('id-ID', { minimumFractionDigits: 0, maximumFractionDigits: 0 });
};

// ── Session Timeout Warning ───────────────────────────────────
let inactivityTimer;
const SESSION_TIMEOUT = 30 * 60 * 1000; // 30 minutes
const WARNING_BEFORE  = 5 * 60 * 1000;  // warn 5 min before

function resetInactivityTimer() {
    clearTimeout(inactivityTimer);
    inactivityTimer = setTimeout(() => {
        if (confirm('Sesi Anda akan berakhir dalam 5 menit karena tidak aktif. Klik OK untuk tetap login.')) {
            // Ping server to keep session alive
            fetch('/ping').catch(() => {});
            resetInactivityTimer();
        }
    }, SESSION_TIMEOUT - WARNING_BEFORE);
}

if (document.body?.dataset?.authenticated === 'true') {
    ['mousemove', 'keypress', 'click', 'scroll', 'touchstart'].forEach(e => {
        document.addEventListener(e, resetInactivityTimer, { passive: true });
    });
    resetInactivityTimer();
}
