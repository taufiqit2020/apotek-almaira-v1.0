<div>
    {{-- ── Toolbar: Search + Filter ─────────────────────────────────── --}}
    <div class="card p-3.5 mb-4 border border-slate-100 shadow-sm rounded-2xl">
        <div class="flex flex-col md:flex-row gap-2.5">
            {{-- Search --}}
            <div class="relative flex-1">
                <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                <input wire:model.live.debounce.400ms="search"
                       type="text"
                       placeholder="Cari nama, kode, barcode, atau kegunaan..."
                       class="input pl-9 w-full rounded-xl">
                <div wire:loading wire:target="search" class="absolute right-3 top-1/2 -translate-y-1/2">
                    <svg class="w-4 h-4 text-emerald-500 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
                </div>
            </div>

            <select wire:model.live="categoryFilter" class="input w-full md:w-48 rounded-xl">
                <option value="">Semua Kategori</option>
                @foreach($categories as $cat)
                <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                @endforeach
            </select>

            <select wire:model.live="statusFilter" class="input w-full md:w-44 rounded-xl">
                <option value="all">Semua Status</option>
                <option value="active">Aktif</option>
                <option value="inactive">Nonaktif</option>
                <option value="low_stock">Stok Kritis</option>
                <option value="exceed_het">Melebihi HET</option>
            </select>

            @if($search || $categoryFilter || $statusFilter !== 'active')
            <button wire:click="clearFilters" class="btn btn-secondary flex items-center justify-center gap-1.5 whitespace-nowrap rounded-xl">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                Reset
            </button>
            @endif
        </div>

        @if($search || $categoryFilter || $statusFilter === 'exceed_het' || $statusFilter === 'low_stock')
        <div class="mt-2.5 flex flex-wrap gap-2">
            @if($search)
            <span class="inline-flex items-center gap-1 px-2.5 py-1 bg-emerald-50 text-emerald-700 text-xs rounded-full font-medium border border-emerald-100">
                "{{ $search }}"
                <button wire:click="$set('search', '')" class="ml-0.5 hover:text-red-500">&times;</button>
            </span>
            @endif
            @if($categoryFilter)
            <span class="inline-flex items-center gap-1 px-2.5 py-1 bg-sky-50 text-sky-700 text-xs rounded-full font-medium border border-sky-100">
                {{ $categories->find($categoryFilter)?->name }}
                <button wire:click="$set('categoryFilter', '')" class="ml-0.5 hover:text-red-500">&times;</button>
            </span>
            @endif
            @if($statusFilter === 'exceed_het')
            <span class="inline-flex items-center gap-1 px-2.5 py-1 bg-rose-50 text-rose-800 text-xs rounded-full font-bold border border-rose-200">
                Melebihi HET
                <button wire:click="$set('statusFilter', 'active')" class="ml-0.5 hover:text-red-500">&times;</button>
            </span>
            @endif
            @if($statusFilter === 'low_stock')
            <span class="inline-flex items-center gap-1 px-2.5 py-1 bg-amber-50 text-amber-800 text-xs rounded-full font-bold border border-amber-200">
                Stok Kritis
                <button wire:click="$set('statusFilter', 'active')" class="ml-0.5 hover:text-red-500">&times;</button>
            </span>
            @endif
        </div>
        @endif
    </div>

    {{-- Ringkasan status (kompak, satu baris) --}}
    <div class="mb-4 grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-2.5">
        <a href="{{ route('catalog.index') }}" target="_blank"
           class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl bg-emerald-50/90 border border-emerald-200/80 text-emerald-800 hover:bg-emerald-50 transition-colors">
            <span class="w-8 h-8 rounded-lg bg-emerald-100 flex items-center justify-center shrink-0">
                <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
            </span>
            <div class="min-w-0 flex-1">
                <p class="text-[10px] font-bold uppercase tracking-wider text-emerald-600/80">E-Catalog</p>
                <p class="text-sm font-extrabold tabular-nums leading-tight">{{ $catalogCount }} <span class="font-semibold text-emerald-700/80">aktif</span></p>
            </div>
            <span class="text-[10px] font-bold text-emerald-600 whitespace-nowrap">Lihat →</span>
        </a>

        @if($lowStockCount > 0)
        <button type="button" wire:click="$set('statusFilter', 'low_stock')"
                class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-left bg-amber-50/90 border border-amber-200/80 text-amber-900 hover:bg-amber-50 transition-colors {{ $statusFilter === 'low_stock' ? 'ring-2 ring-amber-300' : '' }}">
            <span class="w-8 h-8 rounded-lg bg-amber-100 flex items-center justify-center shrink-0">
                <svg class="w-4 h-4 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
            </span>
            <div class="min-w-0 flex-1">
                <p class="text-[10px] font-bold uppercase tracking-wider text-amber-600/80">Stok kritis</p>
                <p class="text-sm font-extrabold tabular-nums leading-tight">{{ $lowStockCount }} <span class="font-semibold text-amber-800/80">produk</span></p>
            </div>
            <span class="text-[10px] font-bold text-amber-700 whitespace-nowrap">Filter</span>
        </button>
        @endif

        @if($exceedHetCount > 0)
        <button type="button" wire:click="$set('statusFilter', 'exceed_het')"
                class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-left bg-rose-50/90 border border-rose-200/80 text-rose-900 hover:bg-rose-50 transition-colors {{ $statusFilter === 'exceed_het' ? 'ring-2 ring-rose-300' : '' }}">
            <span class="w-8 h-8 rounded-lg bg-rose-100 flex items-center justify-center shrink-0">
                <svg class="w-4 h-4 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/></svg>
            </span>
            <div class="min-w-0 flex-1">
                <p class="text-[10px] font-bold uppercase tracking-wider text-rose-600/80">Melebihi HET</p>
                <p class="text-sm font-extrabold tabular-nums leading-tight">{{ $exceedHetCount }} <span class="font-semibold text-rose-800/80">produk</span></p>
            </div>
            <span class="text-[10px] font-bold text-rose-700 whitespace-nowrap">Filter</span>
        </button>
        @endif
    </div>

    @if($statusFilter === 'exceed_het')
    <div class="mb-4 flex flex-wrap items-center gap-3 p-3.5 bg-gradient-to-r from-rose-50 via-white to-amber-50 border border-rose-200 rounded-xl text-sm text-rose-800 shadow-sm">
        <div class="flex-1 min-w-0">
            <p class="font-bold text-rose-900">Filter aktif: Melebihi HET</p>
            <p class="text-xs text-rose-700/80 mt-0.5">Centang produk lalu klik <strong>Perbaiki HET</strong> — jual otomatis ke grosir/HET.</p>
        </div>
        <button type="button" wire:click="fixSelectedAgainstHet"
                wire:confirm="Perbaiki harga jual produk terpilih yang melebihi HET?"
                class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl text-xs font-extrabold bg-rose-600 hover:bg-rose-700 text-white shadow-sm transition-colors whitespace-nowrap">
            Perbaiki HET terpilih
        </button>
    </div>
    @endif

    {{-- ── Panel penyesuaian harga massal ───────────────────────────── --}}
    <div class="mb-4 rounded-2xl border border-emerald-200/90 bg-white shadow-sm overflow-hidden">
        <div class="px-4 sm:px-5 py-3.5 bg-gradient-to-r from-emerald-50 via-white to-teal-50/80 border-b border-emerald-100/80 flex flex-col lg:flex-row lg:items-center gap-3">
            <div class="flex items-start gap-3 flex-1 min-w-0">
                <span class="w-10 h-10 rounded-xl bg-emerald-600 text-white flex items-center justify-center shrink-0 shadow-md shadow-emerald-600/25">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </span>
                <div class="min-w-0">
                    <p class="text-sm font-extrabold text-emerald-950 tracking-tight">Sesuaikan Harga Massal</p>
                    <p class="text-xs text-emerald-800/65 mt-0.5 leading-relaxed">
                        Naikkan/turunkan <strong>harga jual &amp; grosir</strong> sekaligus. Harga beli &amp; HET tetap.
                        Jika jual melebihi HET, otomatis diturunkan ke harga grosir, lalu ke HET jika masih melebihi.
                    </p>
                </div>
            </div>
            <div class="flex flex-wrap items-center gap-2 lg:justify-end">
                <div class="flex items-center gap-1.5 bg-white rounded-xl border border-emerald-200 px-2 py-1.5 shadow-sm">
                    <span class="text-[10px] font-bold uppercase tracking-wider text-emerald-600 pl-1">%</span>
                    <input type="number" step="0.1" min="-90" max="500"
                           wire:model="bulkPricePercent"
                           class="w-16 text-sm font-extrabold tabular-nums text-center text-emerald-900 bg-transparent focus:outline-none"
                           placeholder="10">
                </div>
                @foreach([5, 10, 15, 20] as $preset)
                <button type="button" wire:click="$set('bulkPricePercent', '{{ $preset }}')"
                        class="px-2.5 py-2 rounded-xl text-[11px] font-extrabold border transition-all {{ (string) $bulkPricePercent === (string) $preset ? 'bg-emerald-600 text-white border-emerald-600 shadow-md shadow-emerald-600/25' : 'border-emerald-200 bg-white text-emerald-700 hover:bg-emerald-50 hover:border-emerald-300' }}">
                    +{{ $preset }}%
                </button>
                @endforeach
                <button type="button" wire:click="selectAllFiltered"
                        class="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl text-xs font-bold border border-slate-200 bg-slate-50 text-slate-700 hover:bg-slate-100 hover:border-slate-300 transition-colors whitespace-nowrap">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    Pilih semua filter
                </button>
            </div>
        </div>
    </div>

    {{-- ── Bulk Action Bar (cantik saat ada centang) ─────────────────── --}}
    @if(count($selected) > 0)
    <div class="mb-4 sticky top-2 z-20 animate-in" wire:key="bulk-bar-{{ count($selected) }}">
        <div class="relative overflow-hidden rounded-2xl border border-emerald-400/30 bg-gradient-to-r from-slate-900 via-slate-900 to-emerald-950 shadow-xl shadow-emerald-900/20">
            <div class="absolute inset-y-0 left-0 w-1.5 bg-gradient-to-b from-emerald-400 to-teal-500"></div>
            <div class="absolute -right-8 -top-8 w-32 h-32 rounded-full bg-emerald-500/10 blur-2xl pointer-events-none"></div>
            <div class="relative flex flex-wrap items-center gap-3 p-3.5 sm:p-4 pl-5">
                <div class="flex items-center gap-2.5 min-w-0">
                    <span class="inline-flex items-center justify-center min-w-8 h-8 px-2 rounded-full bg-emerald-500 text-white text-xs font-black shadow-lg shadow-emerald-500/30">{{ count($selected) }}</span>
                    <div class="min-w-0">
                        <p class="text-sm font-bold text-white leading-tight">produk dipilih</p>
                        <p class="text-[10px] text-slate-400 font-medium">Siap untuk aksi massal</p>
                    </div>
                </div>

                <div class="flex flex-wrap items-center gap-2 sm:pl-3 sm:border-l sm:border-white/10">
                    <div class="flex items-center gap-1.5 bg-white/5 rounded-xl px-2.5 py-1.5 border border-white/10 backdrop-blur-sm">
                        <span class="text-[10px] font-bold uppercase tracking-wider text-amber-300/90">Harga</span>
                        <input type="number" step="0.1" min="-90" max="500"
                               wire:model="bulkPricePercent"
                               class="w-14 bg-transparent text-white text-sm font-extrabold tabular-nums text-center focus:outline-none"
                               title="Persentase (+ naik / − turun)">
                        <span class="text-slate-400 text-xs font-bold">%</span>
                    </div>
                    <button type="button"
                            wire:click="bulkAdjustPrices"
                            wire:confirm="Sesuaikan harga jual & grosir {{ count($selected) }} produk sebesar {{ $bulkPricePercent }}%? Harga beli dan HET tidak berubah. Jika melebihi HET, jual otomatis ke grosir/HET."
                            class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl text-xs font-extrabold bg-amber-400 hover:bg-amber-300 text-slate-900 shadow-lg shadow-amber-500/20 transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        Sesuaikan Harga
                    </button>
                    <button type="button"
                            wire:click="fixSelectedAgainstHet"
                            wire:confirm="Perbaiki harga jual yang melebihi HET pada produk terpilih?"
                            class="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl text-xs font-extrabold bg-rose-500/90 hover:bg-rose-400 text-white border border-rose-400/40 transition-colors"
                            title="Turunkan jual ke grosir/HET">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                        Perbaiki HET
                    </button>
                </div>

                <div class="flex flex-wrap items-center gap-2 ml-auto">
                    <button wire:click="bulkShowCatalog" class="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl text-xs font-bold bg-emerald-500/90 hover:bg-emerald-400 text-white border border-emerald-400/40 transition-colors">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                        Ke Katalog
                    </button>
                    <button wire:click="bulkHideCatalog" class="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl text-xs font-bold bg-white/5 hover:bg-white/10 text-slate-200 border border-white/10 transition-colors">
                        Sembunyikan
                    </button>
                    <button wire:click="clearSelection" class="inline-flex items-center gap-1 px-2.5 py-2 rounded-xl text-xs font-semibold text-slate-400 hover:text-white hover:bg-white/5 transition-colors">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        Batal
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- ── Tabel Produk ────────────────────────────────────────────── --}}
    <div class="card overflow-hidden relative {{ count($selected) > 0 ? 'ring-1 ring-emerald-200/80 shadow-md shadow-emerald-900/5' : '' }}">
        <div wire:loading.delay.longer wire:target="search,categoryFilter,statusFilter,toggleSelectPage,toggleSelected,toggleCatalog,bulkShowCatalog,bulkHideCatalog,bulkAdjustPrices,fixSelectedAgainstHet,selectAllFiltered,clearFilters,clearSelection"
             class="absolute inset-0 z-10 flex items-center justify-center bg-white/40 pointer-events-none">
            <svg class="w-6 h-6 text-emerald-500 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
        </div>
        <div class="overflow-x-auto">
                <table class="data-table w-full">
                    <thead>
                        <tr class="bg-gradient-to-r from-slate-50 to-gray-50">
                            <th class="w-10 text-center">
                                <button type="button"
                                        wire:click="toggleSelectPage"
                                        title="Pilih semua di halaman ini"
                                        class="inline-flex items-center justify-center w-5 h-5 rounded-md transition-all {{ $isAllOnPageSelected ? 'bg-emerald-500 text-white shadow-sm shadow-emerald-500/30' : 'bg-white border border-slate-300 text-transparent hover:border-emerald-400 hover:bg-emerald-50' }}">
                                    <svg class="w-3 h-3 {{ $isAllOnPageSelected ? 'opacity-100' : 'opacity-0' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                                </button>
                            </th>
                            <th class="w-12 text-center">No</th>
                            <th class="min-w-[240px]">Produk</th>
                            <th>Kategori</th>
                            <th class="text-center">Stok</th>
                            <th class="text-right">Beli</th>
                            <th class="text-right">Jual</th>
                            <th class="text-right">Grosir</th>
                            <th class="text-center">Exp</th>
                            <th class="text-center">Status</th>
                            <th class="text-center">Katalog</th>
                            <th class="text-center w-28">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($products as $product)
                        @php
                            $isSelected = in_array($product->id, $selected);
                            $meta = $product->catalogMeta();
                            $kegunaan = $meta['indikasi'] ?? null;
                            $bentuk = $meta['bentuk_sediaan'] ?? null;
                        @endphp
                        <tr wire:key="product-row-{{ $product->id }}"
                            class="group transition-colors duration-150 {{ $isSelected ? 'bg-emerald-50/90 ring-1 ring-inset ring-emerald-200/70 shadow-[inset_3px_0_0_0_#10b981]' : 'hover:bg-slate-50/80' }}">
                            <td class="text-center">
                                <button type="button"
                                        wire:click="toggleSelected({{ $product->id }})"
                                        title="{{ $isSelected ? 'Batalkan pilih' : 'Pilih produk' }}"
                                        class="inline-flex items-center justify-center w-5 h-5 rounded-md transition-all {{ $isSelected ? 'bg-emerald-500 text-white shadow-sm shadow-emerald-500/30 scale-105' : 'bg-white border border-slate-300 text-transparent hover:border-emerald-400 hover:bg-emerald-50 group-hover:border-emerald-300' }}">
                                    <svg class="w-3 h-3 {{ $isSelected ? 'opacity-100' : 'opacity-0' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                                </button>
                            </td>
                            <td class="text-center text-gray-400 text-xs font-semibold tabular-nums">{{ $products->firstItem() + $loop->index }}</td>

                            {{-- Produk + kegunaan (rapi di bawah nama) --}}
                            <td class="py-3">
                                <div class="flex items-start gap-3 min-w-0">
                                    <div class="w-10 h-10 rounded-xl overflow-hidden shrink-0 border border-slate-100 shadow-sm bg-white flex items-center justify-center mt-0.5">
                                        <img src="{{ $product->image_url }}" class="w-full h-full {{ $product->has_image ? 'object-cover' : 'object-contain p-1' }}" alt="{{ $product->name }}">
                                    </div>
                                    <div class="min-w-0 flex-1">
                                        <p class="font-semibold text-sm leading-snug transition-colors {{ $isSelected ? 'text-emerald-800' : 'text-slate-800 group-hover:text-emerald-700' }}">{{ $product->name }}</p>
                                        <div class="mt-1 flex flex-wrap items-center gap-1.5">
                                            <span class="inline-flex items-center px-1.5 py-0.5 rounded-md text-[10px] font-mono font-bold bg-slate-100 text-slate-500 border border-slate-200/80">{{ $product->code }}</span>
                                            @if($bentuk)
                                            <span class="inline-flex items-center px-1.5 py-0.5 rounded-md text-[10px] font-semibold bg-violet-50 text-violet-700 border border-violet-100">{{ $bentuk }}</span>
                                            @endif
                                        </div>
                                        @if($kegunaan)
                                        <div class="mt-1.5 max-w-md" title="{{ $kegunaan }}">
                                            <p class="text-[11px] leading-relaxed text-slate-500 line-clamp-2">
                                                <span class="font-bold text-slate-400 uppercase tracking-wide text-[9px] mr-1">Kegunaan</span>
                                                {{ $kegunaan }}
                                            </p>
                                        </div>
                                        @endif
                                    </div>
                                </div>
                            </td>

                            {{-- Kategori pill --}}
                            <td>
                                @if($product->category)
                                <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-[11px] font-semibold bg-sky-50 text-sky-700 border border-sky-100 whitespace-nowrap">
                                    {{ $product->category->name }}
                                </span>
                                @else
                                <span class="text-gray-300 text-xs">—</span>
                                @endif
                            </td>

                            {{-- Stok badge --}}
                            <td class="text-center">
                                @if($product->stock <= 0)
                                <span class="inline-flex flex-col items-center px-2.5 py-1 rounded-lg bg-red-50 border border-red-200">
                                    <span class="font-extrabold text-sm text-red-600">{{ $product->stock }}</span>
                                    <span class="text-[9px] font-bold text-red-500 uppercase tracking-wide">Habis</span>
                                </span>
                                @elseif($product->stock <= $product->stock_min)
                                <span class="inline-flex flex-col items-center px-2.5 py-1 rounded-lg bg-amber-50 border border-amber-200">
                                    <span class="font-extrabold text-sm text-amber-600">{{ $product->stock }} <span class="text-[10px] font-medium text-amber-400">/{{ $product->unit?->name ?? 'pcs' }}</span></span>
                                    <span class="text-[9px] font-bold text-amber-500 uppercase tracking-wide">⚠ Kritis</span>
                                </span>
                                @else
                                <span class="font-bold text-sm text-gray-800">{{ $product->stock }}</span>
                                <span class="text-xs text-gray-400">/{{ $product->unit?->name ?? 'pcs' }}</span>
                                @endif
                            </td>

                            <td class="text-right text-sm text-gray-500 tabular-nums whitespace-nowrap">Rp {{ number_format($product->purchase_price, 0, ',', '.') }}</td>
                            <td class="text-right whitespace-nowrap">
                                @php $exceedsHet = $product->exceedsHet(); @endphp
                                <div class="inline-flex flex-col items-end gap-1">
                                    <span class="font-bold text-sm tabular-nums {{ $exceedsHet ? 'text-rose-700' : 'text-emerald-700' }}">
                                        Rp {{ number_format($product->sell_price, 0, ',', '.') }}
                                    </span>
                                    @if($exceedsHet)
                                    <span class="inline-flex items-center gap-0.5 px-1.5 py-0.5 rounded-md text-[9px] font-extrabold uppercase tracking-wide bg-rose-50 text-rose-700 border border-rose-200"
                                          title="Harga jual melebihi HET Rp {{ number_format($product->het_price, 0, ',', '.') }}">
                                        <svg class="w-2.5 h-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/></svg>
                                        Melebihi HET
                                    </span>
                                    @endif
                                </div>
                            </td>
                            <td class="text-right font-semibold text-sm text-teal-700 tabular-nums whitespace-nowrap">
                                @if(($product->wholesale_price ?? 0) > 0)
                                Rp {{ number_format($product->wholesale_price, 0, ',', '.') }}
                                @else
                                <span class="text-gray-300 font-normal">—</span>
                                @endif
                            </td>

                            {{-- Kadaluarsa --}}
                            <td class="text-center text-xs">
                                @php $expiryBadge = $product->expiryBadge(); @endphp
                                @if($expiryBadge['has_date'])
                                <span class="inline-flex flex-col items-center gap-0.5 px-2 py-1 rounded-lg font-bold border whitespace-nowrap {{ $expiryBadge['chip'] }}" title="{{ $expiryBadge['note'] }}">
                                    <span>{{ $expiryBadge['date'] }}</span>
                                    @if($expiryBadge['note'] && in_array($expiryBadge['state'], ['expired', 'critical', 'warning'], true))
                                    <span class="text-[9px] font-semibold opacity-80">{{ $expiryBadge['note'] }}</span>
                                    @endif
                                </span>
                                @else
                                    <span class="text-gray-300">—</span>
                                @endif
                            </td>

                            {{-- Status --}}
                            <td class="text-center">
                                @if($product->is_active)
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Aktif
                                </span>
                                @else
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-bold bg-gray-100 text-gray-500 border border-gray-200">
                                    <span class="w-1.5 h-1.5 rounded-full bg-gray-400"></span> Nonaktif
                                </span>
                                @endif
                            </td>

                            {{-- Toggle Katalog --}}
                            <td class="text-center">
                                <button
                                    wire:click="toggleCatalog({{ $product->id }})"
                                    wire:loading.attr="disabled"
                                    wire:target="toggleCatalog({{ $product->id }})"
                                    title="{{ $product->show_in_catalog ? 'Tampil di E-Catalog — klik untuk sembunyikan' : 'Tersembunyi — klik untuk tampilkan di E-Catalog' }}"
                                    class="relative inline-flex h-6 w-11 items-center rounded-full transition-colors duration-200 cursor-pointer focus:outline-none focus:ring-2 focus:ring-emerald-400 focus:ring-offset-1 {{ $product->show_in_catalog ? 'bg-emerald-500' : 'bg-gray-300' }}">
                                    <span class="inline-block h-4.5 w-4.5 transform rounded-full bg-white shadow transition-transform duration-200 {{ $product->show_in_catalog ? 'translate-x-6' : 'translate-x-1' }}"></span>
                                </button>
                            </td>

                            {{-- Aksi --}}
                            <td class="text-center">
                                <div class="flex items-center justify-center gap-1">
                                    <a wire:navigate href="{{ route('products.edit', $product) }}"
                                       class="w-8 h-8 rounded-lg flex items-center justify-center text-gray-400 hover:text-blue-600 hover:bg-blue-50 border border-transparent hover:border-blue-100 transition-all"
                                       title="Edit">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                    </a>
                                    <a wire:navigate href="{{ route('products.show', $product) }}"
                                       class="w-8 h-8 rounded-lg flex items-center justify-center text-gray-400 hover:text-emerald-600 hover:bg-emerald-50 border border-transparent hover:border-emerald-100 transition-all"
                                       title="Detail">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                    </a>
                                    <form id="del-prod-{{ $product->id }}" method="POST" action="{{ route('products.destroy', $product) }}" class="inline">
                                        @csrf
                                        @method('DELETE')
                                    </form>
                                    <button type="button"
                                        @click="confirm('del-prod-{{ $product->id }}', 'Hapus Produk', 'Hapus produk {{ addslashes($product->name) }}? Data akan dihapus dari master produk.')"
                                        class="w-8 h-8 rounded-lg flex items-center justify-center text-gray-400 hover:text-red-600 hover:bg-red-50 border border-transparent hover:border-red-100 transition-all"
                                        title="Hapus">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="12" class="text-center py-16">
                                <div class="flex flex-col items-center gap-3 text-gray-400">
                                    <div class="w-16 h-16 rounded-2xl bg-gray-50 border border-gray-100 flex items-center justify-center">
                                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/></svg>
                                    </div>
                                    <p class="text-sm font-medium">Tidak ada produk ditemukan</p>
                                    @if($search)
                                    <button wire:click="clearFilters" class="text-xs text-emerald-600 font-semibold hover:underline cursor-pointer">Reset pencarian</button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
        </div>

        {{-- Footer: info + pagination --}}
        <div class="px-5 py-4 border-t border-gray-100/80 bg-gradient-to-r from-white via-slate-50/60 to-emerald-50/40">
            <div class="flex flex-col lg:flex-row items-center justify-between gap-4">
                <div class="flex items-center gap-3 text-sm text-gray-500 order-2 lg:order-1">
                    <span class="inline-flex items-center justify-center w-9 h-9 rounded-xl bg-emerald-100/80 text-emerald-600 shrink-0">
                        <svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/></svg>
                    </span>
                    <p class="leading-relaxed">
                        Menampilkan
                        <span class="font-bold text-gray-800">{{ $products->firstItem() ?? 0 }}–{{ $products->lastItem() ?? 0 }}</span>
                        dari
                        <span class="font-bold text-gray-800">{{ $products->total() }}</span>
                        produk
                        @if($products->hasPages())
                        <span class="hidden sm:inline text-gray-300 mx-1">·</span>
                        <span class="hidden sm:inline">Halaman <span class="font-bold text-emerald-700">{{ $products->currentPage() }}</span> dari {{ $products->lastPage() }}</span>
                        @endif
                    </p>
                </div>
                @if($products->hasPages())
                <div class="order-1 lg:order-2 w-full lg:w-auto flex justify-center lg:justify-end">
                    {{ $products->links() }}
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
