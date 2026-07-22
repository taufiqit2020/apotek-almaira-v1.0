<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Portal Mitra') · Almaira</title>
    <link rel="icon" type="image/png" href="{{ asset('assets/images/logo-ptnmf.png') }}">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @include('layouts.partials.auth-styles')
    @stack('styles')
</head>
<body>
    <div class="bg-layer"></div>
    <div class="bg-grid"></div>
    <div class="orb orb-1"></div>
    <div class="orb orb-2"></div>
    <div class="orb orb-3"></div>

    @foreach(['toast_success'=>'#059669','toast_error'=>'#dc2626','toast_warning'=>'#d97706','toast_info'=>'#2563eb'] as $key => $color)
    @if(session($key))
    <div class="toast" style="background: {{ $color }};">{{ session($key) }}</div>
    @endif
    @endforeach

    <div class="auth-container @yield('container-class')">
        <div class="brand-header">
            <a href="{{ route('home') }}" class="logo-wrapper" style="text-decoration:none;" title="Beranda">
                <div class="logo-ring"></div>
                <div class="logo-bg">
                    <img src="{{ asset('assets/images/logo-ptnmf.png') }}" alt="PT Nur Madani Farma">
                </div>
            </a>
            <div class="brand-name">
                <span style="color:#6ee7b7;font-weight:800;">PT Nur Madani Farma</span>
            </div>
            <div class="brand-sub">@yield('brand-subtitle', 'Portal Mitra B2B — Apotek Almaira')</div>
            <div class="brand-divider">
                <span></span><div class="dot"></div><div class="dot"></div><div class="dot"></div><span></span>
            </div>
        </div>

        <div class="auth-card @yield('card-class')">
            @yield('content')
        </div>

        <div class="auth-footer">
            © {{ date('Y') }} PT Nur Madani Farma · Apotek Almaira Banjarbaru
        </div>
    </div>
    @stack('scripts')
</body>
</html>
