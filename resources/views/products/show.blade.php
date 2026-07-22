@extends('layouts.app')
@section('title', 'Detail Produk')
@section('page-title', 'Detail Produk')

@section('breadcrumb')
<svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"/></svg>
<a href="{{ route('products.index') }}" class="hover:text-primary-600 transition-colors">Master Produk</a>
<svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"/></svg>
<span class="text-gray-600 font-medium">Detail</span>
@endsection

@section('content')
<div class="animate-in max-w-6xl mx-auto">
    {{-- Hero --}}
    <div class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-emerald-700 via-emerald-600 to-teal-600 text-white shadow-lg shadow-emerald-700/15 mb-5">
        <div class="absolute -right-10 -top-10 w-48 h-48 rounded-full bg-white/10 blur-2xl pointer-events-none"></div>
        <div class="relative px-5 sm:px-7 py-5 sm:py-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div class="flex items-start gap-4 min-w-0">
                <div class="w-14 h-14 rounded-2xl bg-white/15 border border-white/20 overflow-hidden flex items-center justify-center shrink-0 shadow-inner">
                    <img src="{{ $product->image_url }}" alt="" class="w-full h-full {{ $product->has_image ? 'object-cover' : 'object-contain p-2 bg-white' }}">
                </div>
                <div class="min-w-0">
                    <p class="text-[11px] font-bold uppercase tracking-[0.16em] text-emerald-100/80">Detail Produk</p>
                    <h2 class="mt-1 text-xl sm:text-2xl font-extrabold tracking-tight break-words">{{ $product->name }}</h2>
                    <p class="mt-1 text-sm text-emerald-50/90 truncate">
                        {{ $product->code ? 'Kode '.$product->code : 'Tanpa kode' }}
                        @if($product->category)
                        · {{ $product->category->name }}
                        @endif
                    </p>
                </div>
            </div>
            <div class="flex flex-wrap items-center gap-2">
                <a href="{{ route('products.index') }}"
                   x-on:click.prevent="window.history.length > 1 ? window.history.back() : window.location.assign('{{ route('products.index') }}')"
                   class="inline-flex items-center gap-2 px-3.5 py-2.5 rounded-xl bg-white/15 border border-white/25 text-white text-sm font-bold hover:bg-white/25 transition-colors">
                    Kembali
                </a>
                <a href="{{ route('products.edit', $product) }}" wire:navigate
                   class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-white text-emerald-700 text-sm font-extrabold shadow-md hover:bg-emerald-50 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                    Edit Produk
                </a>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">
        {{-- Identitas --}}
        <div class="card p-5 lg:col-span-1 border border-slate-100 shadow-sm">
            <div class="flex flex-col items-center gap-4">
                <div class="w-full aspect-square max-w-[220px] rounded-2xl overflow-hidden border border-slate-100 bg-slate-50 shadow-sm">
                    <img src="{{ $product->image_url }}" alt="{{ $product->name }}"
                         class="w-full h-full {{ $product->has_image ? 'object-cover' : 'object-contain p-6' }}">
                </div>
                <dl class="w-full space-y-2.5 text-sm">
                    <div class="flex justify-between gap-3 py-2 border-b border-slate-100">
                        <dt class="text-xs font-bold uppercase tracking-wider text-slate-400">Kode</dt>
                        <dd class="font-semibold text-slate-700 text-right">{{ $product->code ?? '—' }}</dd>
                    </div>
                    <div class="flex justify-between gap-3 py-2 border-b border-slate-100">
                        <dt class="text-xs font-bold uppercase tracking-wider text-slate-400">Barcode</dt>
                        <dd class="font-semibold text-slate-700 text-right">{{ $product->barcode ?? '—' }}</dd>
                    </div>
                    <div class="flex justify-between gap-3 py-2 border-b border-slate-100">
                        <dt class="text-xs font-bold uppercase tracking-wider text-slate-400">Kategori</dt>
                        <dd class="font-semibold text-emerald-700 text-right">{{ $product->category?->name ?? '—' }}</dd>
                    </div>
                    <div class="flex justify-between gap-3 py-2 border-b border-slate-100">
                        <dt class="text-xs font-bold uppercase tracking-wider text-slate-400">Satuan</dt>
                        <dd class="font-semibold text-slate-700 text-right">{{ $product->unit?->name ?? '—' }}</dd>
                    </div>
                    <div class="flex justify-between gap-3 py-2">
                        <dt class="text-xs font-bold uppercase tracking-wider text-slate-400">Supplier</dt>
                        <dd class="font-semibold text-slate-700 text-right">{{ $product->supplier?->name ?? '—' }}</dd>
                    </div>
                </dl>
            </div>
        </div>

        {{-- Detail --}}
        <div class="card p-5 sm:p-6 lg:col-span-2 border border-slate-100 shadow-sm">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <h3 class="text-base font-extrabold mb-3 tracking-tight text-slate-800">Informasi Farmasi</h3>
                    <div class="space-y-2.5 text-sm">
                        @foreach([
                            'Kandungan' => $product->composition,
                            'Bentuk Sediaan' => $product->dosage_form,
                            'Rute Pemberian' => $product->route,
                            'Golongan' => $product->drug_class,
                            'Pabrik / Merk' => $product->manufacturer,
                        ] as $label => $value)
                        <div class="rounded-xl border border-slate-100 bg-slate-50/60 px-3 py-2">
                            <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400">{{ $label }}</p>
                            <p class="mt-0.5 font-semibold text-slate-700">{{ $value ?: '—' }}</p>
                        </div>
                        @endforeach
                        <div class="rounded-xl border border-slate-100 bg-slate-50/60 px-3 py-2">
                            <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Butuh Resep</p>
                            <p class="mt-0.5 font-semibold {{ $product->requires_prescription ? 'text-amber-700' : 'text-slate-700' }}">
                                {{ $product->requires_prescription ? 'Ya' : 'Tidak' }}
                            </p>
                        </div>
                    </div>
                    <div class="mt-4 rounded-xl border border-emerald-100 bg-emerald-50/40 p-3.5">
                        <p class="text-[10px] font-bold uppercase tracking-wider text-emerald-600 mb-1">Indikasi / Fungsi</p>
                        <p class="text-sm text-slate-700 leading-relaxed">{{ $product->description ?: '—' }}</p>
                    </div>
                </div>

                <div>
                    <h3 class="text-base font-extrabold mb-3 tracking-tight text-slate-800">Harga & Stok</h3>
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-2.5 mb-4">
                        <div class="rounded-xl border border-slate-200 bg-slate-50 p-3">
                            <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Harga Beli</p>
                            <p class="mt-1 text-base font-extrabold text-slate-700 tabular-nums">Rp {{ number_format($product->purchase_price ?? 0,0,',','.') }}</p>
                        </div>
                        <div class="rounded-xl border {{ $product->exceedsHet() ? 'border-rose-200 bg-rose-50/80' : 'border-emerald-200 bg-emerald-50/80' }} p-3">
                            <p class="text-[10px] font-bold uppercase tracking-wider {{ $product->exceedsHet() ? 'text-rose-600' : 'text-emerald-600' }}">Harga Jual</p>
                            <p class="mt-1 text-base font-extrabold tabular-nums {{ $product->exceedsHet() ? 'text-rose-700' : 'text-emerald-700' }}">Rp {{ number_format($product->sell_price ?? 0,0,',','.') }}</p>
                            @if($product->exceedsHet())
                            <span class="inline-flex items-center gap-0.5 mt-1.5 px-1.5 py-0.5 rounded-md text-[9px] font-extrabold uppercase tracking-wide bg-white/80 text-rose-700 border border-rose-200">
                                Melebihi HET
                            </span>
                            @endif
                        </div>
                        <div class="rounded-xl border border-teal-200 bg-teal-50/80 p-3">
                            <p class="text-[10px] font-bold uppercase tracking-wider text-teal-600">Harga Grosir</p>
                            <p class="mt-1 text-base font-extrabold text-teal-700 tabular-nums">Rp {{ number_format($product->wholesale_price ?? 0,0,',','.') }}</p>
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-2.5">
                        <div class="rounded-xl border border-slate-100 bg-white px-3 py-2.5">
                            <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400">HET</p>
                            <p class="mt-0.5 font-extrabold text-slate-700 tabular-nums">Rp {{ number_format($product->het_price ?? 0,0,',','.') }}</p>
                        </div>
                        <div class="rounded-xl border border-slate-100 bg-white px-3 py-2.5">
                            <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Stok</p>
                            <p class="mt-0.5 font-extrabold text-slate-700">{{ $product->stock ?? 0 }} <span class="text-xs font-medium text-slate-400">/ min {{ $product->stock_min ?? 0 }}</span></p>
                        </div>
                        <div class="rounded-xl border border-slate-100 bg-white px-3 py-2.5 col-span-2">
                            <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400 mb-1.5">Kadaluarsa</p>
                            @php $expiryBadge = $product->expiryBadge(); @endphp
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-xs font-bold border {{ $expiryBadge['chip'] }}">
                                {{ $expiryBadge['label'] }}
                                @if($expiryBadge['note'])
                                <span class="font-medium opacity-80">· {{ $expiryBadge['note'] }}</span>
                                @endif
                            </span>
                        </div>
                        <div class="rounded-xl border border-slate-100 bg-white px-3 py-2.5 col-span-2">
                            <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400 mb-1.5">Status</p>
                            @if($product->is_active)
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Aktif
                            </span>
                            @else
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-bold bg-gray-100 text-gray-500 border border-gray-200">Nonaktif</span>
                            @endif
                            @if($product->show_in_catalog)
                            <span class="inline-flex items-center gap-1.5 ml-1.5 px-2.5 py-1 rounded-full text-[11px] font-bold bg-teal-50 text-teal-700 border border-teal-200">Di Katalog</span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            @if($product->images && count($product->images) > 1)
            <hr class="my-6 border-slate-100">
            <h4 class="text-sm font-extrabold text-slate-800 mb-3 tracking-tight">Galeri</h4>
            <div class="grid grid-cols-3 sm:grid-cols-4 gap-2.5">
                @foreach($product->images as $img)
                    <div class="aspect-square rounded-xl overflow-hidden border border-slate-100 bg-slate-50">
                        <img src="{{ asset($img) }}" class="w-full h-full object-cover" alt="img">
                    </div>
                @endforeach
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
