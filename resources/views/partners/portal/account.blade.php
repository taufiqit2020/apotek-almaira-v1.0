@extends('layouts.catalog')
@section('title', 'Akun Mitra')

@section('content')
@php
    $statusBadge = $partner->isApproved()
        ? 'bg-emerald-100 text-emerald-800 border-emerald-200'
        : ($partner->isPending()
            ? 'bg-amber-100 text-amber-800 border-amber-200'
            : 'bg-slate-100 text-slate-600 border-slate-200');
    $cartCount = $cartCount ?? 0;
    $orderStats = $orderStats ?? ['total' => 0, 'open' => 0, 'done' => 0];
@endphp

<div class="max-w-5xl mx-auto px-4 sm:px-6 py-8 sm:py-10">
    @if(session('toast_success'))
    <div class="mb-5 flex items-start gap-3 p-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-800 shadow-sm">
        <div class="w-9 h-9 rounded-xl bg-emerald-100 flex items-center justify-center shrink-0">
            <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
        </div>
        <p class="text-sm font-semibold pt-1.5">{{ session('toast_success') }}</p>
    </div>
    @endif

    {{-- Hero --}}
    <div class="mb-6 rounded-2xl bg-gradient-to-br from-emerald-700 via-emerald-600 to-teal-600 p-5 sm:p-7 text-white shadow-lg shadow-emerald-900/15 relative overflow-hidden">
        <div class="absolute top-0 right-0 w-56 h-56 bg-white/5 rounded-full -translate-y-1/2 translate-x-1/3 pointer-events-none"></div>
        <div class="absolute bottom-0 left-1/4 w-40 h-40 bg-teal-300/10 rounded-full translate-y-1/2 pointer-events-none"></div>
        <div class="relative flex flex-col lg:flex-row lg:items-center lg:justify-between gap-5">
            <div class="flex items-start gap-4 min-w-0">
                <div class="w-14 h-14 rounded-2xl bg-white/15 border border-white/20 flex items-center justify-center shrink-0 text-xl font-extrabold">
                    {{ strtoupper(substr($partner->name, 0, 1)) }}
                </div>
                <div class="min-w-0">
                    <p class="text-[11px] font-bold uppercase tracking-wider text-emerald-100/90">Portal Mitra B2B</p>
                    <h2 class="mt-0.5 font-banner text-xl sm:text-2xl lg:text-3xl font-extrabold leading-tight break-words">
                        {{ $partner->name }}
                    </h2>
                    <div class="flex flex-wrap items-center gap-2 mt-2.5">
                        <span class="inline-flex px-2.5 py-1 rounded-lg text-[11px] font-bold bg-white/15 border border-white/20 font-mono">{{ $partner->code }}</span>
                        <span class="inline-flex px-2.5 py-1 rounded-lg text-[11px] font-bold bg-white/15 border border-white/20">{{ $partner->type_label }}</span>
                        <span class="inline-flex px-2.5 py-1 rounded-lg text-[11px] font-bold border {{ $statusBadge }}">{{ $partner->status_label }}</span>
                    </div>
                    <p class="mt-2 text-sm text-emerald-50/90">
                        Selamat datang{{ $partner->pic_name ? ', '.$partner->pic_name : '' }}. Kelola pesanan dan profil mitra Anda di sini.
                    </p>
                </div>
            </div>

            <div class="flex flex-col sm:flex-row flex-wrap gap-2 shrink-0">
                <a href="{{ route('catalog.index') }}"
                   class="inline-flex items-center justify-center gap-2 px-5 py-2.5 rounded-xl bg-white text-emerald-700 text-sm font-bold shadow-md hover:bg-emerald-50 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
                    Belanja Katalog
                </a>
                <a href="{{ route('mitra.cart') }}"
                   class="inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl bg-white/15 border border-white/25 text-white text-sm font-bold hover:bg-white/25 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                    Keranjang
                    @if($cartCount > 0)
                    <span class="min-w-[20px] h-5 px-1.5 rounded-full bg-white text-emerald-700 text-[10px] font-extrabold flex items-center justify-center">{{ $cartCount }}</span>
                    @endif
                </a>
                <a href="{{ route('mitra.orders.index') }}"
                   class="inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl bg-white/15 border border-white/25 text-white text-sm font-bold hover:bg-white/25 transition-colors">
                    PO Saya
                </a>
                <form action="{{ route('mitra.logout') }}" method="POST" class="sm:ml-0">
                    @csrf
                    <button type="submit"
                            class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl border border-white/20 bg-white/10 text-white text-sm font-bold hover:bg-red-500/90 hover:border-red-400/50 transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                        Logout
                    </button>
                </form>
            </div>
        </div>
    </div>

    {{-- Quick stats --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4 mb-6">
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-4 sm:p-5">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                </div>
                <div class="min-w-0">
                    <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Status</p>
                    <p class="text-sm font-extrabold text-slate-800 truncate">{{ $partner->status_label }}</p>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-4 sm:p-5">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-sky-50 text-sky-600 flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <div class="min-w-0">
                    <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Mode Harga</p>
                    <p class="text-sm font-extrabold text-slate-800 truncate">{{ $partner->price_mode_label }}</p>
                </div>
            </div>
        </div>
        <a href="{{ route('mitra.cart') }}" class="bg-white rounded-2xl border border-slate-100 shadow-sm p-4 sm:p-5 hover:border-emerald-200 hover:shadow-md transition-all group">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center shrink-0 group-hover:scale-105 transition-transform">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                </div>
                <div class="min-w-0">
                    <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Keranjang</p>
                    <p class="text-sm font-extrabold text-slate-800">{{ $cartCount }} item</p>
                </div>
            </div>
        </a>
        <a href="{{ route('mitra.orders.index') }}" class="bg-white rounded-2xl border border-slate-100 shadow-sm p-4 sm:p-5 hover:border-emerald-200 hover:shadow-md transition-all group">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center shrink-0 group-hover:scale-105 transition-transform">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                </div>
                <div class="min-w-0">
                    <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400">PO Aktif</p>
                    <p class="text-sm font-extrabold text-slate-800">{{ $orderStats['open'] }} / {{ $orderStats['total'] }}</p>
                </div>
            </div>
        </a>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-5 gap-5">
        {{-- Kontak --}}
        <section class="lg:col-span-3 bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
            <div class="px-5 py-3.5 border-b border-slate-100 bg-slate-50/80 flex items-center gap-2.5">
                <div class="w-8 h-8 rounded-lg bg-blue-100 text-blue-700 flex items-center justify-center">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                </div>
                <div>
                    <h3 class="text-sm font-bold text-slate-800">Kontak &amp; Alamat</h3>
                    <p class="text-[11px] text-slate-400">Data profil mitra untuk pengiriman &amp; komunikasi</p>
                </div>
            </div>
            <div class="p-5 sm:p-6 grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="flex items-start gap-3 p-3 rounded-xl bg-slate-50/80 border border-slate-100">
                    <div class="w-9 h-9 rounded-lg bg-white border border-slate-200 flex items-center justify-center text-slate-500 shrink-0">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                    </div>
                    <div class="min-w-0">
                        <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400">PIC</p>
                        <p class="text-sm font-bold text-slate-800 break-words">{{ $partner->pic_name ?: '—' }}</p>
                    </div>
                </div>
                <div class="flex items-start gap-3 p-3 rounded-xl bg-slate-50/80 border border-slate-100">
                    <div class="w-9 h-9 rounded-lg bg-white border border-slate-200 flex items-center justify-center text-slate-500 shrink-0">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                    </div>
                    <div class="min-w-0">
                        <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Telepon</p>
                        <p class="text-sm font-bold text-slate-800">{{ $partner->phone ?: '—' }}</p>
                    </div>
                </div>
                <div class="flex items-start gap-3 p-3 rounded-xl bg-slate-50/80 border border-slate-100">
                    <div class="w-9 h-9 rounded-lg bg-white border border-slate-200 flex items-center justify-center text-slate-500 shrink-0">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                    </div>
                    <div class="min-w-0">
                        <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Email</p>
                        <p class="text-sm font-bold text-slate-800 break-all">{{ $partner->email ?: '—' }}</p>
                    </div>
                </div>
                <div class="flex items-start gap-3 p-3 rounded-xl bg-slate-50/80 border border-slate-100">
                    <div class="w-9 h-9 rounded-lg bg-white border border-slate-200 flex items-center justify-center text-slate-500 shrink-0">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                    </div>
                    <div class="min-w-0">
                        <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Kota</p>
                        <p class="text-sm font-bold text-slate-800">{{ $partner->city ?: '—' }}</p>
                    </div>
                </div>
                <div class="sm:col-span-2 flex items-start gap-3 p-3 rounded-xl bg-slate-50/80 border border-slate-100">
                    <div class="w-9 h-9 rounded-lg bg-white border border-slate-200 flex items-center justify-center text-slate-500 shrink-0">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/></svg>
                    </div>
                    <div class="min-w-0">
                        <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Alamat Lengkap</p>
                        <p class="text-sm font-bold text-slate-800 leading-relaxed">{{ $partner->address ?: '—' }}</p>
                    </div>
                </div>
            </div>
        </section>

        {{-- Pembayaran + ringkas PO --}}
        <div class="lg:col-span-2 space-y-5">
            <section class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
                <div class="px-5 py-3.5 border-b border-slate-100 bg-slate-50/80 flex items-center gap-2.5">
                    <div class="w-8 h-8 rounded-lg bg-emerald-100 text-emerald-700 flex items-center justify-center">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
                    </div>
                    <div>
                        <h3 class="text-sm font-bold text-slate-800">Metode Pembayaran</h3>
                        <p class="text-[11px] text-slate-400">Opsi yang diizinkan untuk mitra Anda</p>
                    </div>
                </div>
                <div class="p-5 space-y-3">
                    <div class="flex flex-wrap gap-2">
                        @if($partner->allow_transfer)
                        <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-bold bg-emerald-50 text-emerald-800 border border-emerald-100">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Transfer
                        </span>
                        @endif
                        @if($partner->allow_cod)
                        <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-bold bg-amber-50 text-amber-800 border border-amber-100">
                            <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span> COD
                        </span>
                        @endif
                        @if($partner->canUseInvoice())
                        <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-bold bg-sky-50 text-sky-800 border border-sky-100">
                            <span class="w-1.5 h-1.5 rounded-full bg-sky-500"></span> Invoice {{ $partner->credit_days }} hari
                        </span>
                        @endif
                        @unless($partner->allow_transfer || $partner->allow_cod || $partner->canUseInvoice())
                        <span class="text-xs text-slate-500">Belum ada metode aktif. Hubungi apotek.</span>
                        @endunless
                    </div>
                    <p class="text-xs text-slate-500 leading-relaxed pt-2 border-t border-slate-100">
                        Alur order: Katalog → Keranjang → Checkout PO → pilih metode pembayaran.
                    </p>
                </div>
            </section>

            <section class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
                <div class="px-5 py-3.5 border-b border-slate-100 bg-slate-50/80 flex items-center gap-2.5">
                    <div class="w-8 h-8 rounded-lg bg-indigo-100 text-indigo-700 flex items-center justify-center">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                    </div>
                    <div>
                        <h3 class="text-sm font-bold text-slate-800">Ringkasan PO</h3>
                        <p class="text-[11px] text-slate-400">Aktivitas pesanan mitra</p>
                    </div>
                </div>
                <div class="p-5 grid grid-cols-3 gap-2 text-center">
                    <div class="rounded-xl bg-slate-50 border border-slate-100 py-3">
                        <p class="text-lg font-extrabold text-slate-800">{{ $orderStats['total'] }}</p>
                        <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400 mt-0.5">Total</p>
                    </div>
                    <div class="rounded-xl bg-amber-50 border border-amber-100 py-3">
                        <p class="text-lg font-extrabold text-amber-700">{{ $orderStats['open'] }}</p>
                        <p class="text-[10px] font-bold uppercase tracking-wider text-amber-600/80 mt-0.5">Proses</p>
                    </div>
                    <div class="rounded-xl bg-emerald-50 border border-emerald-100 py-3">
                        <p class="text-lg font-extrabold text-emerald-700">{{ $orderStats['done'] }}</p>
                        <p class="text-[10px] font-bold uppercase tracking-wider text-emerald-600/80 mt-0.5">Selesai</p>
                    </div>
                </div>
                <div class="px-5 pb-5">
                    <a href="{{ route('mitra.orders.index') }}"
                       class="inline-flex w-full items-center justify-center gap-2 px-4 py-2.5 rounded-xl border border-slate-200 bg-white text-sm font-bold text-slate-700 hover:bg-slate-50 hover:border-emerald-200 transition-colors">
                        Lihat semua PO
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    </a>
                </div>
            </section>
        </div>
    </div>
</div>
@endsection
