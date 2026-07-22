@extends('layouts.app')
@section('title', $order->order_no)
@section('page-title', 'Detail PO Mitra')

@section('breadcrumb')
<svg class="w-3 h-3 text-gray-400 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"/></svg>
<a wire:navigate href="{{ route('partner-orders.index') }}" class="hover:text-primary-600 transition-colors whitespace-nowrap">PO Mitra</a>
<svg class="w-3 h-3 text-gray-400 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"/></svg>
<span class="text-gray-600 font-medium truncate max-w-[16rem] sm:max-w-none font-mono text-sm" title="{{ $order->order_no }}">{{ $order->order_no }}</span>
@endsection

@section('content')
@php
    $statusColors = [
        'submitted' => 'bg-blue-100 text-blue-800 border-blue-200',
        'confirmed' => 'bg-indigo-100 text-indigo-800 border-indigo-200',
        'fulfilled' => 'bg-emerald-100 text-emerald-800 border-emerald-200',
        'cancelled' => 'bg-red-100 text-red-800 border-red-200',
    ];
    $payStatusColors = [
        'unpaid'    => 'bg-amber-100 text-amber-800 border-amber-200',
        'awaiting_confirmation' => 'bg-orange-100 text-orange-800 border-orange-200',
        'paid'      => 'bg-emerald-100 text-emerald-800 border-emerald-200',
        'cancelled' => 'bg-slate-100 text-slate-600 border-slate-200',
    ];
    $statusClass = $statusColors[$order->status] ?? 'bg-slate-100 text-slate-700 border-slate-200';
    $payStatusClass = $payStatusColors[$order->payment_status] ?? 'bg-slate-100 text-slate-700 border-slate-200';
    $itemCount = $order->items->count();
    $qtyTotal = $order->items->sum('quantity');
@endphp

