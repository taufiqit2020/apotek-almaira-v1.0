@extends('layouts.catalog')
@section('title', 'Keranjang')

@section('content')
@php
    $stockMetaFor = function ($product) {
        $stock = (int) $product->stock;
        $min = max(1, (int) $product->stock_min);
        if ($stock <= 0) {
            return ['state' => 'habis', 'label' => 'Stok Habis', 'badge' => 'bg-red-100 text-red-700 border border-red-200', 'text' => 'text-red-600', 'ring' => 'ring-red-100', 'bar' => 6, 'bar_color' => 'bg-red-500'];
        }
        if ($stock <= $min) {
            return ['state' => 'terbatas', 'label' => 'Stok Terbatas', 'badge' => 'bg-amber-100 text-amber-800 border border-amber-200', 'text' => 'text-amber-700', 'ring' => 'ring-amber-100', 'bar' => min(72, max(18, (int) round(($stock / ($min * 2)) * 100))), 'bar_color' => 'bg-amber-500'];
        }
        return ['state' => 'tersedia', 'label' => 'Stok Tersedia', 'badge' => 'bg-emerald-100 text-emerald-800 border border-emerald-200', 'text' => 'text-emerald-700', 'ring' => 'ring-emerald-100', 'bar' => min(100, max(55, (int) round(($stock / ($min * 3)) * 100))), 'bar_color' => 'bg-emerald-500'];
    };
    $hasStockIssues = !empty($stockIssues);
