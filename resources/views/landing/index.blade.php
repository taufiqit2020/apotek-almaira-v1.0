@extends('layouts.landing')

@section('title', $apotekName . ' Banjarbaru')

@section('content')

{{-- ═══ HERO ═══════════════════════════════════════════════════════ --}}
<section id="beranda" class="relative min-h-[94vh] flex items-center overflow-hidden bg-gradient-to-br from-[#062e22] via-[#0d4a35] to-[#0f6b4a] pt-24 pb-16">
    <div class="absolute inset-0 opacity-[0.06]" style="background-image: url(&quot;data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='%23ffffff'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/svg%3E&quot;);"></div>
    <div class="absolute -top-40 -right-40 w-[520px] h-[520px] rounded-full bg-emerald-400/12 blur-3xl"></div>
    <div class="absolute -bottom-40 -left-32 w-[440px] h-[440px] rounded-full bg-teal-300/10 blur-3xl"></div>
    <div class="absolute top-0 inset-x-0 h-1 bg-gradient-to-r from-transparent via-amber-400 to-transparent"></div>
    <div class="absolute inset-0 bg-gradient-to-t from-[#062e22]/80 via-transparent to-transparent pointer-events-none"></div>

    <div class="relative max-w-7xl mx-auto px-4 sm:px-6 py-12 lg:py-16 w-full">
        <div class="grid lg:grid-cols-2 gap-10 lg:gap-14 items-center">
            <div class="text-center lg:text-left order-2 lg:order-1">
                <span class="inline-flex items-center gap-2.5 px-5 py-2 rounded-full bg-white/15 text-white text-xs font-bold uppercase tracking-[0.15em] border border-white/25 backdrop-blur-md shadow-lg hero-text-shadow">
                    <span class="w-2.5 h-2.5 rounded-full bg-emerald-300 animate-pulse shadow-[0_0_8px_#6ee7b7]"></span>
                    Apotek Terdaftar & Terpercaya
                </span>

                <h1 class="font-banner mt-7 text-[clamp(1.05rem,2.8vw+0.55rem,2.35rem)] font-black text-white leading-none uppercase tracking-[0.01em] hero-text-shadow whitespace-nowrap">
                    {{ $companyName }}
                </h1>

                <div class="flex items-center justify-center lg:justify-start gap-4 my-6">
                    <span class="h-0.5 w-20 bg-gradient-to-r from-amber-400 to-transparent rounded-full"></span>
                    <svg class="w-5 h-5 text-amber-300 drop-shadow" fill="currentColor" viewBox="0 0 20 20"><path d="M10 2l2.4 5.8H19l-4.8 3.5 1.8 5.7L10 13.4l-6 3.6 1.8-5.7L1 7.8h6.6z"/></svg>
                    <span class="h-0.5 w-20 bg-gradient-to-l from-amber-400 to-transparent rounded-full"></span>
                </div>

                <p class="text-2xl sm:text-[1.75rem] font-extrabold text-white hero-text-shadow leading-tight">
                    {{ $apotekName }}
                </p>
                <p class="text-lg sm:text-xl font-bold text-white mt-1 hero-text-shadow">Banjarbaru, Kalimantan Selatan</p>

                <p class="mt-6 text-base sm:text-lg text-white leading-relaxed max-w-xl mx-auto lg:mx-0 font-semibold hero-text-shadow">
                    <span class="text-amber-200">{{ $tagline }}.</span>
                    Pelayanan kefarmasian profesional, obat berkualitas, dan sistem manajemen modern untuk kesehatan keluarga Anda.
                </p>

                <div class="mt-9 flex flex-col sm:flex-row flex-wrap items-center justify-center lg:justify-start gap-3">
                    <a href="{{ route('catalog.index') }}" class="inline-flex items-center justify-center gap-2.5 px-7 py-4 bg-white text-emerald-900 font-extrabold text-base rounded-2xl shadow-2xl shadow-black/30 hover:bg-emerald-50 transition-all hover:-translate-y-0.5 min-w-[200px]">
                        <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                        Lihat E-Catalog
                        @if($catalogCount > 0)
                        <span class="px-2.5 py-0.5 bg-emerald-100 text-emerald-800 text-xs font-extrabold rounded-full">{{ $catalogCount }} produk</span>
                        @endif
                    </a>
                    <a href="https://wa.me/{{ $waNumber }}" target="_blank" rel="noopener" class="inline-flex items-center justify-center gap-2.5 px-7 py-4 bg-[#25D366] text-white font-extrabold text-base rounded-2xl shadow-xl shadow-emerald-900/40 hover:bg-[#20bd5a] transition-all min-w-[200px]">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347z"/><path d="M12 0C5.373 0 0 5.373 0 12c0 2.11.55 4.09 1.514 5.805L0 24l6.336-1.662C8.09 23.45 10.004 24 12 24c6.627 0 12-5.373 12-12S18.627 0 12 0z"/></svg>
                        Hubungi WhatsApp
                    </a>
                </div>

                {{-- Akses login: Mitra vs Staff --}}
                <div class="mt-5 max-w-xl mx-auto lg:mx-0 p-3.5 sm:p-4 rounded-2xl bg-white/10 backdrop-blur-md border border-white/20">
                    <p class="text-[11px] sm:text-xs font-bold uppercase tracking-[0.18em] text-emerald-100/90 mb-3 text-center lg:text-left">Pilih jenis login</p>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5">
                        <a href="{{ route('mitra.login') }}" class="group flex items-center gap-3 px-4 py-3 rounded-xl bg-gradient-to-r from-amber-400 to-amber-500 text-amber-950 font-extrabold text-sm shadow-lg shadow-amber-900/20 hover:from-amber-300 hover:to-amber-400 transition-all hover:-translate-y-0.5">
                            <span class="w-9 h-9 rounded-lg bg-white/30 flex items-center justify-center shrink-0">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                            </span>
                            <span class="text-left leading-tight">
                                <span class="block">Login Mitra</span>
                                <span class="block text-[11px] font-semibold opacity-80">Mitra B2B: pesan produk &amp; PO</span>
                            </span>
                        </a>
                        <a href="{{ route('login') }}" class="group flex items-center gap-3 px-4 py-3 rounded-xl bg-white text-emerald-900 font-extrabold text-sm shadow-lg shadow-black/15 hover:bg-emerald-50 transition-all hover:-translate-y-0.5">
                            <span class="w-9 h-9 rounded-lg bg-emerald-100 text-emerald-700 flex items-center justify-center shrink-0">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                            </span>
                            <span class="text-left leading-tight">
                                <span class="block">Login Staff</span>
                                <span class="block text-[11px] font-semibold text-emerald-700/80">Karyawan: kasir, stok &amp; admin</span>
                            </span>
                        </a>
                    </div>
                </div>

                <a href="#tentang" class="inline-flex items-center gap-2 mt-4 px-2 py-2 text-white/90 font-semibold text-sm hover:text-white transition-colors">
                    Pelajari Lebih Lanjut
                    <svg class="w-4 h-4 animate-bounce" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                </a>

                <div class="mt-10 grid grid-cols-3 gap-3 max-w-lg mx-auto lg:mx-0 p-4 rounded-2xl bg-white/10 backdrop-blur-md border border-white/15">
                    <div class="text-center lg:text-left px-1">
                        <p class="text-2xl sm:text-3xl font-black text-white hero-text-shadow">100%</p>
                        <p class="text-xs text-white/90 font-bold uppercase tracking-wide mt-0.5 leading-snug">Legal &<br>Terdaftar</p>
                    </div>
                    <div class="text-center lg:text-left border-x border-white/20 px-2">
                        <p class="text-2xl sm:text-3xl font-black text-white hero-text-shadow">APJ</p>
                        <p class="text-xs text-white/90 font-bold uppercase tracking-wide mt-0.5 leading-snug">Apoteker<br>Bersertifikat</p>
                    </div>
                    <div class="text-center lg:text-left px-1">
                        <p class="text-2xl sm:text-3xl font-black text-white hero-text-shadow">B2B</p>
                        <p class="text-xs text-white/90 font-bold uppercase tracking-wide mt-0.5 leading-snug">Mitra<br>Katalog</p>
                    </div>
                </div>
            </div>

            {{-- Logo showcase --}}
            <div class="flex flex-col items-center order-1 lg:order-2">
                <div class="flex items-center justify-center gap-5 sm:gap-8">
                    <div class="text-center logo-float">
                        <div class="w-32 h-32 sm:w-40 sm:h-40 rounded-3xl bg-white p-5 hero-glow ring-4 ring-white/25 flex items-center justify-center">
                            <img src="{{ asset('assets/images/logo-ptnmf.png') }}" alt="{{ $companyName }}" class="w-full h-full object-contain">
                        </div>
                        <p class="mt-4 text-xs font-extrabold text-white uppercase tracking-[0.2em] hero-text-shadow">Perusahaan</p>
                    </div>
                    <div class="text-center logo-float logo-float-delay">
                        <div class="w-32 h-32 sm:w-40 sm:h-40 rounded-3xl bg-white p-5 hero-glow ring-4 ring-white/25 flex items-center justify-center">
                            <img src="{{ asset('assets/images/logo-apotek.png') }}" alt="{{ $apotekName }}" class="w-full h-full object-contain">
                        </div>
                        <p class="mt-4 text-xs font-extrabold text-white uppercase tracking-[0.2em] hero-text-shadow">Apotek</p>
                    </div>
                </div>
                <div class="mt-8 w-full max-w-md location-card rounded-3xl border border-emerald-100 shadow-2xl shadow-black/25 overflow-hidden">
                    <div class="px-6 py-4 bg-gradient-to-r from-emerald-700 via-emerald-600 to-teal-600 text-white">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl bg-white/20 flex items-center justify-center shrink-0">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                            </div>
                            <div class="text-left">
                                <p class="text-[11px] font-bold uppercase tracking-[0.2em] text-emerald-100">Lokasi Operasional</p>
                                <p class="text-sm font-extrabold">{{ $apotekName }}</p>
                            </div>
                        </div>
                    </div>
                    <div class="px-6 py-5 text-left space-y-4">
                        <div>
                            <p class="text-xs font-bold text-emerald-700 uppercase tracking-wide mb-1.5">Alamat</p>
                            <p class="text-sm text-slate-700 font-medium leading-relaxed">{{ $apotekAddress }}</p>
                            <p class="mt-1 text-sm text-emerald-800 font-bold">Banjarbaru, Kalimantan Selatan 70714</p>
                        </div>
                        <div class="pt-4 border-t border-emerald-100">
                            <div class="flex items-start gap-3">
                                <div class="w-9 h-9 rounded-lg bg-amber-100 text-amber-700 flex items-center justify-center shrink-0">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                </div>
                                <div>
                                    <p class="text-xs font-bold text-emerald-700 uppercase tracking-wide mb-1">Jam Buka</p>
                                    <p class="text-sm font-extrabold text-slate-800">Senin – Minggu</p>
                                    <p class="text-base font-black text-emerald-700 mt-0.5">09.00 – 22.00 WITA</p>
                                    <p class="text-xs text-slate-500 mt-1">Buka setiap hari, termasuk hari libur nasional</p>
                                </div>
                            </div>
                        </div>
                        <a href="https://www.google.com/maps/search/?api=1&query={{ urlencode($apotekAddress) }}" target="_blank" rel="noopener" class="flex items-center justify-center gap-2 w-full px-4 py-3 bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-500 hover:to-teal-500 text-white text-sm font-bold rounded-xl transition-all shadow-md shadow-emerald-600/20">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/></svg>
                            Buka di Google Maps
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- ═══ TENTANG ══════════════════════════════════════════════════ --}}
<section id="tentang" class="py-20 lg:py-28 bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6">
        <div class="text-center max-w-3xl mx-auto mb-14">
            <span class="inline-block px-4 py-1.5 rounded-full bg-emerald-50 text-emerald-700 text-xs font-bold uppercase tracking-widest">Tentang Kami</span>
            <h2 class="font-banner mt-4 text-3xl sm:text-4xl font-extrabold text-slate-900">Profil Perusahaan</h2>
            <p class="mt-4 text-slate-600 leading-relaxed">{{ $about }}</p>
        </div>

        <div class="grid md:grid-cols-2 gap-6 lg:gap-8">
            <div class="group p-8 rounded-3xl bg-gradient-to-br from-slate-50 to-emerald-50/50 border border-emerald-100 hover:shadow-xl hover:shadow-emerald-100/50 transition-all duration-300">
                <div class="w-14 h-14 rounded-2xl bg-emerald-600 flex items-center justify-center mb-5 shadow-lg shadow-emerald-600/25">
                    <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                </div>
                <h3 class="text-xl font-extrabold text-slate-900">{{ $companyName }}</h3>
                <p class="text-sm text-emerald-700 font-semibold mt-1">Entitas Perusahaan / Kantor Pusat</p>
                <p class="mt-4 text-slate-600 text-sm leading-relaxed">{{ $officeAddress }}</p>
                @if($bankAccount)
                <p class="mt-3 text-xs text-slate-500">Rekening: <strong>{{ $bankName }}</strong> {{ $bankAccount }} a.n. {{ $bankHolder }}</p>
                @endif
            </div>

            <div class="group p-8 rounded-3xl bg-gradient-to-br from-emerald-600 to-teal-700 text-white hover:shadow-xl hover:shadow-emerald-600/30 transition-all duration-300">
                <div class="w-14 h-14 rounded-2xl bg-white/15 flex items-center justify-center mb-5 backdrop-blur-sm">
                    <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                </div>
                <h3 class="text-xl font-extrabold">{{ $apotekName }} Banjarbaru</h3>
                <p class="text-sm text-emerald-100 font-semibold mt-1">Unit Operasional / Outlet Apotek</p>
                <p class="mt-4 text-emerald-50/90 text-sm leading-relaxed">{{ $apotekAddress }}</p>
                <div class="mt-4 flex items-start gap-2.5 p-3 rounded-xl bg-white/10 border border-white/15">
                    <svg class="w-5 h-5 text-amber-300 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <div>
                        <p class="text-xs font-bold text-emerald-200 uppercase tracking-wide">Jam Buka</p>
                        <p class="text-sm font-extrabold text-white mt-0.5">Senin – Minggu · 09.00 – 22.00 WITA</p>
                    </div>
                </div>
                <p class="mt-4 flex items-center gap-2 text-sm">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                    {{ $phone }}
                </p>
            </div>
        </div>
    </div>
