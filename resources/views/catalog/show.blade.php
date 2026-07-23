@extends('layouts.catalog')
@section('title', $product->name)

@section('meta')
    <meta property="og:type" content="product">
    <meta property="og:title" content="{{ $product->name }} — {{ $apotekName ?? 'Apotek Almaira' }}">
    <meta property="og:description" content="{{ \Illuminate\Support\Str::limit(strip_tags($product->description ?: $product->name), 140) }}">
    <meta property="og:image" content="{{ $product->image_url }}">
    <meta property="og:url" content="{{ route('catalog.show', $product) }}">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:image" content="{{ $product->image_url }}">
@endsection

@section('content')
@php
    $waRaw = $apotekPhone ?? '0851-6665-7070';
    $stockBadge = $product->stockBadge();
    $stockState = $stockBadge['state'];
    $expiryBadge = $product->expiryBadge();
    $meta = $product->catalogMeta();
    $images = is_array($product->images) ? $product->images : [];
    $displayPrice = \App\Services\PartnerPricingService::displayPrice($product, $partner ?? null);
    $waPayload = \App\Services\CatalogWhatsAppService::buttonPayload(
        $product,
        $waRaw,
        $apotekName ?? 'Apotek Almaira',
        (float) $displayPrice,
        $stockState
    );
@endphp

