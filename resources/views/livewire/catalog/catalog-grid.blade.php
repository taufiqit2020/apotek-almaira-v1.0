@php
    $waRaw = \App\Models\Setting::get('apotek_phone', '0851-6665-7070');
    $waNumber = preg_replace('/^0/', '62', preg_replace('/\D/', '', $waRaw));
    $apotekName = \App\Models\Setting::get('apotek_name', 'Apotek Almaira');
    $isMitra = !empty($partner);
@endphp
<div wire:key="catalog-root">
    {{-- ═══ BANNER RESMI — PT NUR MADANI FARMA · Apotek Almaira Banjarbaru ═══ --}}
    <section class="relative overflow-hidden bg-gradient-to-br from-[#0a3d2e] via-[#0f5c3a] to-[#157a4d]">
        {{-- Dekorasi latar --}}
        <div class="absolute inset-0 opacity-[0.04]" style="background-image: url(&quot;data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23ffffff' fill-opacity='1'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E&quot;);"></div>
        <div class="absolute -top-32 -right-32 w-[420px] h-[420px] rounded-full bg-emerald-400/8 blur-3xl"></div>
        <div class="absolute -bottom-40 -left-24 w-[380px] h-[380px] rounded-full bg-teal-300/8 blur-3xl"></div>
        <div class="absolute top-0 inset-x-0 h-[3px] bg-gradient-to-r from-transparent via-amber-400 to-transparent"></div>

        <div class="relative max-w-7xl mx-auto px-4 sm:px-6 py-8 sm:py-10 lg:py-12">
            {{-- Mobile: logo berdampingan di atas --}}
            <div class="flex lg:hidden items-start justify-center gap-6 mb-6">
                <div class="text-center">
                    <div class="w-20 h-20 rounded-xl bg-white p-2 shadow-xl ring-2 ring-white/30 flex items-center justify-center mx-auto">
                        <img src="{{ asset('assets/images/logo-ptnmf.png') }}" class="w-full h-full object-contain" alt="Logo PT">
                    </div>
                    <p class="mt-1.5 text-[8px] font-bold text-emerald-200/70 uppercase tracking-widest">Perusahaan</p>
                </div>
                <div class="text-center">
                    <div class="w-20 h-20 rounded-xl bg-white p-2 shadow-xl ring-2 ring-white/30 flex items-center justify-center mx-auto">
                        <img src="{{ asset('assets/images/logo-apotek.png') }}" class="w-full h-full object-contain" alt="Logo Apotek">
                    </div>
                    <p class="mt-1.5 text-[8px] font-bold text-emerald-200/70 uppercase tracking-widest">Apotek</p>
                </div>
            </div>

            <div class="flex flex-col lg:flex-row items-center justify-center gap-6 lg:gap-10">

                {{-- Logo PT (desktop only) --}}
                <div class="hidden lg:block shrink-0">
                    <div class="relative group">
                        <div class="absolute -inset-1 rounded-2xl bg-gradient-to-br from-emerald-400/30 to-amber-400/20 blur-sm opacity-60 group-hover:opacity-100 transition-opacity"></div>
                        <div class="relative w-24 h-24 sm:w-32 sm:h-32 lg:w-40 lg:h-40 rounded-2xl bg-white p-2.5 sm:p-4 shadow-2xl ring-2 ring-white/30 flex items-center justify-center overflow-hidden">
                            <img src="{{ asset('assets/images/logo-ptnmf.png') }}" class="w-full h-full object-contain" alt="Logo PT Nur Madani Farma">
                        </div>
                        <p class="mt-2 text-center text-[9px] sm:text-[10px] font-bold text-emerald-200/70 uppercase tracking-[0.2em]">Perusahaan</p>
                    </div>
                </div>

                {{-- Teks identitas utama --}}
                <div class="text-center flex-1 max-w-2xl px-2">
                    <span class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full bg-white/8 text-emerald-100 text-[10px] sm:text-[11px] font-bold uppercase tracking-[0.22em] backdrop-blur-sm border border-white/10">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse"></span>
                        E-Catalog Resmi
                    </span>

                    <h1 class="font-banner mt-5 text-[26px] leading-[1.15] sm:text-[38px] lg:text-[44px] font-black text-white uppercase tracking-[0.04em] drop-shadow-lg">
                        PT Nur Madani Farma
                    </h1>

                    {{-- Pembatas emas --}}
                    <div class="flex items-center justify-center gap-3 my-4">
                        <span class="h-px w-12 sm:w-20 bg-gradient-to-r from-transparent via-amber-400/80 to-amber-400"></span>
                        <svg class="w-3 h-3 text-amber-400" fill="currentColor" viewBox="0 0 20 20"><path d="M10 2l2.4 5.8H19l-4.8 3.5 1.8 5.7L10 13.4l-6 3.6 1.8-5.7L1 7.8h6.6z"/></svg>
                        <span class="h-px w-12 sm:w-20 bg-gradient-to-l from-transparent via-amber-400/80 to-amber-400"></span>
                    </div>

                    <p class="font-banner text-2xl sm:text-[34px] lg:text-[38px] font-extrabold leading-tight">
                        <span class="text-transparent bg-clip-text bg-gradient-to-r from-sky-200 via-white to-emerald-200">Apotek Almaira</span>
                        <span class="block sm:inline sm:ml-2 text-lg sm:text-2xl lg:text-[28px] font-bold text-amber-300/95 mt-1 sm:mt-0">Banjarbaru</span>
                    </p>

                    <p class="mt-3 text-[11px] sm:text-xs font-semibold text-emerald-100/70 uppercase tracking-[0.28em]">
                        Kalimantan Selatan &middot; Indonesia
                    </p>

                    <p class="mt-5 text-sm sm:text-[15px] text-emerald-50/85 max-w-lg mx-auto leading-relaxed font-medium">
                        Katalog produk resmi — cek ketersediaan &amp; harga obat, vitamin, dan kebutuhan kesehatan Anda secara real-time.
                    </p>

                    <div class="mt-5 flex flex-wrap items-center justify-center gap-3">
                        <span class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-white/10 text-white text-xs sm:text-sm font-extrabold uppercase tracking-wider border border-white/15 backdrop-blur-sm">
                            <svg class="w-4 h-4 text-amber-300" fill="currentColor" viewBox="0 0 20 20"><path d="M10 2a4 4 0 00-4 4v1H5a1 1 0 00-.994.89l-1 9A1 1 0 004 18h12a1 1 0 00.994-1.11l-1-9A1 1 0 0015 7h-1V6a4 4 0 00-4-4zm2 5V6a2 2 0 10-4 0v1h4z"/></svg>
                            {{ $totalCatalog }} Produk Tersedia
                        </span>
                        <span class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-emerald-500/20 text-emerald-100 text-xs sm:text-sm font-bold border border-emerald-400/25">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                            Terpercaya &amp; Terdaftar
                        </span>
                    </div>
                </div>

                {{-- Logo Apotek (desktop only) --}}
                <div class="hidden lg:block shrink-0">
                    <div class="relative group">
                        <div class="absolute -inset-1 rounded-2xl bg-gradient-to-br from-sky-300/30 to-emerald-300/20 blur-sm opacity-60 group-hover:opacity-100 transition-opacity"></div>
                        <div class="relative w-24 h-24 sm:w-32 sm:h-32 lg:w-40 lg:h-40 rounded-2xl bg-white p-2.5 sm:p-4 shadow-2xl shadow-emerald-950/30 ring-2 ring-white/40 flex items-center justify-center">
                            <img src="{{ asset('assets/images/logo-apotek.png') }}" class="w-full h-full object-contain" alt="Logo Apotek Almaira">
                        </div>
                        <p class="mt-2 text-center text-[9px] sm:text-[10px] font-bold text-emerald-200/70 uppercase tracking-[0.2em]">Apotek</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Gelombang transisi ke konten putih --}}
        <div class="absolute bottom-0 left-0 w-full overflow-hidden leading-none">
            <svg class="relative block w-full h-6 sm:h-8 text-slate-50" viewBox="0 0 1200 40" preserveAspectRatio="none" fill="currentColor">
                <path d="M0,20 C300,50 500,0 600,15 C700,30 900,5 1200,25 L1200,40 L0,40 Z"></path>
            </svg>
        </div>
    </section>

    {{-- ═══ SEARCH + FILTER (satu-satunya elemen sticky, top-0, agar tidak tabrakan) ═══ --}}
    <div class="sticky top-0 z-30 bg-white/95 backdrop-blur-md border-b border-slate-200 shadow-sm">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 py-3 space-y-3">
            {{-- Search + Kategori (dropdown kompak agar produk tetap terlihat) --}}
            <div class="grid grid-cols-1 sm:grid-cols-[minmax(0,1fr)_minmax(200px,260px)] gap-2.5">
                <div class="relative min-w-0">
                    <svg class="absolute left-3.5 top-1/2 -translate-y-1/2 w-4.5 h-4.5 text-slate-400 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    <input
                        wire:model.live.debounce.400ms="search"
                        type="text"
                        placeholder="Cari nama, kandungan, atau indikasi/fungsi..."
                        class="w-full pl-10 pr-4 py-2.5 rounded-xl border border-slate-200 bg-slate-50 text-sm text-slate-700 placeholder:text-slate-400 focus:outline-none focus:ring-2 focus:ring-emerald-400 focus:border-emerald-400 focus:bg-white transition-colors">
                    <div wire:loading wire:target="search" class="absolute right-3.5 top-1/2 -translate-y-1/2">
                        <svg class="w-4 h-4 text-emerald-500 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
                    </div>
                </div>

                @if($categories->count() > 0)
                <div class="relative min-w-0">
                    <label for="catalog-category-filter" class="sr-only">Kategori</label>
                    <svg class="absolute left-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h10M4 18h7"/></svg>
                    <select
                        id="catalog-category-filter"
                        wire:model.live="categoryFilter"
                        class="w-full appearance-none pl-10 pr-10 py-2.5 rounded-xl border border-slate-200 bg-slate-50 text-sm font-semibold text-slate-700 focus:outline-none focus:ring-2 focus:ring-emerald-400 focus:border-emerald-400 focus:bg-white transition-colors cursor-pointer">
                        <option value="">Semua Kategori</option>
                        @foreach($categories as $cat)
                        <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                        @endforeach
                    </select>
                    <svg class="absolute right-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                    <div wire:loading wire:target="categoryFilter" class="absolute right-9 top-1/2 -translate-y-1/2">
                        <svg class="w-3.5 h-3.5 text-emerald-500 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>

    {{-- ═══ GRID PRODUK ════════════════════════════════════════════ --}}
    <section class="max-w-7xl mx-auto px-4 sm:px-6 py-6 sm:py-8">

        @if($search || $categoryFilter)
        <div class="mb-4 flex items-center gap-2 text-sm text-slate-500">
            <span>Menampilkan <strong class="text-slate-700">{{ $products->total() }}</strong> hasil</span>
            @if($search)
            <span class="inline-flex items-center gap-1 px-2.5 py-1 bg-emerald-50 text-emerald-700 text-xs rounded-full font-medium">
                "{{ $search }}"
                <button wire:click="$set('search','')" class="hover:text-red-500">&times;</button>
            </span>
            @endif
        </div>
        @endif

        <div wire:loading.class="opacity-40" wire:target="search,selectCategory,categoryFilter,gotoPage,nextPage,previousPage" class="transition-opacity duration-150">
            @if($products->count() > 0)
            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-3 sm:gap-4">
                @foreach($products as $product)
                @php
                    $stockBadge = $product->stockBadge();
                    $stockState = $stockBadge['state'];
                    $expiryBadge = $product->expiryBadge();
                    $meta = $product->catalogMeta();
                    $priceInfo = \App\Services\PartnerPricingService::catalogPriceInfo($product, $isMitra ? $partner : null);
                    $waPayload = \App\Services\CatalogWhatsAppService::buttonPayload(
                        $product,
                        $waRaw,
                        $apotekName,
                        (float) $priceInfo['primary'],
                        $stockState
                    );
                @endphp
                <article wire:key="catalog-product-{{ $product->id }}"
                    data-live-product="{{ $product->id }}"
                    class="group bg-white rounded-2xl border border-slate-100/80 shadow-[0_1px_3px_rgba(15,23,42,0.04)] hover:shadow-[0_12px_28px_rgba(16,185,129,0.12)] hover:border-emerald-200/60 hover:-translate-y-1 transition-all duration-300 overflow-hidden flex flex-col">

                    {{-- Gambar (klik ke detail) --}}
                    <a href="{{ route('catalog.show', $product) }}" class="relative block aspect-[4/5] sm:aspect-square bg-gradient-to-br from-emerald-50/80 via-white to-teal-50/60 overflow-hidden">
                        <img src="{{ $product->image_url }}" alt="{{ $product->name }}" loading="lazy"
                             class="w-full h-full group-hover:scale-105 transition-transform duration-500 {{ $product->has_image ? 'object-cover' : 'object-contain p-6 sm:p-8' }}">

                        <div class="absolute inset-0 bg-emerald-900/0 group-hover:bg-emerald-900/10 transition-colors duration-300 flex items-center justify-center pointer-events-none">
                            <span class="opacity-0 group-hover:opacity-100 translate-y-2 group-hover:translate-y-0 transition-all duration-300 px-3 py-1.5 rounded-full bg-white/95 text-emerald-700 text-[10px] font-bold shadow-md">
                                Lihat Detail
                            </span>
                        </div>

                        @if($product->category)
                        <span class="absolute top-2 left-2 z-[1] px-2 py-0.5 rounded-md text-[9px] sm:text-[10px] font-bold bg-white/95 text-emerald-700 shadow-sm backdrop-blur-sm max-w-[55%] truncate">
                            {{ $product->category->name }}
                        </span>
                        @endif

                        <span data-live-stock-chip data-live-stock-base="absolute top-2 right-2 z-[1] inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-[9px] sm:text-[10px] font-bold shadow-sm border"
                              class="absolute top-2 right-2 z-[1] inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-[9px] sm:text-[10px] font-bold shadow-sm border {{ $stockBadge['chip'] }}">
                            <span data-live-stock-dot data-live-dot-base="w-1.5 h-1.5 rounded-full" class="w-1.5 h-1.5 rounded-full {{ $stockBadge['dot'] }}"></span>
                            <span data-live-field="stock_short">{{ $stockBadge['short'] }}</span>
                        </span>
                    </a>

                    {{-- Info --}}
                    <div class="p-3 sm:p-3.5 flex flex-col flex-1">
                        <a href="{{ route('catalog.show', $product) }}" class="block">
                            <h3 class="text-xs sm:text-sm font-bold text-slate-800 leading-snug line-clamp-2 group-hover:text-emerald-700 transition-colors" title="{{ $product->name }}">
                                {{ $product->name }}
                            </h3>
                        </a>

                        <div class="mt-1.5 space-y-0.5 text-[10px] sm:text-[11px] text-slate-500 leading-snug">
                            @if($meta['bentuk_sediaan'])
                            <p class="truncate"><span class="text-slate-400">Bentuk:</span> <span class="font-semibold text-slate-600">{{ $meta['bentuk_sediaan'] }}</span></p>
                            @endif
                            @if($meta['pabrik'])
                            <p class="truncate"><span class="text-slate-400">Pabrik:</span> <span class="font-semibold text-slate-600">{{ $meta['pabrik'] }}</span></p>
                            @endif
                            @if($meta['kandungan'])
                            <p class="line-clamp-1"><span class="text-slate-400">Kandungan:</span> <span class="font-medium text-slate-600">{{ $meta['kandungan'] }}</span></p>
                            @endif
                            @if($meta['indikasi'])
                            <p class="line-clamp-2 text-slate-500">{{ $meta['indikasi'] }}</p>
                            @endif
                        </div>

                        <div class="mt-2 flex flex-wrap gap-1.5">
                            <p data-live-stock-chip data-live-stock-base="inline-flex items-center gap-1.5 text-[10px] font-bold border px-2 py-0.5 rounded-lg"
                               class="inline-flex items-center gap-1.5 text-[10px] font-bold {{ $stockBadge['chip'] }} border px-2 py-0.5 rounded-lg">
                                <span data-live-stock-dot data-live-dot-base="w-1.5 h-1.5 rounded-full" class="w-1.5 h-1.5 rounded-full {{ $stockBadge['dot'] }}"></span>
                                <span data-live-field="stock_label">{{ $stockBadge['label'] }}</span>
                            </p>
                            @if($expiryBadge['has_date'])
                            <p class="inline-flex items-center gap-1 text-[10px] font-bold {{ $expiryBadge['chip'] }} border px-2 py-0.5 rounded-lg" title="{{ $expiryBadge['note'] }}">
                                <svg class="w-3 h-3 {{ $expiryBadge['icon'] }}" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                </svg>
                                Exp {{ $expiryBadge['date'] }}
                            </p>
                            @endif
                        </div>

                        <div class="mt-2 flex-1 space-y-1">
                            <p class="text-[10px] text-slate-400">per {{ $product->unit?->name ?? 'pcs' }}</p>
                            <div class="flex items-baseline justify-between gap-2">
                                <span class="text-[10px] font-bold uppercase tracking-wide text-emerald-700/80" data-live-field="price_primary_label">{{ $priceInfo['label'] ?? 'Eceran' }}</span>
                                <p class="text-sm sm:text-base font-extrabold text-emerald-700 tracking-tight tabular-nums" data-live-field="price_primary_formatted">
                                    Rp {{ number_format($priceInfo['primary'], 0, ',', '.') }}
                                </p>
                            </div>
                            <div data-live-field="price_secondary_wrap" class="flex items-baseline justify-between gap-2 rounded-lg bg-teal-50/80 border border-teal-100 px-2 py-1" @if(empty($priceInfo['secondary'])) style="display:none" @endif>
                                <span class="text-[10px] font-bold uppercase tracking-wide text-teal-700" data-live-field="price_secondary_label">{{ $priceInfo['secondary_label'] ?? 'Grosir' }}</span>
                                <p class="text-xs sm:text-sm font-extrabold text-teal-800 tracking-tight tabular-nums" data-live-field="price_secondary_formatted">
                                    @if(!empty($priceInfo['secondary']))
                                    Rp {{ number_format($priceInfo['secondary'], 0, ',', '.') }}
                                    @endif
                                </p>
                            </div>
                            @if($priceInfo['note'])
                            <p class="text-[10px] text-slate-500 leading-snug" data-live-field="price_note">{{ $priceInfo['note'] }}</p>
                            @endif
                        </div>

                        @if($isMitra)
                        <form action="{{ route('mitra.cart.add', $product) }}" method="POST" class="mt-3 mitra-add-cart-form" onclick="event.stopPropagation()" data-product-name="{{ $product->name }}">
                            @csrf
                            <input type="hidden" name="qty" value="1">
                            <button type="submit" @disabled($stockState === 'habis')
                               class="mitra-add-cart-btn inline-flex items-center justify-center gap-1.5 w-full py-2 rounded-xl text-[11px] sm:text-xs font-bold transition-all
                               {{ $stockState === 'habis' ? 'bg-slate-100 text-slate-400 cursor-not-allowed' : 'bg-emerald-600 text-white hover:bg-emerald-700 hover:shadow-md hover:shadow-emerald-600/20' }}">
                                {{ $stockState === 'habis' ? 'Stok Habis' : '+ Keranjang' }}
                            </button>
                        </form>
                        @else
                        <a href="{{ $waPayload['href'] }}"
                           data-wa-share
                           data-wa-href="{{ $waPayload['href'] }}"
                           data-wa-text="{{ e($waPayload['message']) }}"
                           data-wa-image="{{ $waPayload['image_url'] }}"
                           data-wa-detail="{{ $waPayload['detail_url'] }}"
                           data-wa-name="{{ e($waPayload['product_name']) }}"
                           title="Pesan produk via WhatsApp"
                           target="_blank" rel="noopener"
                           class="mt-3 inline-flex items-center justify-center gap-1.5 w-full py-2 rounded-xl text-[11px] sm:text-xs font-bold transition-all
                           {{ $stockState === 'habis' ? 'bg-slate-100 text-slate-500 hover:bg-slate-200' : 'bg-emerald-50 text-emerald-700 hover:bg-emerald-600 hover:text-white hover:shadow-md hover:shadow-emerald-600/20' }}">
                            <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.149-.15.35-.4.5-.6.15-.2.2-.35.3-.6.1-.24.05-.45-.05-.6-.1-.15-.628-1.517-.859-2.076-.229-.559-.462-.483-.639-.492-.15-.007-.35-.007-.55-.007-.198 0-.5.075-.762.375-.283.325-1.14 1.116-1.14 2.716 0 1.6 1.164 3.14 1.32 3.36.16.222 2.084 3.176 5.1 4.325.717.257 1.28.412 1.72.526.72.19 1.38.163 1.9.1.58-.075 1.758-.719 2.006-1.413.25-.694.25-1.29.174-1.415-.077-.124-.297-.198-.594-.347z"/><path d="M12.002 2C6.478 2 2 6.478 2 12c0 1.85.5 3.583 1.373 5.083L2 22l5.084-1.334A9.94 9.94 0 0012.002 22C17.523 22 22 17.522 22 12S17.523 2 12.002 2zm0 18.19a8.17 8.17 0 01-4.166-1.14l-.299-.177-3.02.793.807-2.943-.194-.303A8.17 8.17 0 013.81 12c0-4.517 3.674-8.19 8.192-8.19 4.516 0 8.19 3.673 8.19 8.19 0 4.518-3.674 8.19-8.19 8.19z"/></svg>
                            {{ $stockState === 'habis' ? 'Tanya Ketersediaan' : 'Pesan via WhatsApp' }}
                        </a>
                        @endif
                    </div>
                </article>
                @endforeach
            </div>

            {{-- Pagination rapi --}}
            <div class="mt-8 sm:mt-10">
                <div class="flex flex-col items-center gap-4">
                    <p class="text-xs sm:text-sm text-slate-500">
                        Menampilkan
                        <span class="inline-flex items-center px-2 py-0.5 mx-0.5 rounded-md bg-emerald-50 text-emerald-700 font-bold">
                            {{ $products->firstItem() ?? 0 }}–{{ $products->lastItem() ?? 0 }}
                        </span>
                        dari
                        <span class="font-bold text-slate-700">{{ number_format($products->total(), 0, ',', '.') }}</span>
                        produk
                    </p>

                    @if($products->hasPages())
                    <nav class="inline-flex items-center gap-1 p-1.5 rounded-2xl bg-white border border-slate-200 shadow-sm" aria-label="Pagination">
                        {{-- Previous --}}
                        <button type="button"
                            wire:click.prevent="previousPage"
                            @disabled($products->onFirstPage())
                            class="inline-flex items-center justify-center w-9 h-9 rounded-xl text-slate-500 transition-colors
                                   {{ $products->onFirstPage() ? 'opacity-40 cursor-not-allowed' : 'hover:bg-emerald-50 hover:text-emerald-700' }}">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                        </button>

                        @php
                            $current = $products->currentPage();
                            $last = $products->lastPage();
                            $start = max(1, $current - 2);
                            $end = min($last, $current + 2);
                            if ($end - $start < 4) {
                                $start = max(1, $end - 4);
                                $end = min($last, $start + 4);
                            }
                        @endphp

                        @if($start > 1)
                            <button type="button" wire:click.prevent="gotoPage(1)"
                                class="inline-flex items-center justify-center min-w-[36px] h-9 px-2 rounded-xl text-xs font-bold text-slate-600 hover:bg-emerald-50 hover:text-emerald-700 transition-colors">1</button>
                            @if($start > 2)
                            <span class="px-1 text-slate-300 text-xs font-bold">…</span>
                            @endif
                        @endif

                        @for($page = $start; $page <= $end; $page++)
                            <button type="button" wire:click.prevent="gotoPage({{ $page }})"
                                class="inline-flex items-center justify-center min-w-[36px] h-9 px-2 rounded-xl text-xs font-bold transition-all
                                {{ $page === $current
                                    ? 'bg-emerald-600 text-white shadow-md shadow-emerald-600/25'
                                    : 'text-slate-600 hover:bg-emerald-50 hover:text-emerald-700' }}">
                                {{ $page }}
                            </button>
                        @endfor

                        @if($end < $last)
                            @if($end < $last - 1)
                            <span class="px-1 text-slate-300 text-xs font-bold">…</span>
                            @endif
                            <button type="button" wire:click.prevent="gotoPage({{ $last }})"
                                class="inline-flex items-center justify-center min-w-[36px] h-9 px-2 rounded-xl text-xs font-bold text-slate-600 hover:bg-emerald-50 hover:text-emerald-700 transition-colors">{{ $last }}</button>
                        @endif

                        {{-- Next --}}
                        <button type="button"
                            wire:click.prevent="nextPage"
                            @disabled(!$products->hasMorePages())
                            class="inline-flex items-center justify-center w-9 h-9 rounded-xl text-slate-500 transition-colors
                                   {{ !$products->hasMorePages() ? 'opacity-40 cursor-not-allowed' : 'hover:bg-emerald-50 hover:text-emerald-700' }}">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                        </button>
                    </nav>
                    @endif
                </div>
            </div>

            @else
            {{-- Empty state --}}
            <div class="flex flex-col items-center justify-center gap-3 py-20 text-slate-400">
                <div class="w-20 h-20 rounded-3xl bg-white border border-slate-100 shadow-sm flex items-center justify-center">
                    <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                </div>
                <p class="text-sm font-semibold text-slate-500">Produk tidak ditemukan</p>
                <p class="text-xs text-slate-400 max-w-xs text-center">Coba kata kunci lain atau pilih kategori berbeda.</p>
                @if($search || $categoryFilter)
                <button wire:click="resetFilters" class="mt-1 px-4 py-2 bg-emerald-500 hover:bg-emerald-600 text-white text-xs font-bold rounded-xl transition-colors">
                    Reset Filter
                </button>
                @endif
            </div>
            @endif
        </div>
    </section>
</div>