</section>

{{-- ═══ VISI & MISI ══════════════════════════════════════════════ --}}
<section id="visi-misi" class="py-20 lg:py-28 bg-slate-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6">
        <div class="text-center mb-14">
            <span class="inline-block px-4 py-1.5 rounded-full bg-emerald-50 text-emerald-700 text-xs font-bold uppercase tracking-widest">Visi & Misi</span>
            <h2 class="font-banner mt-4 text-3xl sm:text-4xl font-extrabold text-slate-900">Arah & Komitmen Kami</h2>
        </div>

        <div class="grid lg:grid-cols-5 gap-8">
            <div class="lg:col-span-2">
                <div class="h-full p-8 lg:p-10 rounded-3xl bg-gradient-to-br from-[#064e3b] to-emerald-700 text-white shadow-xl shadow-emerald-900/20 relative overflow-hidden">
                    <div class="absolute top-0 right-0 w-40 h-40 bg-white/5 rounded-full -translate-y-1/2 translate-x-1/2"></div>
                    <div class="relative">
                        <div class="w-12 h-12 rounded-xl bg-white/15 flex items-center justify-center mb-6">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                        </div>
                        <h3 class="text-sm font-bold uppercase tracking-[0.2em] text-emerald-200">Visi</h3>
                        <p class="mt-4 text-lg sm:text-xl font-semibold leading-relaxed text-emerald-50">{{ $vision }}</p>
                    </div>
                </div>
            </div>

            <div class="lg:col-span-3">
                <div class="h-full p-8 lg:p-10 rounded-3xl bg-white border border-slate-100 shadow-sm">
                    <div class="flex items-center gap-3 mb-6">
                        <div class="w-12 h-12 rounded-xl bg-emerald-100 text-emerald-700 flex items-center justify-center">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>
                        </div>
                        <h3 class="text-sm font-bold uppercase tracking-[0.2em] text-emerald-700">Misi</h3>
                    </div>
                    <ol class="space-y-4">
                        @foreach($missions as $index => $mission)
                        <li class="flex gap-4 items-start">
                            <span class="flex-shrink-0 w-8 h-8 rounded-full bg-emerald-600 text-white text-sm font-extrabold flex items-center justify-center shadow-md shadow-emerald-600/30">{{ $index + 1 }}</span>
                            <p class="text-slate-700 leading-relaxed pt-1">{{ $mission }}</p>
                        </li>
                        @endforeach
                    </ol>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- ═══ LAYANAN ══════════════════════════════════════════════════ --}}
