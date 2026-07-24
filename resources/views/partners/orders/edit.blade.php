@extends('layouts.app')
@section('title', 'Edit PO — ' . $partnerOrder->order_no)
@section('page-title', 'Edit PO Mitra')

@section('breadcrumb')
<svg class="w-3 h-3 text-gray-400 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"/></svg>
<a wire:navigate href="{{ route('partner-orders.index') }}" class="hover:text-primary-600 transition-colors whitespace-nowrap">PO Mitra</a>
<svg class="w-3 h-3 text-gray-400 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"/></svg>
<a wire:navigate href="{{ route('partner-orders.show', $partnerOrder) }}" class="hover:text-primary-600 transition-colors whitespace-nowrap font-mono text-sm truncate max-w-[14rem]">{{ $partnerOrder->order_no }}</a>
<svg class="w-3 h-3 text-gray-400 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"/></svg>
<span class="text-gray-600 font-medium">Edit PO</span>
@endsection

@section('content')
@php
    $statusColors = [
        'submitted' => 'bg-blue-100 text-blue-800 border-blue-200',
        'confirmed' => 'bg-indigo-100 text-indigo-800 border-indigo-200',
        'fulfilled' => 'bg-emerald-100 text-emerald-800 border-emerald-200',
        'cancelled' => 'bg-red-100 text-red-800 border-red-200',
    ];
    $statusClass = $statusColors[$partnerOrder->status] ?? 'bg-slate-100 text-slate-700 border-slate-200';
    $orderTotals = $partnerOrder->totalsBreakdown();
@endphp

