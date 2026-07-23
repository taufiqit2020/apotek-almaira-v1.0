<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Login · Almaira</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }

        html {
            overflow-x: hidden;
            overflow-y: auto;
            scroll-behavior: smooth;
        }

        body {
            min-height: 100vh;
            font-family: 'Inter', sans-serif;
            background: #0b1120;
            display: flex;
            flex-direction: column;
            align-items: center;
            overflow-x: hidden;
            overflow-y: auto;
            position: relative;
            padding: 2.5rem 1rem 3rem;
        }

        /* ===== BACKGROUND LAYERS ===== */
        .bg-layer {
            position: fixed;
            inset: 0;
            z-index: 0;
            /* Deep pharmacy gradient */
            background: radial-gradient(ellipse 120% 100% at 10% 0%, #0d2b5e 0%, transparent 60%),
                        radial-gradient(ellipse 80% 80% at 90% 100%, #063d30 0%, transparent 55%),
                        linear-gradient(160deg, #060e20 0%, #0e1d3d 45%, #091a29 100%);
        }

        /* Decorative orbs */
        .orb {
            position: fixed;
            border-radius: 50%;
            filter: blur(80px);
            pointer-events: none;
            animation: floatOrb 8s ease-in-out infinite;
        }
        .orb-1 {
            width: 500px; height: 500px;
            background: radial-gradient(circle, rgba(37,99,235,0.18) 0%, transparent 70%);
            top: -150px; left: -150px;
            animation-delay: 0s;
        }
        .orb-2 {
            width: 400px; height: 400px;
            background: radial-gradient(circle, rgba(16,185,129,0.14) 0%, transparent 70%);
            bottom: -100px; right: -100px;
            animation-delay: 3s;
        }
        .orb-3 {
            width: 300px; height: 300px;
            background: radial-gradient(circle, rgba(99,102,241,0.1) 0%, transparent 70%);
            top: 50%; right: 10%;
            animation-delay: 6s;
        }

        /* Grid overlay texture */
        .bg-grid {
            position: fixed;
            inset: 0;
            background-image:
                linear-gradient(rgba(255,255,255,0.015) 1px, transparent 1px),
                linear-gradient(90deg, rgba(255,255,255,0.015) 1px, transparent 1px);
            background-size: 48px 48px;
            pointer-events: none;
        }

        @keyframes floatOrb {
            0%, 100% { transform: translate(0, 0) scale(1); }
            33% { transform: translate(15px, -20px) scale(1.05); }
            66% { transform: translate(-10px, 10px) scale(0.95); }
        }

        /* ===== TOAST ===== */
        .toast {
            position: fixed;
            top: 1.25rem;
            right: 1.25rem;
            z-index: 9999;
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 14px 18px;
            border-radius: 14px;
            color: white;
            font-size: 14px;
            font-weight: 500;
            max-width: 400px;
            box-shadow: 0 12px 40px rgba(0,0,0,0.35);
            animation: slideIn 0.35s ease;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255,255,255,0.1);
        }
        @keyframes slideIn {
            from { opacity: 0; transform: translateX(20px); }
            to { opacity: 1; transform: translateX(0); }
        }

        /* ===== MAIN CONTAINER ===== */
        .login-container {
            position: relative;
            z-index: 10;
            width: 100%;
            max-width: 960px;
            padding: 0;
            margin-top: auto;
            margin-bottom: auto;
            flex-shrink: 0;
        }

        /* ===== BRAND HEADER (above card) ===== */
        .brand-header {
            text-align: center;
            margin-bottom: 28px;
        }

        .logo-wrapper {
            position: relative;
            display: inline-block;
            margin-bottom: 18px;
        }

        /* Glowing ring around logo */
        .logo-ring {
            position: absolute;
            inset: -8px;
            border-radius: 32px;
            background: conic-gradient(
                from 0deg,
                rgba(37,99,235,0) 0%,
                rgba(37,99,235,0.6) 25%,
                rgba(16,185,129,0.6) 50%,
                rgba(37,99,235,0.6) 75%,
                rgba(37,99,235,0) 100%
            );
            animation: spinRing 4s linear infinite;
            border-radius: 32px;
        }
        @keyframes spinRing {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }

        .logo-bg {
            position: relative;
            width: 130px;
            height: 130px;
            background: #ffffff;
            border-radius: 28px;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 10px;
            box-shadow:
                0 0 40px rgba(37,99,235,0.35),
                0 8px 32px rgba(0,0,0,0.3);
            overflow: hidden;
        }

        .logo-bg img {
            width: 100%;
            height: 100%;
            object-fit: contain;
        }

        .brand-name {
            font-size: 28px;
            font-weight: 800;
            color: #ffffff;
            letter-spacing: -0.5px;
            line-height: 1.2;
            margin-bottom: 6px;
            text-shadow: 0 2px 20px rgba(37,99,235,0.4);
        }

        .brand-sub {
            font-size: 13px;
            color: rgba(147,197,253,0.65);
            font-weight: 400;
            letter-spacing: 0.2px;
        }

        /* Divider with dots */
        .brand-divider {
            display: flex;
            align-items: center;
            gap: 8px;
            justify-content: center;
            margin-top: 12px;
        }
        .brand-divider span {
            display: block;
            height: 1px;
            width: 50px;
            background: linear-gradient(90deg, transparent, rgba(96,165,250,0.4), transparent);
        }
        .brand-divider .dot {
            width: 5px;
            height: 5px;
            border-radius: 50%;
            background: rgba(96,165,250,0.5);
        }

        /* ===== CARD ===== */
        .login-card {
            background: rgba(255,255,255,0.06);
            backdrop-filter: blur(24px);
            -webkit-backdrop-filter: blur(24px);
            border: 1px solid rgba(255,255,255,0.1);
            border-radius: 24px;
            padding: 0;
            overflow: hidden;
            box-shadow:
                0 30px 70px rgba(0,0,0,0.4),
                0 0 0 1px rgba(255,255,255,0.03),
                inset 0 1px 0 rgba(255,255,255,0.08);
        }

        .login-card-top {
            padding: 22px 32px 0;
        }

        .login-card-top .pending-alert {
            margin-bottom: 0;
        }

        .login-card-top:has(.pending-alert:not([style*="display:none"])) + .login-grid .login-col--form {
            padding-top: 22px;
        }

        .login-grid {
            display: grid;
            grid-template-columns: minmax(0, 1.15fr) minmax(280px, 0.85fr);
            align-items: stretch;
        }

        .login-col--form {
            padding: 28px 36px 32px;
        }

        .login-col--side {
            position: relative;
            padding: 32px 28px 32px;
            background:
                linear-gradient(165deg, rgba(15,23,42,0.72) 0%, rgba(6,78,59,0.18) 55%, rgba(30,58,138,0.15) 100%);
            border-left: 1px solid rgba(255,255,255,0.08);
            display: flex;
            flex-direction: column;
            justify-content: center;
            gap: 18px;
        }

        .login-col--side::before {
            content: '';
            position: absolute;
            top: 18%;
            right: -40px;
            width: 140px;
            height: 140px;
            border-radius: 50%;
            background: radial-gradient(circle, rgba(16,185,129,0.18) 0%, transparent 70%);
            pointer-events: none;
        }

        .side-header {
            position: relative;
            z-index: 1;
        }

        .side-eyebrow {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 5px 10px;
            border-radius: 999px;
            background: rgba(16,185,129,0.12);
            border: 1px solid rgba(16,185,129,0.25);
            color: #6ee7b7;
            font-size: 10px;
            font-weight: 700;
            letter-spacing: 0.6px;
            text-transform: uppercase;
            margin-bottom: 10px;
        }

        .side-title {
            font-size: 20px;
            font-weight: 800;
            color: #f8fafc;
            letter-spacing: -0.3px;
            line-height: 1.25;
            margin-bottom: 6px;
        }

        .side-subtitle {
            font-size: 12px;
            color: rgba(148,163,184,0.9);
            line-height: 1.55;
            margin: 0;
        }

        .card-title {
            font-size: 18px;
            font-weight: 700;
            color: rgba(255,255,255,0.9);
            margin-bottom: 8px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .card-title::before {
            content: '';
            display: block;
            width: 4px;
            height: 20px;
            background: linear-gradient(to bottom, #3b82f6, #10b981);
            border-radius: 2px;
            flex-shrink: 0;
        }
        .card-subtitle {
            font-size: 13px;
            color: rgba(203,213,225,0.9);
            line-height: 1.55;
            margin: 0 0 12px 14px;
            font-weight: 500;
        }
        .purpose-box {
            margin: 0 0 20px;
            padding: 12px 14px;
            border-radius: 12px;
            background: rgba(37,99,235,0.12);
            border: 1px solid rgba(96,165,250,0.3);
        }
        .purpose-box strong {
            display: block;
            font-size: 11px;
            font-weight: 800;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            color: #93c5fd;
            margin-bottom: 4px;
        }
        .purpose-box p {
            margin: 0;
            font-size: 13px;
            line-height: 1.55;
            color: rgba(226,232,240,0.92);
            font-weight: 500;
        }
        .purpose-box ul {
            margin: 8px 0 0;
            padding-left: 1.1rem;
            font-size: 12px;
            line-height: 1.55;
            color: rgba(203,213,225,0.88);
        }
        .purpose-box li + li { margin-top: 2px; }

        /* ===== ERROR BOX ===== */
        .error-box {
            background: rgba(239,68,68,0.12);
            border: 1px solid rgba(239,68,68,0.3);
            border-radius: 12px;
            padding: 14px 16px;
            margin-bottom: 22px;
        }
        .error-item {
            display: flex;
            align-items: flex-start;
            gap: 8px;
            color: #fca5a5;
            font-size: 13px;
            line-height: 1.5;
        }
        .error-item + .error-item { margin-top: 6px; }
        .error-icon { width: 16px; height: 16px; flex-shrink: 0; margin-top: 1px; }

        /* ===== FORM FIELDS ===== */
        .field-group { margin-bottom: 18px; }
        .field-label {
            display: block;
            color: rgba(186,230,253,0.75);
            font-size: 12px;
            font-weight: 600;
            margin-bottom: 8px;
            letter-spacing: 0.4px;
            text-transform: uppercase;
        }

        .input-wrap {
            position: relative;
        }
        .input-icon {
            position: absolute;
            left: 14px;
            top: 50%;
            transform: translateY(-50%);
            color: rgba(147,197,253,0.4);
            width: 18px;
            height: 18px;
            pointer-events: none;
        }
        .field-input {
            width: 100%;
            background: rgba(255,255,255,0.07);
            border: 1.5px solid rgba(255,255,255,0.12);
            color: white;
            border-radius: 12px;
            padding: 13px 14px 13px 44px;
            font-size: 14px;
            font-family: 'Inter', sans-serif;
            outline: none;
            transition: border-color 0.2s, background 0.2s, box-shadow 0.2s;
        }
        .field-input::placeholder { color: rgba(147,197,253,0.25); }
        .field-input:focus {
            border-color: rgba(96,165,250,0.7);
            background: rgba(255,255,255,0.1);
            box-shadow: 0 0 0 3px rgba(59,130,246,0.12);
        }
        .field-input:-webkit-autofill {
            -webkit-box-shadow: 0 0 0 30px rgba(14,29,61,0.95) inset !important;
            -webkit-text-fill-color: white !important;
        }

        .input-right-btn {
            position: absolute;
            right: 12px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: rgba(147,197,253,0.4);
            cursor: pointer;
            padding: 4px;
            border-radius: 6px;
            transition: color 0.2s;
        }
        .input-right-btn:hover { color: rgba(147,197,253,0.8); }

        /* ===== REMEMBER ===== */
        .remember-row {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 26px;
        }
        .remember-check {
            width: 17px;
            height: 17px;
            cursor: pointer;
            accent-color: #3b82f6;
            border-radius: 4px;
        }
        .remember-label {
            color: rgba(147,197,253,0.6);
            font-size: 13px;
            cursor: pointer;
            user-select: none;
        }

        /* ===== SUBMIT BUTTON ===== */
        .btn-submit {
            width: 100%;
            background: linear-gradient(135deg, #1d4ed8 0%, #2563eb 50%, #3b82f6 100%);
            color: white;
            border: none;
            border-radius: 12px;
            padding: 14px;
            font-size: 15px;
            font-weight: 700;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            transition: transform 0.2s, box-shadow 0.2s;
            font-family: 'Inter', sans-serif;
            box-shadow: 0 4px 20px rgba(37,99,235,0.45);
            letter-spacing: 0.2px;
        }
        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 28px rgba(37,99,235,0.55);
        }
        .btn-submit:active { transform: translateY(0); }
        .btn-submit:disabled {
            opacity: 0.7;
            cursor: not-allowed;
            transform: none;
        }

        /* ===== FOOTER ===== */
        .login-footer {
            text-align: center;
            color: rgba(147,197,253,0.25);
            font-size: 12px;
            margin-top: 22px;
            line-height: 1.6;
        }

        /* ===== SECURITY BADGE ===== */
        .security-badge {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            margin-top: 18px;
            color: rgba(16,185,129,0.5);
            font-size: 11px;
        }

        @keyframes spin { from { transform: rotate(0deg); } to { transform: rotate(360deg); } }

        /* ===== QUICK LINKS ===== */
        .auth-links {
            position: relative;
            z-index: 1;
            display: flex;
            flex-direction: column;
            gap: 9px;
        }
        .btn-secondary-link {
            display: flex;
            align-items: flex-start;
            justify-content: flex-start;
            gap: 10px;
            width: 100%;
            padding: 12px 14px;
            border-radius: 12px;
            font-size: 13px;
            font-weight: 600;
            color: rgba(226,232,240,0.95);
            border: 1px solid rgba(148,163,184,0.2);
            text-decoration: none;
            background: rgba(255,255,255,0.04);
            transition: background 0.2s, color 0.2s, border-color 0.2s, transform 0.2s;
        }
        .btn-secondary-link:hover {
            background: rgba(255,255,255,0.1);
            color: #fff;
            border-color: rgba(148,163,184,0.38);
            transform: translateX(4px);
        }
        .btn-secondary-link svg { width: 17px; height: 17px; opacity: 0.8; flex-shrink: 0; margin-top: 2px; }
        .link-body { display: flex; flex-direction: column; gap: 2px; min-width: 0; text-align: left; }
        .link-title { font-size: 13px; font-weight: 700; line-height: 1.3; color: inherit; }
        .link-desc { font-size: 11px; font-weight: 500; line-height: 1.4; color: rgba(148,163,184,0.95); }
        .btn-secondary-link:hover .link-desc { color: rgba(203,213,225,0.95); }
        .btn-secondary-link--primary {
            background: linear-gradient(135deg, rgba(37,99,235,0.22), rgba(16,185,129,0.14));
            border-color: rgba(96,165,250,0.35);
            color: #e0f2fe;
        }
        .btn-secondary-link--primary:hover {
            background: linear-gradient(135deg, rgba(37,99,235,0.32), rgba(16,185,129,0.22));
            border-color: rgba(96,165,250,0.5);
        }
        .btn-secondary-link--muted { font-size: 12.5px; color: rgba(203,213,225,0.88); }
        .btn-secondary-link--wa {
            margin-top: 4px;
            border-color: rgba(37,211,102,0.4);
            color: #d1fae5;
            background: linear-gradient(135deg, rgba(37,211,102,0.14), rgba(16,185,129,0.1));
            box-shadow: 0 4px 18px rgba(37,211,102,0.12);
        }
        .btn-secondary-link--wa:hover {
            background: linear-gradient(135deg, rgba(37,211,102,0.22), rgba(16,185,129,0.16));
            color: #ecfdf5;
            border-color: rgba(37,211,102,0.55);
        }

        .pending-alert {
            background: linear-gradient(135deg, rgba(251,191,36,0.15), rgba(249,115,22,0.1));
            border: 1px solid rgba(251,191,36,0.35);
            border-radius: 12px;
            padding: 12px 14px;
            margin-bottom: 18px;
            display: flex;
            align-items: flex-start;
            gap: 10px;
        }
        .pending-alert p { font-size: 12px; color: #fde68a; line-height: 1.55; margin: 0; }
        .pending-alert strong { color: #fff; }

        .forgot-row {
            display: flex;
            justify-content: flex-end;
            margin: -6px 0 14px;
        }
        .forgot-link {
            font-size: 12px;
            font-weight: 600;
            color: rgba(147,197,253,0.75);
            text-decoration: none;
            transition: color 0.2s;
        }
        .forgot-link:hover { color: #93c5fd; }

        .help-box {
            position: relative;
            z-index: 1;
            padding: 14px 16px;
            border-radius: 14px;
            background: rgba(2,6,23,0.35);
            border: 1px solid rgba(148,163,184,0.18);
            box-shadow: inset 0 1px 0 rgba(255,255,255,0.04);
        }
        .help-box p {
            font-size: 11.5px;
            color: rgba(203,213,225,0.82);
            line-height: 1.6;
            margin: 0;
        }

        /* Scrollbar gelap untuk halaman login */
        body::-webkit-scrollbar { width: 8px; }
        body::-webkit-scrollbar-track { background: rgba(15,23,42,0.5); }
        body::-webkit-scrollbar-thumb {
            background: rgba(100,116,139,0.45);
            border-radius: 4px;
        }
        body::-webkit-scrollbar-thumb:hover { background: rgba(148,163,184,0.55); }

        @media (max-height: 820px) {
            body { padding: 1.25rem 1rem 2rem; }
            .brand-header { margin-bottom: 16px; }
            .logo-wrapper { margin-bottom: 12px; }
            .logo-bg { width: 96px; height: 96px; border-radius: 22px; }
            .brand-name { font-size: 1.65rem; }
            .brand-sub { font-size: 0.8rem; margin-top: 4px; }
            .login-col--form { padding: 22px 24px 24px; }
            .login-col--side { padding: 24px 22px; }
            .btn-secondary-link { padding: 10px 12px; font-size: 12px; }
            .help-box { padding: 10px 12px; }
            .login-footer { margin-top: 14px; font-size: 11px; }
        }

        @media (max-width: 860px) {
            .login-grid {
                grid-template-columns: 1fr;
            }
            .login-card-top { padding: 20px 24px 0; }
            .login-col--form { padding: 20px 24px 28px; }
            .login-col--side {
                border-left: none;
                border-top: 1px solid rgba(255,255,255,0.08);
                padding: 24px 24px 28px;
            }
            .btn-secondary-link:hover { transform: none; }
        }
    </style>
</head>
<body>

    <!-- BACKGROUND -->
    <div class="bg-layer"></div>
    <div class="bg-grid"></div>
    <div class="orb orb-1"></div>
    <div class="orb orb-2"></div>
    <div class="orb orb-3"></div>

    <!-- TOASTS -->
    @foreach(['toast_success'=>'#059669','toast_error'=>'#dc2626','toast_warning'=>'#d97706','toast_info'=>'#2563eb'] as $key => $color)
    @if(session($key))
    <div class="toast" id="toast-{{ $loop->index }}" style="background: {{ $color }};">
        <svg class="error-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
        </svg>
        {{ session($key) }}
        <button onclick="document.getElementById('toast-{{ $loop->index }}').remove()" style="margin-left:auto;background:none;border:none;color:white;cursor:pointer;font-size:20px;line-height:1;opacity:0.7;">×</button>
    </div>
    @endif
    @endforeach

    <!-- MAIN -->
    <div class="login-container">

        <!-- BRAND HEADER -->
        <div class="brand-header">
            <a href="{{ route('home') }}" class="logo-wrapper" style="text-decoration:none;" title="Beranda {{ $apotekName }}">
                <div class="logo-ring"></div>
                <div class="logo-bg">
                    <img src="{{ asset('assets/images/logologin.jpeg') }}" alt="Apotek Almaira">
                </div>
            </a>
            <div class="brand-name">
                <span style="color: #34d399; font-weight: 800;">Apotek</span> <span style="color: #ffffff; font-weight: 300;">Almaira</span>
            </div>
            <div class="brand-sub">PT Nur Madani Farma &mdash; Login Staff (Sistem Internal)</div>
            <div class="brand-divider">
                <span></span>
                <div class="dot"></div>
                <div class="dot"></div>
                <div class="dot"></div>
                <span></span>
            </div>
        </div>

        <!-- LOGIN CARD -->
        @php
            $lockoutSeconds = (int) session('lockout_seconds', 0);
            $waForgotMessage = 'Halo Admin Apotek Almaira, saya lupa password akun staff sistem kasir. Mohon bantu reset password saya. Terima kasih.';
            $waForgotUrl = 'https://wa.me/' . $waNumber . '?text=' . rawurlencode($waForgotMessage);
        @endphp
        <div class="login-card" id="loginCard" data-lockout="{{ $lockoutSeconds }}">
            <div class="login-card-top">
                <div class="pending-alert" id="pendingAlert" style="{{ $pendingPartnerCount > 0 ? '' : 'display:none;' }}">
                    <svg style="width:18px;height:18px;flex-shrink:0;color:#fbbf24;margin-top:1px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                    </svg>
                    <p id="pendingAlertText">
                        <strong><span id="pendingCount">{{ $pendingPartnerCount }}</span> pendaftar mitra baru</strong> menunggu approval.
                        Login sebagai admin untuk memproses di menu Mitra Katalog.
                    </p>
                </div>
            </div>

            <div class="login-grid">
                <div class="login-col login-col--form">
                    <div class="card-title">Login Staff</div>
                    <p class="card-subtitle">Masuk ke sistem internal apotek untuk kasir, stok, laporan, dan administrasi.</p>
                    <div class="purpose-box">
                        <strong>Untuk siapa?</strong>
                        <p>Karyawan apotek yang punya akun staff (dibuat admin): kasir, admin, keuangan, dan apoteker.</p>
                        <ul>
                            <li>Kasir &amp; penjualan harian</li>
                            <li>Stok, pembelian, dan laporan</li>
                            <li>Manajemen mitra, karyawan, dan pengaturan</li>
                        </ul>
                    </div>

                    {{-- Error Messages --}}
                    @if($errors->any())
                    <div class="error-box">
                        @foreach($errors->all() as $error)
                        <div class="error-item">
                            <svg class="error-icon" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                            </svg>
                            {{ $error }}
                        </div>
                        @endforeach
                    </div>
                    @endif

                    <form method="POST" action="{{ route('login.post') }}" id="loginForm">
                        @csrf

                        {{-- Username / Email --}}
                        <div class="field-group">
                            <label class="field-label" for="loginField">Username atau Email</label>
                            <div class="input-wrap">
                                <svg class="input-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                </svg>
                                <input
                                    type="text"
                                    name="login"
                                    id="loginField"
                                    value="{{ old('login') }}"
                                    required
                                    autocomplete="username"
                                    placeholder="Masukkan username atau email"
                                    class="field-input"
                                    data-lockout-target
                                >
                            </div>
                        </div>

                        {{-- Password --}}
                        <div class="field-group" style="margin-bottom: 14px;">
                            <label class="field-label" for="passwordInput">Password</label>
                            <div class="input-wrap">
                                <svg class="input-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                                </svg>
                                <input
                                    type="password"
                                    name="password"
                                    id="passwordInput"
                                    required
                                    autocomplete="current-password"
                                    placeholder="Masukkan password"
                                    class="field-input"
                                    style="padding-right: 44px;"
                                    data-lockout-target
                                >
                                <button type="button" class="input-right-btn" onclick="togglePassword()" aria-label="Tampilkan atau sembunyikan password" data-lockout-target>
                                    <svg id="eyeIconShow" style="width:18px;height:18px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                    </svg>
                                    <svg id="eyeIconHide" style="width:18px;height:18px;display:none;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>
                                    </svg>
                                </button>
                            </div>
                        </div>

                        <div class="forgot-row">
                            <a href="{{ $waForgotUrl }}" target="_blank" rel="noopener" class="forgot-link">Lupa password? Hubungi Admin</a>
                        </div>

                        {{-- Remember Me --}}
                        <div class="remember-row">
                            <input type="checkbox" name="remember" id="remember" class="remember-check" data-lockout-target>
                            <label for="remember" class="remember-label">Ingat saya selama 30 hari</label>
                        </div>

                        {{-- Submit --}}
                        <button type="submit" id="submitBtn" class="btn-submit" data-lockout-target>
                            <svg id="submitIcon" style="width:18px;height:18px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/>
                            </svg>
                            <span id="submitLabel">Masuk sebagai Staff</span>
                        </button>
                    </form>

                    <div class="security-badge">
                        <svg style="width:13px;height:13px;" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"/>
                        </svg>
                        Koneksi Aman &amp; Terenkripsi
                    </div>
                </div>

                <div class="login-col login-col--side">
                    <div class="side-header">
                        <div class="side-eyebrow">
                            <svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                            Portal &amp; Bantuan
                        </div>
                        <h2 class="side-title">Bukan Staff?</h2>
                        <p class="side-subtitle">Pilih akses sesuai kebutuhan Anda. Setiap login punya fungsi berbeda.</p>
                    </div>

                    <div class="auth-links">
                        <a href="{{ route('mitra.login') }}" class="btn-secondary-link btn-secondary-link--primary">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                            <span class="link-body">
                                <span class="link-title">Login Mitra B2B</span>
                                <span class="link-desc">Untuk mitra usaha: pesan produk, PO, dan pantau pesanan</span>
                            </span>
                        </a>
                        <a href="{{ route('mitra.register') }}" class="btn-secondary-link btn-secondary-link--muted">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/></svg>
                            <span class="link-body">
                                <span class="link-title">Daftar Mitra Baru</span>
                                <span class="link-desc">Ajukan kemitraan B2B (menunggu approval admin)</span>
                            </span>
                        </a>
                        <a href="{{ route('catalog.index') }}" class="btn-secondary-link btn-secondary-link--muted">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                            <span class="link-body">
                                <span class="link-title">E-Catalog Publik</span>
                                <span class="link-desc">Cek produk &amp; harga tanpa login</span>
                            </span>
                        </a>
                        <a href="{{ route('home') }}" class="btn-secondary-link btn-secondary-link--muted">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                            <span class="link-body">
                                <span class="link-title">Beranda Website</span>
                                <span class="link-desc">Info apotek, lokasi, dan kontak</span>
                            </span>
                        </a>
                        <a href="{{ $waForgotUrl }}" target="_blank" rel="noopener" class="btn-secondary-link btn-secondary-link--wa">
                            <svg fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347z"/><path d="M12 0C5.373 0 0 5.373 0 12c0 2.11.55 4.09 1.514 5.805L0 24l6.336-1.662C8.09 23.45 10.004 24 12 24c6.627 0 12-5.373 12-12S18.627 0 12 0z"/></svg>
                            <span class="link-body">
                                <span class="link-title">Bantuan WhatsApp</span>
                                <span class="link-desc">{{ $apotekPhone }} — reset password &amp; bantuan akun</span>
                            </span>
                        </a>
                    </div>

                    <div class="help-box">
                        <p><strong>Login Staff</strong> hanya untuk karyawan apotek. Mitra B2B wajib pakai <strong>Login Mitra</strong>. Akun staff dibuat admin; lupa password hubungi WhatsApp admin.</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- FOOTER -->
        <div class="login-footer">
            ©Copyright Apotek Almaira v1.0 - {{ date('Y') }} PT Nur Madani Farma • Apotek Almaira - Banjarbaru Kalimantan Selatan
        </div>
    </div>

    <script>
        function togglePassword() {
            const inp = document.getElementById('passwordInput');
            const showIcon = document.getElementById('eyeIconShow');
            const hideIcon = document.getElementById('eyeIconHide');
            if (!inp || !showIcon || !hideIcon) return;
            const visible = inp.type === 'text';
            inp.type = visible ? 'password' : 'text';
            showIcon.style.display = visible ? 'block' : 'none';
            hideIcon.style.display = visible ? 'none' : 'block';
        }

        (function () {
            const card = document.getElementById('loginCard');
            const form = document.getElementById('loginForm');
            const submitBtn = document.getElementById('submitBtn');
            const submitLabel = document.getElementById('submitLabel');
            const submitIcon = document.getElementById('submitIcon');
            const lockoutTargets = () => document.querySelectorAll('[data-lockout-target]');
            let secondsLeft = parseInt(card?.dataset.lockout || '0', 10);

            function setLocked(locked) {
                lockoutTargets().forEach(el => { el.disabled = locked; });
            }

            function tickLockout() {
                if (secondsLeft <= 0) return;
                setLocked(true);
                submitLabel.textContent = 'Terkunci (' + secondsLeft + 's)';
                const timer = setInterval(() => {
                    secondsLeft--;
                    if (secondsLeft <= 0) {
                        clearInterval(timer);
                        setLocked(false);
                        submitLabel.textContent = 'Masuk sebagai Staff';
                        return;
                    }
                    submitLabel.textContent = 'Terkunci (' + secondsLeft + 's)';
                }, 1000);
            }

            if (form && submitBtn) {
                form.addEventListener('submit', function () {
                    if (submitBtn.disabled) return;
                    submitBtn.disabled = true;
                    submitLabel.textContent = 'Memproses...';
                    if (submitIcon) submitIcon.style.animation = 'spin 1s linear infinite';
                });
            }

            const loginField = document.getElementById('loginField');
            if (loginField && secondsLeft <= 0) loginField.focus();

            tickLockout();
        })();

        document.querySelectorAll('.toast').forEach(el => {
            setTimeout(() => el.remove(), 5000);
        });

        (function pollPendingPartners() {
            const alert = document.getElementById('pendingAlert');
            const countEl = document.getElementById('pendingCount');
            if (!alert || !countEl) return;

            const endpoint = @json(route('login.pending-partners'));
            let lastCount = parseInt(countEl.textContent || '0', 10);

            async function refresh() {
                try {
                    const res = await fetch(endpoint, { headers: { 'Accept': 'application/json' } });
                    if (!res.ok) return;
                    const data = await res.json();
                    const count = parseInt(data.pending_count || 0, 10);
                    countEl.textContent = count;
                    alert.style.display = count > 0 ? '' : 'none';
                    if (count > lastCount) {
                        alert.style.animation = 'none';
                        alert.offsetHeight;
                        alert.style.animation = 'slideIn 0.35s ease';
                    }
                    lastCount = count;
                } catch (_) { /* abaikan jika offline */ }
            }

            setInterval(refresh, 15000);
        })();
    </script>
</body>
</html>
