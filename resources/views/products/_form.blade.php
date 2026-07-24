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
    $wholesaleMarkupOptions = $wholesaleMarkupOptions ?? \App\Models\Setting::wholesaleMarkupOptions();
    $wholesaleMarkupDefault = $wholesaleMarkupDefault ?? \App\Models\Setting::wholesaleMarkupDefault();
    $currentWholesaleMarkup = (int) old(
        'wholesale_markup',
        optional($product)->wholesale_markup ?? ($isEdit ? (optional($product)->het_markup ?? 0) : $wholesaleMarkupDefault)
    );
@endphp

<script>
    window.productPriceStock = function() {
        return {
            purchasePrice: {{ old('purchase_price', round(optional($product)->purchase_price ?? 0)) }},
            sellPrice: {{ old('sell_price', round(optional($product)->sell_price ?? 0)) }},
            wholesalePrice: {{ old('wholesale_price', round(optional($product)->wholesale_price ?? 0)) }},
            sellMarkup: {{ old('het_markup', optional($product)->het_markup ?? 0) }},
            wholesaleMarkup: {{ $currentWholesaleMarkup }},
            hetPrice: {{ old('het_price', round(optional($product)->het_price ?? 0)) }},

            init() {
                this.$watch('purchasePrice', () => this.applyMarkupsFromPurchase());
                this.$watch('sellMarkup', () => this.applySellMarkup());
                this.$watch('wholesaleMarkup', () => this.applyWholesaleMarkup());
            },

            /** Markup jual: Harga jual = Harga beli × (1 + %). */
            calcSell(beli, markup) {
                const b = Math.max(0, Math.round(Number(beli) || 0));
                const m = Math.max(0, Number(markup) || 0);
                if (b <= 0 || m <= 0) return this.sellPrice || 0;
                return Math.round(b * (1 + m / 100));
            },

            /**
             * Markup grosir: Harga grosir = Harga jual × (1 − %).
             * Contoh: jual 71662 · 5% → 68079.
             */
            calcWholesale(jual, markup) {
                const j = Math.max(0, Math.round(Number(jual) || 0));
                const m = Math.max(0, Math.min(99, Number(markup) || 0));
                if (j <= 0 || m <= 0) return this.wholesalePrice || 0;
                const grosir = Math.round(j * (1 - m / 100));
                return Math.max(0, Math.min(grosir, j - 1));
            },

            applySellMarkup() {
                if (this.sellMarkup > 0 && this.purchasePrice > 0) {
                    this.sellPrice = this.calcSell(this.purchasePrice, this.sellMarkup);
                }
                this.applyWholesaleMarkup();
            },

            applyWholesaleMarkup() {
                if (this.wholesaleMarkup > 0 && this.sellPrice > 0) {
                    this.wholesalePrice = this.calcWholesale(this.sellPrice, this.wholesaleMarkup);
                }
                this.ensureWholesaleNotAboveSell();
            },

            applyMarkupsFromPurchase() {
                if (this.sellMarkup > 0 && this.purchasePrice > 0) {
                    this.sellPrice = this.calcSell(this.purchasePrice, this.sellMarkup);
                }
                this.applyWholesaleMarkup();
            },

            onSellInput() {
                this.applyWholesaleMarkup();
            },

            get isExceedingHet() {
                return this.hetPrice > 0 && this.sellPrice > this.hetPrice;
            },

            get wholesaleDrop() {
                if (!this.sellPrice || !this.wholesalePrice) return 0;
                return Math.max(0, this.sellPrice - this.wholesalePrice);
            },

            ensureWholesaleNotAboveSell() {
                if (this.sellPrice > 0 && this.wholesalePrice > this.sellPrice) {
                    this.wholesalePrice = this.sellPrice;
                }
            },

            clampSellAgainstHet() {
                this.ensureWholesaleNotAboveSell();
            },

            adjustPercent: 10,

            applyPricePercent() {
                const p = Number(this.adjustPercent);
                if (!p || p < -90 || p > 500) return;
                const factor = 1 + (p / 100);
                if (this.sellPrice > 0) {
                    this.sellPrice = Math.round(this.sellPrice * factor);
                }
                if (this.wholesaleMarkup > 0 && this.sellPrice > 0) {
                    this.wholesalePrice = this.calcWholesale(this.sellPrice, this.wholesaleMarkup);
                } else if (this.wholesalePrice > 0) {
                    this.wholesalePrice = Math.round(this.wholesalePrice * factor);
                }
                this.ensureWholesaleNotAboveSell();
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
            showModal: false,
            cropIndex: null,
            rawImageUrl: null,
            cropper: null,
            _initTimer: null,
            applying: false,

            triggerInput(i) {
                if (this.slots[i].preview) return;
                document.getElementById('img-file-' + i)?.click();
            },

            onFileChosen(e, i) {
                const file = e.target.files?.[0];
                if (!file) return;

                if (!file.type.startsWith('image/')) {
                    alert('File harus berupa gambar (JPG, PNG, atau WEBP).');
                    e.target.value = '';
                    return;
                }

                if (file.size > 8 * 1024 * 1024) {
                    alert('Ukuran file terlalu besar. Maksimal 8MB sebelum di-crop.');
                    e.target.value = '';
                    return;
                }

                const reader = new FileReader();
                reader.onload = (event) => {
                    this.openCropper(i, event.target.result);
                };
                reader.readAsDataURL(file);
            },

            openCropper(i, dataUrl) {
                this.destroyCropper();
                this.cropIndex = i;
                this.rawImageUrl = dataUrl;
                this.showModal = true;
                this.$nextTick(() => this.initCropper());
            },

            recropSlot(i) {
                const preview = this.slots[i]?.preview;
                if (!preview) return;
                this.openCropper(i, preview);
            },

            initCropper() {
                clearTimeout(this._initTimer);
                this._initTimer = setTimeout(() => {
                    const image = this.$refs.cropperImage;
                    if (!image || !this.rawImageUrl || typeof Cropper === 'undefined') return;

                    const start = () => {
                        this.destroyCropper();
                        this.cropper = new Cropper(image, {
                            aspectRatio: 1,
                            viewMode: 1,
                            dragMode: 'move',
                            autoCropArea: 0.9,
                            responsive: true,
                            restore: false,
                            checkOrientation: true,
                            background: false,
                            modal: true,
                            guides: true,
                            center: true,
                            highlight: true,
                            cropBoxMovable: true,
                            cropBoxResizable: true,
                            toggleDragModeOnDblclick: false,
                        });
                    };

                    if (image.complete) start();
                    else image.onload = () => start();
                }, 80);
            },

            destroyCropper() {
                clearTimeout(this._initTimer);
                if (this.cropper) {
                    this.cropper.destroy();
                    this.cropper = null;
                }
            },

            zoomIn() { this.cropper?.zoom(0.1); },
            zoomOut() { this.cropper?.zoom(-0.1); },
            rotateLeft() { this.cropper?.rotate(-90); },
            rotateRight() { this.cropper?.rotate(90); },
            resetCrop() { this.cropper?.reset(); },

            cancelCrop() {
                const i = this.cropIndex;
                this.showModal = false;
                this.destroyCropper();
                this.rawImageUrl = null;
                this.cropIndex = null;
                this.applying = false;

                // Jika slot masih kosong, kosongkan file input
                if (i !== null && !this.slots[i]?.preview) {
                    const input = document.getElementById('img-file-' + i);
                    if (input) input.value = '';
                }
            },

            applyCrop() {
                if (!this.cropper || this.cropIndex === null || this.applying) return;
                this.applying = true;

                const i = this.cropIndex;
                const canvas = this.cropper.getCroppedCanvas({
                    width: 800,
                    height: 800,
                    imageSmoothingEnabled: true,
                    imageSmoothingQuality: 'high',
                });

                if (!canvas) {
                    this.applying = false;
                    return;
                }

                canvas.toBlob((blob) => {
                    if (!blob) {
                        this.applying = false;
                        return;
                    }

                    if (blob.size > 2 * 1024 * 1024) {
                        // Turunkan kualitas jika masih > 2MB
                        canvas.toBlob((smaller) => {
                            this.finishCrop(i, smaller || blob, canvas);
                        }, 'image/jpeg', 0.75);
                        return;
                    }

                    this.finishCrop(i, blob, canvas);
                }, 'image/jpeg', 0.9);
            },

            finishCrop(i, blob, canvas) {
                const file = new File([blob], `produk-slot-${i + 1}.jpg`, { type: 'image/jpeg' });
                const input = document.getElementById('img-file-' + i);
                if (input) {
                    const dt = new DataTransfer();
                    dt.items.add(file);
                    input.files = dt.files;
                }

                this.slots[i] = {
                    preview: canvas.toDataURL('image/jpeg', 0.9),
                    isExisting: false,
                    path: null,
                };

                this.showModal = false;
                this.destroyCropper();
                this.rawImageUrl = null;
                this.cropIndex = null;
                this.applying = false;
            },

            clearSlot(i) {
                const input = document.getElementById('img-file-' + i);
                if (input) input.value = '';

                this.slots[i] = {
                    preview: null,
                    isExisting: false,
                    path: null,
                };
            }
        }
    }
</script>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.1/cropper.min.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.1/cropper.min.js"></script>
<style>
    .product-cropper-box {
        width: 100%;
        height: min(58vh, 420px);
        background: #0f172a;
        border-radius: 1rem;
        overflow: hidden;
    }
    .product-cropper-box img { display: block; max-width: 100%; }
    .product-cropper-box .cropper-container { max-height: min(58vh, 420px); }
    .product-cropper-modal { max-height: calc(100vh - 2rem); }
</style>

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
                    <input type="text" name="code" id="productCodeInput" value="{{ old('code', $product?->code) }}" class="form-input rounded-xl font-mono {{ $errors->has('code') ? 'error' : '' }}" placeholder="Contoh: OBT-0001">
                    @error('code')<p class="form-error">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="form-label font-bold">Barcode</label>
                    <input type="text" name="barcode" value="{{ old('barcode', $product?->barcode) }}" class="form-input rounded-xl font-mono {{ $errors->has('barcode') ? 'error' : '' }}" placeholder="Scan atau ketik barcode">
                    @error('barcode')<p class="form-error">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="form-label font-bold">Kategori</label>
                    <select name="category_id" id="categorySelect" class="form-input rounded-xl">
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
                    <p class="text-[11px] text-gray-400">Maksimal 6 foto · crop 1:1 · max 2MB hasil · Slot 1 = foto utama</p>
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
                                    <div class="absolute inset-0 bg-slate-900/50 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center gap-1.5">
                                        <button type="button" @click.stop="recropSlot(i)" class="w-8 h-8 rounded-full bg-white text-emerald-700 hover:bg-emerald-50 flex items-center justify-center shadow-md" title="Crop / Atur Posisi">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4"/></svg>
                                        </button>
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

                            <input type="file" :name="'images[' + i + ']'" :id="'img-file-' + i" class="hidden" accept="image/jpeg,image/png,image/jpg,image/webp,image/gif" @change="onFileChosen($event, i)">
                            <input type="hidden" :name="'existing_images[' + i + ']'" :value="slot.isExisting ? slot.path : ''">
                        </div>
                    </template>
                </div>
                <p class="mt-3 text-[11px] text-slate-400 leading-relaxed">
                    Setelah memilih foto, atur crop &amp; posisi pada popup. Hover foto untuk crop ulang atau hapus.
                </p>
                @error('images')<p class="form-error mt-2">{{ $message }}</p>@enderror
                @error('images.*')<p class="form-error mt-2">{{ $message }}</p>@enderror
            </div>

            {{-- Modal crop --}}
            <template x-teleport="body">
                <div x-show="showModal"
                     x-cloak
                     class="fixed inset-0 z-[200] flex items-center justify-center p-3 sm:p-6 bg-slate-900/65 backdrop-blur-sm"
                     @keydown.escape.window="if (showModal) cancelCrop()"
                     @click.self="cancelCrop()">
                    <div @click.stop
                         class="product-cropper-modal bg-white rounded-2xl w-full max-w-xl shadow-2xl border border-slate-100 flex flex-col overflow-hidden"
                         x-show="showModal"
                         x-transition:enter="transition ease-out duration-200"
                         x-transition:enter-start="opacity-0 translate-y-2 scale-[0.98]"
                         x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                         x-transition:leave="transition ease-in duration-150"
                         x-transition:leave-start="opacity-100"
                         x-transition:leave-end="opacity-0">
                        <div class="px-5 py-4 border-b border-slate-100 flex justify-between items-center bg-gradient-to-r from-emerald-50 via-white to-teal-50 flex-shrink-0">
                            <div>
                                <h3 class="text-sm font-extrabold text-slate-800 flex items-center gap-2">
                                    <span class="w-8 h-8 rounded-lg bg-emerald-100 text-emerald-700 flex items-center justify-center">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4"/></svg>
                                    </span>
                                    Crop &amp; Atur Posisi Foto
                                </h3>
                                <p class="text-[11px] text-slate-500 mt-1 pl-10">
                                    Slot <span class="font-bold text-emerald-700" x-text="cropIndex === 0 ? 'Utama' : ((cropIndex ?? 0) + 1)"></span>
                                    · rasio 1:1
                                </p>
                            </div>
                            <button type="button" @click="cancelCrop()" class="text-slate-400 hover:text-slate-600 transition-colors p-1.5 rounded-lg hover:bg-slate-100 cursor-pointer" aria-label="Tutup">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                            </button>
                        </div>

                        <div class="p-4 sm:p-5 flex-1 min-h-0">
                            <div class="product-cropper-box ring-1 ring-slate-800/80 shadow-inner">
                                <img x-ref="cropperImage" :src="rawImageUrl" alt="Foto untuk dipotong">
                            </div>
                            <p class="text-[11px] text-slate-400 font-medium text-center mt-3 leading-relaxed">
                                Seret foto untuk geser posisi, gunakan zoom/putar, lalu sesuaikan kotak crop hingga produk terlihat jelas.
                            </p>
                        </div>

                        <div class="px-4 sm:px-5 py-3.5 border-t border-slate-100 flex flex-wrap justify-between items-center bg-slate-50/90 gap-3 flex-shrink-0">
                            <div class="flex gap-1.5">
                                <button type="button" @click="zoomIn()" class="p-2 bg-white border border-slate-200 rounded-lg hover:bg-emerald-50 hover:text-emerald-700 hover:border-emerald-200 transition-colors cursor-pointer" title="Perbesar">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0zM10 7v3m0 0v3m0-3h3m-3 0H7"/></svg>
                                </button>
                                <button type="button" @click="zoomOut()" class="p-2 bg-white border border-slate-200 rounded-lg hover:bg-emerald-50 hover:text-emerald-700 hover:border-emerald-200 transition-colors cursor-pointer" title="Perkecil">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0zM13 10H7"/></svg>
                                </button>
                                <button type="button" @click="rotateLeft()" class="p-2 bg-white border border-slate-200 rounded-lg hover:bg-emerald-50 hover:text-emerald-700 hover:border-emerald-200 transition-colors cursor-pointer" title="Putar kiri">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h6V4M3 10a9 9 0 0115.5-6.36M21 14h-6v6M21 14a9 9 0 01-15.5 6.36"/></svg>
                                </button>
                                <button type="button" @click="rotateRight()" class="p-2 bg-white border border-slate-200 rounded-lg hover:bg-emerald-50 hover:text-emerald-700 hover:border-emerald-200 transition-colors cursor-pointer" title="Putar kanan">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 10h-6V4M21 10a9 9 0 00-15.5-6.36M3 14h6v6M3 14a9 9 0 0015.5 6.36"/></svg>
                                </button>
                                <button type="button" @click="resetCrop()" class="p-2 bg-white border border-slate-200 rounded-lg hover:bg-emerald-50 hover:text-emerald-700 hover:border-emerald-200 transition-colors cursor-pointer" title="Reset">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                                </button>
                            </div>
                            <div class="flex gap-2">
                                <button type="button" @click="cancelCrop()" class="py-2 px-3.5 text-xs font-bold rounded-lg border border-slate-200 bg-white text-slate-600 hover:bg-slate-50 cursor-pointer">
                                    Batal
                                </button>
                                <button type="button" @click="applyCrop()" :disabled="applying"
                                        class="py-2 px-4 text-xs font-bold rounded-lg bg-emerald-600 hover:bg-emerald-700 disabled:opacity-60 text-white shadow-md shadow-emerald-600/20 cursor-pointer inline-flex items-center gap-1.5">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                    <span x-text="applying ? 'Memproses...' : 'Terapkan Crop'"></span>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </template>
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
                        <p class="text-[10px] text-emerald-600/70 mt-1" x-show="sellMarkup > 0" x-cloak>
                            Otomatis markup jual <span x-text="sellMarkup"></span>% dari beli
                        </p>
                        <p class="text-[10px] text-emerald-600/70 mt-1" x-show="!(sellMarkup > 0)">Eceran · ikut penyesuaian %</p>
                    </div>
                    <div class="rounded-2xl border border-teal-200 bg-teal-50/70 p-3.5">
                        <p class="text-[10px] font-bold uppercase tracking-wider text-teal-600 mb-1">Harga Grosir</p>
                        <p class="text-lg font-extrabold text-teal-700 tabular-nums">Rp <span x-text="formatRupiah(wholesalePrice) || '0'"></span></p>
                        <p class="text-[10px] text-teal-600/70 mt-1" x-show="wholesaleMarkup > 0 && wholesaleDrop > 0" x-cloak>
                            Otomatis markup grosir <span x-text="wholesaleMarkup"></span>% dari jual · −Rp <span x-text="formatRupiah(wholesaleDrop)"></span>
                        </p>
                        <p class="text-[10px] text-teal-600/70 mt-1" x-show="!(wholesaleMarkup > 0 && wholesaleDrop > 0)">Mitra/B2B · lebih rendah dari eceran</p>
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
                        <label class="form-label font-bold">
                            Harga Jual (Eceran) <span class="text-red-500">*</span>
                            <span x-show="sellMarkup > 0" x-cloak class="ml-1 text-[10px] font-bold uppercase tracking-wide text-emerald-600">Otomatis</span>
                        </label>
                        <div class="relative">
                            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-emerald-500 text-sm font-semibold">Rp</span>
                            <input type="hidden" name="sell_price" :value="sellPrice">
                            <input type="text" :value="formatRupiah(sellPrice)"
                                   @input="sellPrice = parseRupiah($event.target.value); onSellInput()"
                                   @blur="onSellInput()"
                                   :readonly="sellMarkup > 0"
                                   :class="sellMarkup > 0 ? 'bg-emerald-50/80 cursor-default' : ''"
                                   class="form-input rounded-xl pl-9 font-bold text-emerald-800 border-emerald-100 focus:ring-emerald-400" placeholder="0">
                        </div>
                        <div class="mt-1.5 space-y-1">
                            <span x-show="isExceedingHet" x-cloak
                                  class="inline-flex items-center gap-1 text-[11px] text-rose-800 bg-rose-50 border border-rose-200 px-2 py-1 rounded-lg font-bold">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/></svg>
                                Melebihi HET — harga jual tetap dipakai
                            </span>
                            <p x-show="sellMarkup > 0" x-cloak class="text-[11px] text-emerald-700/80">
                                Beli + <span x-text="sellMarkup"></span>%
                            </p>
                        </div>
                        @error('sell_price')<p class="form-error">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label class="form-label font-bold">
                            Harga Grosir
                            <span x-show="wholesaleMarkup > 0" x-cloak class="ml-1 text-[10px] font-bold uppercase tracking-wide text-teal-600">Otomatis</span>
                        </label>
                        <div class="relative">
                            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-teal-500 text-sm font-semibold">Rp</span>
                            <input type="hidden" name="wholesale_price" :value="wholesalePrice">
                            <input type="text" :value="formatRupiah(wholesalePrice)"
                                   @input="wholesalePrice = parseRupiah($event.target.value); clampSellAgainstHet()"
                                   @blur="clampSellAgainstHet()"
                                   :readonly="wholesaleMarkup > 0"
                                   :class="wholesaleMarkup > 0 ? 'bg-teal-50/80 cursor-default' : ''"
                                   class="form-input rounded-xl pl-9 font-semibold text-teal-800 border-teal-100 focus:ring-teal-400" placeholder="0">
                        </div>
                        <p x-show="wholesaleMarkup > 0" x-cloak class="text-[11px] text-teal-700/80 mt-1.5">
                            Jual − <span x-text="wholesaleMarkup"></span>% · selisih Rp <span x-text="formatRupiah(wholesaleDrop)"></span>
                        </p>
                        @error('wholesale_price')<p class="form-error">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label class="form-label font-bold">HET Markup Jual (%)</label>
                        <select name="het_markup" x-model.number="sellMarkup" class="form-input rounded-xl font-medium text-gray-700">
                            <option value="0">0% (Manual)</option>
                            <option value="5">5%</option>
                            <option value="10">10%</option>
                            <option value="15">15%</option>
                            <option value="20">20%</option>
                            <option value="25">25%</option>
                            <option value="30">30%</option>
                        </select>
                        <p class="text-[11px] text-slate-400 mt-1.5">Otomatis isi <strong>harga jual</strong> dari harga beli</p>
                        @error('het_markup')<p class="form-error">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label class="form-label font-bold">HET Markup Grosir (%)</label>
                        <select name="wholesale_markup" x-model.number="wholesaleMarkup" class="form-input rounded-xl font-medium text-gray-700">
                            <option value="0">0% (Manual)</option>
                            @foreach($wholesaleMarkupOptions as $pct)
                                <option value="{{ $pct }}">{{ $pct }}%</option>
                            @endforeach
                            @if($currentWholesaleMarkup > 0 && ! in_array($currentWholesaleMarkup, $wholesaleMarkupOptions, true))
                                <option value="{{ $currentWholesaleMarkup }}">{{ $currentWholesaleMarkup }}% (tersimpan)</option>
                            @endif
                        </select>
                        <p class="text-[11px] text-slate-400 mt-1.5">Otomatis isi <strong>harga grosir</strong> = harga jual − % · opsi dari Pengaturan</p>
                        @error('wholesale_markup')<p class="form-error">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label class="form-label font-bold">HET (Harga Eceran Tertinggi)</label>
                        <div class="relative">
                            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-sm font-semibold">Rp</span>
                            <input type="hidden" name="het_price" :value="hetPrice">
                            <input type="text" :value="formatRupiah(hetPrice)"
                                   @input="hetPrice = parseRupiah($event.target.value)"
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

<script>
document.addEventListener('DOMContentLoaded', function() {
    const categorySelect = document.getElementById('categorySelect');
    const codeInput = document.getElementById('productCodeInput');
    if (!categorySelect || !codeInput) return;

    const initialCategoryId = "{{ old('category_id', $product?->category_id ?? '') }}";
    const initialCode = "{{ old('code', $product?->code ?? '') }}";
    const productId = "{{ $isEdit ? $product->id : '' }}";

    async function fetchNextCode(catId) {
        if (!catId) return;
        try {
            const baseUrl = "{{ route('api.categories.next-code', ['category' => 'CAT_ID']) }}";
            let urlStr = baseUrl.replace('CAT_ID', catId);
            const url = new URL(urlStr, window.location.origin);
            if (productId) {
                url.searchParams.set('ignore_product_id', productId);
            }
            const res = await fetch(url);
            if (res.ok) {
                const data = await res.json();
                if (data.success && data.code) {
                    codeInput.value = data.code;
                }
            }
        } catch (e) {
            console.error("Gagal mengambil kode produk otomatis:", e);
        }
    }

    categorySelect.addEventListener('change', function() {
        const selectedCat = this.value;
        if (!selectedCat) {
            if (!productId) codeInput.value = '';
            return;
        }

        if (productId && selectedCat == initialCategoryId && initialCode) {
            codeInput.value = initialCode;
            return;
        }

        fetchNextCode(selectedCat);
    });

    if (!productId && categorySelect.value && !codeInput.value) {
        fetchNextCode(categorySelect.value);
    }
});
</script>
