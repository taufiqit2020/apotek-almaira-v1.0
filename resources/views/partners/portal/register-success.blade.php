@extends('layouts.auth-portal')
@section('title', 'Pendaftaran Terkirim')
@section('container-class', 'auth-container--wide')
@section('card-class', 'auth-card--split')

@section('content')
@php
    $code = $summary['code'] ?? null;
    $hasData = filled($summary['code'] ?? null) || filled($summary['name'] ?? null);
@endphp

<div class="success-hero">
    <div class="success-icon-wrap">
        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
        </svg>
    </div>
    <h1 class="success-title">Pendaftaran Terkirim</h1>
    <p class="success-subtitle">
        Data mitra Anda sudah kami terima dan sedang menunggu persetujuan admin apotek.
        Simpan kode mitra Anda untuk referensi.
    </p>
    @if($code)
    <div class="success-code-badge">
        Kode Mitra: <span>{{ $code }}</span>
    </div>
    @endif
    <div class="status-pill">Menunggu Approval Admin</div>
</div>

<div class="success-grid">
    <div class="success-col">
        <div class="panel-heading">Ringkasan Data Pendaftaran</div>

        @if($hasData)
        <div class="summary-table">
            @foreach($summaryFields as $label => $value)
            <div class="summary-row">
                <div class="summary-label">{{ $label }}</div>
                <div class="summary-value @if($label === 'Kode Mitra') summary-value--code @endif">{{ $value }}</div>
            </div>
            @endforeach
        </div>
        @else
        <div class="alert-empty">
            Data pendaftaran tidak ditemukan. Silakan daftar ulang atau hubungi admin apotek via WhatsApp.
        </div>
        @endif
    </div>

    <div class="success-col success-col--side">
        <div>
            <div class="panel-heading">Langkah Selanjutnya</div>
            <div class="steps-list">
                <div class="step-item">
                    <div class="step-num">1</div>
                    <p class="step-text">Tim admin akan memverifikasi data pendaftaran Anda dalam 1–2 hari kerja.</p>
                </div>
                <div class="step-item">
                    <div class="step-num">2</div>
                    <p class="step-text">Klik tombol WhatsApp — <strong>seluruh data pendaftaran terlampir otomatis</strong> di pesan.</p>
                </div>
                <div class="step-item">
                    <div class="step-num">3</div>
                    <p class="step-text">Setelah disetujui, login ke <strong>Portal Mitra</strong> untuk memesan via E-Catalog.</p>
                </div>
            </div>
        </div>

        <div class="action-stack">
            @if($hasData)
            <a href="{{ $waUrl }}" target="_blank" rel="noopener" class="btn-wa-admin">
                <svg style="width:20px;height:20px;" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347z"/><path d="M12 0C5.373 0 0 5.373 0 12c0 2.11.55 4.09 1.514 5.805L0 24l6.336-1.662C8.09 23.45 10.004 24 12 24c6.627 0 12-5.373 12-12S18.627 0 12 0z"/></svg>
                Konfirmasi ke Admin WhatsApp
            </a>
            <p class="wa-hint">{{ $apotekPhone }} · Pesan berisi data pendaftaran terisi otomatis</p>
            @endif

            <a href="{{ route('catalog.index') }}" class="btn-submit btn-submit--link">
                <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                Lihat E-Catalog
            </a>

            <div class="success-footer-links">
                <a href="{{ route('mitra.login') }}" class="btn-secondary-link btn-secondary-link--muted">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/></svg>
                    Login Mitra
                </a>
                <a href="{{ route('home') }}" class="btn-secondary-link btn-secondary-link--muted">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                    Beranda
                </a>
            </div>
        </div>

        <div class="help-box">
            <p>Simpan kode mitra Anda. Jika belum ada kabar dalam 2 hari kerja, konfirmasi langsung ke admin via WhatsApp.</p>
        </div>
    </div>
</div>
@endsection