@endphp
<div class="max-w-6xl mx-auto px-4 sm:px-6 py-8 lg:py-10">
    {{-- Header --}}
    <div class="mb-8 rounded-2xl bg-gradient-to-br from-emerald-700 via-emerald-600 to-teal-600 p-6 sm:p-7 text-white shadow-lg shadow-emerald-900/15 relative overflow-hidden">
        <div class="absolute top-0 right-0 w-48 h-48 bg-white/5 rounded-full -translate-y-1/2 translate-x-1/3 pointer-events-none"></div>
        <div class="relative flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div class="flex items-start gap-4">
                <div class="w-12 h-12 rounded-xl bg-white/15 flex items-center justify-center shrink-0">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                </div>
                <div>
                    <h2 class="font-banner text-2xl sm:text-3xl font-extrabold uppercase tracking-wide">Keranjang PO</h2>
                    <p class="text-emerald-50/90 text-sm mt-1">{{ $partner->name }} · {{ $partner->code }}</p>
                    <p class="text-emerald-100/80 text-xs mt-1">Skema harga: <span class="font-semibold text-white">{{ $priceLabel }}</span></p>
                </div>
            </div>
            <a href="{{ route('catalog.index') }}"
               class="inline-flex items-center justify-center gap-2 px-5 py-2.5 rounded-xl bg-white text-emerald-700 text-sm font-bold shadow-md hover:bg-emerald-50 transition-colors shrink-0">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Tambah Produk
            </a>
        </div>
    </div>

    @if(empty($cart['items']))
    <div class="bg-white rounded-2xl border border-slate-200 p-12 sm:p-16 text-center shadow-sm">
        <div class="w-20 h-20 mx-auto mb-5 rounded-2xl bg-slate-100 flex items-center justify-center">
            <svg class="w-10 h-10 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
        </div>
        <h3 class="font-banner text-lg font-extrabold text-slate-800">Keranjang Masih Kosong</h3>
        <p class="text-sm text-slate-500 mt-2 max-w-sm mx-auto">Pilih produk dari E-Catalog dan tambahkan ke keranjang untuk membuat Purchase Order.</p>
        <a href="{{ route('catalog.index') }}" class="mt-6 inline-flex items-center gap-2 px-6 py-3 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-bold shadow-md shadow-emerald-600/25 transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
            Buka E-Catalog
        </a>
    </div>
    @else
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 lg:gap-8 items-start">
        {{-- Daftar produk --}}
        <div class="lg:col-span-8">
            <form action="{{ route('mitra.cart.update') }}" method="POST" id="cartForm" data-auto-submit="1">
                @csrf
                <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
                    <div class="hidden sm:grid sm:grid-cols-12 gap-3 px-5 py-3.5 bg-slate-50 border-b border-slate-100 text-[11px] font-bold uppercase tracking-wider text-slate-500">
                        <div class="col-span-6">Produk</div>
                        <div class="col-span-2 text-center">Qty</div>
                        <div class="col-span-2 text-right">Subtotal</div>
                        <div class="col-span-2 text-right">Aksi</div>
                    </div>

                    <div class="divide-y divide-slate-100">
                        @foreach($cart['items'] as $i => $line)
                        @php
                            $p = $line['product'];
                            $stockMeta = $stockMetaFor($p);
                            $unitName = $p->unit?->name ?? 'pcs';
                            $qtyOverStock = $line['qty'] > $p->stock;
                        @endphp
                        <div class="p-4 sm:px-5 sm:py-4 hover:bg-slate-50/60 transition-colors cart-line-row" data-product-id="{{ $p->id }}">
                            <div class="grid grid-cols-1 sm:grid-cols-12 gap-4 sm:gap-3 sm:items-center">
                                {{-- Produk --}}
                                <div class="sm:col-span-6 flex items-center gap-3.5 min-w-0">
                                    <div class="w-14 h-14 rounded-xl bg-white border border-slate-200 overflow-hidden shrink-0">
                                        <img src="{{ $p->image_url }}" alt="{{ $p->name }}" class="w-full h-full {{ $p->has_image ? 'object-cover' : 'object-contain p-1.5' }}">
                                    </div>
                                    <div class="min-w-0 flex-1">
                                        <p class="font-bold text-slate-800 text-sm leading-snug truncate">{{ $p->name }}</p>
                                        <div class="flex flex-wrap items-center gap-1.5 mt-1.5">
                                            <span class="inline-flex px-2 py-0.5 rounded-md bg-slate-100 text-[10px] font-semibold text-slate-600">{{ $p->unit?->name ?? 'pcs' }}</span>
                                            <span class="cart-price-badge inline-flex px-2 py-0.5 rounded-md text-[10px] font-bold {{ $line['price_type'] === 'grosir' ? 'bg-amber-100 text-amber-800' : 'bg-blue-50 text-blue-700' }}">
                                                {{ $line['price_type'] === 'grosir' ? 'Grosir' : 'Eceran' }}
                                            </span>
                                        </div>
                                        <p class="cart-unit-price text-xs text-slate-500 mt-1">@ Rp {{ number_format($line['unit_price'], 0, ',', '.') }}</p>

                                        <div class="cart-stock-wrap mt-2.5 rounded-xl bg-slate-50/80 border border-slate-100 px-3 py-2.5 ring-1 {{ $stockMeta['ring'] }}"
                                             data-stock="{{ $p->stock }}"
                                             data-stock-min="{{ $p->stock_min }}"
                                             data-unit="{{ $unitName }}">
                                            <div class="flex items-center gap-2.5">
                                                <div class="w-8 h-8 rounded-lg bg-white border border-slate-200 flex items-center justify-center shrink-0 shadow-sm">
                                                    <svg class="w-4 h-4 {{ $stockMeta['text'] }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                                                    </svg>
                                                </div>
                                                <div class="flex-1 min-w-0">
                                                    <div class="flex items-center justify-between gap-2 mb-1">
                                                        <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Stok Apotek</span>
                                                        <span class="cart-stock-badge inline-flex items-center px-2 py-0.5 rounded-md text-[10px] font-bold {{ $stockMeta['badge'] }}">{{ $stockMeta['label'] }}</span>
                                                    </div>
                                                    <div class="flex items-baseline gap-1.5">
                                                        <span class="cart-stock-qty text-base font-extrabold {{ $stockMeta['text'] }}">{{ number_format($p->stock, 0, ',', '.') }}</span>
                                                        <span class="cart-stock-unit text-[11px] font-semibold text-slate-500">{{ $unitName }}</span>
                                                    </div>
                                                    <div class="mt-1.5 h-1.5 rounded-full bg-slate-200/80 overflow-hidden">
                                                        <div class="cart-stock-bar h-full rounded-full transition-all duration-500 {{ $stockMeta['bar_color'] }}" style="width: {{ $stockMeta['bar'] }}%"></div>
                                                    </div>
                                                </div>
                                            </div>
                                            <p class="cart-stock-warn mt-2 text-[10px] font-bold text-red-600 flex items-center gap-1 {{ $qtyOverStock ? '' : 'hidden' }}">
                                                <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/></svg>
                                                <span class="cart-stock-warn-text">Qty PO ({{ $line['qty'] }}) melebihi stok tersedia ({{ $p->stock }})</span>
                                            </p>
                                        </div>

                                        <input type="hidden" name="items[{{ $i }}][id]" value="{{ $p->id }}">
                                    </div>
                                </div>

                                {{-- Qty --}}
                                <div class="sm:col-span-2 flex sm:justify-center">
                                    <div class="qty-stepper flex items-center gap-0 rounded-xl border border-slate-200 bg-white overflow-hidden shadow-sm">
                                        <button type="button" onclick="adjustQty(this, -1)"
                                                class="qty-btn-minus w-9 h-9 flex items-center justify-center text-slate-500 hover:bg-slate-50 hover:text-slate-800 transition-colors font-bold text-lg leading-none disabled:opacity-40 disabled:cursor-not-allowed">−</button>
                                        <input type="number" name="items[{{ $i }}][qty]" value="{{ $line['qty'] }}" min="0" max="9999"
                                               class="cart-qty w-12 h-9 border-x border-slate-200 text-sm text-center font-bold text-slate-800 outline-none [appearance:textfield] [&::-webkit-outer-spin-button]:appearance-none [&::-webkit-inner-spin-button]:appearance-none"
                                               data-unit-price="{{ $line['unit_price'] }}"
                                               data-price-eceran="{{ $p->sell_price }}"
                                               data-price-grosir="{{ $p->wholesale_price > 0 ? $p->wholesale_price : $p->sell_price }}"
                                               data-price-mode="{{ $partner->price_mode }}"
                                               data-grosir-min="{{ \App\Services\PartnerPricingService::AUTO_GROSIR_MIN_QTY }}">
                                        <button type="button" onclick="adjustQty(this, 1)"
                                                class="qty-btn-plus w-9 h-9 flex items-center justify-center text-slate-500 hover:bg-slate-50 hover:text-slate-800 transition-colors font-bold text-lg leading-none disabled:opacity-40 disabled:cursor-not-allowed">+</button>
                                    </div>
                                </div>

                                {{-- Subtotal --}}
                                <div class="sm:col-span-2 sm:text-right">
                                    <p class="sm:hidden text-[11px] font-bold uppercase text-slate-400 mb-0.5">Subtotal</p>
                                    <p class="cart-line-subtotal text-base font-extrabold text-emerald-700">Rp {{ number_format($line['subtotal'], 0, ',', '.') }}</p>
                                </div>

                                {{-- Hapus --}}
                                <div class="sm:col-span-2 sm:text-right">
                                    <button type="button"
                                       data-remove-url="{{ route('mitra.cart.remove', $p) }}"
                                       onclick="removeCartItem(this)"
                                       class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-bold text-red-600 hover:bg-red-50 border border-transparent hover:border-red-100 transition-colors">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                        Hapus
                                    </button>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </form>
        </div>

        {{-- Ringkasan pesanan --}}
        <div class="lg:col-span-4">
            <div class="lg:sticky lg:top-6 space-y-4">
                <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
                    <div class="px-5 py-4 bg-slate-50 border-b border-slate-100">
                        <h3 class="font-banner text-sm font-extrabold text-slate-800 uppercase tracking-wide">Ringkasan PO</h3>
                    </div>
                    <div class="p-5 space-y-4">
                        <div class="flex items-center justify-between text-sm">
                            <span class="text-slate-500">Mitra</span>
                            <span class="font-semibold text-slate-800 text-right max-w-[55%] truncate">{{ $partner->name }}</span>
                        </div>
                        <div class="flex items-center justify-between text-sm">
                            <span class="text-slate-500">Tipe</span>
                            <span class="font-semibold text-slate-700">{{ $partner->type_label }}</span>
                        </div>
                        <div class="flex items-center justify-between text-sm">
                            <span class="text-slate-500">Total Item</span>
                            <span class="inline-flex items-center justify-center min-w-[28px] h-7 px-2 rounded-lg bg-emerald-100 text-emerald-800 text-sm font-extrabold" id="cartTotalItems">{{ $cart['count'] }}</span>
                        </div>
                        <div class="flex items-center justify-between text-sm">
                            <span class="text-slate-500">Jenis Produk</span>
                            <span class="font-semibold text-slate-700" id="cartProductKinds">{{ count($cart['items']) }} produk</span>
                        </div>

                        <div>
                            <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400 mb-2">Detail Produk</p>
                            <div id="cartSummaryItems" class="rounded-xl border border-slate-100 divide-y divide-slate-100 max-h-52 overflow-y-auto">
                                @foreach($cart['items'] as $line)
                                @php
                                    $p = $line['product'];
                                    $overStock = $line['qty'] > $p->stock;
                                    $unitName = $p->unit?->name ?? 'pcs';
                                @endphp
                                <div class="cart-summary-line px-3 py-2.5 text-xs {{ $overStock ? 'bg-red-50/60' : '' }}" data-product-id="{{ $p->id }}">
                                    <div class="flex items-start justify-between gap-2">
                                        <div class="min-w-0 flex-1">
                                            <p class="font-bold text-slate-800 truncate">{{ $p->name }}</p>
                                            <p class="text-slate-500 mt-0.5 cart-summary-qty-line">
                                                <span class="cart-summary-qty">{{ $line['qty'] }}</span> {{ $unitName }}
                                                × Rp <span class="cart-summary-unit">{{ number_format($line['unit_price'], 0, ',', '.') }}</span>
                                            </p>
                                        </div>
                                        <span class="cart-summary-subtotal font-bold text-emerald-700 shrink-0">Rp {{ number_format($line['subtotal'], 0, ',', '.') }}</span>
                                    </div>
                                    <p class="cart-summary-stock-warn mt-1 text-[10px] font-bold text-red-600 {{ $overStock ? '' : 'hidden' }}">
                                        Stok {{ $p->stock }} — qty melebihi stok
                                    </p>
                                </div>
                                @endforeach
                            </div>
                        </div>

                        <div id="cartStockAlert" class="rounded-xl border border-red-200 bg-red-50 p-3 {{ $hasStockIssues ? '' : 'hidden' }}">
                            <p class="text-xs font-bold text-red-800 flex items-center gap-1.5">
                                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01"/></svg>
                                Checkout diblokir — ada qty melebihi stok
                            </p>
                            <p class="text-[11px] text-red-700 mt-1" id="cartStockAlertDetail">
                                @if($hasStockIssues)
                                    {{ collect($stockIssues)->take(2)->pluck('name')->implode(', ') }}{{ count($stockIssues) > 2 ? ' dan lainnya' : '' }}
                                @endif
                            </p>
                        </div>

                        <div class="pt-4 border-t border-slate-100">
                            @include('partners.portal._order-totals-summary', [
                                'totals' => $ppn,
                                'subtotalId' => 'cartSubtotal',
                                'discId' => 'cartDiscAmount',
                                'ppnId' => 'cartPpnAmount',
                                'grandId' => 'cartGrandTotal',
                                'ppnRowId' => 'cartPpnRow',
                            ])
                            <p class="text-[11px] text-slate-400 mt-2 leading-relaxed">Harga final dikonfirmasi saat checkout PO.</p>
                        </div>

                        <div class="space-y-2.5 pt-1">
                            <button type="button" id="cartSyncBtn" onclick="syncCartAjax(true)"
                                    class="w-full inline-flex items-center justify-center gap-2 px-4 py-3 rounded-xl border border-slate-200 bg-white text-sm font-bold text-slate-700 hover:bg-slate-50 transition-colors disabled:opacity-60">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                                <span id="cartSyncLabel">Perbarui Keranjang</span>
                            </button>
                            <a href="{{ route('mitra.checkout') }}" id="cartCheckoutBtn"
                               @if($hasStockIssues) aria-disabled="true" tabindex="-1" @endif
                               class="w-full inline-flex items-center justify-center gap-2 px-5 py-3.5 rounded-xl text-sm font-bold transition-colors
                                      {{ $hasStockIssues ? 'bg-slate-300 text-slate-500 cursor-not-allowed pointer-events-none shadow-none' : 'bg-emerald-600 hover:bg-emerald-700 text-white shadow-md shadow-emerald-600/25' }}">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                <span id="cartCheckoutLabel">{{ $hasStockIssues ? 'Checkout Diblokir' : 'Checkout PO' }}</span>
                            </a>
                            <a href="{{ route('catalog.index') }}"
                               class="w-full inline-flex items-center justify-center gap-2 px-4 py-3 rounded-xl border border-slate-200 bg-white text-sm font-bold text-slate-600 hover:bg-slate-50 hover:text-emerald-700 transition-colors">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                                Kembali ke E-Catalog
                            </a>
                        </div>
                    </div>
                </div>

                <div class="rounded-2xl border border-emerald-100 bg-emerald-50/60 p-4">
                    <p class="text-xs text-emerald-800 leading-relaxed">
                        <span class="font-bold">Tips:</span> Qty ≥ 10 otomatis mendapat harga grosir. Perubahan qty tersimpan otomatis di latar belakang tanpa reload halaman.
                    </p>
                </div>

                <a href="{{ route('catalog.index') }}" class="lg:hidden inline-flex items-center gap-1.5 text-sm text-slate-500 hover:text-emerald-600 font-semibold transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                    Lanjut belanja di E-Catalog
                </a>
            </div>
        </div>
    </div>
    @endif