<div class="animate-in space-y-5 pb-6">

    {{-- Header Banner --}}
    <div class="rounded-2xl bg-gradient-to-br from-emerald-700 via-emerald-600 to-teal-600 p-5 sm:p-6 text-white shadow-lg relative overflow-hidden">
        <div class="absolute top-0 right-0 w-56 h-56 bg-white/5 rounded-full -translate-y-1/2 translate-x-1/3 pointer-events-none"></div>
        <div class="absolute bottom-0 left-1/3 w-32 h-32 bg-white/5 rounded-full translate-y-1/2 pointer-events-none"></div>
        <div class="relative flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
            <div class="min-w-0">
                <div class="flex items-center gap-2 mb-1">
                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-[10px] font-bold bg-white/20 text-white border border-white/25">
                        <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M2.166 4.999A11.954 11.954 0 0010 1.944 11.954 11.954 0 0017.834 5c.11.65.166 1.32.166 2 0 5.523-3.997 10.114-9.335 11.532C3.998 17.114 0 12.523 0 7c0-.68.057-1.35.166-2.001zm11.541 3.708a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                        Mode Kepala IT
                    </span>
                    <span class="inline-flex px-2.5 py-1 rounded-lg text-[11px] font-bold border {{ $statusClass }}">{{ $partnerOrder->status_label }}</span>
                </div>
                <p class="font-mono text-xs font-bold text-emerald-100/90 tracking-wide">{{ $partnerOrder->order_no }}</p>
                <h2 class="text-xl sm:text-2xl font-extrabold mt-1 leading-tight">Edit PO — {{ $partnerOrder->partner?->name }}</h2>
                <p class="text-emerald-100/80 text-sm mt-1">{{ $partnerOrder->partner?->code }} · {{ $partnerOrder->items->count() }} produk · Rp {{ number_format($partnerOrder->total, 0, ',', '.') }}</p>
            </div>
            <div class="flex gap-2 shrink-0">
                <a wire:navigate href="{{ route('partner-orders.show', $partnerOrder) }}"
                   class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-white/15 text-white text-sm font-bold border border-white/25 hover:bg-white/25 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                    Lihat Detail
                </a>
                <a wire:navigate href="{{ route('partner-orders.index') }}"
                   class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-white text-emerald-700 text-sm font-bold shadow-md hover:bg-emerald-50 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                    Daftar PO
                </a>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 xl:grid-cols-12 gap-5 items-start">

        {{-- Kolom Kiri: Daftar Item + Tambah Item --}}
        <div class="xl:col-span-8 space-y-5 min-w-0">

            {{-- Flash Messages --}}
            @if(session('toast_success'))
            <div class="flex items-center gap-3 px-4 py-3 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800">
                <svg class="w-5 h-5 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <span class="text-sm font-semibold">{{ session('toast_success') }}</span>
            </div>
            @endif
            @if(session('toast_error'))
            <div class="flex items-center gap-3 px-4 py-3 rounded-xl bg-red-50 border border-red-200 text-red-800">
                <svg class="w-5 h-5 text-red-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <span class="text-sm font-semibold">{{ session('toast_error') }}</span>
            </div>
            @endif

            {{-- Daftar Item PO --}}
            <div class="card bg-white border border-gray-100 rounded-2xl shadow-sm overflow-hidden">
                <div class="px-5 py-4 border-b border-gray-100 bg-gradient-to-r from-slate-50 to-gray-50/50 flex items-center justify-between">
                    <div>
                        <h3 class="text-sm font-bold text-gray-800">Daftar Item PO</h3>
                        <p class="text-[11px] text-gray-400 mt-0.5">{{ $partnerOrder->items->count() }} produk &middot; Total: <span class="font-bold text-emerald-700">Rp {{ number_format($orderTotals['grand_total'], 0, ',', '.') }}</span></p>
                    </div>
                    <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-[11px] font-bold bg-emerald-100 text-emerald-700">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                        {{ $partnerOrder->items->count() }} item
                    </span>
                </div>

                @if($partnerOrder->items->isEmpty())
                <div class="py-12 text-center text-gray-400">
                    <svg class="w-10 h-10 mx-auto mb-3 text-gray-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/></svg>
                    <p class="text-sm font-semibold">Belum ada item di PO ini</p>
                    <p class="text-xs mt-1">Tambahkan item menggunakan form di bawah.</p>
                </div>
                @else
                <div class="overflow-x-auto">
                    <table class="w-full min-w-[700px] text-sm">
                        <thead>
                            <tr class="bg-slate-50 border-b border-gray-100">
                                <th class="px-4 py-3 text-left text-[11px] font-bold uppercase tracking-wider text-gray-500 w-8">#</th>
                                <th class="px-4 py-3 text-left text-[11px] font-bold uppercase tracking-wider text-gray-500">Produk</th>
                                <th class="px-4 py-3 text-center text-[11px] font-bold uppercase tracking-wider text-gray-500">Tipe</th>
                                <th class="px-4 py-3 text-right text-[11px] font-bold uppercase tracking-wider text-gray-500">Qty</th>
                                <th class="px-4 py-3 text-right text-[11px] font-bold uppercase tracking-wider text-gray-500">Harga</th>
                                <th class="px-4 py-3 text-right text-[11px] font-bold uppercase tracking-wider text-gray-500">Subtotal</th>
                                <th class="px-4 py-3 text-center text-[11px] font-bold uppercase tracking-wider text-gray-500 w-20">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50">
                            @foreach($partnerOrder->items as $i => $item)
                            @php $meta = $item->catalogDisplay(); @endphp
                            <tr class="hover:bg-emerald-50/30 transition-colors group">
                                <td class="px-4 py-3 text-xs text-gray-400 font-semibold">{{ $i + 1 }}</td>
                                <td class="px-4 py-3">
                                    <p class="font-semibold text-gray-800 leading-snug">{{ $item->product_name }}</p>
                                    <p class="text-[10px] text-gray-400 font-mono mt-0.5">{{ $meta['code'] }} &middot; {{ $meta['unit'] }}</p>
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <span class="inline-flex px-2 py-0.5 rounded-md text-[10px] font-bold {{ $item->price_type === 'grosir' ? 'bg-amber-100 text-amber-800' : 'bg-blue-50 text-blue-700' }}">
                                        {{ $item->price_type_label }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-right font-bold text-gray-800">{{ $item->quantity }}</td>
                                <td class="px-4 py-3 text-right text-gray-600 whitespace-nowrap">Rp {{ number_format($item->unit_price, 0, ',', '.') }}</td>
                                <td class="px-4 py-3 text-right font-bold text-emerald-700 whitespace-nowrap">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</td>
                                <td class="px-4 py-3 text-center">
                                    <form action="{{ route('partner-orders.items.remove', [$partnerOrder, $item]) }}" method="POST"
                                          onsubmit="return confirm('Hapus item \"{{ addslashes($item->product_name) }}\" dari PO ini?')">
                                        @csrf @method('DELETE')
                                        <button type="submit"
                                            class="inline-flex items-center justify-center w-7 h-7 rounded-lg bg-red-50 text-red-500 hover:bg-red-100 hover:text-red-700 transition-colors"
                                            title="Hapus item ini">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                            </svg>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="border-t-2 border-gray-100">
                            @if($orderTotals['discount_amount'] > 0)
                            <tr>
                                <td colspan="5" class="px-4 py-2 text-right text-xs text-gray-500">Subtotal</td>
                                <td colspan="2" class="px-4 py-2 text-right text-sm font-semibold text-gray-700">Rp {{ number_format($orderTotals['subtotal'], 0, ',', '.') }}</td>
                            </tr>
                            <tr>
                                <td colspan="5" class="px-4 py-1 text-right text-xs text-gray-500">Diskon</td>
                                <td colspan="2" class="px-4 py-1 text-right text-sm font-semibold text-red-600">-Rp {{ number_format($orderTotals['discount_amount'], 0, ',', '.') }}</td>
                            </tr>
                            @endif
                            @if($orderTotals['ppn_enabled'])
                            <tr>
                                <td colspan="5" class="px-4 py-1 text-right text-xs text-gray-500">PPN {{ rtrim(rtrim(number_format($orderTotals['ppn_percent'], 2, ',', '.'), '0'), ',') }}%</td>
                                <td colspan="2" class="px-4 py-1 text-right text-sm font-semibold text-gray-700">Rp {{ number_format($orderTotals['ppn_amount'], 0, ',', '.') }}</td>
                            </tr>
                            @endif
                            <tr class="bg-gradient-to-r from-emerald-50/60 to-teal-50/30">
                                <td colspan="5" class="px-4 py-3.5 text-right text-sm font-bold text-gray-600 uppercase tracking-wide">Total PO</td>
                                <td colspan="2" class="px-4 py-3.5 text-right text-lg font-extrabold text-emerald-700 whitespace-nowrap">Rp {{ number_format($orderTotals['grand_total'], 0, ',', '.') }}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
                @endif
            </div>

            {{-- Form Tambah Item --}}
            <div class="card bg-white border border-gray-100 rounded-2xl shadow-sm overflow-hidden">
                <div class="px-5 py-4 border-b border-gray-100 bg-gradient-to-r from-emerald-50 to-teal-50/50">
                    <h3 class="text-sm font-bold text-emerald-800 flex items-center gap-2">
                        <span class="inline-flex w-6 h-6 rounded-lg bg-emerald-600 items-center justify-center">
                            <svg class="w-3.5 h-3.5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                        </span>
                        Tambah Item Baru ke PO
                    </h3>
                    <p class="text-[11px] text-emerald-600 mt-1 ml-8">Pilih produk dari katalog aktif, lalu tentukan qty dan harga.</p>
                </div>
                <div class="p-5">
                    <form action="{{ route('partner-orders.items.add', $partnerOrder) }}" method="POST" id="addItemForm">
                        @csrf
                        @if($errors->any())
                        <div class="mb-4 p-3 rounded-xl bg-red-50 border border-red-200">
                            <ul class="text-xs text-red-700 space-y-1">
                                @foreach($errors->all() as $e)
                                <li class="flex items-center gap-1"><svg class="w-3 h-3 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg> {{ $e }}</li>
                                @endforeach
                            </ul>
                        </div>
                        @endif

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            {{-- Pilih Produk --}}
                            <div class="sm:col-span-2">
                                <label class="block text-xs font-bold text-gray-600 mb-1.5">
                                    Produk <span class="text-red-500">*</span>
                                </label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-3 flex items-center pointer-events-none">
                                        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                                    </div>
                                    <input type="text" id="productSearch" placeholder="Ketik nama produk untuk mencari..."
                                           class="form-input pl-10 text-sm w-full" autocomplete="off">
                                    <input type="hidden" name="product_id" id="productId" value="{{ old('product_id') }}" required>
                                    <div id="productDropdown"
                                         class="hidden absolute z-20 top-full left-0 right-0 mt-1 bg-white border border-gray-200 rounded-xl shadow-xl max-h-56 overflow-y-auto">
                                    </div>
                                </div>
                                <div id="selectedProduct" class="hidden mt-2 flex items-center gap-2 px-3 py-2 rounded-lg bg-emerald-50 border border-emerald-200">
                                    <svg class="w-4 h-4 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                    <span id="selectedProductName" class="text-xs font-semibold text-emerald-800 truncate"></span>
                                    <button type="button" onclick="clearProduct()" class="ml-auto text-emerald-500 hover:text-red-500 shrink-0">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                    </button>
                                </div>
                            </div>

                            {{-- Qty --}}
                            <div>
                                <label class="block text-xs font-bold text-gray-600 mb-1.5">
                                    Qty <span class="text-red-500">*</span>
                                </label>
                                <input type="number" name="quantity" min="1" value="{{ old('quantity', 1) }}" required
                                       class="form-input text-sm w-full" placeholder="1">
                            </div>

                            {{-- Harga --}}
                            <div>
                                <label class="block text-xs font-bold text-gray-600 mb-1.5">
                                    Harga Satuan (Rp) <span class="text-red-500">*</span>
                                </label>
                                <input type="number" name="unit_price" min="0" step="1" value="{{ old('unit_price') }}" required
                                       class="form-input text-sm w-full" placeholder="0" id="unitPriceInput">
                            </div>

                            {{-- Tipe Harga (Auto) --}}
                            @php
                                $resolvedPriceMode = $partnerOrder->payment_method === \App\Models\PartnerOrder::PAY_INVOICE 
                                    ? 'grosir' 
                                    : ($partnerOrder->price_mode_snapshot ?? 'eceran');
                            @endphp
                            <div>
                                <label class="block text-xs font-bold text-gray-600 mb-1.5">
                                    Tipe Harga <span class="text-red-500">*</span>
                                </label>
                                <input type="text" class="form-input text-sm w-full bg-slate-100 text-slate-600 cursor-not-allowed capitalize font-semibold" 
                                       value="{{ $resolvedPriceMode === 'grosir' ? 'Grosir (Sesuai PO)' : 'Eceran (Sesuai PO)' }}" disabled>
                                <input type="hidden" name="price_type" value="{{ $resolvedPriceMode }}">
                            </div>

                            {{-- Preview Subtotal --}}
                            <div>
                                <label class="block text-xs font-bold text-gray-600 mb-1.5">Subtotal (otomatis)</label>
                                <div class="form-input text-sm w-full bg-gray-50 text-emerald-700 font-bold" id="subtotalPreview">—</div>
                            </div>
                        </div>

                        <div class="mt-5 flex items-center gap-3">
                            <button type="submit" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl text-sm font-bold text-white shadow-md transition-all"
                                    style="background:linear-gradient(135deg,#059669 0%,#10b981 100%);box-shadow:0 6px 18px -8px rgba(5,150,105,0.7);">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                                Tambah Item ke PO
                            </button>
                            <button type="reset" onclick="clearProduct()" class="px-4 py-2.5 rounded-xl text-sm font-semibold text-gray-600 bg-gray-100 hover:bg-gray-200 transition-colors">
                                Reset
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- Kolom Kanan: Info PO + Update Header + Hapus PO --}}
        <div class="xl:col-span-4 space-y-4 xl:sticky xl:top-4">

            {{-- Info Ringkas PO --}}
            <div class="card bg-white border border-gray-100 rounded-2xl shadow-sm overflow-hidden">
                <div class="px-5 py-3 border-b border-gray-100 bg-slate-50/80">
                    <h3 class="text-xs font-bold uppercase tracking-wider text-gray-500">Informasi PO</h3>
                </div>
                <div class="p-5 space-y-3 text-sm">
                    <div class="flex justify-between gap-2">
                        <span class="text-gray-500">Mitra</span>
                        <span class="font-bold text-gray-800 text-right">{{ $partnerOrder->partner?->name }}</span>
                    </div>
                    <div class="flex justify-between gap-2">
                        <span class="text-gray-500">Metode Bayar</span>
                        <span class="font-semibold text-gray-700">{{ $partnerOrder->payment_method_label }}</span>
                    </div>
                    <div class="flex justify-between gap-2">
                        <span class="text-gray-500">Status</span>
                        <span class="inline-flex px-2 py-0.5 rounded-md text-[10px] font-bold border {{ $statusClass }}">{{ $partnerOrder->status_label }}</span>
                    </div>
                    <div class="flex justify-between gap-2">
                        <span class="text-gray-500">Dibuat</span>
                        <span class="font-semibold text-gray-700">{{ $partnerOrder->created_at?->format('d/m/Y H:i') }}</span>
                    </div>
                    @if($partnerOrder->due_date)
                    <div class="flex justify-between gap-2">
                        <span class="text-gray-500">Jatuh Tempo</span>
                        <span class="font-semibold {{ $partnerOrder->isCreditOverdue() ? 'text-red-600' : 'text-amber-700' }}">{{ $partnerOrder->due_date->format('d/m/Y') }}</span>
                    </div>
                    @endif
                    <div class="pt-2 border-t border-gray-100 flex justify-between gap-2">
                        <span class="text-gray-500 font-semibold">Total PO</span>
                        <span class="text-lg font-extrabold text-emerald-700">Rp {{ number_format($orderTotals['grand_total'], 0, ',', '.') }}</span>
                    </div>
                </div>
            </div>

            {{-- Form Update Header PO --}}
            <form action="{{ route('partner-orders.update', $partnerOrder) }}" method="POST"
                  class="card bg-white border border-gray-100 rounded-2xl shadow-sm overflow-hidden">
                @csrf @method('PUT')
                <div class="px-5 py-3 border-b border-gray-100 bg-slate-50/80">
                    <h3 class="text-xs font-bold uppercase tracking-wider text-gray-500">Update Data PO</h3>
                </div>
                <div class="p-5 space-y-4">
                    <div>
                        <label class="block text-xs font-bold text-gray-600 mb-1.5">Jatuh Tempo</label>
                        <input type="date" name="due_date"
                               value="{{ old('due_date', $partnerOrder->due_date?->format('Y-m-d')) }}"
                               class="form-input text-sm w-full">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-600 mb-1.5">Catatan Mitra</label>
                        <textarea name="notes" rows="2" class="form-input text-sm w-full resize-none"
                                  placeholder="Catatan dari mitra...">{{ old('notes', $partnerOrder->notes) }}</textarea>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-600 mb-1.5">Catatan Admin (Internal)</label>
                        <textarea name="admin_notes" rows="3" class="form-input text-sm w-full resize-none"
                                  placeholder="Catatan internal admin...">{{ old('admin_notes', $partnerOrder->admin_notes) }}</textarea>
                    </div>
                    <button type="submit"
                            class="w-full inline-flex items-center justify-center gap-2 py-2.5 rounded-xl text-sm font-bold text-white transition-all"
                            style="background:linear-gradient(135deg,#0f766e,#059669);box-shadow:0 4px 14px -6px rgba(5,150,105,0.6);">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        Simpan Perubahan
                    </button>
                </div>
            </form>

            {{-- Zona Berbahaya: Hapus PO --}}
            <div class="card bg-white border border-red-100 rounded-2xl shadow-sm overflow-hidden">
                <div class="px-5 py-3 border-b border-red-100 bg-red-50/60">
                    <h3 class="text-xs font-bold uppercase tracking-wider text-red-600 flex items-center gap-1.5">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                        Zona Berbahaya
                    </h3>
                </div>
                <div class="p-5">
                    <p class="text-xs text-red-600 mb-3 leading-relaxed">
                        Hapus PO <span class="font-bold font-mono">{{ $partnerOrder->order_no }}</span> beserta seluruh itemnya secara permanen.
                        <span class="font-bold">Tindakan ini tidak dapat dibatalkan.</span>
                    </p>
                    <form action="{{ route('partner-orders.destroy', $partnerOrder) }}" method="POST"
                          onsubmit="return confirm('HAPUS PERMANEN PO {{ $partnerOrder->order_no }}?\n\nSeluruh item akan ikut terhapus.\nTindakan ini TIDAK DAPAT dibatalkan!')">
                        @csrf @method('DELETE')
                        <button type="submit"
                                class="w-full inline-flex items-center justify-center gap-2 py-2.5 rounded-xl text-sm font-bold text-white transition-all hover:opacity-90"
                                style="background:linear-gradient(135deg,#991b1b,#dc2626);">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                            Hapus PO Permanen
                        </button>
                    </form>
                </div>
            </div>

        </div>
    </div>
