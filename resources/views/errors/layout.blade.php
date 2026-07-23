{{-- Layout error pages — Apotek Almaira (inline CSS agar tetap tampil meski Vite gagal) --}}
@php
    $code = (string) ($code ?? 'Error');
    $title = $title ?? 'Terjadi Kesalahan';
    $message = $message ?? 'Maaf, permintaan Anda tidak dapat diproses.';
    $hint = $hint ?? null;
    $tone = $tone ?? 'emerald'; // emerald | amber | rose | slate
    $icon = $icon ?? 'alert';
    $user = auth()->user();
    if ($user) {
        if (method_exists($user, 'isMitra') && $user->isMitra()) {
            $homeUrl = route('mitra.account');
            $homeLabel = 'Ke Akun Mitra';
        } else {
            $homeUrl = route('dashboard');
            $homeLabel = 'Ke Dashboard';
        }
    } else {
        $homeUrl = url('/');
        $homeLabel = 'Ke Beranda';
    }
@endphp
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex">
    <title>{{ $code }} · {{ $title }} · Almaira</title>
    <link rel="icon" type="image/png" href="{{ asset('assets/images/logo-apotek.png') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --ink: #0f172a;
            --muted: #64748b;
            --line: #e2e8f0;
            --card: #ffffff;
            --bg1: #f0fdf4;
            --bg2: #ecfeff;
            --accent: #059669;
            --accent-soft: #d1fae5;
            --accent-ink: #065f46;
            --badge: #047857;
        }
        .tone-amber {
            --bg1: #fffbeb;
            --bg2: #fff7ed;
            --accent: #d97706;
            --accent-soft: #fef3c7;
            --accent-ink: #92400e;
            --badge: #b45309;
        }
        .tone-rose {
            --bg1: #fff1f2;
            --bg2: #fff7ed;
            --accent: #e11d48;
            --accent-soft: #ffe4e6;
            --accent-ink: #9f1239;
            --badge: #be123c;
        }
        .tone-slate {
            --bg1: #f8fafc;
            --bg2: #f1f5f9;
            --accent: #475569;
            --accent-soft: #e2e8f0;
            --accent-ink: #1e293b;
            --badge: #334155;
        }
        * { box-sizing: border-box; margin: 0; padding: 0; }
        html, body { min-height: 100%; }
        body {
            font-family: 'Plus Jakarta Sans', system-ui, sans-serif;
            color: var(--ink);
            background:
                radial-gradient(ellipse 80% 60% at 10% -10%, var(--bg1), transparent 55%),
                radial-gradient(ellipse 70% 50% at 100% 0%, var(--bg2), transparent 50%),
                linear-gradient(180deg, #ffffff 0%, #f8fafc 100%);
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 2rem 1.25rem 2.5rem;
        }
        .shell { width: 100%; max-width: 34rem; position: relative; }
        .brand {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.75rem;
            margin-bottom: 1.5rem;
        }
        .brand img {
            width: 2.75rem;
            height: 2.75rem;
            object-fit: contain;
            border-radius: 0.75rem;
            background: #fff;
            border: 1px solid var(--line);
            box-shadow: 0 4px 14px rgba(15, 23, 42, 0.06);
        }
        .brand-text { text-align: left; line-height: 1.15; }
        .brand-text strong {
            display: block;
            font-size: 0.95rem;
            font-weight: 800;
            letter-spacing: -0.02em;
        }
        .brand-text span {
            display: block;
            font-size: 0.68rem;
            color: var(--muted);
            font-weight: 600;
            letter-spacing: 0.04em;
            text-transform: uppercase;
            margin-top: 0.15rem;
        }
        .card {
            background: var(--card);
            border: 1px solid var(--line);
            border-radius: 1.5rem;
            box-shadow:
                0 1px 2px rgba(15, 23, 42, 0.04),
                0 18px 40px -20px rgba(15, 23, 42, 0.18);
            overflow: hidden;
            animation: rise 0.45s ease-out both;
        }
        @keyframes rise {
            from { opacity: 0; transform: translateY(10px) scale(0.98); }
            to { opacity: 1; transform: none; }
        }
        .card-top {
            padding: 1.75rem 1.5rem 1.25rem;
            text-align: center;
            background: linear-gradient(180deg, var(--accent-soft) 0%, transparent 100%);
            border-bottom: 1px solid rgba(226, 232, 240, 0.7);
        }
        .badge {
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            padding: 0.35rem 0.75rem;
            border-radius: 999px;
            background: #fff;
            border: 1px solid rgba(5, 150, 105, 0.2);
            color: var(--badge);
            font-size: 0.72rem;
            font-weight: 800;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            margin-bottom: 1rem;
            box-shadow: 0 2px 8px rgba(15, 23, 42, 0.04);
        }
        .tone-amber .badge { border-color: rgba(217, 119, 6, 0.25); }
        .tone-rose .badge { border-color: rgba(225, 29, 72, 0.25); }
        .tone-slate .badge { border-color: rgba(71, 85, 105, 0.25); }
        .icon-wrap {
            width: 4.25rem;
            height: 4.25rem;
            margin: 0 auto 1rem;
            border-radius: 1.25rem;
            display: grid;
            place-items: center;
            background: #fff;
            border: 1px solid rgba(5, 150, 105, 0.18);
            box-shadow: 0 10px 24px -12px rgba(5, 150, 105, 0.45);
            color: var(--accent);
        }
        .tone-amber .icon-wrap { border-color: rgba(217, 119, 6, 0.2); box-shadow: 0 10px 24px -12px rgba(217, 119, 6, 0.4); }
        .tone-rose .icon-wrap { border-color: rgba(225, 29, 72, 0.2); box-shadow: 0 10px 24px -12px rgba(225, 29, 72, 0.4); }
        .tone-slate .icon-wrap { border-color: rgba(71, 85, 105, 0.2); box-shadow: 0 10px 24px -12px rgba(71, 85, 105, 0.35); }
        .icon-wrap svg { width: 2rem; height: 2rem; }
        .code {
            font-size: clamp(2.6rem, 8vw, 3.4rem);
            font-weight: 800;
            letter-spacing: -0.05em;
            line-height: 1;
            color: var(--accent-ink);
            margin-bottom: 0.55rem;
        }
        .headline {
            font-size: 1.2rem;
            font-weight: 800;
            letter-spacing: -0.02em;
            color: var(--ink);
            margin-bottom: 0.45rem;
        }
        .message {
            font-size: 0.925rem;
            line-height: 1.55;
            color: var(--muted);
            max-width: 28rem;
            margin: 0 auto;
        }
        .card-body { padding: 1.25rem 1.5rem 1.5rem; }
        .hint {
            display: flex;
            gap: 0.7rem;
            align-items: flex-start;
            padding: 0.85rem 1rem;
            border-radius: 0.9rem;
            background: #f8fafc;
            border: 1px solid var(--line);
            margin-bottom: 1.15rem;
            text-align: left;
        }
        .hint svg {
            width: 1.1rem;
            height: 1.1rem;
            flex-shrink: 0;
            margin-top: 0.1rem;
            color: var(--accent);
        }
        .hint p {
            font-size: 0.8rem;
            line-height: 1.45;
            color: #475569;
        }
        .actions {
            display: flex;
            flex-wrap: wrap;
            gap: 0.6rem;
            justify-content: center;
        }
        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.4rem;
            min-height: 2.55rem;
            padding: 0.55rem 1rem;
            border-radius: 0.8rem;
            font-size: 0.85rem;
            font-weight: 700;
            text-decoration: none;
            border: 1px solid transparent;
            cursor: pointer;
            transition: transform 0.15s ease, box-shadow 0.15s ease, background 0.15s ease;
            font-family: inherit;
            background: none;
        }
        .btn:hover { transform: translateY(-1px); }
        .btn:active { transform: none; }
        .btn svg { width: 1rem; height: 1rem; }
        .btn-primary {
            background: var(--accent);
            color: #fff;
            box-shadow: 0 8px 18px -10px rgba(5, 150, 105, 0.7);
        }
        .tone-amber .btn-primary { box-shadow: 0 8px 18px -10px rgba(217, 119, 6, 0.65); }
        .tone-rose .btn-primary { box-shadow: 0 8px 18px -10px rgba(225, 29, 72, 0.65); }
        .tone-slate .btn-primary { box-shadow: 0 8px 18px -10px rgba(71, 85, 105, 0.55); }
        .btn-primary:hover { filter: brightness(1.05); }
        .btn-secondary {
            background: #fff;
            color: #334155;
            border-color: var(--line);
        }
        .btn-secondary:hover { background: #f8fafc; }
        .meta {
            margin-top: 1.35rem;
            text-align: center;
            font-size: 0.72rem;
            color: #94a3b8;
            line-height: 1.5;
        }
        .meta strong { color: #64748b; font-weight: 700; }
        @media (max-width: 420px) {
            .actions { flex-direction: column; }
            .btn { width: 100%; }
        }
    </style>
</head>
<body class="tone-{{ $tone }}">
    <div class="shell">
        <div class="brand">
            <img src="{{ asset('assets/images/logo-apotek.png') }}" alt="Apotek Almaira">
            <div class="brand-text">
                <strong>Apotek Almaira</strong>
                <span>PT Nur Madani Farma</span>
            </div>
        </div>

        <div class="card" role="main">
            <div class="card-top">
                <div class="badge">HTTP {{ $code }}</div>
                <div class="icon-wrap">
                    @if($icon === 'lock')
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                    @elseif($icon === 'search')
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    @elseif($icon === 'clock')
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    @elseif($icon === 'ban')
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/></svg>
                    @elseif($icon === 'server')
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M5 12h14M5 12a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v4a2 2 0 01-2 2M5 12a2 2 0 00-2 2v4a2 2 0 002 2h14a2 2 0 002-2v-4a2 2 0 00-2-2m-2-4h.01M17 16h.01"/></svg>
                    @elseif($icon === 'auth')
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                    @else
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                    @endif
                </div>
                <div class="code">{{ $code }}</div>
                <h1 class="headline">{{ $title }}</h1>
                <p class="message">{{ $message }}</p>
            </div>

            <div class="card-body">
                @if($hint)
                <div class="hint">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M12 2a10 10 0 100 20A10 10 0 0012 2z"/></svg>
                    <p>{{ $hint }}</p>
                </div>
                @endif

                <div class="actions">
                    <a href="{{ $homeUrl }}" class="btn btn-primary">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                        {{ $homeLabel }}
                    </a>
                    <button type="button" class="btn btn-secondary" onclick="if (history.length > 1) history.back(); else location.href='{{ $homeUrl }}'">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                        Kembali
                    </button>
                    @guest
                    <a href="{{ route('mitra.login') }}" class="btn btn-secondary">
                        Login Mitra
                    </a>
                    <a href="{{ route('login') }}" class="btn btn-secondary">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/></svg>
                        Login Staff
                    </a>
                    @endguest
                </div>
            </div>
        </div>

        <p class="meta">
            © {{ date('Y') }} <strong>PT Nur Madani Farma</strong> · Apotek Almaira Banjarbaru<br>
            Jika masalah berlanjut, hubungi Super Admin / IT.
        </p>
    </div>
</body>
</html>
