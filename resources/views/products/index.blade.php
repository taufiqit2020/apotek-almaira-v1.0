@extends('layouts.app')
@section('title', 'Produk')
@section('page-title', 'Master Produk')

@section('breadcrumb')
<svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"/></svg>
<span class="text-gray-600 font-medium">Master Produk</span>
@endsection

@section('content')
<div class="animate-in">
    {{-- Header --}}
    <div class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-emerald-700 via-emerald-600 to-teal-600 text-white shadow-lg shadow-emerald-700/15 mb-5">
        <div class="absolute -right-10 -top-10 w-48 h-48 rounded-full bg-white/10 blur-2xl pointer-events-none"></div>
        <div class="relative px-5 sm:px-7 py-5 sm:py-6 flex flex-col sm:flex-row sm:items-end sm:justify-between gap-4">
            <div>
                <p class="text-[11px] font-bold uppercase tracking-[0.16em] text-emerald-100/80">Inventori</p>
                <h2 class="mt-1 text-2xl sm:text-3xl font-extrabold tracking-tight">Daftar Produk / Obat</h2>
                <p class="mt-1.5 text-sm text-emerald-50/90 max-w-xl">Kelola produk, harga jual &amp; grosir, serta tampilan E-Catalog.</p>
            </div>
            <div class="flex flex-wrap items-center gap-2">
                <a href="{{ route('products.export') }}"
                   onclick="this.href='{{ route('products.export') }}'+window.location.search;"
                   class="inline-flex items-center gap-2 px-3.5 py-2.5 rounded-xl bg-white/15 border border-white/25 text-white text-sm font-bold hover:bg-white/25 transition-colors"
                   title="Unduh Excel sesuai filter aktif di Master Produk">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v2a2 2 0 002 2h12a2 2 0 002-2v-2M7 10l5 5m0 0l5-5m-5 5V4"/></svg>
                    Unduh Excel
                </a>
                <a href="{{ route('catalog.index') }}" target="_blank" class="inline-flex items-center gap-2 px-3.5 py-2.5 rounded-xl bg-white/15 border border-white/25 text-white text-sm font-bold hover:bg-white/25 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H5a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L10.828 14H8v-2.828l8.586-8.586z"/></svg>
                    E-Catalog
                </a>
                @if(auth()->user()->isKepalaIt() || auth()->user()->isKepalaOperasional() || auth()->user()->isStaffKeuangan())
                <a wire:navigate href="{{ route('products.import.form') }}" class="inline-flex items-center gap-2 px-3.5 py-2.5 rounded-xl bg-white/15 border border-white/25 text-white text-sm font-bold hover:bg-white/25 transition-colors">
                    Import Excel
                </a>
                @endif
                <a wire:navigate href="{{ route('products.create') }}" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-white text-emerald-700 text-sm font-extrabold shadow-md hover:bg-emerald-50 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    Tambah Produk
                </a>
            </div>
        </div>
    </div>

    {{-- ✅ Livewire Component: search + filter + pagination live tanpa reload --}}
    <livewire:products.product-table />

</div>
@endsection