</div>
@endsection

@push('scripts')
@php
    $mappedProducts = $products->map(function($p) {
        return [
            'id'    => $p->id,
            'name'  => $p->name,
            'code'  => $p->code ?? '',
            'unit'  => $p->unit?->name ?? '',
            'eceran'=> (float)($p->sell_price ?? 0),
            'grosir'=> (float)($p->wholesale_price ?? $p->sell_price ?? 0),
        ];
    })->values()->all();
@endphp
<script>
// Data produk untuk pencarian
const allProducts = @json($mappedProducts);

const searchInput  = document.getElementById('productSearch');
const dropdown     = document.getElementById('productDropdown');
const productIdInp = document.getElementById('productId');
const selectedDiv  = document.getElementById('selectedProduct');
const selectedName = document.getElementById('selectedProductName');
const unitPriceInp = document.getElementById('unitPriceInput');
const qtyInp       = document.querySelector('input[name="quantity"]');
const priceTypeInp = document.querySelector('input[name="price_type"]');
const subtotalPrev = document.getElementById('subtotalPreview');

function formatRp(n) {
    return 'Rp ' + Math.round(n).toLocaleString('id-ID');
}

function updateSubtotal() {
    const qty = parseFloat(qtyInp?.value) || 0;
    const price = parseFloat(unitPriceInp?.value) || 0;
    subtotalPrev.textContent = qty > 0 && price > 0 ? formatRp(qty * price) : '—';
}

