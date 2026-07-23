<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="{{ $apotekName }} Banjarbaru — dinaungi {{ $companyName }}. {{ $tagline }}">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', ($apotekName ?? 'Apotek Almaira') . ' Banjarbaru') · Almaira</title>
    <link rel="icon" type="image/jpeg" href="{{ asset('assets/images/logodashboard.jpeg') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@600;700;800;900&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        .font-banner { font-family: 'Montserrat', 'Plus Jakarta Sans', sans-serif; letter-spacing: 0.02em; }
        .font-body   { font-family: 'Plus Jakarta Sans', 'Inter', sans-serif; }
        [x-cloak] { display: none !important; }
        body { -webkit-font-smoothing: antialiased; }

        /* Navbar — mode hero (gradien hijau elegan) */
        #landing-nav.nav-hero {
            background: linear-gradient(135deg, rgba(4, 47, 34, 0.92) 0%, rgba(8, 78, 56, 0.88) 45%, rgba(6, 95, 70, 0.85) 100%);
            backdrop-filter: blur(16px) saturate(1.2);
            border-bottom: 1px solid rgba(255,255,255,0.12);
            box-shadow: 0 4px 30px rgba(0,0,0,0.15), inset 0 1px 0 rgba(255,255,255,0.08);
        }
        #landing-nav.nav-hero::after {
            content: '';
            position: absolute;
            bottom: 0; left: 0; right: 0;
            height: 2px;
            background: linear-gradient(90deg, transparent, rgba(251,191,36,0.6) 30%, rgba(52,211,153,0.8) 70%, transparent);
        }
        #landing-nav.nav-hero .nav-brand-title { color: #fff; text-shadow: 0 1px 3px rgba(0,0,0,0.35), 0 2px 8px rgba(0,0,0,0.2); }
        #landing-nav.nav-hero .nav-brand-sub { color: #a7f3d0; font-weight: 700; }
        #landing-nav.nav-hero .nav-link { color: rgba(255,255,255,0.95); font-weight: 600; }
        #landing-nav.nav-hero .nav-link:hover { color: #fff; background: rgba(255,255,255,0.12); }
        #landing-nav.nav-hero .nav-link-active { background: rgba(255,255,255,0.15); color: #fff; }
        #landing-nav.nav-hero .nav-btn-catalog {
            color: #fff;
            background: linear-gradient(135deg, rgba(255,255,255,0.15), rgba(255,255,255,0.08));
            border-color: rgba(255,255,255,0.35);
            box-shadow: inset 0 1px 0 rgba(255,255,255,0.1);
        }
        #landing-nav.nav-hero .nav-btn-catalog:hover { background: linear-gradient(135deg, rgba(255,255,255,0.25), rgba(255,255,255,0.15)); }
        #landing-nav.nav-hero .nav-btn-wa {
            background: linear-gradient(135deg, #25D366, #1ebe5d);
            box-shadow: 0 4px 14px rgba(37,211,102,0.35);
        }
        #landing-nav.nav-hero .nav-btn-wa:hover { background: linear-gradient(135deg, #2ee070, #25D366); }
        #landing-nav.nav-hero .nav-btn-login {
            background: linear-gradient(135deg, #fff 0%, #ecfdf5 100%);
            color: #065f46;
            border-color: transparent;
            box-shadow: 0 2px 10px rgba(0,0,0,0.12);
        }
        #landing-nav.nav-hero .nav-btn-login:hover { background: #fff; }
        #landing-nav.nav-hero .nav-btn-mitra {
            background: linear-gradient(135deg, #fbbf24 0%, #f59e0b 100%);
            color: #78350f;
            border-color: transparent;
            box-shadow: 0 2px 12px rgba(245,158,11,0.35);
        }
        #landing-nav.nav-hero .nav-btn-mitra:hover {
            background: linear-gradient(135deg, #fcd34d 0%, #fbbf24 100%);
            transform: translateY(-1px);
        }
        #landing-nav.nav-hero .nav-login-group {
            background: rgba(255,255,255,0.1);
            border: 1px solid rgba(255,255,255,0.18);
            box-shadow: inset 0 1px 0 rgba(255,255,255,0.08);
        }
        #landing-nav.nav-hero .nav-btn-menu { color: #fff; }
        #landing-nav.nav-hero .nav-btn-menu:hover { background: rgba(255,255,255,0.12); }

        /* Navbar — mode scroll (gradien putih-hijau muda) */
        #landing-nav.nav-scrolled {
            background: linear-gradient(180deg, rgba(255,255,255,0.98) 0%, rgba(236,253,245,0.95) 100%);
            backdrop-filter: blur(16px);
            box-shadow: 0 4px 20px rgba(6,78,59,0.08);
            border-bottom: 1px solid #d1fae5;
        }
        #landing-nav.nav-scrolled::after {
            content: '';
            position: absolute;
            bottom: 0; left: 0; right: 0;
            height: 2px;
            background: linear-gradient(90deg, transparent, #34d399 40%, #10b981 60%, transparent);
        }
        #landing-nav.nav-scrolled .nav-brand-title { color: #064e3b; text-shadow: none; }
        #landing-nav.nav-scrolled .nav-brand-sub { color: #047857; }
        #landing-nav.nav-scrolled .nav-link { color: #475569; }
        #landing-nav.nav-scrolled .nav-link:hover { color: #047857; background: #ecfdf5; }
        #landing-nav.nav-scrolled .nav-btn-catalog {
            color: #047857;
            background: linear-gradient(135deg, #ecfdf5, #d1fae5);
            border-color: #a7f3d0;
        }
        #landing-nav.nav-scrolled .nav-btn-catalog:hover { background: linear-gradient(135deg, #d1fae5, #a7f3d0); }
        #landing-nav.nav-scrolled .nav-btn-wa {
            background: linear-gradient(135deg, #25D366, #16a34a);
            box-shadow: 0 4px 12px rgba(22,163,74,0.25);
        }
        #landing-nav.nav-scrolled .nav-btn-login { color: #065f46; background: #fff; border-color: #a7f3d0; }
        #landing-nav.nav-scrolled .nav-btn-login:hover { background: #ecfdf5; border-color: #6ee7b7; }
        #landing-nav.nav-scrolled .nav-btn-mitra {
            background: linear-gradient(135deg, #f59e0b, #d97706);
            color: #fff;
            border-color: transparent;
            box-shadow: 0 2px 10px rgba(217,119,6,0.25);
        }
        #landing-nav.nav-scrolled .nav-btn-mitra:hover { background: linear-gradient(135deg, #fbbf24, #f59e0b); }
        #landing-nav.nav-scrolled .nav-login-group {
            background: #ecfdf5;
            border: 1px solid #a7f3d0;
        }
        #landing-nav.nav-scrolled .nav-btn-menu { color: #475569; }
        #landing-nav.nav-scrolled .nav-btn-menu:hover { background: #f1f5f9; }

        .hero-text-shadow { text-shadow: 0 1px 3px rgba(0,0,0,0.4), 0 2px 20px rgba(0,0,0,0.2); }
        .hero-glow { box-shadow: 0 0 60px rgba(16, 185, 129, 0.15), 0 25px 50px rgba(0,0,0,0.25); }
        .location-card { background: linear-gradient(160deg, #ffffff 0%, #f0fdf4 100%); }
        @keyframes logo-float {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-6px); }
        }
        .logo-float { animation: logo-float 4.5s ease-in-out infinite; }
        .logo-float-delay { animation-delay: 1.2s; }
        @media (prefers-reduced-motion: reduce) {
            .logo-float, .logo-float-delay { animation: none; }
        }
    </style>
    @stack('head')
