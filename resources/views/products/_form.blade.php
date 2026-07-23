@php
    $product = $product ?? null;
    $isEdit = (bool) $product;
    $formId = 'product-form';
    $action = $isEdit ? route('products.update', $product) : route('products.store');
    $listQuery = $listQuery ?? array_filter([
        'q' => request('q', request('return_q')),
        'cat' => request('cat', request('return_cat')),
        'status' => request('status', request('return_status')),
        'page' => request('page', request('return_page')),
    ], fn ($v) => $v !== null && $v !== '');
    $indexUrl = route('products.index', $listQuery);
@endphp

<script>
    window.productPriceStock = function() {
        return {
            purchasePrice: {{ old('purchase_price', round(optional($product)->purchase_price ?? 0)) }},
            sellPrice: {{ old('sell_price', round(optional($product)->sell_price ?? 0)) }},
            wholesalePrice: {{ old('wholesale_price', round(optional($product)->wholesale_price ?? 0)) }},
            hetMarkup: {{ old('het_markup', optional($product)->het_markup ?? 0) }},
            hetPrice: {{ old('het_price', round(optional($product)->het_price ?? 0)) }},
            hetAutoNote: false,

            init() {
                this.$watch('purchasePrice', () => this.updateHetPrice());
                this.$watch('hetMarkup', () => this.updateHetPrice());
                this.clampSellAgainstHet();
            },

            updateHetPrice() {
                if (this.hetMarkup > 0) {
                    this.sellPrice = Math.round(this.purchasePrice * (1 + this.hetMarkup / 100));
                    this.wholesalePrice = Math.round(this.sellPrice * (1 - 1 / 100));
                }
                this.clampSellAgainstHet();
            },

            get isExceedingHet() {
                return this.hetPrice > 0 && this.sellPrice > this.hetPrice;
            },

            /** Jika jual > HET → ke grosir dulu; jika masih melebihi → ke HET. */
            clampSellAgainstHet() {
                if (this.hetPrice > 0 && this.sellPrice > this.hetPrice) {
                    if (this.wholesalePrice > 0) {
                        this.sellPrice = this.wholesalePrice;
                    }
                    if (this.sellPrice > this.hetPrice) {
                        this.sellPrice = this.hetPrice;
                    }
                    this.hetAutoNote = true;
                }
                if (this.sellPrice > 0 && this.wholesalePrice > this.sellPrice) {
                    this.wholesalePrice = this.sellPrice;
                }
            },

            adjustPercent: 10,

            applyPricePercent() {
                const p = Number(this.adjustPercent);
                if (!p || p < -90 || p > 500) return;
                const factor = 1 + (p / 100);
                if (this.sellPrice > 0) {
                    this.sellPrice = Math.round(this.sellPrice * factor);
                }
                if (this.wholesalePrice > 0) {
                    this.wholesalePrice = Math.round(this.wholesalePrice * factor);
                }
                this.clampSellAgainstHet();
            },

            formatRupiah(val) {
                if (!val && val !== 0) return '';
                return new Intl.NumberFormat('id-ID').format(val);
            },

            parseRupiah(val) {
                const clean = val.toString().replace(/\D/g, '');
                return clean ? parseInt(clean) : 0;
            }
        }
    }

    window.productImageUpload = function() {
        const initialSlots = Array.from({ length: 6 }, () => ({
            preview: null,
            isExisting: false,
            path: null
        }));

        @if($isEdit && $product->images)
            const dbImages = @json($product->images);
            dbImages.forEach((img, idx) => {
                if (idx < 6) {
                    initialSlots[idx] = {
                        preview: '/' + img,
                        isExisting: true,
                        path: img
                    };
                }
            });
        @endif

        return {
            slots: initialSlots,

            triggerInput(i) {
                if (this.slots[i].preview) return;
                document.getElementById('img-file-' + i).click();
            },

            previewImage(e, i) {
                const file = e.target.files[0];
                if (!file) return;

                if (file.size > 2 * 1024 * 1024) {
                    alert('Ukuran file maksimal adalah 2MB!');
                    e.target.value = '';
                    return;
                }

                const reader = new FileReader();
                reader.onload = (event) => {
                    this.slots[i] = {
                        preview: event.target.result,
                        isExisting: false,
                        path: null
                    };
                };
                reader.readAsDataURL(file);
            },

            clearSlot(i) {
                const input = document.getElementById('img-file-' + i);
                if (input) input.value = '';

                this.slots[i] = {
                    preview: null,
                    isExisting: false,
                    path: null
                };
            }
        }
    }