<div class="max-w-6xl mx-auto px-4 sm:px-6 py-6 sm:py-10" x-data="{ activeImg: 0 }">
    {{-- Breadcrumb --}}
    <nav class="mb-6 flex items-center gap-2 text-xs sm:text-sm text-slate-500">
        <a href="{{ route('catalog.index') }}" class="font-semibold text-emerald-700 hover:text-emerald-800 transition-colors">E-Catalog</a>
        <svg class="w-3.5 h-3.5 text-slate-300" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"/></svg>
        @if($product->category)
        <span class="truncate max-w-[120px] sm:max-w-none">{{ $product->category->name }}</span>
        <svg class="w-3.5 h-3.5 text-slate-300" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"/></svg>
        @endif
        <span class="font-medium text-slate-700 truncate">{{ $product->name }}</span>
    </nav>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 lg:gap-10">
        {{-- Galeri gambar --}}
        <div>
            <div class="relative aspect-square rounded-3xl overflow-hidden bg-gradient-to-br from-emerald-50 via-white to-teal-50 border border-slate-100 shadow-sm">
                @if(count($images))
                    @foreach($images as $i => $img)
                    <img @if($i > 0) x-show="activeImg === {{ $i }}" x-cloak @else x-show="activeImg === 0" @endif
                         src="{{ asset($img) }}" alt="{{ $product->name }}"
                         class="absolute inset-0 w-full h-full object-contain p-6 sm:p-10">
                    @endforeach
                @else
                    <img src="{{ $product->image_url }}" alt="{{ $product->name }}"
                         class="absolute inset-0 w-full h-full object-contain p-8 sm:p-12">
                @endif

                <span class="absolute top-4 right-4 inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-bold shadow-lg border {{ $stockBadge['chip'] }}">
                    <span class="w-2 h-2 rounded-full {{ $stockBadge['dot'] }}"></span>
                    {{ $stockBadge['label'] }}
                </span>
            </div>

            @if(count($images) > 1)
            <div class="mt-3 flex gap-2 overflow-x-auto pb-1">
                @foreach($images as $i => $img)
                <button type="button" @click="activeImg = {{ $i }}"
                    :class="activeImg === {{ $i }} ? 'ring-2 ring-emerald-500 border-emerald-400' : 'border-slate-200 opacity-70 hover:opacity-100'"
                    class="shrink-0 w-16 h-16 sm:w-20 sm:h-20 rounded-xl border-2 overflow-hidden bg-white transition-all">
                    <img src="{{ asset($img) }}" alt="thumb" class="w-full h-full object-cover">
                </button>
                @endforeach
            </div>
            @endif
        </div>

        {{-- Info produk --}}
        @php
            $priceInfo = \App\Services\PartnerPricingService::catalogPriceInfo($product, $partner ?? null);
            $factRows = collect([
                ['label' => 'Bentuk Sediaan', 'value' => $meta['bentuk_sediaan'] ?? null],
                ['label' => 'Rute Pemberian', 'value' => $meta['rute'] ?? null],
                ['label' => 'Golongan', 'value' => $meta['golongan'] ?? null],
                ['label' => 'Pabrik', 'value' => $meta['pabrik'] ?? null],
            ])->filter(fn ($row) => filled($row['value']))->values();
        @endphp
        <div class="flex flex-col">
            @if($product->category)
            <span class="inline-flex self-start px-2.5 py-1 rounded-md bg-emerald-50 text-emerald-700 text-[10px] sm:text-[11px] font-bold uppercase tracking-[0.14em] border border-emerald-100/80">
                {{ $product->category->name }}
            </span>
            @endif

            <h1 class="font-banner mt-3 text-2xl sm:text-3xl lg:text-[2.1rem] font-extrabold text-slate-900 leading-[1.15] tracking-tight">
                {{ $product->name }}
            </h1>

            <p class="mt-2 text-sm text-slate-500 leading-relaxed">
                per <span class="font-semibold text-slate-700">{{ $product->unit?->name ?? 'pcs' }}</span>
                @if($meta['bentuk_sediaan'])
                <span class="mx-1.5 text-slate-300">·</span>
                <span>{{ $meta['bentuk_sediaan'] }}</span>
                @endif
                @if($meta['pabrik'])
                <span class="mx-1.5 text-slate-300">·</span>
                <span>{{ $meta['pabrik'] }}</span>
                @endif
            </p>

            <div class="mt-3.5 flex flex-wrap items-center gap-2">
                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-[11px] font-bold border {{ $stockBadge['chip'] }}">
                    <span class="w-1.5 h-1.5 rounded-full {{ $stockBadge['dot'] }}"></span>
                    {{ $stockBadge['label'] }}
                </span>
                @if($expiryBadge['has_date'])
                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-[11px] font-bold border {{ $expiryBadge['chip'] }}" title="{{ $expiryBadge['note'] }}">
                    <svg class="w-3.5 h-3.5 {{ $expiryBadge['icon'] }}" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                    Exp {{ $expiryBadge['date'] }}
                    @if($expiryBadge['note'])
                    <span class="font-semibold opacity-75">· {{ $expiryBadge['note'] }}</span>
                    @endif
                </span>
                @endif
            </div>

            <div class="mt-5 rounded-2xl border border-emerald-100/80 bg-gradient-to-br from-emerald-50/70 via-white to-teal-50/40 px-4 py-4 sm:px-5 sm:py-5">
                <p class="text-[10px] font-bold uppercase tracking-[0.16em] text-emerald-700/70 mb-2">Harga</p>
                <div class="space-y-2.5">
                    <div class="flex flex-wrap items-end justify-between gap-x-3 gap-y-1">
                        <span class="text-[11px] font-bold text-slate-500 uppercase tracking-wider">{{ $priceInfo['label'] ?? 'Eceran' }}</span>
                        <p class="text-3xl sm:text-4xl font-black text-emerald-600 tracking-tight leading-none tabular-nums">
                            Rp {{ number_format($priceInfo['primary'], 0, ',', '.') }}
                        </p>
                    </div>
                    @if(!empty($priceInfo['secondary']))
                    <div class="flex flex-wrap items-center justify-between gap-x-3 gap-y-1 rounded-xl bg-teal-50 border border-teal-100 px-3 py-2.5">
                        <span class="text-[11px] font-bold text-teal-700 uppercase tracking-wider">{{ $priceInfo['secondary_label'] ?? 'Grosir' }}</span>
                        <p class="text-xl sm:text-2xl font-extrabold text-teal-800 tracking-tight leading-none tabular-nums">
                            Rp {{ number_format($priceInfo['secondary'], 0, ',', '.') }}
                        </p>
                    </div>
                    @endif
                </div>
                @if($priceInfo['note'])
                <p class="mt-2 text-xs text-slate-500">{{ $priceInfo['note'] }}</p>
                @endif
            </div>

            @if($product->requires_prescription)
            <div class="mt-4 flex items-start gap-3 rounded-2xl border border-amber-200/70 bg-amber-50/80 px-4 py-3.5 text-amber-900">
                <div class="mt-0.5 w-8 h-8 rounded-xl bg-amber-100 flex items-center justify-center shrink-0">
                    <svg class="w-4 h-4 text-amber-600" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
                </div>
                <div>
                    <p class="font-bold text-xs uppercase tracking-wider text-amber-800">Memerlukan Resep Dokter</p>
                    <p class="text-xs mt-0.5 text-amber-700/90 leading-relaxed">Produk ini hanya dapat dibeli dengan resep yang valid.</p>
                </div>
            </div>
            @endif

            <div class="mt-5 rounded-2xl border border-slate-200/80 bg-white shadow-[0_1px_2px_rgba(15,23,42,0.04)] overflow-hidden">
                <div class="px-4 sm:px-5 py-3 border-b border-slate-100 bg-slate-50/80">
                    <p class="text-[11px] font-bold uppercase tracking-[0.14em] text-slate-500">Informasi Produk</p>
                </div>

                @if($factRows->isNotEmpty())
                <div class="grid grid-cols-1 sm:grid-cols-2">
                    @foreach($factRows as $index => $row)
                    <div class="px-4 sm:px-5 py-3.5 {{ $index % 2 === 0 ? 'sm:border-r' : '' }} border-b border-slate-100">
                        <p class="text-[10px] font-bold uppercase tracking-[0.14em] text-slate-400 mb-1">{{ $row['label'] }}</p>
                        <p class="text-sm font-semibold text-slate-800 leading-snug">{{ $row['value'] }}</p>
                    </div>
                    @endforeach
                </div>
                @endif

                @if($meta['kandungan'])
                <div class="px-4 sm:px-5 py-3.5 border-b border-slate-100">
                    <p class="text-[10px] font-bold uppercase tracking-[0.14em] text-slate-400 mb-1">Kandungan</p>
                    <p class="text-sm text-slate-700 leading-relaxed">{{ $meta['kandungan'] }}</p>
                </div>
                @endif

                @if($meta['indikasi'])
                <div class="px-4 sm:px-5 py-3.5 border-b border-slate-100">
                    <p class="text-[10px] font-bold uppercase tracking-[0.14em] text-slate-400 mb-1">Indikasi</p>
                    <p class="text-sm text-slate-700 leading-relaxed">{{ $meta['indikasi'] }}</p>
                </div>
                @endif

                <div class="grid grid-cols-1 sm:grid-cols-3 divide-y sm:divide-y-0 sm:divide-x divide-slate-100">
                    <div class="px-4 sm:px-5 py-3.5">
                        <p class="text-[10px] font-bold uppercase tracking-[0.14em] text-slate-400 mb-1">Kode</p>
                        <p class="text-sm font-bold text-slate-800 font-mono tracking-wide">{{ $product->code ?: '—' }}</p>
                    </div>
                    <div class="px-4 sm:px-5 py-3.5">
                        <p class="text-[10px] font-bold uppercase tracking-[0.14em] text-slate-400 mb-1">Stok</p>
                        <p class="inline-flex items-center gap-1.5 text-sm font-bold {{ $stockState === 'habis' ? 'text-red-600' : ($stockState === 'terbatas' ? 'text-amber-600' : 'text-emerald-600') }}">
                            <span class="w-1.5 h-1.5 rounded-full {{ $stockBadge['dot'] }}"></span>
                            {{ $stockBadge['label'] }}
                        </p>
                    </div>
                    <div class="px-4 sm:px-5 py-3.5">
                        <p class="text-[10px] font-bold uppercase tracking-[0.14em] text-slate-400 mb-1">Expired</p>
                        <p class="text-sm font-bold {{ $expiryBadge['text'] }}">{{ $expiryBadge['label'] }}</p>
                        @if($expiryBadge['note'])
                        <p class="mt-0.5 text-[11px] font-medium {{ $expiryBadge['text'] }} opacity-75">{{ $expiryBadge['note'] }}</p>
                        @endif
                    </div>
                </div>
            </div>

            <div class="mt-6 flex flex-col sm:flex-row gap-2.5">
                @if(!empty($partner))
                <form action="{{ route('mitra.cart.add', $product) }}" method="POST" class="flex-1 flex gap-2">
                    @csrf
                    <input type="number" name="qty" value="1" min="1" max="9999"
                           class="w-20 sm:w-24 rounded-2xl border border-slate-200 bg-white px-3 py-3.5 text-sm font-bold text-center focus:outline-none focus:ring-2 focus:ring-emerald-500/30 focus:border-emerald-400">
                    <button type="submit" @disabled($stockState === 'habis')
                       class="flex-1 inline-flex items-center justify-center gap-2 px-5 py-3.5 rounded-2xl text-sm font-bold text-white shadow-lg transition-all
                       {{ $stockState === 'habis' ? 'bg-slate-400 cursor-not-allowed shadow-none' : 'bg-emerald-600 hover:bg-emerald-700 shadow-emerald-600/20 hover:-translate-y-0.5' }}">
                        {{ $stockState === 'habis' ? 'Stok Habis' : 'Tambah ke Keranjang' }}
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
                   class="flex-1 inline-flex items-center justify-center gap-2.5 px-5 py-3.5 rounded-2xl text-sm font-bold text-white shadow-lg shadow-emerald-600/20 transition-all
                   {{ $stockState === 'habis' ? 'bg-slate-500 hover:bg-slate-600 shadow-slate-500/20' : 'bg-emerald-600 hover:bg-emerald-700 hover:-translate-y-0.5' }}">
                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.149-.15.35-.4.5-.6.15-.2.2-.35.3-.6.1-.24.05-.45-.05-.6-.1-.15-.628-1.517-.859-2.076-.229-.559-.462-.483-.639-.492-.15-.007-.35-.007-.55-.007-.198 0-.5.075-.762.375-.283.325-1.14 1.116-1.14 2.716 0 1.6 1.164 3.14 1.32 3.36.16.222 2.084 3.176 5.1 4.325.717.257 1.28.412 1.72.526.72.19 1.38.163 1.9.1.58-.075 1.758-.719 2.006-1.413.25-.694.25-1.29.174-1.415-.077-.124-.297-.198-.594-.347z"/><path d="M12.002 2C6.478 2 2 6.478 2 12c0 1.85.5 3.583 1.373 5.083L2 22l5.084-1.334A9.94 9.94 0 0012.002 22C17.523 22 22 17.522 22 12S17.523 2 12.002 2zm0 18.19a8.17 8.17 0 01-4.166-1.14l-.299-.177-3.02.793.807-2.943-.194-.303A8.17 8.17 0 013.81 12c0-4.517 3.674-8.19 8.192-8.19 4.516 0 8.19 3.673 8.19 8.19 0 4.518-3.674 8.19-8.19 8.19z"/></svg>
                    {{ $stockState === 'habis' ? 'Tanya Ketersediaan via WhatsApp' : 'Pesan via WhatsApp' }}
                </a>
                @endif
                <a href="{{ route('catalog.index') }}"
                   class="inline-flex items-center justify-center gap-2 px-5 py-3.5 rounded-2xl text-sm font-bold text-slate-600 bg-white border border-slate-200 hover:bg-slate-50 hover:border-slate-300 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                    Kembali
                </a>
            </div>

            <p class="mt-3.5 text-[11px] text-slate-400 leading-relaxed">
                @if(!empty($partner))
                Harga sesuai mode mitra Anda. Ajukan PO melalui keranjang setelah memilih produk.
                @else
                Harga dapat berubah sewaktu-waktu. Konfirmasi ketersediaan &amp; harga terbaru melalui WhatsApp Apotek Almaira.
                @endif
            </p>
        </div>
    </div>

    {{-- Produk terkait --}}
    @if($related->count() > 0)
    <section class="mt-14 sm:mt-16">
        <div class="flex items-end justify-between mb-5">
            <div>
                <h2 class="font-banner text-lg sm:text-xl font-extrabold text-slate-800">Produk Serupa</h2>
                <p class="text-xs sm:text-sm text-slate-500 mt-0.5">Kategori {{ $product->category?->name ?? 'terkait' }}</p>
            </div>
            <a href="{{ route('catalog.index') }}{{ $product->category_id ? '?cat='.$product->category_id : '' }}" class="text-xs sm:text-sm font-bold text-emerald-600 hover:text-emerald-700">Lihat semua →</a>
        </div>
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 sm:gap-4">
            @foreach($related as $item)
            @php
                $itemBadge = $item->stockBadge();
                $itemMeta = $item->catalogMeta();
            @endphp
            <a href="{{ route('catalog.show', $item) }}" class="group bg-white rounded-2xl border border-slate-100 shadow-sm hover:shadow-md hover:-translate-y-0.5 transition-all overflow-hidden">
                <div class="relative aspect-square bg-gradient-to-br from-emerald-50 to-teal-50">
                    <img src="{{ $item->image_url }}" alt="{{ $item->name }}" loading="lazy"
                         class="w-full h-full group-hover:scale-105 transition-transform duration-300 {{ $item->has_image ? 'object-cover' : 'object-contain p-4' }}">
                    <span class="absolute top-2 right-2 inline-flex items-center gap-1 px-1.5 py-0.5 rounded text-[9px] font-bold border {{ $itemBadge['chip'] }}">
                        <span class="w-1.5 h-1.5 rounded-full {{ $itemBadge['dot'] }}"></span>
                        {{ $itemBadge['short'] }}
                    </span>
                </div>
                <div class="p-3">
                    <h3 class="text-xs sm:text-sm font-bold text-slate-800 line-clamp-2 leading-snug group-hover:text-emerald-700 transition-colors">{{ $item->name }}</h3>
                    @if($itemMeta['bentuk_sediaan'] || $itemMeta['pabrik'])
                    <p class="mt-1 text-[10px] text-slate-400 truncate">
                        {{ collect([$itemMeta['bentuk_sediaan'], $itemMeta['pabrik']])->filter()->implode(' · ') }}
                    </p>
                    @endif
                    <p class="mt-1.5 text-sm font-extrabold text-emerald-600">Rp {{ number_format(\App\Services\PartnerPricingService::displayPrice($item, $partner ?? null), 0, ',', '.') }}</p>
                </div>
            </a>
            @endforeach
        </div>
    </section>
    @endif
</div>
@endsection