<section id="layanan" class="py-20 lg:py-28 bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6">
        <div class="text-center mb-14">
            <span class="inline-block px-4 py-1.5 rounded-full bg-emerald-50 text-emerald-700 text-xs font-bold uppercase tracking-widest">Layanan</span>
            <h2 class="font-banner mt-4 text-3xl sm:text-4xl font-extrabold text-slate-900">Pelayanan Kami</h2>
            <p class="mt-4 text-slate-600 max-w-2xl mx-auto">Solusi kesehatan lengkap untuk masyarakat, institusi, dan mitra usaha di Banjarbaru.</p>
        </div>

        @php
        $services = [
            ['icon' => 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z', 'title' => 'Penjualan Obat & Produk Kesehatan', 'desc' => 'Obat bebas, bebas terbatas, obat keras resep dokter, vitamin, alat kesehatan, dan produk perawatan tubuh.'],
            ['icon' => 'M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z', 'title' => 'Konsultasi Kefarmasian', 'desc' => 'Pendampingan penggunaan obat yang tepat, aman, dan efektif oleh Apoteker Penanggung Jawab bersertifikat.'],
            ['icon' => 'M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10', 'title' => 'E-Catalog Publik', 'desc' => 'Cek ketersediaan dan harga produk secara online. Pesan via WhatsApp tanpa perlu login.'],
            ['icon' => 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z', 'title' => 'Kemitraan B2B', 'desc' => 'Portal mitra katalog untuk PO, kredit tempo, dan distribusi produk ke institusi & rekanan usaha.'],
            ['icon' => 'M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z', 'title' => 'Pembayaran Fleksibel', 'desc' => 'Tunai, transfer bank, QRIS, dan faktur invoice tempo untuk pelanggan kredit terdaftar.'],
            ['icon' => 'M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z', 'title' => 'Manajemen Terintegrasi', 'desc' => 'Sistem apotek modern: inventori, resep, laporan keuangan, dan audit trail untuk transparansi penuh.'],
        ];
        @endphp

        <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($services as $service)
            <div class="p-6 rounded-2xl border border-slate-100 bg-slate-50/50 hover:bg-white hover:border-emerald-200 hover:shadow-lg hover:shadow-emerald-50 transition-all duration-300 group">
                <div class="w-12 h-12 rounded-xl bg-emerald-100 text-emerald-700 flex items-center justify-center mb-4 group-hover:bg-emerald-600 group-hover:text-white transition-colors">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $service['icon'] }}"/></svg>
                </div>
                <h3 class="font-bold text-slate-900 mb-2">{{ $service['title'] }}</h3>
                <p class="text-sm text-slate-600 leading-relaxed">{{ $service['desc'] }}</p>
            </div>
            @endforeach
        </div>
    </div>