</script>

<div class="animate-in max-w-5xl mx-auto pb-40">
    {{-- Hero --}}
    <div class="mb-5 rounded-2xl bg-gradient-to-br from-emerald-700 via-emerald-600 to-teal-600 p-5 sm:p-6 text-white shadow-lg shadow-emerald-900/10 relative overflow-hidden">
        <div class="absolute top-0 right-0 w-56 h-56 bg-white/5 rounded-full -translate-y-1/2 translate-x-1/3 pointer-events-none"></div>
        <div class="absolute bottom-0 left-1/3 w-40 h-40 bg-teal-300/10 rounded-full translate-y-1/2 pointer-events-none"></div>
        <div class="relative flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div class="flex items-start gap-4 min-w-0">
                <div class="w-14 h-14 rounded-2xl bg-white/15 border border-white/20 overflow-hidden flex items-center justify-center shrink-0 shadow-inner">
                    @if($isEdit)
                        <img src="{{ $product->image_url }}" alt="" class="w-full h-full {{ $product->has_image ? 'object-cover' : 'object-contain p-2 bg-white' }}">
                    @else
                        <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    @endif
                </div>
                <div class="min-w-0">
                    <p class="text-[11px] font-bold uppercase tracking-wider text-emerald-100/90">
                        {{ $isEdit ? 'Edit Master Produk' : 'Tambah Master Produk' }}
                    </p>
                    <h2 class="text-xl sm:text-2xl font-extrabold leading-tight mt-0.5 break-words">
                        {{ $isEdit ? ($product->name ?: 'Produk') : 'Produk Baru' }}
                    </h2>
                    <div class="flex flex-wrap items-center gap-2 mt-2">
                        @if($isEdit && $product->code)
                        <span class="inline-flex px-2.5 py-1 rounded-lg text-[11px] font-bold bg-white/15 border border-white/20 font-mono">{{ $product->code }}</span>
                        @endif
                        @if($isEdit && $product->category)
                        <span class="inline-flex px-2.5 py-1 rounded-lg text-[11px] font-bold bg-white/15 border border-white/20">{{ $product->category->name }}</span>
                        @endif
                        @if($isEdit)
                        <span class="inline-flex px-2.5 py-1 rounded-lg text-[11px] font-bold border {{ $product->is_active ? 'bg-emerald-100 text-emerald-800 border-emerald-200' : 'bg-slate-100 text-slate-600 border-slate-200' }}">
                            {{ $product->is_active ? 'Aktif' : 'Nonaktif' }}
                        </span>
                        @endif
                    </div>
                </div>
            </div>
            <div class="flex items-center gap-2 shrink-0">
                @if($isEdit)
                <a href="{{ route('products.show', array_merge(['product' => $product], $listQuery)) }}"
                   class="inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl bg-white/15 border border-white/25 text-white text-sm font-bold hover:bg-white/25 transition-colors whitespace-nowrap">
                    Detail
                </a>
                @endif
                <a href="{{ $indexUrl }}"
                   class="inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl bg-white text-emerald-700 text-sm font-bold shadow-md hover:bg-emerald-50 transition-colors whitespace-nowrap">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                    Daftar Produk
                </a>
            </div>
        </div>
    </div>

    @if($errors->any())
    <div class="mb-5 flex items-start gap-3 p-4 rounded-2xl bg-red-50 border border-red-200 text-red-800 shadow-sm">
        <div class="w-9 h-9 rounded-xl bg-red-100 flex items-center justify-center shrink-0">
            <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        </div>
        <div>
            <p class="text-sm font-bold">Periksa kembali isian form</p>
            <ul class="text-sm list-disc pl-4 mt-1 space-y-0.5">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
        </div>
    </div>
    @endif

    <form id="{{ $formId }}" method="POST" action="{{ $action }}" enctype="multipart/form-data" class="space-y-5">
        @csrf
        @if($isEdit) @method('PUT') @endif
        @foreach($listQuery as $key => $value)
            <input type="hidden" name="return_{{ $key }}" value="{{ $value }}">
        @endforeach

        {{-- Informasi Dasar --}}
        <section class="card overflow-hidden bg-white border border-gray-100 rounded-2xl shadow-sm">
            <div class="px-5 py-3.5 border-b border-gray-100 bg-slate-50/80 flex items-center gap-2.5">
                <div class="w-8 h-8 rounded-lg bg-blue-100 flex items-center justify-center">
                    <svg class="w-4 h-4 text-blue-700" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <div>
                    <h3 class="text-sm font-bold text-gray-800">Informasi Dasar</h3>
                    <p class="text-[11px] text-gray-400">Identitas produk, kandungan, bentuk sediaan, dan indikasi</p>
                </div>
            </div>
            <div class="p-5 sm:p-6 grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="md:col-span-2">
                    <label class="form-label font-bold">Nama Produk / Obat <span class="text-red-500">*</span></label>
                    <input type="text" name="name" value="{{ old('name', $product?->name) }}" class="form-input rounded-xl {{ $errors->has('name') ? 'error' : '' }}" placeholder="Contoh: Paracetamol 500mg" required>
                    @error('name')<p class="form-error">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="form-label font-bold">Kode Produk</label>
                    <input type="text" name="code" value="{{ old('code', $product?->code) }}" class="form-input rounded-xl font-mono {{ $errors->has('code') ? 'error' : '' }}" placeholder="Contoh: OBT-001">
                    @error('code')<p class="form-error">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="form-label font-bold">Barcode</label>
                    <input type="text" name="barcode" value="{{ old('barcode', $product?->barcode) }}" class="form-input rounded-xl font-mono {{ $errors->has('barcode') ? 'error' : '' }}" placeholder="Scan atau ketik barcode">
                    @error('barcode')<p class="form-error">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="form-label font-bold">Kategori</label>
                    <select name="category_id" class="form-input rounded-xl">
                        <option value="">— Pilih Kategori —</option>
                        @foreach($categories as $cat)
                        <option value="{{ $cat->id }}" {{ (string) old('category_id', $product?->category_id) === (string) $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="form-label font-bold">Satuan</label>
                    <select name="unit_id" class="form-input rounded-xl">
                        <option value="">— Pilih Satuan —</option>
                        @foreach($units as $u)
                        <option value="{{ $u->id }}" {{ (string) old('unit_id', $product?->unit_id) === (string) $u->id ? 'selected' : '' }}>{{ $u->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="form-label font-bold">Supplier</label>
                    <select name="supplier_id" class="form-input rounded-xl">
                        <option value="">— Pilih Supplier —</option>
                        @foreach($suppliers as $s)
                        <option value="{{ $s->id }}" {{ (string) old('supplier_id', $product?->supplier_id) === (string) $s->id ? 'selected' : '' }}>{{ $s->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="form-label font-bold">Pabrik / Merk</label>
                    <input type="text" name="manufacturer" value="{{ old('manufacturer', $product?->manufacturer) }}" class="form-input rounded-xl" placeholder="Contoh: Kimia Farma">
                </div>

                <div>
                    <label class="form-label font-bold">Golongan</label>
                    <input type="text" name="drug_class" value="{{ old('drug_class', $product?->drug_class) }}" class="form-input rounded-xl" placeholder="Contoh: Obat Keras / Obat Bebas">
                </div>
                <div>
                    <label class="form-label font-bold">Bentuk Sediaan</label>
                    <input type="text" name="dosage_form" value="{{ old('dosage_form', $product?->dosage_form) }}" class="form-input rounded-xl" placeholder="Contoh: Tablet, Larutan Injeksi">
                </div>

                <div>
                    <label class="form-label font-bold">Rute Pemberian</label>
                    <input type="text" name="route" value="{{ old('route', $product?->route) }}" class="form-input rounded-xl" placeholder="Contoh: Oral, Injeksi (IV/IM)">
                </div>
                <div>
                    <label class="form-label font-bold">Kandungan</label>
                    <input type="text" name="composition" value="{{ old('composition', $product?->composition) }}" class="form-input rounded-xl" placeholder="Contoh: Allopurinol 100 mg">
                </div>

                <div class="md:col-span-2">
                    <label class="form-label font-bold">Indikasi / Fungsi</label>
                    <textarea name="description" rows="3" class="form-input rounded-xl" placeholder="Contoh: Untuk membantu menurunkan kadar asam urat.">{{ old('description', $product?->description) }}</textarea>
                </div>

                <div class="md:col-span-2">
                    <label class="flex items-start gap-3 p-3.5 rounded-xl border border-red-100 bg-red-50/40 cursor-pointer hover:bg-red-50/70 transition-colors">
                        <input type="checkbox" name="requires_prescription" value="1" {{ old('requires_prescription', $product?->requires_prescription) ? 'checked' : '' }} class="mt-0.5 w-4 h-4 accent-red-500">
                        <div>
                            <span class="font-bold text-gray-800 text-sm">Obat Keras / Butuh Resep Dokter</span>
                            <p class="text-xs text-gray-500 mt-0.5">Centang jika produk ini memerlukan resep dokter untuk dibeli</p>
                        </div>
                    </label>
                </div>
            </div>
        </section>

        {{-- Foto Produk --}}
        <section class="card overflow-hidden bg-white border border-gray-100 rounded-2xl shadow-sm" x-data="productImageUpload()">
            <div class="px-5 py-3.5 border-b border-gray-100 bg-slate-50/80 flex items-center gap-2.5">
                <div class="w-8 h-8 rounded-lg bg-indigo-100 flex items-center justify-center">
                    <svg class="w-4 h-4 text-indigo-700" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                </div>
                <div>
                    <h3 class="text-sm font-bold text-gray-800">Foto Produk</h3>
                    <p class="text-[11px] text-gray-400">Maksimal 6 foto · rasio 1:1 · max 2MB · Slot 1 = foto utama</p>
                </div>
            </div>
            <div class="p-5 sm:p-6">
                <div class="grid grid-cols-3 sm:grid-cols-6 gap-3">
                    <template x-for="(slot, i) in slots" :key="i">
                        <div class="relative group aspect-square rounded-xl border-2 border-dashed flex flex-col items-center justify-center overflow-hidden transition-all bg-slate-50/80 hover:bg-emerald-50/50 cursor-pointer"
                             :class="slot.preview ? 'border-emerald-200 bg-white' : 'border-slate-200 hover:border-emerald-400'"
                             @click="triggerInput(i)">

                            <template x-if="slot.preview">
                                <div class="w-full h-full relative">
                                    <img :src="slot.preview" class="w-full h-full object-cover" alt="">
                                    <div class="absolute inset-0 bg-slate-900/45 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center">
                                        <button type="button" @click.stop="clearSlot(i)" class="w-8 h-8 rounded-full bg-red-600 hover:bg-red-700 text-white flex items-center justify-center shadow-md" title="Hapus Foto">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                        </button>
                                    </div>
                                    <span class="absolute top-1.5 left-1.5 px-1.5 py-0.5 rounded-md text-[9px] font-bold bg-white/95 text-emerald-700 shadow-sm" x-text="i === 0 ? 'Utama' : (i + 1)"></span>
                                </div>
                            </template>

                            <template x-if="!slot.preview">
                                <div class="text-center p-2">
                                    <div class="w-8 h-8 mx-auto mb-1.5 rounded-lg bg-white border border-slate-200 flex items-center justify-center group-hover:border-emerald-300 transition-colors">
                                        <svg class="w-4 h-4 text-slate-400 group-hover:text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                    </div>
                                    <span class="text-[10px] font-bold text-slate-500 group-hover:text-emerald-700" x-text="i === 0 ? 'Utama' : 'Slot ' + (i + 1)"></span>
                                </div>
                            </template>

                            <input type="file" :name="'images[' + i + ']'" :id="'img-file-' + i" class="hidden" accept="image/*" @change="previewImage($event, i)">
                            <input type="hidden" :name="'existing_images[' + i + ']'" :value="slot.isExisting ? slot.path : ''">
                        </div>
                    </template>
                </div>
                @error('images')<p class="form-error mt-2">{{ $message }}</p>@enderror
            </div>
        </section>

        {{-- Harga & Stok --}}
        <section class="card overflow-hidden bg-white border border-gray-100 rounded-2xl shadow-sm" x-data="productPriceStock()" x-init="init()">
            <div class="px-5 py-3.5 border-b border-gray-100 bg-gradient-to-r from-emerald-50/90 via-white to-teal-50/60 flex flex-col sm:flex-row sm:items-center gap-3">
                <div class="flex items-center gap-2.5 flex-1 min-w-0">
                    <div class="w-9 h-9 rounded-xl bg-emerald-100 flex items-center justify-center shrink-0">
                        <svg class="w-4.5 h-4.5 text-emerald-700" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                    <div class="min-w-0">
                        <h3 class="text-sm font-extrabold text-gray-800 tracking-tight">Harga & Stok</h3>
                        <p class="text-[11px] text-gray-400">Harga beli tetap sebagai HPP · jual & grosir bisa disesuaikan persen</p>
                    </div>
                </div>
                <div class="flex flex-wrap items-center gap-1.5 sm:justify-end">
                    <span class="text-[10px] font-bold uppercase tracking-wider text-emerald-700/80 mr-1">Sesuaikan</span>
                    <template x-for="p in [5, 10, 15, 20]" :key="p">
                        <button type="button"
                                @click="adjustPercent = p; applyPricePercent()"
                                class="px-2.5 py-1.5 rounded-lg text-[11px] font-extrabold border border-emerald-200 bg-white text-emerald-700 hover:bg-emerald-50 hover:border-emerald-300 transition-colors shadow-sm">
                            +<span x-text="p"></span>%
                        </button>
                    </template>
                    <div class="flex items-center gap-1 ml-1 pl-2 border-l border-emerald-100">
                        <input type="number" step="0.1" min="-90" max="500" x-model.number="adjustPercent"
                               class="w-16 form-input rounded-lg py-1.5 text-center text-sm font-bold tabular-nums" title="Persen custom">
                        <button type="button" @click="applyPricePercent()"
                                class="px-2.5 py-1.5 rounded-lg text-[11px] font-extrabold bg-emerald-600 text-white hover:bg-emerald-700 shadow-sm shadow-emerald-600/25 transition-colors">
                            Terapkan
                        </button>
                    </div>
                </div>
            </div>
            <div class="p-5 sm:p-6">
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 mb-5">
                    <div class="rounded-2xl border border-slate-200 bg-slate-50/80 p-3.5">
                        <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400 mb-1">Harga Beli</p>
                        <p class="text-lg font-extrabold text-slate-700 tabular-nums">Rp <span x-text="formatRupiah(purchasePrice) || '0'"></span></p>
                        <p class="text-[10px] text-slate-400 mt-1">HPP · tidak ikut naik persen</p>
                    </div>
                    <div class="rounded-2xl border border-emerald-200 bg-emerald-50/70 p-3.5">
                        <p class="text-[10px] font-bold uppercase tracking-wider text-emerald-600 mb-1">Harga Jual</p>
                        <p class="text-lg font-extrabold text-emerald-700 tabular-nums">Rp <span x-text="formatRupiah(sellPrice) || '0'"></span></p>
                        <p class="text-[10px] text-emerald-600/70 mt-1">Eceran · ikut penyesuaian %</p>
                    </div>
                    <div class="rounded-2xl border border-teal-200 bg-teal-50/70 p-3.5">
                        <p class="text-[10px] font-bold uppercase tracking-wider text-teal-600 mb-1">Harga Grosir</p>
                        <p class="text-lg font-extrabold text-teal-700 tabular-nums">Rp <span x-text="formatRupiah(wholesalePrice) || '0'"></span></p>
                        <p class="text-[10px] text-teal-600/70 mt-1">Mitra/B2B · ikut penyesuaian %</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                    <div>
                        <label class="form-label font-bold">Harga Beli <span class="text-red-500">*</span></label>
                        <div class="relative">
                            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-sm font-semibold">Rp</span>
                            <input type="hidden" name="purchase_price" :value="purchasePrice">
                            <input type="text" :value="formatRupiah(purchasePrice)" @input="purchasePrice = parseRupiah($event.target.value)" class="form-input rounded-xl pl-9 font-semibold text-gray-800" placeholder="0">
                        </div>
                        @error('purchase_price')<p class="form-error">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label class="form-label font-bold">Harga Jual (Eceran) <span class="text-red-500">*</span></label>
                        <div class="relative">
                            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-emerald-500 text-sm font-semibold">Rp</span>
                            <input type="hidden" name="sell_price" :value="sellPrice">
                            <input type="text" :value="formatRupiah(sellPrice)"
                                   @input="sellPrice = parseRupiah($event.target.value); hetAutoNote = false"
                                   @blur="clampSellAgainstHet()"
                                   class="form-input rounded-xl pl-9 font-bold text-emerald-800 border-emerald-100 focus:ring-emerald-400" placeholder="0">
                        </div>
                        <div class="mt-1.5 space-y-1">
                            <span x-show="isExceedingHet" x-cloak
                                  class="inline-flex items-center gap-1 text-[11px] text-amber-800 bg-amber-50 border border-amber-200 px-2 py-1 rounded-lg font-bold">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/></svg>
                                Melebihi HET — akan disesuaikan ke grosir/HET
                            </span>
                            <span x-show="hetAutoNote && !isExceedingHet" x-cloak
                                  class="inline-flex items-center gap-1 text-[11px] text-emerald-800 bg-emerald-50 border border-emerald-200 px-2 py-1 rounded-lg font-bold">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                Jual disesuaikan (grosir / HET)
                            </span>
                        </div>
                        @error('sell_price')<p class="form-error">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label class="form-label font-bold">Harga Grosir</label>
                        <div class="relative">
                            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-teal-500 text-sm font-semibold">Rp</span>
                            <input type="hidden" name="wholesale_price" :value="wholesalePrice">
                            <input type="text" :value="formatRupiah(wholesalePrice)"
                                   @input="wholesalePrice = parseRupiah($event.target.value); clampSellAgainstHet()"
                                   @blur="clampSellAgainstHet()"
                                   class="form-input rounded-xl pl-9 font-semibold text-teal-800 border-teal-100 focus:ring-teal-400" placeholder="0">
                        </div>
                        @error('wholesale_price')<p class="form-error">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label class="form-label font-bold">HET Markup (%)</label>
                        <select name="het_markup" x-model.number="hetMarkup" class="form-input rounded-xl font-medium text-gray-700">
                            <option value="0">0% (Tanpa otomatis)</option>
                            <option value="5">5%</option>
                            <option value="10">10%</option>
                            <option value="15">15%</option>
                            <option value="20">20%</option>
                            <option value="25">25%</option>
                            <option value="30">30%</option>
                        </select>
                        @error('het_markup')<p class="form-error">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label class="form-label font-bold">HET (Harga Eceran Tertinggi)</label>
                        <div class="relative">
                            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-sm font-semibold">Rp</span>
                            <input type="hidden" name="het_price" :value="hetPrice">
                            <input type="text" :value="formatRupiah(hetPrice)"
                                   @input="hetPrice = parseRupiah($event.target.value); clampSellAgainstHet()"
                                   @blur="clampSellAgainstHet()"
                                   class="form-input rounded-xl pl-9 font-semibold text-gray-800" placeholder="0">
                        </div>
                        @error('het_price')<p class="form-error">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label class="form-label font-bold">Stok Saat Ini <span class="text-red-500">*</span></label>
                        <input type="number" name="stock" value="{{ old('stock', $product?->stock ?? 0) }}" class="form-input rounded-xl {{ $isEdit ? 'bg-slate-50 text-slate-600' : '' }}" min="0" {{ $isEdit ? 'readonly' : '' }}>
                        @if($isEdit)
                        <p class="text-[11px] text-slate-400 mt-1.5">Ubah stok lewat Barang Masuk / Keluar</p>
                        @endif
                        @error('stock')<p class="form-error">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label class="form-label font-bold">Stok Minimum <span class="text-red-500">*</span></label>
                        <input type="number" name="stock_min" value="{{ old('stock_min', $product?->stock_min ?? 10) }}" class="form-input rounded-xl" min="0">
                        <p class="text-[11px] text-slate-400 mt-1.5">Peringatan saat stok menipis</p>
                        @error('stock_min')<p class="form-error">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label class="form-label font-bold">Tanggal Kadaluarsa</label>
                        <input type="date" name="expired_date" value="{{ old('expired_date', $product?->expired_date?->format('Y-m-d')) }}" class="form-input rounded-xl">
                        @error('expired_date')<p class="form-error">{{ $message }}</p>@enderror
                    </div>

                    @if($isEdit)
                    <div>
                        <label class="form-label font-bold">Status Produk</label>
                        <select name="is_active" class="form-input rounded-xl">
                            <option value="1" {{ old('is_active', $product->is_active) ? 'selected' : '' }}>Aktif</option>
                            <option value="0" {{ !old('is_active', $product->is_active) ? 'selected' : '' }}>Nonaktif</option>
                        </select>
                    </div>
                    @endif
                </div>
            </div>
        </section>
    </form>

    {{-- Sticky action bar di atas footer fixed --}}
    <div class="app-sticky-bar fixed bottom-[4.75rem] right-0 z-30 px-4 sm:px-6 pointer-events-none"
         :class="{ 'is-sidebar-collapsed': collapsed }">
        <div class="max-w-5xl mx-auto pointer-events-auto">
            <div class="flex flex-col-reverse sm:flex-row sm:items-center sm:justify-between gap-3 p-3 sm:p-4 rounded-2xl bg-white/95 backdrop-blur-md border border-emerald-100/80 shadow-lg shadow-emerald-900/10 ring-1 ring-emerald-50">
                <p class="text-[11px] text-slate-500 hidden sm:block pl-1">
                    {{ $isEdit ? 'Simpan untuk memperbarui master produk · jual & grosir selaras dengan penyesuaian %.' : 'Produk baru akan langsung aktif di inventori.' }}
                </p>
                <div class="flex items-center justify-end gap-2.5 w-full sm:w-auto">
                    <a href="{{ $indexUrl }}"
                       class="inline-flex items-center justify-center gap-1.5 px-4 py-2.5 rounded-xl border border-gray-200 bg-white text-gray-700 text-sm font-bold hover:bg-gray-50 transition-colors flex-1 sm:flex-none">
                        Batal
                    </a>
                    <button type="submit" form="{{ $formId }}"
                            class="inline-flex items-center justify-center gap-1.5 px-5 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-extrabold shadow-md shadow-emerald-600/25 transition-colors flex-1 sm:flex-none">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        {{ $isEdit ? 'Simpan Perubahan' : 'Tambah Produk' }}
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
