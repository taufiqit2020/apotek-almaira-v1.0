<style>
    * { box-sizing: border-box; margin: 0; padding: 0; }
    body {
        min-height: 100vh;
        font-family: 'Plus Jakarta Sans', 'Inter', sans-serif;
        background: #0b1120;
        display: flex;
        flex-direction: column;
        align-items: center;
        overflow-x: hidden;
        overflow-y: auto;
        position: relative;
        padding: 2.5rem 1rem 3rem;
        -webkit-font-smoothing: antialiased;
    }
    .bg-layer {
        position: fixed; inset: 0; z-index: 0;
        background: radial-gradient(ellipse 120% 100% at 10% 0%, #0d2b5e 0%, transparent 60%),
                    radial-gradient(ellipse 80% 80% at 90% 100%, #063d30 0%, transparent 55%),
                    linear-gradient(160deg, #060e20 0%, #0e1d3d 45%, #091a29 100%);
    }
    .orb { position: fixed; border-radius: 50%; filter: blur(80px); pointer-events: none; animation: floatOrb 8s ease-in-out infinite; }
    .orb-1 { width: 500px; height: 500px; background: radial-gradient(circle, rgba(37,99,235,0.18) 0%, transparent 70%); top: -150px; left: -150px; }
    .orb-2 { width: 400px; height: 400px; background: radial-gradient(circle, rgba(16,185,129,0.14) 0%, transparent 70%); bottom: -100px; right: -100px; animation-delay: 3s; }
    .orb-3 { width: 300px; height: 300px; background: radial-gradient(circle, rgba(99,102,241,0.1) 0%, transparent 70%); top: 50%; right: 10%; animation-delay: 6s; }
    .bg-grid {
        position: fixed; inset: 0;
        background-image: linear-gradient(rgba(255,255,255,0.02) 1px, transparent 1px), linear-gradient(90deg, rgba(255,255,255,0.02) 1px, transparent 1px);
        background-size: 48px 48px; pointer-events: none;
    }
    @keyframes floatOrb {
        0%, 100% { transform: translate(0,0) scale(1); }
        33% { transform: translate(15px,-20px) scale(1.05); }
        66% { transform: translate(-10px,10px) scale(0.95); }
    }
    .toast {
        position: fixed; top: 1.25rem; right: 1.25rem; z-index: 9999;
        display: flex; align-items: center; gap: 12px; padding: 14px 18px;
        border-radius: 14px; color: white; font-size: 14px; font-weight: 600;
        max-width: 400px; box-shadow: 0 12px 40px rgba(0,0,0,0.35);
        animation: slideIn 0.35s ease; backdrop-filter: blur(10px);
        border: 1px solid rgba(255,255,255,0.12);
    }
    @keyframes slideIn { from { opacity:0; transform:translateX(20px); } to { opacity:1; transform:translateX(0); } }
    .auth-container {
        position: relative; z-index: 10; width: 100%; max-width: {{ $maxWidth ?? '480px' }};
        padding: 0; margin-top: auto; margin-bottom: auto; flex-shrink: 0;
    }
    .auth-container--wide { max-width: 960px; }
    .brand-header { text-align: center; margin-bottom: 28px; }
    .logo-wrapper { position: relative; display: inline-block; margin-bottom: 18px; }
    .logo-ring {
        position: absolute; inset: -8px; border-radius: 32px;
        background: conic-gradient(from 0deg, rgba(37,99,235,0) 0%, rgba(37,99,235,0.6) 25%, rgba(16,185,129,0.6) 50%, rgba(37,99,235,0.6) 75%, rgba(37,99,235,0) 100%);
        animation: spinRing 4s linear infinite;
    }
    @keyframes spinRing { from { transform: rotate(0deg); } to { transform: rotate(360deg); } }
    .logo-bg {
        position: relative; width: 120px; height: 120px; background: #ffffff; border-radius: 28px;
        display: flex; align-items: center; justify-content: center; padding: 12px;
        box-shadow: 0 0 40px rgba(37,99,235,0.35), 0 8px 32px rgba(0,0,0,0.3); overflow: hidden;
    }
    .logo-bg img { width: 100%; height: 100%; object-fit: contain; }
    .brand-name { font-size: 1.35rem; font-weight: 800; color: #fff; letter-spacing: -0.02em; line-height: 1.3; margin-bottom: 6px; }
    .brand-sub { font-size: 0.875rem; color: rgba(186,230,253,0.85); font-weight: 500; }
    .brand-divider { display: flex; align-items: center; gap: 8px; justify-content: center; margin-top: 14px; }
    .brand-divider span { display: block; height: 1px; width: 50px; background: linear-gradient(90deg, transparent, rgba(96,165,250,0.5), transparent); }
    .brand-divider .dot { width: 5px; height: 5px; border-radius: 50%; background: rgba(96,165,250,0.6); }
    .auth-card {
        background: rgba(255,255,255,0.08); backdrop-filter: blur(24px);
        border: 1px solid rgba(255,255,255,0.14); border-radius: 24px;
        padding: 32px 32px 28px;
        box-shadow: 0 30px 70px rgba(0,0,0,0.4), inset 0 1px 0 rgba(255,255,255,0.1);
    }
    .auth-card--split { padding: 0; overflow: hidden; }

    .auth-grid {
        display: grid;
        grid-template-columns: minmax(0, 1.15fr) minmax(280px, 0.85fr);
        align-items: stretch;
    }
    .auth-col--form { padding: 28px 36px 32px; }
    .auth-col--side {
        position: relative;
        padding: 32px 28px 32px;
        background: linear-gradient(165deg, rgba(15,23,42,0.72) 0%, rgba(6,78,59,0.18) 55%, rgba(30,58,138,0.15) 100%);
        border-left: 1px solid rgba(255,255,255,0.08);
        display: flex; flex-direction: column; justify-content: center; gap: 18px;
    }
    .auth-col--side::before {
        content: ''; position: absolute; top: 18%; right: -40px;
        width: 140px; height: 140px; border-radius: 50%;
        background: radial-gradient(circle, rgba(16,185,129,0.18) 0%, transparent 70%);
        pointer-events: none;
    }
    .side-header { position: relative; z-index: 1; }
    .side-eyebrow {
        display: inline-flex; align-items: center; gap: 6px;
        padding: 5px 10px; border-radius: 999px;
        background: rgba(16,185,129,0.12); border: 1px solid rgba(16,185,129,0.25);
        color: #6ee7b7; font-size: 10px; font-weight: 700;
        letter-spacing: 0.6px; text-transform: uppercase; margin-bottom: 10px;
    }
    .side-title {
        font-size: 1.25rem; font-weight: 800; color: #f8fafc;
        letter-spacing: -0.3px; line-height: 1.25; margin-bottom: 6px;
    }
    .side-subtitle {
        font-size: 0.75rem; color: rgba(148,163,184,0.9);
        line-height: 1.55; margin: 0;
    }
    .card-title {
        font-size: 1.25rem; font-weight: 800; color: #f8fafc; margin-bottom: 6px;
        display: flex; align-items: center; gap: 10px; letter-spacing: -0.02em;
    }
    .card-title::before {
        content: ''; display: block; width: 4px; height: 22px;
        background: linear-gradient(to bottom, #3b82f6, #10b981); border-radius: 2px; flex-shrink: 0;
    }
    .card-subtitle {
        font-size: 0.875rem; color: rgba(203,213,225,0.9); line-height: 1.55;
        margin-bottom: 22px; padding-left: 14px; font-weight: 500;
    }
    .error-box { background: rgba(239,68,68,0.15); border: 1px solid rgba(239,68,68,0.35); border-radius: 12px; padding: 14px 16px; margin-bottom: 18px; }
    .error-item { display: flex; align-items: flex-start; gap: 8px; color: #fecaca; font-size: 0.875rem; font-weight: 500; line-height: 1.5; }
    .error-item + .error-item { margin-top: 6px; }
    .field-group { margin-bottom: 18px; }
    .field-label {
        display: block; color: #e2e8f0; font-size: 0.8125rem; font-weight: 700;
        margin-bottom: 8px; letter-spacing: 0.03em;
    }
    .input-wrap { position: relative; }
    .input-icon {
        position: absolute; left: 14px; top: 50%; transform: translateY(-50%);
        color: rgba(148,163,184,0.75); width: 18px; height: 18px; pointer-events: none;
    }
    .field-input, .field-select, .field-textarea {
        width: 100%; background: rgba(15,23,42,0.55); border: 1.5px solid rgba(148,163,184,0.25);
        color: #f8fafc; border-radius: 12px; font-size: 0.9375rem; font-weight: 500;
        font-family: 'Plus Jakarta Sans', 'Inter', sans-serif;
        outline: none; transition: border-color 0.2s, background 0.2s, box-shadow 0.2s;
    }
    .field-input, .field-select { padding: 13px 14px 13px 44px; }
    .field-input.has-toggle { padding-right: 48px; }
    .field-textarea { padding: 12px 14px; min-height: 80px; resize: vertical; }
    .field-input::placeholder, .field-textarea::placeholder { color: rgba(148,163,184,0.55); font-weight: 400; }
    .field-input:focus, .field-select:focus, .field-textarea:focus {
        border-color: rgba(96,165,250,0.8); background: rgba(15,23,42,0.75);
        box-shadow: 0 0 0 3px rgba(59,130,246,0.18);
    }
    .field-input:-webkit-autofill {
        -webkit-box-shadow: 0 0 0 30px rgba(14,29,61,0.95) inset !important;
        -webkit-text-fill-color: #f8fafc !important;
    }
    .field-select option { color: #111; background: #fff; }
    .input-right-btn {
        position: absolute; right: 10px; top: 50%; transform: translateY(-50%);
        background: rgba(255,255,255,0.06); border: 1px solid rgba(255,255,255,0.1);
        color: rgba(203,213,225,0.9); cursor: pointer; padding: 6px;
        border-radius: 8px; display: flex; align-items: center; justify-content: center;
        transition: background 0.15s, color 0.15s;
    }
    .input-right-btn:hover { background: rgba(255,255,255,0.12); color: #fff; }
    .input-right-btn:focus-visible { outline: 2px solid rgba(59,130,246,0.6); outline-offset: 2px; }
    .remember-row {
        display: flex; align-items: center; gap: 10px; margin-bottom: 22px;
        color: #cbd5e1; font-size: 0.875rem; font-weight: 500; cursor: pointer; user-select: none;
    }
    .remember-row input[type="checkbox"] {
        width: 18px; height: 18px; accent-color: #3b82f6; cursor: pointer; flex-shrink: 0;
    }
    .btn-submit {
        width: 100%; background: linear-gradient(135deg, #1d4ed8 0%, #2563eb 50%, #3b82f6 100%);
        color: white; border: none; border-radius: 12px; padding: 14px 16px; font-size: 1rem; font-weight: 700;
        cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 8px;
        transition: transform 0.2s, box-shadow 0.2s;
        font-family: 'Plus Jakarta Sans', 'Inter', sans-serif;
        box-shadow: 0 4px 20px rgba(37,99,235,0.45); letter-spacing: 0.01em;
    }
    .btn-submit:hover { transform: translateY(-2px); box-shadow: 0 8px 28px rgba(37,99,235,0.55); }
    .btn-submit:active { transform: translateY(0); }
    .auth-links {
        position: relative; z-index: 1;
        display: flex; flex-direction: column; gap: 9px;
    }
    .btn-secondary-link {
        display: flex; align-items: center; justify-content: flex-start; gap: 10px; width: 100%;
        padding: 12px 14px; border-radius: 12px; font-size: 0.8125rem; font-weight: 600;
        color: rgba(226,232,240,0.95); border: 1px solid rgba(148,163,184,0.2); text-decoration: none;
        background: rgba(255,255,255,0.04);
        transition: background 0.2s, color 0.2s, border-color 0.2s, transform 0.2s;
    }
    .btn-secondary-link:hover {
        background: rgba(255,255,255,0.1); color: #fff;
        border-color: rgba(148,163,184,0.38); transform: translateX(4px);
    }
    .btn-secondary-link svg { width: 17px; height: 17px; opacity: 0.8; flex-shrink: 0; }
    .btn-secondary-link--primary {
        background: linear-gradient(135deg, rgba(37,99,235,0.22), rgba(16,185,129,0.14));
        border-color: rgba(96,165,250,0.35); color: #e0f2fe;
    }
    .btn-secondary-link--primary:hover {
        background: linear-gradient(135deg, rgba(37,99,235,0.32), rgba(16,185,129,0.22));
        border-color: rgba(96,165,250,0.5);
    }
    .btn-secondary-link--muted { font-size: 0.78rem; color: rgba(203,213,225,0.88); }
    .btn-secondary-link--wa {
        margin-top: 4px; border-color: rgba(37,211,102,0.4); color: #d1fae5;
        background: linear-gradient(135deg, rgba(37,211,102,0.14), rgba(16,185,129,0.1));
        box-shadow: 0 4px 18px rgba(37,211,102,0.12);
    }
    .btn-secondary-link--wa:hover {
        background: linear-gradient(135deg, rgba(37,211,102,0.22), rgba(16,185,129,0.16));
        color: #ecfdf5; border-color: rgba(37,211,102,0.55);
    }
    .help-box {
        position: relative; z-index: 1; padding: 14px 16px; border-radius: 14px;
        background: rgba(2,6,23,0.35); border: 1px solid rgba(148,163,184,0.18);
        box-shadow: inset 0 1px 0 rgba(255,255,255,0.04);
    }
    .help-box p {
        font-size: 0.72rem; color: rgba(203,213,225,0.82); line-height: 1.6; margin: 0;
    }
    .security-badge {
        display: flex; align-items: center; justify-content: center; gap: 6px;
        margin-top: 18px; color: rgba(16,185,129,0.5); font-size: 0.6875rem;
    }
    .forgot-row { display: flex; justify-content: flex-end; margin: -6px 0 14px; }
    .forgot-link {
        font-size: 0.75rem; font-weight: 600; color: rgba(147,197,253,0.75);
        text-decoration: none; transition: color 0.2s;
    }
    .forgot-link:hover { color: #93c5fd; }

    /* ===== Register success page ===== */
    .success-hero {
        text-align: center;
        padding: 28px 32px 24px;
        border-bottom: 1px solid rgba(255,255,255,0.08);
        background: linear-gradient(180deg, rgba(16,185,129,0.08) 0%, transparent 100%);
    }
    .success-icon-wrap {
        width: 76px; height: 76px; margin: 0 auto 16px; border-radius: 50%;
        background: linear-gradient(135deg, rgba(16,185,129,0.22), rgba(37,99,235,0.12));
        border: 2px solid rgba(52,211,153,0.35);
        display: flex; align-items: center; justify-content: center;
        box-shadow: 0 0 30px rgba(16,185,129,0.2);
    }
    .success-icon-wrap svg { width: 36px; height: 36px; color: #34d399; }
    .success-title {
        font-size: 1.5rem; font-weight: 800; color: #f8fafc;
        letter-spacing: -0.02em; margin-bottom: 8px;
    }
    .success-subtitle {
        font-size: 0.875rem; color: rgba(203,213,225,0.92);
        line-height: 1.65; max-width: 520px; margin: 0 auto 14px;
    }
    .success-code-badge {
        display: inline-flex; align-items: center; gap: 8px;
        padding: 8px 14px; border-radius: 999px;
        background: rgba(15,23,42,0.55); border: 1px solid rgba(52,211,153,0.3);
        font-size: 0.8125rem; font-weight: 700; color: #6ee7b7;
    }
    .success-code-badge span { color: #fff; font-size: 0.9375rem; }
    .status-pill {
        display: inline-flex; align-items: center; gap: 6px;
        padding: 5px 12px; border-radius: 999px; margin-top: 12px;
        background: rgba(251,191,36,0.15); border: 1px solid rgba(251,191,36,0.35);
        color: #fde68a; font-size: 0.75rem; font-weight: 700;
    }
    .status-pill::before {
        content: ''; width: 7px; height: 7px; border-radius: 50%;
        background: #fbbf24; animation: pulseDot 1.5s ease-in-out infinite;
    }
    @keyframes pulseDot {
        0%, 100% { opacity: 1; transform: scale(1); }
        50% { opacity: 0.5; transform: scale(0.85); }
    }
    .success-grid {
        display: grid;
        grid-template-columns: minmax(0, 1.2fr) minmax(300px, 0.8fr);
        align-items: stretch;
    }
    .success-col { padding: 28px 32px 32px; }
    .success-col--side {
        background: linear-gradient(165deg, rgba(15,23,42,0.72) 0%, rgba(6,78,59,0.18) 55%, rgba(30,58,138,0.12) 100%);
        border-left: 1px solid rgba(255,255,255,0.08);
        display: flex; flex-direction: column; gap: 18px;
    }
    .panel-heading {
        font-size: 0.6875rem; font-weight: 800; letter-spacing: 0.1em;
        text-transform: uppercase; color: #6ee7b7; margin-bottom: 14px;
        display: flex; align-items: center; gap: 8px;
    }
    .panel-heading::before {
        content: ''; width: 4px; height: 14px; border-radius: 2px;
        background: linear-gradient(to bottom, #3b82f6, #10b981); flex-shrink: 0;
    }
    .summary-table {
        border: 1px solid rgba(148,163,184,0.18);
        border-radius: 14px; overflow: hidden;
        background: rgba(2,6,23,0.35);
    }
    .summary-row {
        display: grid; grid-template-columns: 128px 1fr; gap: 12px;
        padding: 11px 16px; border-bottom: 1px solid rgba(255,255,255,0.06);
        align-items: start;
    }
    .summary-row:last-child { border-bottom: none; }
    .summary-row:nth-child(even) { background: rgba(255,255,255,0.02); }
    .summary-label {
        font-size: 0.75rem; font-weight: 700; color: #94a3b8;
        line-height: 1.45; padding-top: 1px;
    }
    .summary-value {
        font-size: 0.8125rem; font-weight: 500; color: #f1f5f9;
        line-height: 1.5; word-break: break-word;
    }
    .summary-value--code {
        font-size: 0.9375rem; font-weight: 800; color: #34d399;
        letter-spacing: 0.02em;
    }
    .steps-list { display: flex; flex-direction: column; gap: 12px; }
    .step-item {
        display: flex; align-items: flex-start; gap: 12px;
        padding: 12px 14px; border-radius: 12px;
        background: rgba(2,6,23,0.35); border: 1px solid rgba(148,163,184,0.15);
    }
    .step-num {
        width: 28px; height: 28px; border-radius: 50%; flex-shrink: 0;
        background: linear-gradient(135deg, #1d4ed8, #10b981);
        color: #fff; font-size: 0.75rem; font-weight: 800;
        display: flex; align-items: center; justify-content: center;
    }
    .step-text {
        font-size: 0.8125rem; color: rgba(226,232,240,0.92);
        line-height: 1.55; margin: 0; padding-top: 4px;
    }
    .step-text strong { color: #fff; }
    .btn-wa-admin {
        width: 100%;
        background: linear-gradient(135deg, #128C7E 0%, #25D366 50%, #20bd5a 100%);
        color: white; border: none; border-radius: 12px;
        padding: 15px 16px; font-size: 0.9375rem; font-weight: 700;
        cursor: pointer; display: flex; align-items: center; justify-content: center;
        gap: 10px; text-decoration: none;
        transition: transform 0.2s, box-shadow 0.2s;
        font-family: 'Plus Jakarta Sans', 'Inter', sans-serif;
        box-shadow: 0 4px 22px rgba(37,211,102,0.38);
        letter-spacing: 0.01em;
    }
    .btn-wa-admin:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 30px rgba(37,211,102,0.48);
        color: white;
    }
    .wa-hint {
        text-align: center; font-size: 0.6875rem; color: rgba(148,163,184,0.8);
        line-height: 1.5; margin-top: -8px;
    }
    .action-stack { display: flex; flex-direction: column; gap: 9px; }
    .btn-submit--link { text-decoration: none; }
    .alert-empty {
        padding: 16px 18px; border-radius: 14px;
        background: rgba(239,68,68,0.12); border: 1px solid rgba(239,68,68,0.3);
        font-size: 0.8125rem; color: #fecaca; line-height: 1.6;
    }
    .success-footer-links {
        display: grid; grid-template-columns: 1fr 1fr; gap: 9px; margin-top: 4px;
    }
    .success-footer-links .btn-secondary-link { justify-content: center; font-size: 0.75rem; padding: 10px 12px; }
    .success-footer-links .btn-secondary-link:hover { transform: none; }

    @media (max-width: 860px) {
        .success-grid { grid-template-columns: 1fr; }
        .success-col--side {
            border-left: none; border-top: 1px solid rgba(255,255,255,0.08);
        }
        .success-hero { padding: 24px 20px 20px; }
        .success-col { padding: 22px 20px 26px; }
        .summary-row { grid-template-columns: 110px 1fr; }
    }
    @media (max-width: 480px) {
        .summary-row { grid-template-columns: 1fr; gap: 4px; }
        .success-footer-links { grid-template-columns: 1fr; }
    }
    .auth-footer {
        text-align: center; color: rgba(148,163,184,0.65); font-size: 0.8125rem;
        font-weight: 500; margin-top: 22px; line-height: 1.6;
    }
    .section-label {
        font-size: 0.6875rem; font-weight: 700; letter-spacing: 0.12em; text-transform: uppercase;
        color: rgba(148,163,184,0.7); margin: 18px 0 10px; padding-top: 4px;
        border-top: 1px solid rgba(255,255,255,0.08);
    }
    .grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }
    body::-webkit-scrollbar { width: 8px; }
    body::-webkit-scrollbar-track { background: rgba(15,23,42,0.5); }
    body::-webkit-scrollbar-thumb { background: rgba(100,116,139,0.45); border-radius: 4px; }
    body::-webkit-scrollbar-thumb:hover { background: rgba(148,163,184,0.55); }

    @media (max-width: 860px) {
        .auth-grid { grid-template-columns: 1fr; }
        .auth-col--form { padding: 20px 24px 28px; }
        .auth-col--side {
            border-left: none; border-top: 1px solid rgba(255,255,255,0.08);
            padding: 24px 24px 28px;
        }
        .btn-secondary-link:hover { transform: none; }
    }
    @media (max-width: 520px) {
        .grid-2 { grid-template-columns: 1fr; }
        .auth-card:not(.auth-card--split) { padding: 24px 20px 22px; }
        body { padding: 1.25rem 1rem 2rem; }
    }
</style>