</section>

{{-- ═══ TIM FARMASI ══════════════════════════════════════════════ --}}
<section class="py-20 lg:py-28 bg-gradient-to-b from-slate-50 via-white to-emerald-50/30 relative overflow-hidden">
    <div class="absolute top-0 inset-x-0 h-px bg-gradient-to-r from-transparent via-emerald-300 to-transparent"></div>
    <div class="absolute -top-24 -right-24 w-72 h-72 rounded-full bg-emerald-100/40 blur-3xl pointer-events-none"></div>
    <div class="absolute -bottom-24 -left-24 w-72 h-72 rounded-full bg-teal-100/40 blur-3xl pointer-events-none"></div>

    <div class="relative max-w-7xl mx-auto px-4 sm:px-6">
        <div class="text-center max-w-2xl mx-auto mb-14">
            <span class="inline-flex items-center gap-2 px-5 py-2 rounded-full bg-emerald-100/80 text-emerald-800 text-xs font-bold uppercase tracking-[0.2em] border border-emerald-200/60">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                Tim Profesional
            </span>
            <h2 class="font-banner mt-5 text-3xl sm:text-4xl font-extrabold text-slate-900">Penanggung Jawab & Kepemimpinan</h2>
            <p class="mt-4 text-slate-600 leading-relaxed">Dipimpin oleh tim berpengalaman yang berkomitmen pada pelayanan farmasi berkualitas dan kepatuhan regulasi.</p>
        </div>

        <div class="grid md:grid-cols-3 gap-6 lg:gap-8 max-w-5xl mx-auto">
            {{-- 1. Direktur — posisi kiri/awal --}}
            <div class="group relative p-7 lg:p-8 rounded-3xl bg-gradient-to-br from-slate-800 via-slate-900 to-emerald-950 text-white shadow-xl shadow-slate-900/20 hover:shadow-2xl hover:-translate-y-1 transition-all duration-300 overflow-hidden">
                <div class="absolute top-0 right-0 w-32 h-32 bg-white/5 rounded-full -translate-y-1/2 translate-x-1/2"></div>
                <div class="absolute bottom-0 left-0 w-24 h-24 bg-emerald-500/10 rounded-full translate-y-1/2 -translate-x-1/2"></div>
                <div class="relative">
                    <div class="flex items-center justify-between mb-5">
                        <div class="w-16 h-16 rounded-2xl bg-gradient-to-br from-amber-400 to-amber-600 text-white flex items-center justify-center text-lg font-black shadow-lg shadow-amber-900/30 ring-4 ring-white/10">
                            DR
                        </div>
                        <span class="px-3 py-1 rounded-full bg-amber-400/20 text-amber-200 text-[10px] font-bold uppercase tracking-wider border border-amber-400/30">Kepemimpinan</span>
                    </div>
                    <h3 class="font-bold text-base lg:text-lg leading-snug">{{ $pimpinanName }}</h3>
                    <p class="mt-2 text-sm font-semibold text-emerald-300">Direktur</p>
                    <p class="text-xs text-slate-400 mt-1">{{ $companyName }}</p>
                    <div class="mt-5 pt-4 border-t border-white/10 flex items-center gap-2 text-xs text-slate-400">
                        <svg class="w-4 h-4 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                        Pengarah strategis perusahaan
                    </div>
                </div>
            </div>

            {{-- 2. Apoteker Penanggung Jawab --}}
            <div class="group p-7 lg:p-8 rounded-3xl bg-white border border-emerald-100 shadow-lg shadow-emerald-100/50 hover:shadow-xl hover:border-emerald-200 hover:-translate-y-1 transition-all duration-300">
                <div class="flex items-center justify-between mb-5">
                    <div class="w-16 h-16 rounded-2xl bg-gradient-to-br from-emerald-500 to-teal-600 text-white flex items-center justify-center text-lg font-black shadow-lg shadow-emerald-600/25 ring-4 ring-emerald-50">
                        APJ
                    </div>
                    <span class="px-3 py-1 rounded-full bg-emerald-50 text-emerald-700 text-[10px] font-bold uppercase tracking-wider border border-emerald-100">Farmasi</span>
                </div>
                <h3 class="font-bold text-slate-900 text-sm lg:text-base leading-snug">{{ $apoteker1Name }}</h3>
                <p class="mt-2 text-sm font-semibold text-emerald-700">Apoteker Penanggung Jawab</p>
                <p class="text-xs text-slate-500 mt-1">{{ $apotekName }}</p>
            </div>

            {{-- 3. Apoteker Pendamping --}}
            <div class="group p-7 lg:p-8 rounded-3xl bg-white border border-teal-100 shadow-lg shadow-teal-100/50 hover:shadow-xl hover:border-teal-200 hover:-translate-y-1 transition-all duration-300">
                <div class="flex items-center justify-between mb-5">
                    <div class="w-16 h-16 rounded-2xl bg-gradient-to-br from-teal-500 to-cyan-600 text-white flex items-center justify-center text-lg font-black shadow-lg shadow-teal-600/25 ring-4 ring-teal-50">
                        AP
                    </div>
                    <span class="px-3 py-1 rounded-full bg-teal-50 text-teal-700 text-[10px] font-bold uppercase tracking-wider border border-teal-100">Farmasi</span>
                </div>
                <h3 class="font-bold text-slate-900 text-sm lg:text-base leading-snug">{{ $apoteker2Name }}</h3>
                <p class="mt-2 text-sm font-semibold text-teal-700">Apoteker Pendamping</p>
                <p class="text-xs text-slate-500 mt-1">{{ $apotekName }}</p>
            </div>
        </div>
    </div>