</head>
<body class="font-body bg-slate-50 text-slate-800 antialiased">

{{-- ═══ NAVBAR ════════════════════════════════════════════════════ --}}
<nav id="landing-nav" class="fixed top-0 inset-x-0 z-50 transition-all duration-500 nav-hero">
    <div class="max-w-7xl mx-auto px-4 sm:px-6">
        <div class="flex items-center justify-between h-16 lg:h-[72px]">
            <a href="{{ route('home') }}#beranda" class="flex items-center gap-3 shrink-0 group">
                <div class="flex items-center gap-1.5">
                    <div class="w-10 h-10 rounded-xl bg-white shadow-md ring-2 ring-white/30 p-1.5 flex items-center justify-center group-hover:scale-105 transition-transform">
                        <img src="{{ asset('assets/images/logo-ptnmf.png') }}" alt="PT NMF" class="w-full h-full object-contain">
                    </div>
                    <div class="w-10 h-10 rounded-xl bg-white shadow-md ring-2 ring-white/30 p-1.5 flex items-center justify-center group-hover:scale-105 transition-transform">
                        <img src="{{ asset('assets/images/logo-apotek.png') }}" alt="Apotek" class="w-full h-full object-contain">
                    </div>
                </div>
                <div class="hidden sm:block leading-tight">
                    <p class="nav-brand-title font-banner text-[13px] font-black uppercase tracking-wide">{{ $companyName }}</p>
                    <p class="nav-brand-sub text-xs font-bold">{{ $apotekName }} Banjarbaru</p>
                </div>
            </a>

            <div class="hidden lg:flex items-center gap-0.5">
                <a href="#beranda" class="nav-link text-sm font-semibold px-3.5 py-2 rounded-lg transition-colors">Beranda</a>
                <a href="#tentang" class="nav-link text-sm font-semibold px-3.5 py-2 rounded-lg transition-colors">Tentang</a>
                <a href="#visi-misi" class="nav-link text-sm font-semibold px-3.5 py-2 rounded-lg transition-colors">Visi & Misi</a>
                <a href="#layanan" class="nav-link text-sm font-semibold px-3.5 py-2 rounded-lg transition-colors">Layanan</a>
                <a href="#kontak" class="nav-link text-sm font-semibold px-3.5 py-2 rounded-lg transition-colors">Kontak</a>
            </div>

            <div class="hidden lg:flex items-center gap-2">
                <a href="{{ route('catalog.index') }}" class="nav-btn-catalog inline-flex items-center gap-1.5 px-3.5 py-2 text-sm font-bold border rounded-xl transition-colors">
                    E-Catalog
                </a>
                <a href="https://wa.me/{{ $waNumber }}" target="_blank" rel="noopener" class="nav-btn-wa inline-flex items-center gap-1.5 px-3.5 py-2 text-sm font-bold text-white rounded-xl transition-all hover:-translate-y-0.5">
                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347z"/><path d="M12 0C5.373 0 0 5.373 0 12c0 2.11.55 4.09 1.514 5.805L0 24l6.336-1.662C8.09 23.45 10.004 24 12 24c6.627 0 12-5.373 12-12S18.627 0 12 0z"/></svg>
                    WhatsApp
                </a>
                <div class="nav-login-group flex items-center gap-1.5 p-1 rounded-2xl" title="Pilih jenis login">
                    <a href="{{ route('mitra.login') }}" class="nav-btn-mitra inline-flex items-center gap-1.5 px-3.5 py-2 text-sm font-bold rounded-xl transition-all">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                        Login Mitra
                    </a>
                    <a href="{{ route('login') }}" class="nav-btn-login inline-flex items-center gap-1.5 px-3.5 py-2 text-sm font-bold border rounded-xl transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                        Login Staff
                    </a>
                </div>
            </div>

            <button type="button" id="mobile-menu-btn" class="nav-btn-menu lg:hidden p-2.5 rounded-xl transition-colors" aria-label="Menu" aria-expanded="false">
                <svg id="icon-menu-open" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                <svg id="icon-menu-close" class="w-6 h-6 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
    </div>

    <div id="mobile-menu" class="hidden lg:hidden bg-white border-t border-slate-100 shadow-lg">
        <div class="px-4 py-4 space-y-1">
            <a href="#beranda" class="mobile-nav-link block px-3 py-2.5 text-sm font-semibold text-slate-700 hover:bg-emerald-50 rounded-lg">Beranda</a>
            <a href="#tentang" class="mobile-nav-link block px-3 py-2.5 text-sm font-semibold text-slate-700 hover:bg-emerald-50 rounded-lg">Tentang Kami</a>
            <a href="#visi-misi" class="mobile-nav-link block px-3 py-2.5 text-sm font-semibold text-slate-700 hover:bg-emerald-50 rounded-lg">Visi & Misi</a>
            <a href="#layanan" class="mobile-nav-link block px-3 py-2.5 text-sm font-semibold text-slate-700 hover:bg-emerald-50 rounded-lg">Layanan</a>
            <a href="#kontak" class="mobile-nav-link block px-3 py-2.5 text-sm font-semibold text-slate-700 hover:bg-emerald-50 rounded-lg">Kontak</a>
            <div class="pt-3 flex flex-col gap-2 border-t border-slate-100">
                <a href="{{ route('catalog.index') }}" class="btn btn-primary text-center">E-Catalog</a>
                <a href="https://wa.me/{{ $waNumber }}" target="_blank" rel="noopener" class="inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl bg-[#25D366] text-white text-sm font-bold">WhatsApp</a>
                <div class="grid grid-cols-2 gap-2">
                    <a href="{{ route('mitra.login') }}" class="inline-flex items-center justify-center gap-1.5 px-3 py-2.5 rounded-xl bg-amber-500 text-amber-950 text-sm font-bold shadow-sm">Login Mitra</a>
                    <a href="{{ route('login') }}" class="inline-flex items-center justify-center gap-1.5 px-3 py-2.5 rounded-xl bg-emerald-700 text-white text-sm font-bold shadow-sm">Login Staff</a>
                </div>
                <a href="{{ route('mitra.register') }}" class="text-center text-xs font-semibold text-emerald-700 hover:text-emerald-800 py-1">Belum punya akun? Daftar Mitra B2B</a>
            </div>
        </div>
    </div>