<div class="animate-in space-y-5 pb-4">

    {{-- Header PO --}}
    <div class="rounded-2xl bg-gradient-to-br from-emerald-700 via-emerald-600 to-teal-600 p-5 sm:p-6 text-white shadow-lg shadow-emerald-900/10 relative overflow-hidden">
        <div class="absolute top-0 right-0 w-56 h-56 bg-white/5 rounded-full -translate-y-1/2 translate-x-1/3 pointer-events-none"></div>
        <div class="relative flex flex-col lg:flex-row lg:items-start lg:justify-between gap-4">
            <div class="min-w-0 flex-1">
                <p class="font-mono text-xs font-bold text-emerald-100/90 tracking-wide">{{ $order->order_no }}</p>
                <h2 class="text-xl sm:text-2xl font-extrabold mt-1 leading-tight break-words">{{ $order->partner?->name }}</h2>
                <p class="text-emerald-50/90 text-sm mt-1">{{ $order->partner?->code }} · {{ $order->partner?->type_label }}</p>
                <div class="flex flex-wrap items-center gap-2 mt-3">
                    <span class="inline-flex px-2.5 py-1 rounded-lg text-[11px] font-bold border {{ $statusClass }}">{{ $order->status_label }}</span>
                    <span class="inline-flex px-2.5 py-1 rounded-lg text-[11px] font-bold border {{ $payStatusClass }}">{{ $order->payment_status_label }}</span>
                    <span class="inline-flex px-2.5 py-1 rounded-lg text-[11px] font-bold bg-white/15 text-white border border-white/20">{{ $order->payment_method_label }}</span>
                </div>
            </div>
            <div class="flex flex-col sm:flex-row lg:flex-col items-stretch sm:items-end gap-3 shrink-0">
                <div class="text-left sm:text-right lg:text-right text-sm">
                    <p class="text-emerald-100/80 text-[11px] font-semibold uppercase tracking-wider">Diajukan</p>
                    <p class="font-bold">{{ $order->created_at?->format('d/m/Y H:i') }}</p>
                </div>
                <a wire:navigate href="{{ route('partner-orders.index') }}"
                   class="inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl bg-white text-emerald-700 text-sm font-bold shadow-md hover:bg-emerald-50 transition-colors whitespace-nowrap">
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                    Kembali ke Daftar PO
                </a>
            </div>
        </div>
    </div>

    {{-- Ringkasan angka --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3">
        <div class="card bg-white border border-gray-100 rounded-2xl p-4 shadow-sm">
            <p class="text-[10px] font-bold uppercase tracking-wider text-gray-400">Jenis Produk</p>
            <p class="text-2xl font-extrabold text-gray-800 mt-1">{{ $itemCount }}</p>
        </div>
        <div class="card bg-white border border-gray-100 rounded-2xl p-4 shadow-sm">
            <p class="text-[10px] font-bold uppercase tracking-wider text-gray-400">Total Qty</p>
            <p class="text-2xl font-extrabold text-gray-800 mt-1">{{ $qtyTotal }}</p>
        </div>
        <div class="card bg-white border border-gray-100 rounded-2xl p-4 shadow-sm col-span-2 lg:col-span-1">
            <p class="text-[10px] font-bold uppercase tracking-wider text-gray-400">Total PO</p>
            <p class="text-xl sm:text-2xl font-extrabold text-emerald-700 mt-1">Rp {{ number_format($order->total, 0, ',', '.') }}</p>
        </div>
        <div class="card bg-white border border-gray-100 rounded-2xl p-4 shadow-sm col-span-2 lg:col-span-1">
            <p class="text-[10px] font-bold uppercase tracking-wider text-gray-400">Skema Harga</p>
            <p class="text-sm font-bold text-gray-800 mt-1 capitalize">{{ str_replace('_', ' ', $order->price_mode_snapshot ?? '-') }}</p>
            @if($order->due_date)
            <p class="text-[11px] text-amber-700 font-semibold mt-1 {{ $order->isCreditOverdue() ? 'text-red-600' : '' }}">
                Jatuh tempo {{ $order->due_date->format('d/m/Y') }}
            </p>
            @endif
        </div>
    </div>

    <div class="grid grid-cols-1 xl:grid-cols-12 gap-5 items-start">

        {{-- Kolom kiri: pembayaran + item --}}
        <div class="xl:col-span-8 space-y-5 min-w-0">

            {{-- Info Pembayaran --}}
            <div class="card bg-white border border-gray-100 rounded-2xl shadow-sm overflow-hidden">
                <div class="px-5 py-3 border-b border-gray-100 bg-slate-50/80">
                    <h3 class="text-xs font-bold uppercase tracking-wider text-gray-500">Informasi Pembayaran</h3>
                </div>
                <div class="p-5">
                    @if($order->payment_method === 'transfer')
                    <div class="space-y-4">
                        {{-- Rekening --}}
                        <div class="rounded-xl border border-emerald-200 bg-emerald-50/50 p-4">
                            <p class="text-[11px] font-extrabold text-emerald-800 uppercase tracking-wider mb-3">Rekening Transfer</p>
                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-2.5">
                                <div class="rounded-xl bg-white border border-emerald-100/90 px-3.5 py-3">
                                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wide">Bank</p>
                                    <p class="text-sm font-bold text-slate-800 mt-1">{{ $bankName }}</p>
                                </div>
                                <div class="rounded-xl bg-white border border-emerald-100/90 px-3.5 py-3">
                                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wide">No. Rekening</p>
                                    <div class="flex items-center justify-between gap-2 mt-1">
                                        <p id="poBankAccount" class="text-sm font-extrabold text-emerald-700 tracking-wide truncate">{{ $bankAccount }}</p>
                                        <button type="button" onclick="copyPoText('poBankAccount','Nomor rekening')"
                                                class="shrink-0 text-[10px] font-bold text-emerald-700 hover:text-emerald-900 px-2 py-1 rounded-md bg-emerald-50 border border-emerald-100">Salin</button>
                                    </div>
                                </div>
                                <div class="rounded-xl bg-white border border-emerald-100/90 px-3.5 py-3">
                                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wide">Atas Nama</p>
                                    <p class="text-sm font-bold text-slate-800 mt-1 break-words leading-snug">{{ $bankHolder }}</p>
                                </div>
                            </div>
                        </div>

                        {{-- Status bukti (jika sudah ada) --}}
                        @if($order->transfer_proof)
                        <div class="rounded-xl border border-emerald-200 bg-white px-4 py-3.5 flex flex-wrap items-center gap-3">
                            <div class="w-9 h-9 rounded-xl bg-emerald-100 text-emerald-700 flex items-center justify-center shrink-0">
                                <svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            </div>
                            <div class="min-w-0 flex-1">
                                <p class="text-sm font-bold text-slate-800">Bukti sudah terlampir</p>
                                <p class="text-[11px] text-slate-500 mt-0.5">{{ $order->transfer_proof_at?->format('d/m/Y H:i') ?? '—' }}</p>
                            </div>
                            @if($order->payment_status === 'awaiting_confirmation')
                            <span class="text-[10px] font-extrabold px-2.5 py-1 rounded-full bg-amber-50 text-amber-700 border border-amber-100">Menunggu konfirmasi</span>
                            @endif
                            <a href="{{ asset('storage/' . $order->transfer_proof) }}" target="_blank" rel="noopener"
                               class="inline-flex items-center gap-1.5 text-xs font-bold text-emerald-700 hover:text-emerald-900 px-3 py-1.5 rounded-lg bg-emerald-50 border border-emerald-100">
                                Lihat file
                            </a>
                        </div>
                        @endif

                        {{-- Form unggah + tombol simpan eksplisit --}}
                        @if($order->canUploadProof())
                        <div class="rounded-xl border border-sky-200 bg-white shadow-sm">
                            <div class="px-4 py-3 border-b border-sky-100 bg-sky-50 flex items-center gap-3 rounded-t-xl">
                                <div class="w-9 h-9 rounded-xl bg-sky-600 text-white flex items-center justify-center shrink-0">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                                </div>
                                <div class="min-w-0">
                                    <h4 class="text-sm font-extrabold text-slate-800">
                                        {{ $order->transfer_proof ? 'Ganti bukti transfer' : 'Lampirkan bukti transfer' }}
                                    </h4>
                                    <p class="text-[11px] text-slate-500 mt-0.5">Pilih file, lalu tekan Simpan di bawah</p>
                                </div>
                            </div>

                            <form action="{{ route('partner-orders.proof', $order) }}" method="POST" enctype="multipart/form-data"
                                  class="p-4 space-y-4" id="po-transfer-proof-form">
                                @csrf

                                <div>
                                    <label for="po_transfer_proof" class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-1.5">File bukti</label>
                                    <input type="file"
                                           id="po_transfer_proof"
                                           name="transfer_proof"
                                           accept=".jpg,.jpeg,.png,.webp,.pdf"
                                           required
                                           class="block w-full text-sm text-slate-700
                                                  file:mr-3 file:py-2.5 file:px-4 file:rounded-lg file:border-0
                                                  file:bg-sky-600 file:text-white file:text-xs file:font-bold file:cursor-pointer
                                                  hover:file:bg-sky-700
                                                  border border-slate-200 rounded-xl bg-slate-50 px-3 py-2.5
                                                  focus:outline-none focus:ring-2 focus:ring-sky-100 focus:border-sky-400">
                                    <p id="po_transfer_proof_name" class="text-[11px] font-semibold text-sky-700 mt-1.5 hidden"></p>
                                    <p class="text-[10px] text-slate-400 mt-1.5">Format: JPG, PNG, WEBP, PDF · Maksimal 4 MB</p>
                                    @error('transfer_proof')
                                    <p class="text-xs font-semibold text-red-600 mt-1.5">{{ $message }}</p>
                                    @enderror
                                </div>

                                <button type="submit" id="po-transfer-proof-submit"
                                        class="w-full inline-flex items-center justify-center gap-2 min-h-[52px] px-5 py-3.5 rounded-xl
                                               bg-emerald-600 hover:bg-emerald-700 active:scale-[0.99]
                                               text-white text-base font-extrabold shadow-lg shadow-emerald-600/30 transition-all cursor-pointer">
                                    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                    Simpan Bukti Transfer
                                </button>

                                <p class="text-[11px] text-slate-500 leading-relaxed">
                                    Setelah bukti tersimpan, lanjut tekan <span class="font-bold text-slate-700">Tandai Lunas</span> di panel kanan jika pembayaran sudah cocok.
                                </p>
                            </form>
                        </div>
                        @endif
                    </div>
                    @elseif($order->payment_method === 'invoice')
                    <div class="rounded-xl border border-amber-200 bg-amber-50/60 p-4">
                        <p class="text-xs font-bold text-amber-800 uppercase tracking-wider mb-3">Invoice Tempo</p>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <div class="rounded-lg bg-white border border-amber-100 px-3 py-2.5">
                                <p class="text-[10px] font-bold text-gray-400 uppercase">Tanggal Invoice</p>
                                <p class="font-extrabold text-gray-800 mt-1">{{ $order->created_at?->locale('id')->translatedFormat('d F Y') }}</p>
                            </div>
                            <div class="rounded-lg bg-white border border-amber-100 px-3 py-2.5">
                                <p class="text-[10px] font-bold text-gray-400 uppercase">Jatuh Tempo</p>
                                <p class="font-extrabold {{ $order->isCreditOverdue() ? 'text-red-600' : 'text-amber-700' }} mt-1">
                                    {{ $order->due_date?->locale('id')->translatedFormat('d F Y') ?? '-' }}
                                </p>
                                @if($order->isCreditOverdue())
                                <p class="text-[10px] font-bold text-red-600 mt-1">⚠ Lewat jatuh tempo</p>
                                @endif
                            </div>
                        </div>
                    </div>
                    @else
                    <div class="rounded-xl border border-slate-200 bg-slate-50 p-4 flex items-start gap-3">
                        <div class="w-9 h-9 rounded-lg bg-slate-200 flex items-center justify-center shrink-0">
                            <svg class="w-5 h-5 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2z"/></svg>
                        </div>
                        <div>
                            <p class="text-sm font-bold text-gray-800">COD — Bayar di Tempat</p>
                            <p class="text-xs text-gray-500 mt-1">Pembayaran dilakukan saat barang diterima mitra. Tidak ada transfer bank.</p>
                        </div>
                    </div>
                    @endif
                </div>
            </div>

            {{-- Tabel Item --}}
            <div class="card bg-white border border-gray-100 rounded-2xl shadow-sm overflow-hidden">
                <div class="px-5 py-3 border-b border-gray-100 bg-slate-50/80 flex flex-wrap items-center justify-between gap-2">
                    <h3 class="text-xs font-bold uppercase tracking-wider text-gray-500">Daftar Item PO</h3>
                    <span class="text-[11px] font-semibold text-gray-400">{{ $itemCount }} produk · {{ $qtyTotal }} qty</span>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full min-w-[640px] text-sm">
                        <thead>
                            <tr class="bg-slate-50 border-b border-gray-100">
                                <th class="px-4 py-3 text-left text-[11px] font-bold uppercase tracking-wider text-gray-500 w-8">#</th>
                                <th class="px-4 py-3 text-left text-[11px] font-bold uppercase tracking-wider text-gray-500">Produk</th>
                                <th class="px-4 py-3 text-left text-[11px] font-bold uppercase tracking-wider text-gray-500">Tipe</th>
                                <th class="px-4 py-3 text-right text-[11px] font-bold uppercase tracking-wider text-gray-500">Qty PO</th>
                                <th class="px-4 py-3 text-right text-[11px] font-bold uppercase tracking-wider text-gray-500">Stok</th>
                                <th class="px-4 py-3 text-right text-[11px] font-bold uppercase tracking-wider text-gray-500">Harga</th>
                                <th class="px-4 py-3 text-right text-[11px] font-bold uppercase tracking-wider text-gray-500">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50">
                            @foreach($order->items as $i => $item)
                            @php
                                $stock = $item->product?->stock;
                                $overStock = $stock !== null && $item->quantity > $stock;
                            @endphp
                            <tr class="hover:bg-slate-50/80 {{ $overStock ? 'bg-red-50/50' : '' }}">
                                <td class="px-4 py-3 text-xs text-gray-400 font-semibold">{{ $i + 1 }}</td>
                                <td class="px-4 py-3 min-w-[180px]">
                                    <p class="font-semibold text-gray-800 leading-snug">{{ $item->product_name }}</p>
                                    <p class="text-[11px] text-gray-400 mt-0.5">{{ $item->product_code }} · {{ $item->unit_name }}</p>
                                </td>
                                <td class="px-4 py-3">
                                    <span class="inline-flex px-2 py-0.5 rounded-md text-[10px] font-bold {{ $item->price_type === 'grosir' ? 'bg-amber-100 text-amber-800' : 'bg-blue-50 text-blue-700' }}">
                                        {{ $item->price_type_label }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-right font-bold text-gray-800">{{ $item->quantity }}</td>
                                <td class="px-4 py-3 text-right">
                                    @if($stock !== null)
                                    <span class="font-semibold {{ $overStock ? 'text-red-600' : 'text-gray-700' }}">{{ $stock }}</span>
                                    @if($overStock)
                                    <p class="text-[10px] font-bold text-red-600 mt-0.5">Kurang stok</p>
                                    @endif
                                    @else
                                    <span class="text-gray-400 text-xs">—</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-right text-gray-600 whitespace-nowrap">Rp {{ number_format($item->unit_price, 0, ',', '.') }}</td>
                                <td class="px-4 py-3 text-right font-bold text-gray-800 whitespace-nowrap">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            @php $orderTotals = $order->totalsBreakdown(); @endphp
                            <tr class="border-t border-gray-100">
                                <td colspan="6" class="px-4 py-2 text-right text-xs text-gray-500">Subtotal</td>
                                <td class="px-4 py-2 text-right text-sm font-semibold text-gray-700 whitespace-nowrap">Rp {{ number_format($orderTotals['subtotal'], 0, ',', '.') }}</td>
                            </tr>
                            @if($orderTotals['discount_amount'] > 0)
                            <tr>
                                <td colspan="6" class="px-4 py-1 text-right text-xs text-gray-500">Disc</td>
                                <td class="px-4 py-1 text-right text-sm font-semibold text-red-600 whitespace-nowrap">-Rp {{ number_format($orderTotals['discount_amount'], 0, ',', '.') }}</td>
                            </tr>
                            @endif
                            @if($orderTotals['ppn_enabled'])
                            <tr>
                                <td colspan="6" class="px-4 py-1 text-right text-xs text-gray-500">
                                    PPN {{ rtrim(rtrim(number_format($orderTotals['ppn_percent'], 2, ',', '.'), '0'), ',') }}%
                                    @if($orderTotals['ppn_bearer_label'])
                                    <span class="block text-[10px] text-gray-400">{{ $orderTotals['ppn_bearer_label'] }}</span>
                                    @endif
                                </td>
                                <td class="px-4 py-1 text-right text-sm font-semibold text-gray-700 whitespace-nowrap">Rp {{ number_format($orderTotals['ppn_amount'], 0, ',', '.') }}</td>
                            </tr>
                            @endif
                            <tr class="bg-emerald-50/60 border-t border-emerald-100">
                                <td colspan="6" class="px-4 py-3.5 text-right text-sm font-bold text-gray-600 uppercase tracking-wide">Total PO</td>
                                <td class="px-4 py-3.5 text-right text-lg font-extrabold text-emerald-700 whitespace-nowrap">Rp {{ number_format($orderTotals['grand_total'], 0, ',', '.') }}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>

        {{-- Kolom kanan --}}
        <div class="xl:col-span-4 space-y-4 xl:sticky xl:top-4">

            {{-- Pengiriman --}}
            <div class="card bg-white border border-gray-100 rounded-2xl shadow-sm overflow-hidden">
                <div class="px-5 py-3 border-b border-gray-100 bg-slate-50/80">
                    <h3 class="text-xs font-bold uppercase tracking-wider text-gray-500">Pengiriman & PIC</h3>
                </div>
                <div class="p-5 text-sm space-y-3">
                    <div>
                        <p class="text-[10px] font-bold uppercase tracking-wider text-gray-400">PIC</p>
                        <p class="font-semibold text-gray-800 mt-0.5">{{ $order->pic_name }}</p>
                        <a href="tel:{{ $order->pic_phone }}" class="text-emerald-700 font-semibold text-sm hover:underline">{{ $order->pic_phone }}</a>
                    </div>
                    <div>
                        <p class="text-[10px] font-bold uppercase tracking-wider text-gray-400">Alamat Pengiriman</p>
                        <p class="text-gray-700 mt-1 leading-relaxed whitespace-pre-line break-words">{{ $order->shipping_address }}</p>
                    </div>
                    @if($order->notes)
                    <div class="pt-2 border-t border-gray-100">
                        <p class="text-[10px] font-bold uppercase tracking-wider text-gray-400">Catatan Mitra</p>
                        <p class="text-gray-600 mt-1 text-xs leading-relaxed break-words">{{ $order->notes }}</p>
                    </div>
                    @endif
                    @if($order->cancel_reason)
                    <div class="pt-2 border-t border-red-100 bg-red-50/50 -mx-5 px-5 py-3">
                        <p class="text-[10px] font-bold uppercase tracking-wider text-red-500">Alasan Batal</p>
                        <p class="text-red-700 mt-1 text-xs font-semibold break-words">{{ $order->cancel_reason }}</p>
                    </div>
                    @endif
                </div>
            </div>

            {{-- Timeline --}}
            <div class="card bg-white border border-gray-100 rounded-2xl shadow-sm p-5 text-xs space-y-2">
                <p class="text-[10px] font-bold uppercase tracking-wider text-gray-400 mb-2">Riwayat Status</p>
                <div class="flex justify-between gap-2"><span class="text-gray-500">Diajukan</span><span class="font-semibold text-gray-700">{{ $order->created_at?->format('d/m/Y H:i') }}</span></div>
                @if($order->confirmed_at)
                <div class="flex justify-between gap-2"><span class="text-gray-500">Dikonfirmasi</span><span class="font-semibold text-gray-700">{{ $order->confirmed_at->format('d/m/Y H:i') }}</span></div>
                @endif
                @if($order->fulfilled_at)
                <div class="flex justify-between gap-2"><span class="text-gray-500">Selesai</span><span class="font-semibold text-emerald-700">{{ $order->fulfilled_at->format('d/m/Y H:i') }}</span></div>
                @endif
                @if($order->settled_at)
                <div class="flex justify-between gap-2"><span class="text-gray-500">Lunas</span><span class="font-semibold text-emerald-700">{{ $order->settled_at->format('d/m/Y H:i') }}</span></div>
                @endif
                @if($order->cancelled_at)
                <div class="flex justify-between gap-2"><span class="text-gray-500">Dibatalkan</span><span class="font-semibold text-red-600">{{ $order->cancelled_at->format('d/m/Y H:i') }}</span></div>
                @endif
            </div>

            {{-- Aksi --}}
            <div class="card bg-white border border-gray-100 rounded-2xl shadow-sm overflow-visible">
                <div class="px-5 py-3 border-b border-gray-100 bg-slate-50/80">
                    <h3 class="text-xs font-bold uppercase tracking-wider text-gray-500">Aksi Admin</h3>
                </div>
                <div class="p-5 space-y-2">
                    @if($order->status !== 'cancelled')
                    @include('partners.orders._print-menu', ['order' => $order])
                    @endif
                    @if($order->status === 'submitted')
                    <form action="{{ route('partner-orders.confirm', $order) }}" method="POST">@csrf
                        <button type="submit" class="btn btn-primary w-full btn-sm">Konfirmasi PO</button>
                    </form>
                    @endif
                    @if($order->payment_status !== 'paid' && $order->status !== 'cancelled')
                    <form action="{{ route('partner-orders.mark-paid', $order) }}" method="POST">@csrf
                        <button type="submit" class="btn btn-secondary w-full btn-sm">Tandai Lunas</button>
                    </form>
                    @endif
                    @if(in_array($order->status, ['submitted', 'confirmed']))
                    <form action="{{ route('partner-orders.fulfill', $order) }}" method="POST" onsubmit="return confirm('Tandai PO selesai? Stok produk akan dipotong sesuai qty PO.')">@csrf
                        <button type="submit" class="btn btn-primary w-full btn-sm">Tandai Selesai</button>
                    </form>
                    @endif
                    @if(!in_array($order->status, ['fulfilled', 'cancelled']))
                    <form action="{{ route('partner-orders.cancel', $order) }}" method="POST" class="space-y-2 pt-3 mt-3 border-t border-gray-100" onsubmit="return confirm('Batalkan PO ini?')">
                        @csrf
                        <input type="text" name="cancel_reason" class="form-input text-sm" placeholder="Alasan batal *" required>
                        <button type="submit" class="btn btn-danger w-full btn-sm" style="background:#dc2626;color:#fff;">Batalkan PO</button>
                    </form>
                    @endif
                </div>
            </div>

            {{-- Catatan admin --}}
            <form action="{{ route('partner-orders.notes', $order) }}" method="POST" class="card bg-white border border-gray-100 rounded-2xl shadow-sm overflow-hidden">
                @csrf
                <div class="px-5 py-3 border-b border-gray-100 bg-slate-50/80">
                    <label class="text-xs font-bold uppercase tracking-wider text-gray-500">Catatan Admin</label>
                </div>
                <div class="p-5 space-y-3">
                    <textarea name="admin_notes" rows="4" class="form-input text-sm w-full resize-y min-h-[88px]" placeholder="Catatan internal untuk PO ini...">{{ old('admin_notes', $order->admin_notes) }}</textarea>
                    <button type="submit" class="btn btn-secondary btn-sm w-full sm:w-auto">Simpan Catatan</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function copyPoText(elementId, label) {
    const el = document.getElementById(elementId);
    if (!el) return;
    const text = el.textContent.trim();
    const done = () => {
        window.dispatchEvent(new CustomEvent('toast', { detail: { message: (label || 'Teks') + ' berhasil disalin.', type: 'success' } }));
    };
    if (navigator.clipboard?.writeText) {
        navigator.clipboard.writeText(text).then(done).catch(() => {
            const ta = document.createElement('textarea');
            ta.value = text;
            document.body.appendChild(ta);
            ta.select();
            document.execCommand('copy');
            document.body.removeChild(ta);
            done();
        });
    }
}

(function () {
    const form = document.getElementById('po-transfer-proof-form');
    const input = document.getElementById('po_transfer_proof');
    const nameEl = document.getElementById('po_transfer_proof_name');
    const submitBtn = document.getElementById('po-transfer-proof-submit');
    if (!form || !input) return;

    input.addEventListener('change', () => {
        const file = input.files?.[0];
        if (!nameEl) return;
        if (file) {
            nameEl.textContent = 'File dipilih: ' + file.name;
            nameEl.classList.remove('hidden');
        } else {
            nameEl.textContent = '';
            nameEl.classList.add('hidden');
        }
    });

    form.addEventListener('submit', () => {
        if (!submitBtn || !input.files?.length) return;
        submitBtn.disabled = true;
        submitBtn.classList.add('opacity-70', 'cursor-wait');
        submitBtn.innerHTML = 'Menyimpan bukti...';
    });
})();
</script>
@endpush