</div>
@endsection

@push('scripts')
<script>
(function () {
    const PRICE_GROSIR = @json(\App\Models\Partner::PRICE_GROSIR);
    const PRICE_AUTO = @json(\App\Models\Partner::PRICE_AUTO);
    const PPN_ENABLED = @json($ppn['ppn_enabled']);
    const PPN_PERCENT = @json($ppn['ppn_percent']);
    const PPN_BEARER = @json($ppn['ppn_bearer']);
    const updateUrl = @json(route('mitra.cart.update'));
    let syncTimer = null;
    let syncing = false;

    function formatRp(amount) {
        return window.MitraCart?.formatRp(amount) || ('Rp ' + Math.round(amount).toLocaleString('id-ID'));
    }

    function resolveUnitPrice(input, qty) {
        const mode = input.dataset.priceMode || '';
        const eceran = parseFloat(input.dataset.priceEceran) || 0;
        const grosir = parseFloat(input.dataset.priceGrosir) || eceran;
        const grosirMin = parseInt(input.dataset.grosirMin || '10', 10);

        if (mode === PRICE_GROSIR) return { type: 'grosir', price: grosir };
        if (mode === PRICE_AUTO && qty >= grosirMin) return { type: 'grosir', price: grosir };
        return { type: 'eceran', price: eceran };
    }

    function computeStockMeta(stock, stockMin) {
        stock = parseInt(stock, 10) || 0;
        const min = Math.max(1, parseInt(stockMin, 10) || 1);

        if (stock <= 0) {
            return { state: 'habis', label: 'Stok Habis', badge: 'bg-red-100 text-red-700 border border-red-200', text: 'text-red-600', ring: 'ring-red-100', bar: 6, bar_color: 'bg-red-500' };
        }
        if (stock <= min) {
            return { state: 'terbatas', label: 'Stok Terbatas', badge: 'bg-amber-100 text-amber-800 border border-amber-200', text: 'text-amber-700', ring: 'ring-amber-100', bar: Math.min(72, Math.max(18, Math.round((stock / (min * 2)) * 100))), bar_color: 'bg-amber-500' };
        }
        return { state: 'tersedia', label: 'Stok Tersedia', badge: 'bg-emerald-100 text-emerald-800 border border-emerald-200', text: 'text-emerald-700', ring: 'ring-emerald-100', bar: Math.min(100, Math.max(55, Math.round((stock / (min * 3)) * 100))), bar_color: 'bg-emerald-500' };
    }

    function applyStockDisplay(row, qty, item) {
        const wrap = row.querySelector('.cart-stock-wrap');
        if (!wrap) return;

        const stock = item?.stock !== undefined ? parseInt(item.stock, 10) : parseInt(wrap.dataset.stock, 10) || 0;
        const stockMin = item?.stock_min !== undefined ? parseInt(item.stock_min, 10) : parseInt(wrap.dataset.stockMin, 10) || 1;
        const unitName = item?.unit_name || wrap.dataset.unit || 'pcs';
        const meta = computeStockMeta(stock, stockMin);

        wrap.dataset.stock = stock;
        wrap.dataset.stockMin = stockMin;
        wrap.dataset.unit = unitName;
        wrap.classList.remove('ring-red-100', 'ring-amber-100', 'ring-emerald-100');
        wrap.classList.add(meta.ring);

        const qtyEl = wrap.querySelector('.cart-stock-qty');
        const unitEl = wrap.querySelector('.cart-stock-unit');
        const badgeEl = wrap.querySelector('.cart-stock-badge');
        const barEl = wrap.querySelector('.cart-stock-bar');
        const warnEl = wrap.querySelector('.cart-stock-warn');
        const warnTextEl = wrap.querySelector('.cart-stock-warn-text');
        const iconEl = wrap.querySelector('.w-8 svg');

        if (qtyEl) {
            qtyEl.textContent = stock.toLocaleString('id-ID');
            qtyEl.className = 'cart-stock-qty text-base font-extrabold ' + meta.text;
        }
        if (unitEl) unitEl.textContent = unitName;
        if (badgeEl) {
            badgeEl.textContent = meta.label;
            badgeEl.className = 'cart-stock-badge inline-flex items-center px-2 py-0.5 rounded-md text-[10px] font-bold ' + meta.badge;
        }
        if (barEl) {
            barEl.style.width = meta.bar + '%';
            barEl.className = 'cart-stock-bar h-full rounded-full transition-all duration-500 ' + meta.bar_color;
        }
        if (iconEl) iconEl.className = 'w-4 h-4 ' + meta.text;
        if (warnEl && warnTextEl) {
            if (qty > stock) {
                warnEl.classList.remove('hidden');
                warnTextEl.textContent = 'Qty PO (' + qty + ') melebihi stok tersedia (' + stock + ')';
            } else {
                warnEl.classList.add('hidden');
            }
        }
    }

    function applyLineFromServer(row, item) {
        const input = row.querySelector('.cart-qty');
        const badge = row.querySelector('.cart-price-badge');
        const unitEl = row.querySelector('.cart-unit-price');
        const subEl = row.querySelector('.cart-line-subtotal');

        if (input) input.value = item.qty;
        if (badge) {
            badge.textContent = item.price_type === 'grosir' ? 'Grosir' : 'Eceran';
            badge.className = 'cart-price-badge inline-flex px-2 py-0.5 rounded-md text-[10px] font-bold ' +
                (item.price_type === 'grosir' ? 'bg-amber-100 text-amber-800' : 'bg-blue-50 text-blue-700');
        }
        if (unitEl) unitEl.textContent = '@ ' + formatRp(item.unit_price);
        if (subEl) subEl.textContent = formatRp(item.subtotal);
        applyStockDisplay(row, item.qty, item);
        refreshSummaryForRow(row);
    }

    function refreshLinePreview(input) {
        const row = input.closest('.cart-line-row');
        if (!row) return;

        const qty = Math.max(0, parseInt(input.value, 10) || 0);
        const priced = resolveUnitPrice(input, qty);
        const badge = row.querySelector('.cart-price-badge');
        const unitEl = row.querySelector('.cart-unit-price');
        const subEl = row.querySelector('.cart-line-subtotal');

        if (badge) {
            badge.textContent = priced.type === 'grosir' ? 'Grosir' : 'Eceran';
            badge.className = 'cart-price-badge inline-flex px-2 py-0.5 rounded-md text-[10px] font-bold ' +
                (priced.type === 'grosir' ? 'bg-amber-100 text-amber-800' : 'bg-blue-50 text-blue-700');
        }
        if (unitEl) unitEl.textContent = '@ ' + formatRp(priced.price);
        if (subEl) subEl.textContent = formatRp(priced.price * qty);
        applyStockDisplay(row, qty, null);
        refreshSummaryForRow(row);
    }

    function refreshSummaryForRow(row) {
        const productId = parseInt(row.dataset.productId, 10);
        const input = row.querySelector('.cart-qty');
        const summaryLine = document.querySelector('.cart-summary-line[data-product-id="' + productId + '"]');
        if (!summaryLine || !input) return;

        const qty = Math.max(0, parseInt(input.value, 10) || 0);
        const priced = resolveUnitPrice(input, qty);
        const stock = parseInt(row.querySelector('.cart-stock-wrap')?.dataset.stock, 10) || 0;
        const overStock = qty > stock;

        const qtyEl = summaryLine.querySelector('.cart-summary-qty');
        const unitEl = summaryLine.querySelector('.cart-summary-unit');
        const subEl = summaryLine.querySelector('.cart-summary-subtotal');
        const warnEl = summaryLine.querySelector('.cart-summary-stock-warn');

        if (qtyEl) qtyEl.textContent = qty;
        if (unitEl) unitEl.textContent = Math.round(priced.price).toLocaleString('id-ID');
        if (subEl) subEl.textContent = formatRp(priced.price * qty);
        summaryLine.classList.toggle('bg-red-50/60', overStock);
        if (warnEl) {
            warnEl.classList.toggle('hidden', !overStock);
            if (overStock) warnEl.textContent = 'Stok ' + stock + ' — qty melebihi stok';
        }
    }

    function refreshCheckoutState(cart) {
        let hasIssues = false;
        const issues = [];

        if (cart && cart.has_stock_issues !== undefined) {
            hasIssues = !!cart.has_stock_issues;
            (cart.stock_issues || []).forEach(function (issue) {
                if (issue.name) issues.push(issue.name);
            });
        } else {
            document.querySelectorAll('.cart-line-row').forEach(function (row) {
                const input = row.querySelector('.cart-qty');
                const stockWrap = row.querySelector('.cart-stock-wrap');
                if (!input || !stockWrap) return;
                const qty = parseInt(input.value, 10) || 0;
                const stock = parseInt(stockWrap.dataset.stock, 10) || 0;
                if (qty > stock) {
                    hasIssues = true;
                    const nameEl = row.querySelector('p.font-bold');
                    if (nameEl) issues.push(nameEl.textContent.trim());
                }
            });
        }

        const btn = document.getElementById('cartCheckoutBtn');
        const label = document.getElementById('cartCheckoutLabel');
        const alert = document.getElementById('cartStockAlert');
        const alertDetail = document.getElementById('cartStockAlertDetail');

        if (btn) {
            btn.classList.toggle('bg-slate-300', hasIssues);
            btn.classList.toggle('text-slate-500', hasIssues);
            btn.classList.toggle('cursor-not-allowed', hasIssues);
            btn.classList.toggle('pointer-events-none', hasIssues);
            btn.classList.toggle('shadow-none', hasIssues);
            btn.classList.toggle('bg-emerald-600', !hasIssues);
            btn.classList.toggle('hover:bg-emerald-700', !hasIssues);
            btn.classList.toggle('text-white', !hasIssues);
            btn.classList.toggle('shadow-md', !hasIssues);
            btn.classList.toggle('shadow-emerald-600/25', !hasIssues);
            if (hasIssues) {
                btn.setAttribute('aria-disabled', 'true');
                btn.setAttribute('tabindex', '-1');
            } else {
                btn.removeAttribute('aria-disabled');
                btn.removeAttribute('tabindex');
            }
        }
        if (label) label.textContent = hasIssues ? 'Checkout Diblokir' : 'Checkout PO';
        if (alert) alert.classList.toggle('hidden', !hasIssues);
        if (alertDetail) {
            alertDetail.textContent = issues.length
                ? issues.slice(0, 2).join(', ') + (issues.length > 2 ? ' dan lainnya' : '')
                : '';
        }
    }

    function applyPpnTotals(subtotal, cart) {
        let discountAmount = 0;
        let ppnAmount = 0;
        let grandTotal = subtotal;

        if (cart && cart.grand_total !== undefined) {
            discountAmount = cart.discount_amount || 0;
            ppnAmount = cart.ppn_amount || 0;
            grandTotal = cart.grand_total ?? subtotal;
        } else {
            const netSubtotal = Math.max(0, subtotal - discountAmount);
            if (PPN_ENABLED) {
                ppnAmount = Math.round(netSubtotal * PPN_PERCENT / 100);
                grandTotal = PPN_BEARER === 'buyer' ? netSubtotal + ppnAmount : netSubtotal;
            } else {
                grandTotal = netSubtotal;
            }
        }

        const subEl = document.getElementById('cartSubtotal');
        const discEl = document.getElementById('cartDiscAmount');
        const ppnEl = document.getElementById('cartPpnAmount');
        const ppnRow = document.getElementById('cartPpnRow');
        const grandEl = document.getElementById('cartGrandTotal');

        if (subEl) subEl.textContent = formatRp(subtotal);
        if (discEl) {
            discEl.textContent = (discountAmount > 0 ? '-' : '') + formatRp(discountAmount);
            discEl.classList.toggle('text-red-600', discountAmount > 0);
            discEl.classList.toggle('text-slate-700', discountAmount <= 0);
        }
        const showPpn = PPN_ENABLED || (cart && cart.ppn_enabled);
        if (ppnRow) ppnRow.classList.toggle('hidden', !showPpn);
        if (ppnEl) ppnEl.textContent = formatRp(ppnAmount);
        if (grandEl) grandEl.textContent = formatRp(grandTotal);
    }

    function refreshCartPreview() {
        let totalQty = 0;
        let subtotal = 0;

        document.querySelectorAll('.cart-qty').forEach(function (input) {
            const qty = Math.max(0, parseInt(input.value, 10) || 0);
            const priced = resolveUnitPrice(input, qty);
            totalQty += qty;
            subtotal += priced.price * qty;
        });

        const totalEl = document.getElementById('cartTotalItems');
        const kindsEl = document.getElementById('cartProductKinds');
        if (totalEl) totalEl.textContent = totalQty;
        if (kindsEl) kindsEl.textContent = document.querySelectorAll('.cart-line-row').length + ' produk';
        applyPpnTotals(subtotal, null);
        document.querySelectorAll('.cart-line-row').forEach(refreshSummaryForRow);
        refreshCheckoutState();
    }

    function applyCartFromServer(cart) {
        const map = {};
        (cart.items || []).forEach(item => { map[item.product_id] = item; });

        document.querySelectorAll('.cart-line-row').forEach(function (row) {
            const id = parseInt(row.dataset.productId, 10);
            if (!map[id]) {
                row.style.transition = 'opacity 0.25s ease, transform 0.25s ease';
                row.style.opacity = '0';
                row.style.transform = 'translateX(12px)';
                setTimeout(() => row.remove(), 260);
                return;
            }
            applyLineFromServer(row, map[id]);
        });

        const totalEl = document.getElementById('cartTotalItems');
        const kindsEl = document.getElementById('cartProductKinds');
        if (totalEl) totalEl.textContent = cart.count;
        if (kindsEl) kindsEl.textContent = cart.product_count + ' produk';
        applyPpnTotals(cart.subtotal, cart);
        if (kindsEl) kindsEl.textContent = cart.product_count + ' produk';
        window.MitraCart?.updateBadge(cart.count);
        refreshCheckoutState(cart);

        (cart.items || []).forEach(function (item) {
            const summaryLine = document.querySelector('.cart-summary-line[data-product-id="' + item.product_id + '"]');
            if (!summaryLine) return;
            const qtyEl = summaryLine.querySelector('.cart-summary-qty');
            const unitEl = summaryLine.querySelector('.cart-summary-unit');
            const subEl = summaryLine.querySelector('.cart-summary-subtotal');
            const warnEl = summaryLine.querySelector('.cart-summary-stock-warn');
            const overStock = item.qty > item.stock;
            if (qtyEl) qtyEl.textContent = item.qty;
            if (unitEl) unitEl.textContent = Math.round(item.unit_price).toLocaleString('id-ID');
            if (subEl) subEl.textContent = formatRp(item.subtotal);
            summaryLine.classList.toggle('bg-red-50/60', overStock);
            if (warnEl) {
                warnEl.classList.toggle('hidden', !overStock);
                if (overStock) warnEl.textContent = 'Stok ' + item.stock + ' — qty melebihi stok';
            }
        });

        if ((cart.items || []).length === 0) {
            document.querySelectorAll('.cart-line-row').forEach(r => r.remove());
        }
    }

    function setSyncing(active) {
        syncing = active;
        const btn = document.getElementById('cartSyncBtn');
        const label = document.getElementById('cartSyncLabel');
        document.querySelectorAll('.qty-btn-minus, .qty-btn-plus').forEach(el => { el.disabled = active; });
        if (btn) btn.disabled = active;
        if (label) label.textContent = active ? 'Menyimpan...' : 'Perbarui Keranjang';
    }

    window.syncCartAjax = function (immediate = false) {
        const run = async () => {
            if (syncing) return;
            const form = document.getElementById('cartForm');
            if (!form || !window.MitraCart) return;

            setSyncing(true);
            try {
                const data = await window.MitraCart.post(updateUrl, new FormData(form));
                if (data.cart) applyCartFromServer(data.cart);
            } catch (err) {
                window.MitraCart.showToast(err.message || 'Gagal memperbarui keranjang.', 'error');
            } finally {
                setSyncing(false);
            }
        };

        clearTimeout(syncTimer);
        if (immediate) {
            run();
        } else {
            syncTimer = setTimeout(run, 450);
        }
    };

    window.adjustQty = function (btn, delta) {
        const wrap = btn.closest('.qty-stepper');
        const input = wrap?.querySelector('.cart-qty');
        if (!input) return;

        const current = parseInt(input.value, 10) || 0;
        const next = Math.max(0, Math.min(9999, current + delta));
        input.value = next;

        refreshLinePreview(input);
        refreshCartPreview();
        syncCartAjax(false);
    };

    window.removeCartItem = async function (btn) {
        const url = btn.dataset.removeUrl;
        if (!url || !window.MitraCart) return;

        const row = btn.closest('.cart-line-row');
        btn.disabled = true;

        try {
            const body = new FormData();
            body.append('_token', window.MitraCart.csrf());
            const data = await window.MitraCart.post(url, body);
            if (row) {
                row.style.transition = 'opacity 0.25s ease';
                row.style.opacity = '0';
                setTimeout(() => row.remove(), 250);
            }
            if (data.cart) applyCartFromServer(data.cart);
        } catch (err) {
            btn.disabled = false;
            window.MitraCart.showToast(err.message || 'Gagal menghapus item.', 'error');
        }
    };

    document.querySelectorAll('.cart-qty').forEach(function (input) {
        input.addEventListener('input', function () {
            refreshLinePreview(input);
            refreshCartPreview();
        });
        input.addEventListener('change', function () {
            syncCartAjax(false);
        });
    });

    refreshCartPreview();
})();
</script>
@endpush
