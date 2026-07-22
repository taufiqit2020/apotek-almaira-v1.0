@extends('layouts.catalog')
@section('title', 'Purchase Order Saya')

@section('content')
@php
    $statusStyles = [
        'submitted' => ['badge' => 'bg-blue-100 text-blue-800 border-blue-200', 'dot' => 'bg-blue-500'],
        'confirmed' => ['badge' => 'bg-indigo-100 text-indigo-800 border-indigo-200', 'dot' => 'bg-indigo-500'],
        'fulfilled' => ['badge' => 'bg-emerald-100 text-emerald-800 border-emerald-200', 'dot' => 'bg-emerald-500'],
        'cancelled' => ['badge' => 'bg-red-100 text-red-700 border-red-200', 'dot' => 'bg-red-500'],
    ];
    $payMethodIcons = [
        'transfer' => 'M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z',
        'cod'      => 'M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2z',
        'invoice'  => 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z',
    ];
@endphp
<div class="max-w-6xl mx-auto px-4 sm:px-6 py-8 lg:py-10">

    {{-- Header --}}
    <div class="mb-6 rounded-2xl bg-gradient-to-br from-emerald-700 via-emerald-600 to-teal-600 p-6 sm:p-7 text-white shadow-lg shadow-emerald-900/15 relative overflow-hidden">
        <div class="absolute top-0 right-0 w-48 h-48 bg-white/5 rounded-full -translate-y-1/2 translate-x-1/3 pointer-events-none"></div>
        <div class="relative flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div class="flex items-start gap-4 min-w-0">
                <div class="w-12 h-12 rounded-xl bg-white/15 flex items-center justify-center shrink-0">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                </div>
                <div class="min-w-0">
                    <h2 class="font-banner text-2xl sm:text-3xl font-extrabold uppercase tracking-wide">Purchase Order</h2>
                    <p class="text-emerald-50/90 text-sm mt-1 truncate">{{ $partner->name }} · {{ $partner->code }}</p>
                    <p class="text-emerald-100/80 text-xs mt-1">Riwayat &amp; status PO Anda</p>
                </div>
            </div>
            <div class="flex flex-col sm:flex-row gap-2 shrink-0">
                <a href="{{ route('catalog.index') }}"
                   class="inline-flex items-center justify-center gap-2 px-5 py-2.5 rounded-xl bg-white text-emerald-700 text-sm font-bold shadow-md hover:bg-emerald-50 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    Order Baru
                </a>
                <a href="{{ route('mitra.cart') }}"
                   class="inline-flex items-center justify-center gap-2 px-5 py-2.5 rounded-xl bg-white/15 border border-white/25 text-white text-sm font-bold hover:bg-white/25 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                    Keranjang
                </a>
            </div>
        </div>
    </div>

    {{-- Statistik --}}
    <div class="grid grid-cols-3 gap-3 mb-6">
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-4 text-center">
            <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Total PO</p>
            <p class="text-2xl font-extrabold text-slate-800 mt-1">{{ $stats['total'] }}</p>
        </div>
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-4 text-center">
            <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Diproses</p>
            <p class="text-2xl font-extrabold text-amber-600 mt-1">{{ $stats['open'] }}</p>
        </div>
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-4 text-center">
            <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Selesai</p>
            <p class="text-2xl font-extrabold text-emerald-700 mt-1">{{ $stats['fulfilled'] }}</p>
        </div>
    </div>

    {{-- Filter --}}
    <div class="mb-5 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <form method="GET" class="flex flex-wrap items-center gap-2">
            <label class="text-xs font-bold text-slate-500 uppercase tracking-wider shrink-0">Filter Status</label>
            <select name="status"
                    class="rounded-xl border border-slate-200 bg-white px-3.5 py-2.5 text-sm font-semibold text-slate-700 focus:ring-2 focus:ring-emerald-500/30 focus:border-emerald-500 outline-none min-w-[160px]"
                    onchange="this.form.submit()">
                <option value="">Semua status</option>
                @foreach(\App\Models\PartnerOrder::statusOptions() as $k => $l)
                <option value="{{ $k }}" @selected(request('status') === $k)>{{ $l }}</option>
                @endforeach
            </select>
            @if(request('status'))
            <a href="{{ route('mitra.orders.index') }}" class="text-xs font-bold text-slate-500 hover:text-emerald-600 underline underline-offset-2">Reset</a>
            @endif
        </form>
        @if($orders->total() > 0)
        <p class="text-xs text-slate-400 font-medium">{{ $orders->total() }} PO ditemukan</p>
        @endif
    </div>

    {{-- Daftar PO --}}
    @if($orders->isEmpty())
    <div class="bg-white rounded-2xl border border-slate-200 p-12 sm:p-16 text-center shadow-sm">
        <div class="w-20 h-20 mx-auto mb-5 rounded-2xl bg-slate-100 flex items-center justify-center">
            <svg class="w-10 h-10 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
        </div>
        <h3 class="font-banner text-lg font-extrabold text-slate-800">Belum Ada Purchase Order</h3>
        <p class="text-sm text-slate-500 mt-2 max-w-sm mx-auto">
            @if(request('status'))
                Tidak ada PO dengan status filter ini. Coba reset filter atau buat order baru.
            @else
                Mulai belanja di E-Catalog dan ajukan PO pertama Anda.
            @endif
        </p>
        <a href="{{ route('catalog.index') }}"
           class="mt-6 inline-flex items-center gap-2 px-6 py-3 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-bold shadow-md shadow-emerald-600/25 transition-colors">
            Buka E-Catalog
        </a>
    </div>
    @else
    <div class="space-y-3">
        @foreach($orders as $o)
        @php
            $style = $statusStyles[$o->status] ?? ['badge' => 'bg-slate-100 text-slate-700 border-slate-200', 'dot' => 'bg-slate-400'];
            $iconPath = $payMethodIcons[$o->payment_method] ?? $payMethodIcons['transfer'];
        @endphp
        <a href="{{ route('mitra.orders.show', $o) }}"
           class="group block bg-white rounded-2xl border border-slate-200 shadow-sm hover:shadow-md hover:border-emerald-200 transition-all overflow-hidden">
            <div class="p-4 sm:p-5">
                <div class="flex flex-col sm:flex-row sm:items-center gap-4">
                    {{-- Icon & nomor --}}
                    <div class="flex items-start gap-3.5 min-w-0 flex-1">
                        <div class="w-11 h-11 rounded-xl bg-emerald-50 border border-emerald-100 flex items-center justify-center shrink-0 group-hover:bg-emerald-100 transition-colors">
                            <svg class="w-5 h-5 text-emerald-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $iconPath }}"/>
                            </svg>
                        </div>
                        <div class="min-w-0 flex-1">
                            <div class="flex flex-wrap items-center gap-2">
                                <p class="font-mono text-xs font-bold text-slate-500">{{ $o->order_no }}</p>
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-[10px] font-bold border {{ $style['badge'] }}">
                                    <span class="w-1.5 h-1.5 rounded-full {{ $style['dot'] }}"></span>
                                    {{ $o->status_label }}
                                </span>
                            </div>
                            <p class="text-sm font-bold text-slate-800 mt-1">{{ $o->payment_method_label }}</p>
                            <div class="flex flex-wrap items-center gap-x-3 gap-y-1 mt-1 text-[11px] text-slate-400">
                                <span class="inline-flex items-center gap-1">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                                    {{ $o->items_count }} item
                                </span>
                                <span class="inline-flex items-center gap-1">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    {{ $o->created_at->timezone('Asia/Makassar')->locale('id')->translatedFormat('d M Y, H:i') }}
                                </span>
                                @if($o->due_date)
                                <span class="inline-flex items-center gap-1 {{ $o->isCreditOverdue() ? 'text-red-600 font-bold' : '' }}">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                    Jatuh tempo {{ $o->due_date->format('d/m/Y') }}
                                </span>
                                @endif
                            </div>
                            <p class="text-[10px] font-semibold text-slate-400 mt-1.5">{{ $o->payment_status_label }}</p>
                        </div>
                    </div>

                    {{-- Total & arrow --}}
                    <div class="flex sm:flex-col items-center sm:items-end justify-between sm:justify-center gap-2 shrink-0 sm:min-w-[140px] pl-0 sm:pl-4 sm:border-l sm:border-slate-100">
                        <div class="text-left sm:text-right">
                            <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Total</p>
                            <p class="text-lg sm:text-xl font-extrabold text-emerald-700">Rp {{ number_format($o->total, 0, ',', '.') }}</p>
                        </div>
                        <span class="inline-flex items-center gap-1 text-xs font-bold text-emerald-600 group-hover:translate-x-0.5 transition-transform">
                            Detail
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                        </span>
                    </div>
                </div>
            </div>
        </a>
        @endforeach
    </div>

    @if($orders->hasPages())
    <div class="mt-6 flex justify-center">{{ $orders->links() }}</div>
    @endif
    @endif
</div>
@endsection
