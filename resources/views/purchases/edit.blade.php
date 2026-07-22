@extends('layouts.app')
@section('title', 'Edit Barang Masuk / PO')
@section('page-title', 'Barang Masuk / PO')

@section('breadcrumb')
<svg class="w-3 h-3 text-gray-400" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"/></svg>
<a wire:navigate href="{{ route('purchases.index') }}" class="hover:text-primary-600 transition-colors">Barang Masuk</a>
<svg class="w-3 h-3 text-gray-400" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"/></svg>
<span class="text-gray-600 font-medium">Edit</span>
@endsection

@section('content')
<script>
    window.purchaseForm = function() {
        return {
            items: @json($purchase->items->map(function($item) {
                return [
                    'product_id' => $item->product_id,
                    'product_name' => $item->product_name,
                    'quantity' => $item->quantity,
                    'unit_name' => $item->product?->unit?->name ?? 'pcs',
                    'purchase_price' => (int) round((float) $item->purchase_price),
                    'sell_price' => (int) round((float) $item->sell_price),
                    'expired_date' => $item->expired_date ? $item->expired_date->format('Y-m-d') : '',
                ];
            })).map(i => ({ ...i, key: Math.random() + Date.now() })),
            addItem() {
                this.items.push({
                    key: Date.now() + Math.random(),
                    product_id: '',
                    product_name: '',
                    quantity: 1,
                    unit_name: '-',
                    purchase_price: 0,
                    sell_price: 0,
                    expired_date: ''
                });
                this.$nextTick(() => {
                    const nodes = document.querySelectorAll('[data-purchase-product]');
                    const last = nodes[nodes.length - 1];
                    last?.focus();
                });
            },
            removeItem(index) {
                if (this.items.length > 1) this.items.splice(index, 1);
            },
            incQty(item) {
                item.quantity = (parseInt(item.quantity, 10) || 1) + 1;
            },
            decQty(item) {
                item.quantity = Math.max(1, (parseInt(item.quantity, 10) || 1) - 1);
            },
            fixQty(item) {
                item.quantity = Math.max(1, parseInt(item.quantity, 10) || 1);
            },
            filledCount() {
                return this.items.filter(i => i.product_id).length;
            },
            lineSubtotal(item) {
                return (Number(item.quantity) || 0) * (Number(item.purchase_price) || 0);
            },
            grandTotal() {
                return this.items.reduce((sum, item) => sum + this.lineSubtotal(item), 0);
            },
            formatMoney(val) {
                const n = Math.round(parseFloat(val) || 0);
                return new Intl.NumberFormat('id-ID').format(n);
            },
            parseMoney(val) {
                const clean = String(val ?? '').replace(/\D/g, '');
                return clean ? parseInt(clean, 10) : 0;
            },
            formatRupiah(val) {
                return 'Rp ' + this.formatMoney(val);
            }
        }
    }
</script>