qtyInp?.addEventListener('input', updateSubtotal);
unitPriceInp?.addEventListener('input', updateSubtotal);

function renderDropdown(filtered) {
    if (!filtered.length) {
        dropdown.innerHTML = '<div class="px-4 py-3 text-xs text-gray-400 text-center">Produk tidak ditemukan</div>';
    } else {
        dropdown.innerHTML = filtered.slice(0, 20).map(p => `
            <div class="flex items-center gap-3 px-4 py-2.5 hover:bg-emerald-50 cursor-pointer transition-colors border-b border-gray-50 last:border-0"
                 onclick="selectProduct(${p.id}, '${p.name.replace(/'/g,"\\'")}', ${p.eceran}, ${p.grosir})">
                <div class="w-6 h-6 rounded-md bg-emerald-100 flex items-center justify-center shrink-0">
                    <svg class="w-3.5 h-3.5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10"/></svg>
                </div>
                <div class="min-w-0">
                    <p class="text-sm font-semibold text-gray-800 truncate">${p.name}</p>
                    <p class="text-[10px] text-gray-400 font-mono">${p.code} &middot; ${p.unit}</p>
                </div>
                <div class="ml-auto text-right shrink-0">
                    <p class="text-xs font-bold text-emerald-700">${formatRp(p.eceran)}</p>
                </div>
            </div>
        `).join('');
    }
    dropdown.classList.remove('hidden');
}

