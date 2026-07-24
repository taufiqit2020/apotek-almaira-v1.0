@php
    $isKepalaItRoute = str_contains($exception->getMessage() ?? '', 'akses_kepala_it');
@endphp

@if($isKepalaItRoute)
{{-- Halaman cantik khusus: fitur ini hanya untuk Kepala IT --}}
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Akses Dibatasi — Apotek Almaira</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        :root {
            --emerald: #059669;
            --emerald-dark: #047857;
            --emerald-light: #d1fae5;
            --emerald-muted: #6ee7b7;
            --amber: #d97706;
            --ink: #0f172a;
            --muted: #64748b;
            --bg: #f0fdf4;
            --card: #fff;
            --border: #d1fae5;
        }
        html, body {
            min-height: 100vh;
            font-family: 'Inter', system-ui, sans-serif;
            background: linear-gradient(135deg, #ecfdf5 0%, #f0f9ff 50%, #fefce8 100%);
            color: var(--ink);
        }
        .shell {
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 2rem 1.25rem;
            position: relative;
            overflow: hidden;
        }
        /* Decorative blobs */
        .blob {
            position: fixed;
            border-radius: 50%;
            filter: blur(80px);
            opacity: 0.35;
            pointer-events: none;
            z-index: 0;
        }
        .blob-1 { width: 420px; height: 420px; background: #a7f3d0; top: -100px; right: -100px; }
        .blob-2 { width: 320px; height: 320px; background: #bae6fd; bottom: -80px; left: -80px; }
        .blob-3 { width: 200px; height: 200px; background: #fde68a; bottom: 20%; right: 5%; }

        .content { position: relative; z-index: 1; width: 100%; max-width: 480px; }

        /* Logo + Brand */
        .brand {
            display: flex;
            align-items: center;
            gap: 0.85rem;
            justify-content: center;
            margin-bottom: 2rem;
            animation: fadeDown 0.6s ease both;
        }
        .brand img {
            height: 52px;
            width: auto;
            object-fit: contain;
            filter: drop-shadow(0 4px 12px rgba(5,150,105,0.25));
        }
        .brand-text strong {
            display: block;
            font-size: 1rem;
            font-weight: 800;
            color: var(--emerald-dark);
            letter-spacing: -0.01em;
        }
        .brand-text span {
            display: block;
            font-size: 0.72rem;
            color: var(--muted);
            font-weight: 500;
            margin-top: 1px;
        }

        /* Main card */
        .card {
            background: rgba(255,255,255,0.85);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border-radius: 1.5rem;
            border: 1px solid rgba(209,250,229,0.8);
            box-shadow: 0 20px 60px -15px rgba(5,150,105,0.15), 0 4px 16px -4px rgba(0,0,0,0.06);
            overflow: hidden;
            animation: fadeUp 0.6s 0.1s ease both;
        }
        .card-top {
            background: linear-gradient(135deg, var(--emerald) 0%, #10b981 60%, #0891b2 100%);
            padding: 2.25rem 2rem 2rem;
            text-align: center;
            position: relative;
            overflow: hidden;
        }
        .card-top::before {
            content: '';
            position: absolute;
            top: -60px; right: -60px;
            width: 200px; height: 200px;
            background: rgba(255,255,255,0.08);
            border-radius: 50%;
        }
        .card-top::after {
            content: '';
            position: absolute;
            bottom: -40px; left: -40px;
            width: 140px; height: 140px;
            background: rgba(255,255,255,0.06);
            border-radius: 50%;
        }

        /* Shield icon */
        .shield-wrap {
            position: relative;
            z-index: 1;
            margin: 0 auto 1.25rem;
            width: 72px;
            height: 72px;
            background: rgba(255,255,255,0.18);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 2px solid rgba(255,255,255,0.3);
            box-shadow: 0 0 0 8px rgba(255,255,255,0.08);
        }
        .shield-wrap svg { width: 38px; height: 38px; color: #fff; }

        .badge-403 {
            position: relative;
            z-index: 1;
            display: inline-block;
            font-size: 0.65rem;
            font-weight: 800;
            letter-spacing: 0.12em;
            text-transform: uppercase;
            color: rgba(255,255,255,0.75);
            background: rgba(255,255,255,0.12);
            border: 1px solid rgba(255,255,255,0.2);
            border-radius: 2rem;
            padding: 0.2rem 0.7rem;
            margin-bottom: 0.75rem;
        }
        .card-title {
            position: relative;
            z-index: 1;
            font-size: 1.35rem;
            font-weight: 900;
            color: #fff;
            letter-spacing: -0.02em;
            line-height: 1.25;
            margin-bottom: 0.5rem;
        }
        .card-subtitle {
            position: relative;
            z-index: 1;
            font-size: 0.875rem;
            color: rgba(255,255,255,0.82);
            line-height: 1.5;
        }

        /* Card body */
        .card-body { padding: 1.75rem 2rem; }

        .quote-block {
            background: linear-gradient(135deg, #f0fdf4, #ecfdf5);
            border: 1px solid var(--border);
            border-left: 4px solid var(--emerald);
            border-radius: 0.85rem;
            padding: 1rem 1.1rem;
            margin-bottom: 1.35rem;
        }
        .quote-block p {
            font-size: 0.85rem;
            line-height: 1.65;
            color: #047857;
            font-style: italic;
            font-weight: 500;
        }
        .quote-block .quote-author {
            font-size: 0.72rem;
            color: var(--muted);
            margin-top: 0.5rem;
            font-style: normal;
            font-weight: 600;
        }

        /* Info box */
        .info-box {
            display: flex;
            gap: 0.75rem;
            align-items: flex-start;
            background: #fffbeb;
            border: 1px solid #fde68a;
            border-radius: 0.85rem;
            padding: 0.9rem 1rem;
            margin-bottom: 1.5rem;
        }
        .info-box svg { width: 1.1rem; height: 1.1rem; color: var(--amber); flex-shrink: 0; margin-top: 2px; }
        .info-box p { font-size: 0.8rem; color: #92400e; line-height: 1.5; }
        .info-box strong { color: #78350f; }

        /* Divider */
        .divider {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            margin-bottom: 1.35rem;
        }
        .divider::before, .divider::after {
            content: ''; flex: 1; height: 1px; background: #e2e8f0;
        }
        .divider span { font-size: 0.7rem; color: #94a3b8; font-weight: 600; white-space: nowrap; }

        /* Actions */
        .actions { display: flex; flex-wrap: wrap; gap: 0.65rem; }
        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.4rem;
            padding: 0.6rem 1.1rem;
            border-radius: 0.8rem;
            font-size: 0.85rem;
            font-weight: 700;
            text-decoration: none;
            border: 1px solid transparent;
            cursor: pointer;
            transition: all 0.18s ease;
            font-family: inherit;
            background: none;
        }
        .btn:hover { transform: translateY(-1px); }
        .btn:active { transform: none; }
        .btn svg { width: 0.9rem; height: 0.9rem; }
        .btn-emerald {
            flex: 1;
            background: linear-gradient(135deg, var(--emerald) 0%, #10b981 100%);
            color: #fff;
            box-shadow: 0 6px 18px -8px rgba(5,150,105,0.7);
        }
        .btn-emerald:hover { filter: brightness(1.07); box-shadow: 0 8px 22px -8px rgba(5,150,105,0.8); }
        .btn-outline {
            background: #fff;
            color: #334155;
            border-color: #e2e8f0;
            flex: 1;
        }
        .btn-outline:hover { background: #f8fafc; border-color: #cbd5e1; }

        /* Role badge */
        .role-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
            background: linear-gradient(135deg, var(--emerald-dark), var(--emerald));
            color: #fff;
            font-size: 0.7rem;
            font-weight: 800;
            letter-spacing: 0.04em;
            padding: 0.25rem 0.65rem;
            border-radius: 2rem;
        }
        .role-badge svg { width: 0.75rem; height: 0.75rem; }

        /* Footer meta */
        .meta {
            margin-top: 1.35rem;
            text-align: center;
            font-size: 0.72rem;
            color: #94a3b8;
            line-height: 1.6;
            animation: fadeUp 0.6s 0.3s ease both;
        }
        .meta strong { color: #64748b; }

        /* Animations */
        @keyframes fadeUp {
            from { opacity: 0; transform: translateY(18px); }
            to   { opacity: 1; transform: translateY(0); }
        }
        @keyframes fadeDown {
            from { opacity: 0; transform: translateY(-12px); }
            to   { opacity: 1; transform: translateY(0); }
        }

        @media (max-width: 440px) {
            .card-top { padding: 1.75rem 1.25rem 1.5rem; }
            .card-body { padding: 1.25rem 1.25rem; }
            .actions { flex-direction: column; }
            .brand img { height: 42px; }
        }
    </style>
</head>
<body>
    <div class="blob blob-1"></div>
    <div class="blob blob-2"></div>
    <div class="blob blob-3"></div>

    <div class="shell">
        <div class="content">
            {{-- Brand Logo --}}
            <div class="brand">
                <img src="{{ asset('assets/images/logo-ptnmf.png') }}" alt="PT Nur Madani Farma">
                <div class="brand-text">
                    <strong>PT Nur Madani Farma</strong>
                    <span>Apotek Almaira · Banjarbaru</span>
                </div>
            </div>

            {{-- Main Card --}}
            <div class="card">
                <div class="card-top">
                    <div class="shield-wrap">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75"
                                d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                        </svg>
                    </div>
                    <div class="badge-403">Akses Terbatas · 403</div>
                    <h1 class="card-title">Fitur Khusus Kepala IT</h1>
                    <p class="card-subtitle">
                        Halaman yang Anda tuju memerlukan<br>otorisasi tingkat tertinggi dalam sistem.
                    </p>
                </div>

                <div class="card-body">
                    {{-- Motivational Quote --}}
                    <div class="quote-block">
                        <p>
                            "Keamanan sistem adalah fondasi kepercayaan. Setiap batasan akses hadir untuk
                            menjaga integritas data dan melindungi seluruh operasional perusahaan."
                        </p>
                        <div class="quote-author">— Kebijakan Keamanan Sistem, PT Nur Madani Farma</div>
                    </div>

                    {{-- Info box --}}
                    <div class="info-box">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                        </svg>
                        <p>
                            Fitur <strong>Edit & Hapus PO Mitra</strong> hanya dapat diakses oleh
                            <span class="role-badge">
                                <svg fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M2.166 4.999A11.954 11.954 0 0010 1.944 11.954 11.954 0 0017.834 5c.11.65.166 1.32.166 2 0 5.523-3.997 10.114-9.335 11.532C3.998 17.114 0 12.523 0 7c0-.68.057-1.35.166-2.001zm11.541 3.708a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                </svg>
                                Kepala IT
                            </span>.
                            Jika Anda merasa berhak mendapatkan akses ini, silakan hubungi administrator sistem.
                        </p>
                    </div>

                    <div class="divider"><span>Navigasi</span></div>

                    {{-- Actions --}}
                    <div class="actions">
                        @auth
                        <a href="{{ route('partner-orders.index') }}" class="btn btn-emerald">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                            </svg>
                            Daftar PO Mitra
                        </a>
                        @else
                        <a href="{{ route('login') }}" class="btn btn-emerald">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/>
                            </svg>
                            Masuk Sistem
                        </a>
                        @endauth
                        <button type="button" class="btn btn-outline"
                            onclick="if (history.length > 1) history.back(); else location.href='{{ route('dashboard') }}'">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                            </svg>
                            Kembali
                        </button>
                    </div>
                </div>
            </div>

            <p class="meta">
                &copy; {{ date('Y') }} <strong>PT Nur Madani Farma</strong> &middot; Apotek Almaira Banjarbaru<br>
                Sistem Manajemen Apotek v1.0 &middot; Kontak: <strong>085246900376</strong>
            </p>
        </div>
    </div>
</body>
</html>
@else
@include('errors.layout', [
    'code' => '403',
    'title' => 'Akses Ditolak',
    'message' => $exception->getMessage() ?: 'Anda tidak memiliki akses ke halaman ini.',
    'hint' => 'Halaman ini hanya untuk role tertentu. Pastikan Anda login dengan akun yang berwenang, atau kembali ke menu yang diizinkan.',
    'tone' => 'rose',
    'icon' => 'lock',
])
@endif