<style>
    .purchase-create .po-label {
        display: block;
        font-size: 10px;
        font-weight: 800;
        letter-spacing: 0.06em;
        text-transform: uppercase;
        color: #94a3b8;
        margin-bottom: 0.35rem;
        white-space: nowrap;
    }
    .purchase-create .po-input {
        width: 100%;
        border: 1px solid #e2e8f0;
        background: #fff;
        border-radius: 0.75rem;
        padding: 0.6rem 0.85rem;
        font-size: 0.875rem;
        line-height: 1.3;
        color: #0f172a;
        min-height: 40px;
        transition: border-color .15s, box-shadow .15s, background .15s;
    }
    .purchase-create .po-input:focus {
        outline: none;
        border-color: #34d399;
        box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.15);
        background: #fff;
    }
    .purchase-create input[type="number"]::-webkit-outer-spin-button,
    .purchase-create input[type="number"]::-webkit-inner-spin-button {
        -webkit-appearance: none;
        margin: 0;
    }
    .purchase-create input[type="number"] { -moz-appearance: textfield; }

    .purchase-create .po-input-money {
        font-variant-numeric: tabular-nums;
        font-weight: 700;
        text-align: right;
    }
    .purchase-create .po-money-wrap { position: relative; }
    .purchase-create .po-money-prefix {
        position: absolute;
        left: 0.75rem;
        top: 50%;
        transform: translateY(-50%);
        color: #94a3b8;
        font-size: 0.7rem;
        font-weight: 800;
        pointer-events: none;
    }
    .purchase-create .po-input-with-rp { padding-left: 2.1rem; }

    {{-- Ikon & padding kolom pencarian dibuat eksplisit (bukan util Tailwind pl-9/pr-9/left-3)
         supaya tidak pernah lagi tertimpa teks input akibat CSS belum di-build ulang. --}}
    .purchase-create .po-search-icon {
        position: absolute;
        left: 0.75rem;
        top: 50%;
        transform: translateY(-50%);
        pointer-events: none;
        display: flex;
        align-items: center;
        z-index: 1;
    }
    .purchase-create .po-search-clear {
        position: absolute;
        right: 0.6rem;
        top: 50%;
        transform: translateY(-50%);
        z-index: 1;
    }
    .purchase-create .po-search-input {
        padding-left: 2.4rem !important;
        padding-right: 2.4rem !important;
    }

    .purchase-create .po-qty-stepper {
        display: flex;
        align-items: stretch;
        border: 1px solid #e2e8f0;
        background: #fff;
        border-radius: 0.75rem;
        min-height: 40px;
        overflow: hidden;
        transition: border-color .15s, box-shadow .15s;
    }
    .purchase-create .po-qty-stepper:focus-within {
        border-color: #34d399;
        box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.15);
    }
    .purchase-create .po-qty-btn {
        flex: 0 0 30px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #64748b;
        font-weight: 800;
        background: #f8fafc;
        border: none;
        cursor: pointer;
        transition: background .15s, color .15s;
    }
    .purchase-create .po-qty-btn:hover { background: #ecfdf5; color: #047857; }
    .purchase-create .po-qty-input {
        flex: 1 1 auto;
        min-width: 0;
        width: 100%;
        border: none;
        text-align: center;
        font-weight: 800;
        font-size: 0.9rem;
        color: #0f172a;
        background: transparent;
    }
    .purchase-create .po-qty-input:focus { outline: none; box-shadow: none; }

    .purchase-create .po-item {
        border: 1px solid #e2e8f0;
        background: #fff;
        border-radius: 0.875rem;
        padding: 0.9rem 1rem;
        transition: border-color .15s, box-shadow .15s;
    }
    .purchase-create .po-item:hover { border-color: #cbd5e1; }
    .purchase-create .po-item.is-selected {
        border-color: #a7f3d0;
        background: linear-gradient(180deg, #f0fdf4 0%, #fff 72%);
        box-shadow: 0 1px 0 rgba(16, 185, 129, 0.08);
    }
    .purchase-create .po-item-head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 0.75rem;
        margin-bottom: 0.75rem;
        padding-bottom: 0.65rem;
        border-bottom: 1px solid #f1f5f9;
    }

    .purchase-create .po-item-fields {
        display: flex;
        flex-wrap: wrap;
        gap: 0.6rem;
    }
    .purchase-create .po-field-qty     { flex: 0 1 108px; min-width: 96px; }
    .purchase-create .po-field-unit    { flex: 0 1 128px; min-width: 108px; }
    .purchase-create .po-field-buy     { flex: 1 1 160px; min-width: 142px; }
    .purchase-create .po-field-sell    { flex: 1 1 160px; min-width: 142px; }
    .purchase-create .po-field-expired { flex: 1 1 180px; min-width: 158px; }

    .purchase-create .po-search-drop {
        position: absolute;
        left: 0;
        right: 0;
        top: calc(100% + 0.35rem);
        z-index: 100;
        background: #fff;
        border: 1px solid #e2e8f0;
        border-radius: 0.9rem;
        box-shadow: 0 18px 40px -16px rgba(15, 23, 42, 0.28);
        max-height: 16rem;
        overflow-y: auto;
    }
    .purchase-create .po-search-result {
        display: flex;
        width: 100%;
        align-items: center;
        justify-content: space-between;
        gap: 0.75rem;
        padding: 0.7rem 0.85rem;
        text-align: left;
        border-bottom: 1px solid #f1f5f9;
        transition: background .15s;
    }
    .purchase-create .po-search-result:last-child { border-bottom: 0; }
    .purchase-create .po-search-result:hover,
    .purchase-create .po-search-result:focus {
        outline: none;
        background: #ecfdf5;
    }
    .purchase-create .po-sticky-bar {
        position: sticky;
        bottom: 0.75rem;
        z-index: 30;
        backdrop-filter: blur(10px);
        background: rgba(255, 255, 255, 0.92);
        border: 1px solid #e2e8f0;
        border-radius: 1rem;
        box-shadow: 0 10px 30px -12px rgba(15, 23, 42, 0.25);
    }
</style>

<div class="animate-in purchase-create max-w-7xl mx-auto pb-4" x-data="purchaseForm()">
    <div class="page-header mb-5">
        <div>
            <h2 class="page-title text-2xl font-bold text-gray-800">Edit Faktur / PO</h2>
            <p class="page-subtitle text-gray-500 font-mono text-sm">{{ $purchase->reference_no }}</p>
        </div>
        <a wire:navigate href="{{ route('purchases.show', $purchase) }}" class="btn btn-secondary flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            Kembali
        </a>
    </div>

    @if($errors->any())
    <div class="mb-5 p-4 bg-red-50 border border-red-200 rounded-xl text-red-700 text-sm">
        <ul class="list-disc pl-5 space-y-0.5">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <form action="{{ route('purchases.update', $purchase) }}" method="POST" class="space-y-5">
        @csrf
        @method('PUT')

        <section class="rounded-2xl border border-slate-200 bg-white shadow-sm overflow-hidden">
            <div class="px-5 py-3.5 border-b border-slate-100 bg-slate-50/80 flex items-center gap-2.5">
                <div class="w-8 h-8 rounded-lg bg-emerald-600 text-white flex items-center justify-center shrink-0">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                </div>
                <div>
                    <h3 class="text-sm font-extrabold text-slate-800">Informasi Faktur</h3>
                    <p class="text-[11px] text-slate-400">Ubah data faktur lalu sesuaikan item di bawah</p>
                </div>
            </div>

            <div class="p-5 grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4">
                <div class="sm:col-span-2 xl:col-span-1">
                    <label class="po-label">No. Faktur <span class="text-rose-500">*</span></label>
                    <input type="text" name="reference_no" value="{{ old('reference_no', $purchase->reference_no) }}" class="po-input font-mono font-semibold tracking-tight" required>
                </div>
                <div>
                    <label class="po-label">Supplier <span class="text-rose-500">*</span></label>
                    <select name="supplier_id" class="po-input" required>
                        <option value="">Pilih supplier…</option>
                        @foreach($suppliers as $s)
                        <option value="{{ $s->id }}" {{ old('supplier_id', $purchase->supplier_id) == $s->id ? 'selected' : '' }}>{{ $s->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="po-label">Tanggal Masuk <span class="text-rose-500">*</span></label>
                    <input type="date" name="purchase_date" value="{{ old('purchase_date', $purchase->purchase_date->format('Y-m-d')) }}" class="po-input" required>
                </div>
                <div>
                    <label class="po-label">Status <span class="text-rose-500">*</span></label>
                    <select name="status" class="po-input" required>
                        <option value="received" {{ old('status', $purchase->status) === 'received' ? 'selected' : '' }}>Diterima (Stok +)</option>
                        <option value="sent" {{ old('status', $purchase->status) === 'sent' ? 'selected' : '' }}>Dikirim (PO)</option>
                        <option value="draft" {{ old('status', $purchase->status) === 'draft' ? 'selected' : '' }}>Draft</option>
                    </select>
                </div>
                <div class="sm:col-span-2 xl:col-span-4">
                    <label class="po-label">Catatan (opsional)</label>
                    <input type="text" name="notes" value="{{ old('notes', $purchase->notes) }}" class="po-input" placeholder="Catatan singkat untuk faktur ini…">
                </div>
            </div>
        </section>

        <section class="rounded-2xl border border-slate-200 bg-white shadow-sm overflow-hidden">
            <div class="px-5 py-3.5 border-b border-slate-100 bg-slate-50/80 flex flex-wrap items-center justify-between gap-3">
                <div class="flex items-center gap-2.5">
                    <div class="w-8 h-8 rounded-lg bg-sky-600 text-white flex items-center justify-center shrink-0">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                    </div>
                    <div>
                        <h3 class="text-sm font-extrabold text-slate-800">Detail Item Barang</h3>
                        <p class="text-[11px] text-slate-400">Cari produk, isi qty & harga — nominal otomatis berformat titik</p>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-emerald-50 text-emerald-700 border border-emerald-100 text-[11px] font-bold">
                        <span x-text="filledCount()"></span> produk dipilih
                    </span>
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-slate-100 text-slate-600 text-[11px] font-bold">
                        <span x-text="items.length"></span> baris
                    </span>
                </div>
            </div>

            <div class="p-4 sm:p-5 space-y-3">
                <template x-for="(item, index) in items" :key="item.key">
                    <div class="po-item" :class="item.product_id ? 'is-selected' : ''">
                        <div class="po-item-head">
                            <div class="flex items-center gap-2 min-w-0">
                                <span class="shrink-0 w-7 h-7 rounded-lg bg-slate-100 text-slate-600 text-xs font-extrabold flex items-center justify-center" x-text="index + 1"></span>
                                <div class="min-w-0">
                                    <p class="text-xs font-bold text-slate-700 truncate" x-text="item.product_id ? item.product_name : 'Belum pilih produk'"></p>
                                    <p class="text-[10px] text-slate-400 font-medium" x-show="item.product_id">
                                        Satuan: <span class="text-slate-600 font-bold" x-text="item.unit_name"></span>
                                    </p>
                                </div>
                            </div>
                            <div class="flex items-center gap-2 shrink-0">
                                <span class="hidden sm:inline-flex text-[11px] font-extrabold text-emerald-700 bg-emerald-50 border border-emerald-100 px-2.5 py-1 rounded-lg tabular-nums"
                                      x-text="formatRupiah(lineSubtotal(item))"></span>
                                <button type="button" @click="removeItem(index)"
                                        class="p-1.5 rounded-lg text-slate-400 hover:text-rose-600 hover:bg-rose-50 transition-colors disabled:opacity-30"
                                        :disabled="items.length === 1" title="Hapus baris">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                </button>
                            </div>
                        </div>

                        <div class="relative mb-3" x-data="{ openSearch: false, results: [], loading: false, searched: false }">
                            <label class="po-label">Cari Produk <span class="text-rose-500">*</span></label>
                            <input type="hidden" :name="'items['+index+'][product_id]'" :value="item.product_id">
                            <div class="relative" @click.away="openSearch = false">
                                <span class="po-search-icon"
                                      :class="item.product_id ? 'text-emerald-600' : 'text-slate-400'">
                                    <svg x-show="item.product_id" x-cloak class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                    <svg x-show="!item.product_id" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                                </span>
                                <input type="text" data-purchase-product placeholder="Ketik minimal 2 huruf nama / kode produk…"
                                       class="po-input po-search-input"
                                       :class="item.product_id ? 'border-emerald-300 bg-emerald-50/40 font-semibold' : ''"
                                       x-model="item.product_name"
                                       @input="
                                            item.product_id = '';
                                            item.unit_name = '-';
                                            searched = false;
                                       "
                                       @input.debounce.300ms="
                                            if (item.product_name.length > 1) {
                                                loading = true;
                                                openSearch = true;
                                                fetch(`/products/search?q=${encodeURIComponent(item.product_name)}`)
                                                    .then(res => res.json())
                                                    .then(data => { results = data; searched = true; })
                                                    .catch(() => { results = []; searched = true; })
                                                    .finally(() => { loading = false; });
                                            } else {
                                                results = [];
                                                openSearch = false;
                                                loading = false;
                                            }
                                       "
                                       @focus="if (item.product_name.length > 1) openSearch = true" required>
                                <button type="button" x-show="item.product_name" x-cloak
                                        @click="
                                            item.product_id = '';
                                            item.product_name = '';
                                            item.unit_name = '-';
                                            item.purchase_price = 0;
                                            item.sell_price = 0;
                                            results = [];
                                            openSearch = false;
                                            $nextTick(() => $el.previousElementSibling.focus());
                                        "
                                        class="po-search-clear w-5 h-5 rounded-full bg-slate-100 hover:bg-rose-100 text-slate-400 hover:text-rose-600 flex items-center justify-center"
                                        title="Kosongkan produk">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/></svg>
                                </button>

                                <div class="po-search-drop" x-show="openSearch" x-cloak>
                                    <div x-show="loading" class="px-4 py-4 text-center text-xs font-semibold text-slate-500">Mencari produk…</div>
                                    <div x-show="!loading && searched && results.length === 0" class="px-4 py-4 text-center">
                                        <p class="text-xs font-bold text-slate-600">Produk tidak ditemukan</p>
                                        <p class="text-[10px] text-slate-400 mt-1">Coba nama atau kode produk lain</p>
                                    </div>
                                    <template x-for="p in results" :key="p.id">
                                        <button type="button" x-show="!loading" class="po-search-result"
                                                @click="
                                                    item.product_id = p.id;
                                                    item.product_name = p.name;
                                                    item.unit_name = p.unit || 'pcs';
                                                    item.purchase_price = Math.round(parseFloat(p.purchase_price) || 0);
                                                    item.sell_price = Math.round(parseFloat(p.sell_price) || 0);
                                                    openSearch = false;
                                                    searched = false;
                                                ">
                                            <div class="min-w-0">
                                                <p class="text-sm font-bold text-slate-800 truncate" x-text="p.name"></p>
                                                <p class="text-[11px] text-slate-400 font-mono mt-0.5" x-text="p.code"></p>
                                            </div>
                                            <div class="text-right shrink-0">
                                                <p class="text-[11px] font-extrabold text-emerald-600" x-text="'Stok ' + p.stock"></p>
                                                <p class="text-[10px] text-slate-400 mt-0.5" x-text="formatRupiah(p.purchase_price)"></p>
                                            </div>
                                        </button>
                                    </template>
                                </div>
                            </div>
                        </div>

                        <div class="po-item-fields">
                            <div class="po-field-qty">
                                <label class="po-label">Qty <span class="text-rose-500">*</span></label>
                                <input type="hidden" :name="'items['+index+'][quantity]'" :value="item.quantity">
                                <div class="po-qty-stepper">
                                    <button type="button" class="po-qty-btn" @click="decQty(item)" tabindex="-1">−</button>
                                    <input type="number"
                                           class="po-qty-input"
                                           x-model.number="item.quantity"
                                           @input="fixQty(item)" @blur="fixQty(item)"
                                           min="1" required>
                                    <button type="button" class="po-qty-btn" @click="incQty(item)" tabindex="-1">+</button>
                                </div>
                            </div>

                            <div class="po-field-unit">
                                <label class="po-label">Satuan</label>
                                <div class="po-input bg-slate-50 text-slate-600 font-bold text-center flex items-center justify-center overflow-hidden text-ellipsis whitespace-nowrap"
                                     x-text="item.unit_name || '-'"></div>
                            </div>

                            <div class="po-field-buy">
                                <label class="po-label">Harga Beli <span class="text-rose-500">*</span></label>
                                <input type="hidden" :name="'items['+index+'][purchase_price]'" :value="item.purchase_price">
                                <div class="po-money-wrap">
                                    <span class="po-money-prefix">Rp</span>
                                    <input type="text" inputmode="numeric" autocomplete="off" class="po-input po-input-money po-input-with-rp"
                                           :class="item.purchase_price > 0 ? 'text-slate-800' : 'text-slate-400'"
                                           :value="formatMoney(item.purchase_price)"
                                           @focus="$event.target.select()"
                                           @input="item.purchase_price = parseMoney($event.target.value)"
                                           @blur="$event.target.value = formatMoney(item.purchase_price)"
                                           placeholder="0" required>
                                </div>
                            </div>

                            <div class="po-field-sell">
                                <label class="po-label">Harga Jual <span class="text-rose-500">*</span></label>
                                <input type="hidden" :name="'items['+index+'][sell_price]'" :value="item.sell_price">
                                <div class="po-money-wrap">
                                    <span class="po-money-prefix">Rp</span>
                                    <input type="text" inputmode="numeric" autocomplete="off" class="po-input po-input-money po-input-with-rp"
                                           :class="item.sell_price > 0 ? 'text-slate-800' : 'text-slate-400'"
                                           :value="formatMoney(item.sell_price)"
                                           @focus="$event.target.select()"
                                           @input="item.sell_price = parseMoney($event.target.value)"
                                           @blur="$event.target.value = formatMoney(item.sell_price)"
                                           placeholder="0" required>
                                </div>
                            </div>

                            <div class="po-field-expired">
                                <label class="po-label">Expired</label>
                                <input type="date" :name="'items['+index+'][expired_date]'" x-model="item.expired_date" class="po-input">
                            </div>
                        </div>

                        <div class="sm:hidden mt-3 pt-3 border-t border-slate-100 flex justify-between items-center">
                            <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Subtotal</span>
                            <span class="text-sm font-extrabold text-emerald-700 tabular-nums" x-text="formatRupiah(lineSubtotal(item))"></span>
                        </div>
                    </div>
                </template>

                <button type="button" @click="addItem()"
                        class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl border-2 border-dashed border-slate-200 text-slate-600 hover:border-emerald-300 hover:text-emerald-700 hover:bg-emerald-50/40 text-sm font-bold transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    Tambah Baris Produk
                </button>
            </div>
        </section>

        <div class="po-sticky-bar px-4 sm:px-5 py-3.5 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div>
                <p class="text-[10px] font-extrabold uppercase tracking-wider text-slate-400">Grand Total Pembelian</p>
                <p class="text-2xl font-black text-emerald-600 tabular-nums leading-tight" x-text="formatRupiah(grandTotal())"></p>
            </div>
            <div class="flex items-center gap-2 sm:gap-3">
                <a wire:navigate href="{{ route('purchases.show', $purchase) }}" class="btn btn-secondary flex-1 sm:flex-none justify-center">Batal</a>
                <button type="submit" class="btn btn-primary flex-1 sm:flex-none justify-center min-w-[11rem]">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"/></svg>
                    Simpan Perubahan
                </button>
            </div>
        </div>
    </form>
</div>
@endsection