searchInput?.addEventListener('input', function() {
    const q = this.value.toLowerCase().trim();
    if (!q) { dropdown.classList.add('hidden'); return; }
    const filtered = allProducts.filter(p =>
        p.name.toLowerCase().includes(q) || p.code.toLowerCase().includes(q)
    );
    renderDropdown(filtered);
});

searchInput?.addEventListener('focus', function() {
    if (this.value.trim().length > 0) {
        const filtered = allProducts.filter(p =>
            p.name.toLowerCase().includes(this.value.toLowerCase()) ||
            p.code.toLowerCase().includes(this.value.toLowerCase())
        );
        renderDropdown(filtered);
    }
});

function selectProduct(id, name, eceranPrice, grosirPrice) {
    productIdInp.value = id;
    searchInput.value  = name;
    selectedName.textContent = name;
    selectedDiv.classList.remove('hidden');
    searchInput.classList.add('hidden');
    dropdown.classList.add('hidden');

    // Auto-isi harga sesuai tipe
    const tipe = priceTypeInp?.value || 'eceran';
    unitPriceInp.value = tipe === 'grosir' ? grosirPrice : eceranPrice;
    updateSubtotal();
}

priceTypeInp?.addEventListener('change', function() {
    const id = productIdInp.value;
    if (!id) return;
    const p = allProducts.find(x => x.id == id);
    if (!p) return;
    unitPriceInp.value = this.value === 'grosir' ? p.grosir : p.eceran;
    updateSubtotal();
});

function clearProduct() {
    productIdInp.value = '';
    searchInput.value  = '';
    searchInput.classList.remove('hidden');
    selectedDiv.classList.add('hidden');
    dropdown.classList.add('hidden');
    unitPriceInp.value = '';
    subtotalPrev.textContent = '—';
}

// Tutup dropdown saat klik di luar
document.addEventListener('click', function(e) {
    if (!searchInput?.contains(e.target) && !dropdown?.contains(e.target)) {
        dropdown?.classList.add('hidden');
    }
});
</script>
@endpush