</section>

{{-- ═══ KONTAK & LOKASI ══════════════════════════════════════════ --}}
<section id="kontak" class="py-20 lg:py-28 bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6">
        <div class="text-center mb-14">
            <span class="inline-block px-4 py-1.5 rounded-full bg-emerald-50 text-emerald-700 text-xs font-bold uppercase tracking-widest">Kontak</span>
            <h2 class="font-banner mt-4 text-3xl sm:text-4xl font-extrabold text-slate-900">Hubungi & Kunjungi Kami</h2>
        </div>

        <div class="grid lg:grid-cols-2 gap-8">
            <div class="space-y-6">
                <div class="p-6 rounded-2xl border border-slate-100 bg-slate-50">
                    <h3 class="font-bold text-slate-900 flex items-center gap-2">
                        <span class="w-8 h-8 rounded-lg bg-emerald-600 text-white flex items-center justify-center text-xs font-black">1</span>
                        Kantor {{ $companyName }}
                    </h3>
                    <p class="mt-3 text-sm text-slate-600 leading-relaxed">{{ $officeAddress }}</p>
                    <a href="https://www.google.com/maps/search/?api=1&query={{ urlencode($officeAddress) }}" target="_blank" rel="noopener" class="inline-flex items-center gap-1.5 mt-3 text-sm font-semibold text-emerald-700 hover:text-emerald-800">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/></svg>
                        Buka di Google Maps
                    </a>
                </div>
                <div class="p-6 rounded-2xl border border-emerald-200 bg-emerald-50/50">
                    <h3 class="font-bold text-slate-900 flex items-center gap-2">
                        <span class="w-8 h-8 rounded-lg bg-emerald-600 text-white flex items-center justify-center text-xs font-black">2</span>
                        {{ $apotekName }} (Outlet)
                    </h3>
                    <p class="mt-3 text-sm text-slate-600 leading-relaxed">{{ $apotekAddress }}</p>
                    <div class="mt-3 flex items-center gap-2.5 p-3 rounded-xl bg-white border border-emerald-100">
                        <svg class="w-5 h-5 text-amber-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <div>
                            <p class="text-xs font-bold text-emerald-700 uppercase">Jam Buka</p>
                            <p class="text-sm font-extrabold text-slate-800">Senin – Minggu · 09.00 – 22.00 WITA</p>
                        </div>
                    </div>
                    <a href="https://www.google.com/maps/search/?api=1&query={{ urlencode($apotekAddress) }}" target="_blank" rel="noopener" class="inline-flex items-center gap-1.5 mt-3 text-sm font-semibold text-emerald-700 hover:text-emerald-800">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/></svg>
                        Buka di Google Maps
                    </a>
                </div>
            </div>

            <div class="p-8 rounded-3xl bg-gradient-to-br from-slate-900 to-slate-800 text-white">
                <h3 class="text-xl font-extrabold">Informasi Kontak</h3>
                <p class="mt-2 text-slate-400 text-sm">Siap melayani pertanyaan, pemesanan, dan kerja sama mitra.</p>
                <ul class="mt-8 space-y-5">
                    <li class="flex items-start gap-4">
                        <div class="w-10 h-10 rounded-xl bg-emerald-600/20 flex items-center justify-center shrink-0">
                            <svg class="w-5 h-5 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                        </div>
                        <div>
                            <p class="text-xs text-slate-500 uppercase tracking-wider font-semibold">Telepon / WhatsApp</p>
                            <a href="https://wa.me/{{ $waNumber }}" target="_blank" rel="noopener" class="text-lg font-bold text-white hover:text-emerald-300 transition-colors">{{ $phone }}</a>
                        </div>
                    </li>
                    <li class="flex items-start gap-4">
                        <div class="w-10 h-10 rounded-xl bg-emerald-600/20 flex items-center justify-center shrink-0">
                            <svg class="w-5 h-5 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                        </div>
                        <div>
                            <p class="text-xs text-slate-500 uppercase tracking-wider font-semibold">Email</p>
                            <a href="mailto:{{ $email }}" class="text-base font-semibold text-white hover:text-emerald-300 transition-colors break-all">{{ $email }}</a>
                        </div>
                    </li>
                    <li class="flex items-start gap-4">
                        <div class="w-10 h-10 rounded-xl bg-emerald-600/20 flex items-center justify-center shrink-0">
                            <svg class="w-5 h-5 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 4V2a1 1 0 011-1h8a1 1 0 011 1v2m-9 0h10m-10 0a2 2 0 00-2 2v14a2 2 0 002 2h10a2 2 0 002-2V6a2 2 0 00-2-2"/></svg>
                        </div>
                        <div>
                            <p class="text-xs text-slate-500 uppercase tracking-wider font-semibold">Instagram</p>
                            <p class="text-base font-semibold">{{ $instagram }}</p>
                        </div>
                    </li>
                </ul>
                <div class="mt-8 flex flex-col sm:flex-row gap-3">
                    <a href="https://wa.me/{{ $waNumber }}" target="_blank" rel="noopener" class="flex-1 inline-flex items-center justify-center gap-2 px-5 py-3 bg-emerald-600 hover:bg-emerald-500 font-bold rounded-xl transition-colors">
                        Chat WhatsApp
                    </a>
                    <a href="{{ route('mitra.login') }}" class="flex-1 inline-flex items-center justify-center gap-2 px-5 py-3 bg-amber-500 hover:bg-amber-400 text-amber-950 font-bold rounded-xl transition-colors">
                        Login Mitra
                    </a>
                    <a href="{{ route('mitra.register') }}" class="flex-1 inline-flex items-center justify-center gap-2 px-5 py-3 bg-white/10 hover:bg-white/15 border border-white/20 font-bold rounded-xl transition-colors">
                        Daftar Mitra
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- ═══ CTA BAND ═════════════════════════════════════════════════ --}}
<section class="py-16 bg-gradient-to-r from-emerald-600 via-emerald-700 to-teal-700">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 text-center">
        <h2 class="font-banner text-2xl sm:text-3xl font-extrabold text-white">Butuh Obat atau Produk Kesehatan?</h2>
        <p class="mt-3 text-emerald-100">Cek ketersediaan produk di E-Catalog kami atau hubungi langsung via WhatsApp.</p>
        <div class="mt-8 flex flex-col sm:flex-row items-center justify-center gap-3 sm:gap-4">
            <a href="{{ route('catalog.index') }}" class="inline-flex items-center gap-2 px-8 py-3.5 bg-white text-emerald-800 font-bold rounded-xl shadow-xl hover:bg-emerald-50 transition-colors">
                Buka E-Catalog Sekarang
            </a>
            <a href="{{ route('mitra.login') }}" class="inline-flex items-center gap-2 px-7 py-3.5 bg-amber-400 text-amber-950 font-bold rounded-xl shadow-lg hover:bg-amber-300 transition-colors">
                Login Mitra
            </a>
            <a href="{{ route('login') }}" class="inline-flex items-center gap-2 px-7 py-3.5 text-white font-semibold border border-white/30 rounded-xl hover:bg-white/10 transition-colors">
                Login Staff
            </a>
        </div>
    </div>
</section>

@endsection