</nav>

<main>
    @yield('content')
</main>

{{-- ═══ FOOTER ════════════════════════════════════════════════════ --}}
<footer class="bg-slate-900 text-slate-300">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 py-12">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8">
            <div class="lg:col-span-2">
                <div class="flex items-center gap-2 mb-4">
                    <div class="w-10 h-10 rounded-xl bg-white p-1.5"><img src="{{ asset('assets/images/logo-apotek.png') }}" alt="" class="w-full h-full object-contain"></div>
                    <div>
                        <p class="font-banner text-white font-extrabold text-sm uppercase">{{ $apotekName }}</p>
                        <p class="text-xs text-emerald-400 font-semibold">{{ $companyName }}</p>
                    </div>
                </div>
                <p class="text-sm text-slate-400 leading-relaxed max-w-md">{{ $tagline }}. Melayani masyarakat Banjarbaru dan sekitarnya dengan pelayanan farmasi profesional.</p>
            </div>
            <div>
                <h4 class="text-white font-bold text-sm mb-3">Tautan Cepat</h4>
                <ul class="space-y-2 text-sm">
                    <li><a href="{{ route('catalog.index') }}" class="hover:text-emerald-400 transition-colors">E-Catalog Produk</a></li>
                    <li><a href="{{ route('mitra.register') }}" class="hover:text-emerald-400 transition-colors">Daftar Mitra B2B</a></li>
                    <li><a href="{{ route('mitra.login') }}" class="hover:text-emerald-400 transition-colors">Login Portal Mitra</a></li>
                    <li><a href="{{ route('login') }}" class="hover:text-emerald-400 transition-colors">Login Staff / Admin</a></li>
                </ul>
            </div>
            <div>
                <h4 class="text-white font-bold text-sm mb-3">Hubungi Kami</h4>
                <ul class="space-y-2 text-sm">
                    <li class="flex items-start gap-2">
                        <svg class="w-4 h-4 text-emerald-500 mt-0.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                        <a href="tel:{{ preg_replace('/\D/', '', $phone) }}" class="hover:text-emerald-400">{{ $phone }}</a>
                    </li>
                    <li class="flex items-start gap-2">
                        <svg class="w-4 h-4 text-emerald-500 mt-0.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                        <a href="mailto:{{ $email }}" class="hover:text-emerald-400 break-all">{{ $email }}</a>
                    </li>
                    <li class="flex items-start gap-2">
                        <svg class="w-4 h-4 text-emerald-500 mt-0.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                        <span>Banjarbaru, Kalimantan Selatan</span>
                    </li>
                </ul>
            </div>
        </div>
        <div class="mt-10 pt-6 border-t border-slate-800 flex flex-col sm:flex-row items-center justify-between gap-3 text-xs text-slate-500">
            <p>© {{ date('Y') }} {{ $companyName }} — {{ $apotekName }} Banjarbaru. Semua hak dilindungi.</p>
            <p>Apotek Almaira v1.0 · Sistem Manajemen Apotek Terintegrasi</p>
        </div>
    </div>
