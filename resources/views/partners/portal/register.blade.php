@extends('layouts.auth-portal')
@section('title', 'Daftar Mitra')
@section('brand-subtitle', 'Pendaftaran Mitra B2B E-Catalog')

@push('styles')
<style>.auth-container { max-width: 720px; }</style>
@endpush

@section('content')
<div class="card-title">Daftar Mitra B2B</div>
<p style="font-size:12px;color:rgba(147,197,253,0.55);margin:-12px 0 18px;line-height:1.5;">
    Daftarkan usaha/institusi Anda untuk order via e-catalog. Akun aktif setelah disetujui admin.
</p>

@if($errors->any())
<div class="error-box">
    @foreach($errors->all() as $error)
    <div class="error-item">{{ $error }}</div>
    @endforeach
</div>
@endif

<form action="{{ route('mitra.register.post') }}" method="POST" x-data="{ type: '{{ old('type', 'umkm') }}' }">
    @csrf

    <div class="section-label">Identitas Usaha</div>
    <div class="field-group">
        <label class="field-label">Nama Usaha / Institusi *</label>
        <div class="input-wrap">
            <svg class="input-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
            <input type="text" name="name" value="{{ old('name') }}" required class="field-input" placeholder="Contoh: RSUD Banjarbaru">
        </div>
    </div>
    <div class="grid-2">
        <div class="field-group">
            <label class="field-label">Tipe Mitra *</label>
            <select name="type" x-model="type" required class="field-select" style="padding-left:14px;">
                @foreach($types as $key => $label)
                <option value="{{ $key }}" @selected(old('type', 'umkm') === $key)>{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <div class="field-group">
            <label class="field-label">Kota</label>
            <input type="text" name="city" value="{{ old('city') }}" class="field-input" style="padding-left:14px;" placeholder="Banjarbaru">
        </div>
    </div>
    <div class="grid-2">
        <div class="field-group">
            <label class="field-label">NPWP</label>
            <input type="text" name="npwp" value="{{ old('npwp') }}" class="field-input" style="padding-left:14px;">
        </div>
        <div class="field-group">
            <label class="field-label">NIB / Izin</label>
            <input type="text" name="nib" value="{{ old('nib') }}" class="field-input" style="padding-left:14px;">
        </div>
    </div>
    <div class="field-group">
        <label class="field-label">Alamat</label>
        <textarea name="address" class="field-textarea" placeholder="Alamat lengkap usaha">{{ old('address') }}</textarea>
    </div>

    <div class="section-label">Kontak PIC</div>
    <div class="grid-2">
        <div class="field-group">
            <label class="field-label">Nama PIC *</label>
            <input type="text" name="pic_name" value="{{ old('pic_name') }}" required class="field-input" style="padding-left:14px;">
        </div>
        <div class="field-group">
            <label class="field-label">Telepon / WA *</label>
            <input type="text" name="phone" value="{{ old('phone') }}" required class="field-input" style="padding-left:14px;" placeholder="08xxxxxxxxxx">
        </div>
    </div>
    <div class="field-group">
        <label class="field-label">Email *</label>
        <div class="input-wrap">
            <svg class="input-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
            <input type="email" name="email" value="{{ old('email') }}" required class="field-input" placeholder="email@perusahaan.com">
        </div>
    </div>

    <div class="section-label">Akun Login</div>
    <div class="field-group">
        <label class="field-label">Username *</label>
        <div class="input-wrap">
            <svg class="input-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
            <input type="text" name="username" value="{{ old('username') }}" required autocomplete="username" class="field-input">
        </div>
    </div>
    <div class="grid-2">
        <div class="field-group">
            <label class="field-label">Password *</label>
            <input type="password" name="password" required minlength="6" autocomplete="new-password" class="field-input" style="padding-left:14px;">
        </div>
        <div class="field-group">
            <label class="field-label">Konfirmasi *</label>
            <input type="password" name="password_confirmation" required minlength="6" autocomplete="new-password" class="field-input" style="padding-left:14px;">
        </div>
    </div>

    <button type="submit" class="btn-submit" style="margin-top:8px;">Kirim Pendaftaran</button>
    <a href="{{ route('mitra.login') }}" class="btn-secondary-link">Sudah punya akun? Login Mitra</a>
    <a href="{{ route('catalog.index') }}" class="btn-secondary-link" style="margin-top:8px;">← Kembali ke E-Catalog</a>
</form>
@endsection
