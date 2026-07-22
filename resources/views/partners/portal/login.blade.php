@extends('layouts.auth-portal')
@section('title', 'Login Mitra')
@section('container-class', 'auth-container--wide')
@section('card-class', 'auth-card--split')

@section('content')
@php
    $waNumber = preg_replace('/\D/', '', $apotekPhone);
    if (str_starts_with($waNumber, '0')) {
        $waNumber = '62' . substr($waNumber, 1);
    }
    $waForgotMessage = 'Halo Admin Apotek Almaira, saya lupa password akun portal mitra B2B. Mohon bantu reset password saya. Terima kasih.';
    $waHelpMessage = 'Halo Admin Apotek Almaira, saya butuh bantuan terkait akun portal mitra B2B. Terima kasih.';
    $waForgotUrl = 'https://wa.me/' . $waNumber . '?text=' . rawurlencode($waForgotMessage);
    $waHelpUrl = 'https://wa.me/' . $waNumber . '?text=' . rawurlencode($waHelpMessage);
@endphp

<div class="auth-grid">
    <div class="auth-col auth-col--form">
        <div class="card-title">Login Mitra</div>
        <p class="card-subtitle">Portal B2B untuk mitra yang sudah disetujui admin. Masuk untuk memesan produk melalui E-Catalog.</p>

        @if($errors->any())
        <div class="error-box">
            @foreach($errors->all() as $error)
            <div class="error-item">
                <svg class="w-4 h-4 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                {{ $error }}
            </div>
            @endforeach
        </div>
        @endif

        <form action="{{ route('mitra.login.post') }}" method="POST" autocomplete="on" id="mitraLoginForm">
            @csrf
            <div class="field-group">
                <label class="field-label" for="mitra-login">Username atau Email</label>
                <div class="input-wrap">
                    <svg class="input-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                    <input type="text" name="login" id="mitra-login" value="{{ old('login') }}" required autocomplete="username" class="field-input" placeholder="Masukkan username atau email">
                </div>
            </div>

            <div class="field-group" style="margin-bottom: 14px;">
                <label class="field-label" for="mitra-password">Password</label>
                <div class="input-wrap">
                    <svg class="input-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                    <input type="password" name="password" id="mitra-password" required autocomplete="current-password" class="field-input has-toggle" placeholder="Masukkan password">
                    <button type="button" class="input-right-btn" onclick="toggleMitraPassword()" aria-label="Tampilkan atau sembunyikan password" title="Tampilkan password">
                        <svg id="mitra-eye-show" width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                        </svg>
                        <svg id="mitra-eye-hide" width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="display:none;">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>
                        </svg>
                    </button>
                </div>
            </div>

            <div class="forgot-row">
                <a href="{{ $waForgotUrl }}" target="_blank" rel="noopener" class="forgot-link">Lupa password? Hubungi Admin</a>
            </div>

            <label class="remember-row">
                <input type="checkbox" name="remember" value="1" {{ old('remember') ? 'checked' : '' }}>
                <span>Ingat saya di perangkat ini</span>
            </label>

            <button type="submit" class="btn-submit" id="mitraSubmitBtn">
                <svg id="mitraSubmitIcon" width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/></svg>
                <span id="mitraSubmitLabel">Masuk ke Portal Mitra</span>
            </button>
        </form>

        <div class="security-badge">
            <svg style="width:13px;height:13px;" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"/>
            </svg>
            Koneksi Aman &amp; Terenkripsi
        </div>
    </div>

    <div class="auth-col auth-col--side">
        <div class="side-header">
            <div class="side-eyebrow">
                <svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                Portal &amp; Bantuan
            </div>
            <h2 class="side-title">Akses Cepat Lainnya</h2>
            <p class="side-subtitle">Belum punya akun mitra, butuh katalog, atau ingin login sebagai staff apotek.</p>
        </div>

        <div class="auth-links">
            <a href="{{ route('mitra.register') }}" class="btn-secondary-link btn-secondary-link--primary">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/></svg>
                Daftar Mitra Baru
            </a>
            <a href="{{ route('login') }}" class="btn-secondary-link btn-secondary-link--muted">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                Staff Apotek — Login Sistem Kasir
            </a>
            <a href="{{ route('catalog.index') }}" class="btn-secondary-link btn-secondary-link--muted">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                Lihat E-Catalog Publik
            </a>
            <a href="{{ route('home') }}" class="btn-secondary-link btn-secondary-link--muted">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                Beranda Website
            </a>
            <a href="{{ $waHelpUrl }}" target="_blank" rel="noopener" class="btn-secondary-link btn-secondary-link--wa">
                <svg fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347z"/><path d="M12 0C5.373 0 0 5.373 0 12c0 2.11.55 4.09 1.514 5.805L0 24l6.336-1.662C8.09 23.45 10.004 24 12 24c6.627 0 12-5.373 12-12S18.627 0 12 0z"/></svg>
                Bantuan via WhatsApp {{ $apotekPhone }}
            </a>
        </div>

        <div class="help-box">
            <p>Portal ini hanya untuk mitra B2B yang sudah disetujui admin. Belum terdaftar? Ajukan pendaftaran mitra terlebih dahulu. Staff apotek silakan gunakan login sistem kasir terpisah.</p>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function toggleMitraPassword() {
    const input = document.getElementById('mitra-password');
    const showIcon = document.getElementById('mitra-eye-show');
    const hideIcon = document.getElementById('mitra-eye-hide');
    const btn = input?.closest('.input-wrap')?.querySelector('.input-right-btn');
    if (!input || !showIcon || !hideIcon) return;

    const visible = input.type === 'text';
    input.type = visible ? 'password' : 'text';
    showIcon.style.display = visible ? '' : 'none';
    hideIcon.style.display = visible ? 'none' : '';
    if (btn) btn.setAttribute('title', visible ? 'Tampilkan password' : 'Sembunyikan password');
}

(function () {
    const form = document.getElementById('mitraLoginForm');
    const submitBtn = document.getElementById('mitraSubmitBtn');
    const submitLabel = document.getElementById('mitraSubmitLabel');
    const submitIcon = document.getElementById('mitraSubmitIcon');
    const loginField = document.getElementById('mitra-login');

    if (loginField) loginField.focus();

    if (form && submitBtn) {
        form.addEventListener('submit', function () {
            if (submitBtn.disabled) return;
            submitBtn.disabled = true;
            submitLabel.textContent = 'Memproses...';
            if (submitIcon) submitIcon.style.animation = 'spin 1s linear infinite';
        });
    }
})();
</script>
<style>@keyframes spin { from { transform: rotate(0deg); } to { transform: rotate(360deg); } }</style>
@endpush