</footer>

@stack('scripts')
<script>
(function () {
    const nav = document.getElementById('landing-nav');
    const menuBtn = document.getElementById('mobile-menu-btn');
    const menu = document.getElementById('mobile-menu');
    const iconOpen = document.getElementById('icon-menu-open');
    const iconClose = document.getElementById('icon-menu-close');

    function setMenuOpen(open) {
        menu.classList.toggle('hidden', !open);
        iconOpen.classList.toggle('hidden', open);
        iconClose.classList.toggle('hidden', !open);
        menuBtn.setAttribute('aria-expanded', open ? 'true' : 'false');
    }

    function updateNavTheme() {
        if (!nav) return;
        const hero = document.getElementById('beranda');
        const heroBottom = hero ? hero.offsetHeight - 72 : 400;
        const onHero = window.scrollY < heroBottom * 0.85;
        nav.classList.toggle('nav-hero', onHero);
        nav.classList.toggle('nav-scrolled', !onHero);
    }

    menuBtn?.addEventListener('click', () => setMenuOpen(menu.classList.contains('hidden')));
    document.querySelectorAll('.mobile-nav-link').forEach((el) => el.addEventListener('click', () => setMenuOpen(false)));

    updateNavTheme();
    window.addEventListener('scroll', updateNavTheme, { passive: true });
})();
</script>
</body>
</html>
