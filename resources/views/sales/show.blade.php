@extends('layouts.app')

@section('title', 'Detail Penjualan ' . $sale->invoice_no)

@section('breadcrumb')
    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
    </svg>
    <a wire:navigate href="{{ route('sales.index') }}" class="hover:text-primary-600 transition-colors">Riwayat Penjualan</a>
    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
    </svg>
    <span class="text-gray-600 font-medium">{{ $sale->document_label }} {{ $sale->invoice_no }}</span>
@endsection

@section('content')
<div class="max-w-4xl mx-auto flex flex-col gap-6 animate-in">
    
    {{-- Header Actions --}}
    <div class="flex justify-between items-center">
        <div class="flex items-center gap-2">
            <a wire:navigate href="{{ route('sales.index') }}" class="btn btn-secondary btn-sm" title="Kembali ke Daftar">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                Kembali
            </a>
            <h2 class="text-lg font-bold text-gray-900">{{ $sale->isInvoicePayment() ? 'Detail Penjualan Invoice' : 'Detail Faktur Penjualan' }}</h2>
        </div>
        <div class="flex gap-2">
            <button type="button" 
                    onclick="
                        const btn = this;
                        btn.disabled = true;
                        const oldText = btn.innerHTML;
                        btn.innerHTML = 'Printing...';
                        fetch('{{ route('sales.print-thermal', $sale->id) }}', {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                'Accept': 'application/json'
                            }
                        })
                        .then(res => res.json())
                        .then(data => {
                            btn.disabled = false;
                            btn.innerHTML = oldText;
                            if (data.success) {
                                window.dispatchEvent(new CustomEvent('toast', { detail: { type: 'success', message: data.message } }));
                            } else {
                                window.dispatchEvent(new CustomEvent('toast', { detail: { type: 'error', message: data.message } }));
                            }
                        })
                        .catch(err => {
                            btn.disabled = false;
                            btn.innerHTML = oldText;
                            window.dispatchEvent(new CustomEvent('toast', { detail: { type: 'error', message: 'Terjadi kesalahan koneksi printer!' } }));
                        });
                    "
                    class="btn btn-secondary btn-sm flex items-center gap-1.5 shadow-sm text-emerald-700 bg-emerald-50 hover:bg-emerald-100 border border-emerald-200">
                🖨️ Cetak Termal
            </button>
            <a wire:navigate href="{{ route('sales.print', $sale->id) }}" target="_blank" class="btn btn-primary btn-sm flex items-center gap-1.5 shadow-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                </svg>
                Cetak Struk
            </a>
            @if($sale->payment_method === 'Invoice')
            <a href="{{ route('invoices.print', $sale->id) }}" target="_blank" rel="noopener noreferrer" class="btn btn-primary btn-sm flex items-center gap-1.5 shadow-sm !bg-blue-600 hover:!bg-blue-700 !border-blue-600 text-white">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                </svg>
                Cetak Invoice
            </a>
            <a href="{{ route('invoices.export', $sale->id) }}" class="btn btn-primary btn-sm flex items-center gap-1.5 shadow-sm !bg-green-600 hover:!bg-green-700 !border-green-600 text-white">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                </svg>
                Export Excel
            </a>
            @endif
            @if($sale->payment_method === 'Invoice' && $sale->payment_status === 'unpaid' && $sale->status !== 'cancelled')
            <button type="button" onclick="document.getElementById('pay-modal').classList.remove('hidden')"
                    class="btn btn-sm flex items-center gap-1.5 shadow-sm text-white bg-orange-500 hover:bg-orange-600 border border-orange-600 transition-all cursor-pointer">
                💰 Tandai Lunas
            </button>
            @endif
            @if(auth()->user()->isSuperAdmin() || auth()->user()->isKasir())
            <a wire:navigate href="{{ route('pos.index') }}" class="btn btn-secondary btn-sm flex items-center gap-1.5">
                <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                POS Kasir
            </a>
            @endif
        </div>
    </div>

    {{-- Main Invoice Card --}}
    <div class="card p-6 md:p-8 flex flex-col gap-6 relative overflow-hidden">
        
        {{-- Watermark background --}}
        <div class="absolute inset-0 flex items-center justify-center opacity-[0.08] pointer-events-none select-none z-0">
            <img src="{{ asset('assets/images/watermark-apotek.png') }}" class="w-[300px] md:w-[420px] object-contain" alt="Watermark">
        </div>

        {{-- Brand / Invoice Title Row --}}
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center pb-6 border-b border-gray-100 gap-4 relative z-10">
            <div class="flex items-center gap-3">
                <div class="w-12 h-12 rounded-xl bg-white border border-emerald-500/15 shadow-[0_0_12px_rgba(16,185,129,0.15)] flex items-center justify-center overflow-hidden">
                    <img src="{{ asset('assets/images/logodashboard.jpeg') }}" alt="Logo" class="w-10 h-10 object-contain rounded-lg">
                </div>
                <div>
                    <h3 class="text-base font-bold text-gray-900 leading-tight">Apotek Almaira</h3>
                </div>
            </div>
            <div class="text-left md:text-right">
                <span class="text-xs text-gray-400 font-bold uppercase tracking-wider block">{{ $sale->document_label }}</span>
                <span class="text-lg font-bold text-primary-700 tracking-wider font-mono">{{ $sale->invoice_no }}</span>
            </div>
        </div>

        @if($sale->status === 'cancelled')
        <div class="p-4 bg-red-50 border border-red-200 rounded-2xl flex items-start gap-3 text-red-800 relative z-10">
            <svg class="w-5 h-5 text-red-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
            </svg>
            <div>
                <strong class="font-bold text-sm block">Transaksi Dibatalkan</strong>
                <p class="text-xs text-red-700 mt-0.5 leading-relaxed">
                    Alasan Pembatalan: <span class="font-semibold italic">"{{ $sale->cancel_reason }}"</span>
                </p>
            </div>
        </div>
        @endif

        {{-- Metadata Grid --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4 bg-gradient-to-br from-gray-50/80 to-white p-5 rounded-2xl border border-gray-200/60 shadow-sm relative z-10">
            <div class="p-3.5 bg-white/70 backdrop-blur-sm rounded-xl border border-gray-100 shadow-sm flex flex-col justify-between">
                <span class="text-[10px] font-bold text-gray-400 uppercase tracking-wider block">Pelanggan</span>
                <span class="font-extrabold text-gray-800 text-sm mt-1 block">{{ $sale->customer_name }}</span>
            </div>
            <div class="p-3.5 bg-white/70 backdrop-blur-sm rounded-xl border border-gray-100 shadow-sm flex flex-col justify-between">
                <span class="text-[10px] font-bold text-gray-400 uppercase tracking-wider block">Kasir / Petugas</span>
                <span class="font-semibold text-gray-700 text-sm mt-1 block">{{ $sale->user?->name ?? 'System' }}</span>
            </div>
            <div class="p-3.5 bg-white/70 backdrop-blur-sm rounded-xl border border-gray-100 shadow-sm flex flex-col justify-between">
                <span class="text-[10px] font-bold text-gray-400 uppercase tracking-wider block">Waktu Transaksi</span>
                <span class="text-xs font-semibold text-gray-600 mt-1 block">{{ $sale->sold_at->format('d M Y, H:i') }} WIB</span>
            </div>
            <div class="p-3.5 bg-white/70 backdrop-blur-sm rounded-xl border border-gray-100 shadow-sm flex flex-col justify-between">
                <span class="text-[10px] font-bold text-gray-400 uppercase tracking-wider block">Metode Pembayaran</span>
                <div class="mt-1">
                    @if($sale->payment_method === 'QRIS')
                    <span class="inline-flex items-center px-2.5 py-1 text-xs font-bold bg-violet-50 text-violet-750 rounded-lg ring-1 ring-violet-500/20">
                        {{ $sale->payment_method }}
                    </span>
                    @elseif($sale->payment_method === 'Transfer')
                    <span class="inline-flex items-center px-2.5 py-1 text-xs font-bold bg-blue-50 text-blue-700 rounded-lg ring-1 ring-blue-500/20">
                        {{ $sale->payment_method }}
                    </span>
                    @elseif($sale->payment_method === 'Invoice')
                    <span class="inline-flex items-center px-2.5 py-1 text-xs font-bold bg-orange-50 text-orange-700 rounded-lg ring-1 ring-orange-500/20">
                        {{ $sale->payment_method }} (Tempo)
                    </span>
                    @else
                    <span class="inline-flex items-center px-2.5 py-1 text-xs font-bold bg-emerald-50 text-emerald-700 rounded-lg ring-1 ring-emerald-500/20">
                        {{ $sale->payment_method }}
                    </span>
                    @endif
                </div>
            </div>
            <div class="p-3.5 bg-white/70 backdrop-blur-sm rounded-xl border border-gray-100 shadow-sm flex flex-col justify-between">
                <span class="text-[10px] font-bold text-gray-400 uppercase tracking-wider block">Status Pembayaran</span>
                <div class="mt-1">
                    @if($sale->status === 'cancelled')
                    <span class="inline-flex items-center px-2.5 py-1 text-xs font-bold bg-red-50 text-red-700 rounded-lg ring-1 ring-red-500/20">
                        Dibatalkan
                    </span>
                    @else
                        @if($sale->payment_status === 'paid')
                        <span class="inline-flex items-center px-2.5 py-1 text-xs font-bold bg-green-50 text-green-700 rounded-lg ring-1 ring-green-500/20">
                            Lunas
                        </span>
                        @else
                        <span class="inline-flex items-center px-2.5 py-1 text-xs font-bold bg-red-50 text-red-700 rounded-lg ring-1 ring-red-500/20">
                            Belum Lunas
                        </span>
                        @endif
                    @endif
                </div>
            </div>
            @if($sale->payment_method === 'Invoice')
            <div class="p-3.5 bg-white/70 backdrop-blur-sm rounded-xl border border-gray-100 shadow-sm flex flex-col justify-between">
                <span class="text-[10px] font-bold text-gray-400 uppercase tracking-wider block">Jatuh Tempo (30 Hari)</span>
                <span class="text-xs font-bold mt-1 block {{ $sale->payment_status === 'unpaid' && $sale->due_date && $sale->due_date->isPast() ? 'text-red-650' : 'text-gray-700' }}">
                    {{ $sale->due_date ? $sale->due_date->format('d M Y') : '-' }}
                    @if($sale->payment_status === 'unpaid' && $sale->due_date && $sale->due_date->isPast())
                        <span class="ml-1 inline-flex items-center px-1.5 py-0.5 rounded text-[8px] font-extrabold bg-red-100 text-red-800 tracking-wider">OVERDUE</span>
                    @endif
                </span>
            </div>
            @if($sale->payment_status === 'paid' && $sale->settled_at)
            <div class="p-3.5 bg-emerald-50/60 backdrop-blur-sm rounded-xl border border-emerald-100 shadow-sm flex flex-col justify-between">
                <span class="text-[10px] font-bold text-emerald-500 uppercase tracking-wider block">Dilunasi Pada</span>
                <span class="text-xs font-bold text-emerald-800 mt-1 block">
                    {{ $sale->settled_at->format('d M Y, H:i') }} WIB
                </span>
                <span class="text-[10px] text-emerald-600 mt-0.5">
                    oleh {{ $sale->settledBy?->name ?? '-' }}
                    @if($sale->settlement_method)
                        &mdash; {{ $sale->settlement_method === 'cash' ? 'Tunai' : 'Transfer' }}
                    @endif
                </span>
            </div>
            @endif
            @elseif($sale->notes)
            <div class="p-3.5 bg-white/70 backdrop-blur-sm rounded-xl border border-gray-100 shadow-sm flex flex-col justify-between">
                <span class="text-[10px] font-bold text-gray-400 uppercase tracking-wider block">Catatan</span>
                <p class="text-xs text-gray-600 mt-1 italic leading-relaxed">
                    {{ $sale->notes }}
                </p>
            </div>
            @endif
        </div>

        {{-- Items Table --}}
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="text-xs font-bold text-gray-500 uppercase tracking-wider border-b border-gray-100 bg-gray-50/80">
                        <th class="py-3 px-4 w-8 text-center">No</th>
                        <th class="py-3 px-4">Nama Produk</th>
                        <th class="py-3 px-4 text-center">Harga Unit</th>
                        <th class="py-3 px-4 text-center">Qty</th>
                        <th class="py-3 px-4 text-center">Diskon Row</th>
                        <th class="py-3 px-4 text-right">Subtotal</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 text-sm">
                    @foreach($sale->items as $index => $item)
                    <tr>
                        <td class="py-3.5 px-4 text-center text-gray-400 font-semibold">{{ $index + 1 }}</td>
                        <td class="py-3.5 px-4">
                            <div class="flex flex-col">
                                <span class="font-bold text-gray-900">{{ $item->product_name }}</span>
                                <span class="text-[11px] text-gray-400">SKU: {{ $item->product_code }} | Satuan: {{ $item->unit_name }}</span>
                            </div>
                        </td>
                        <td class="py-3.5 px-4 text-center">
                            <span class="text-gray-800 font-medium">Rp {{ number_format($item->unit_price, 0, ',', '.') }}</span>
                            <span class="block text-[10px] text-gray-400 mt-0.5 capitalize">{{ $item->price_type }}</span>
                        </td>
                        <td class="py-3.5 px-4 text-center font-bold text-gray-700">
                            {{ $item->quantity }}
                        </td>
                        <td class="py-3.5 px-4 text-center">
                            @if($item->discount_percent > 0)
                                <span class="text-red-500 font-semibold">{{ number_format($item->discount_percent, 1, ',', '.') }}%</span>
                                <span class="block text-[10px] text-gray-400 mt-0.5">-Rp {{ number_format($item->discount_amount, 0, ',', '.') }}</span>
                            @else
                                <span class="text-gray-400">-</span>
                            @endif
                        </td>
                        <td class="py-3.5 px-4 text-right font-bold text-gray-900">
                            Rp {{ number_format($item->subtotal, 0, ',', '.') }}
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{-- Breakdown Summary --}}
        <div class="pt-6 border-t border-gray-100 flex flex-col md:flex-row justify-between items-start gap-6">
            {{-- QRIS BNI static NMID note (for reference) --}}
            <div class="w-full md:w-1/2 text-xs text-gray-400 flex flex-col gap-1 italic">
                @if($sale->payment_method === 'QRIS')
                <div class="flex items-center gap-2 p-3 bg-emerald-50/50 rounded-xl border border-emerald-100/50 text-emerald-800 leading-relaxed">
                    <svg class="w-8 h-8 text-emerald-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h.01M16 20h.01M21 12h.01M12 17h.01M17 12h.01M16 16h.01M5 8h.01M9 8h.01M5 12h.01M9 12h.01M5 16h.01M9 16h.01M6 21h12a2 2 0 002-2V5a2 2 0 00-2-2H6a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                    </svg>
                    <span>Dibayar melalui QRIS BNI dengan NMID ID1026522359276 (Apotek Almaira). Transaksi Lunas.</span>
                </div>
                @endif
            </div>

            {{-- Price Summaries --}}
            <div class="w-full md:w-1/2 flex flex-col gap-2.5">
                <div class="flex justify-between items-center text-xs text-gray-500">
                    <span>Subtotal Belanja:</span>
                    <span class="font-semibold text-gray-800">Rp {{ number_format($sale->subtotal, 0, ',', '.') }}</span>
                </div>
                @if($sale->discount_amount > 0)
                <div class="flex justify-between items-center text-xs text-gray-500">
                    <span>Diskon Global ({{ number_format($sale->discount_percent, 1, ',', '.') }}%):</span>
                    <span class="font-semibold text-red-500">-Rp {{ number_format($sale->discount_amount, 0, ',', '.') }}</span>
                </div>
                @endif
                @if($sale->ppn_active)
                <div class="flex justify-between items-center text-xs text-gray-500">
                    <span>PPN (11%):</span>
                    <span class="font-semibold text-gray-800">
                        Rp {{ number_format($sale->ppn_amount, 0, ',', '.') }}
                        @if($sale->ppn_bearer === 'Ditanggung Penjual')
                            <span class="text-[10px] text-gray-400 italic block mt-0.5 text-right">(Ditanggung Penjual / Absorbed)</span>
                        @else
                            <span class="text-[10px] text-gray-400 italic block mt-0.5 text-right">(Ditanggung Pembeli / Added)</span>
                        @endif
                    </span>
                </div>
                @endif
                <div class="flex justify-between items-center pt-3 border-t border-gray-200 mt-2">
                    <span class="text-sm font-bold text-gray-900">Total Pembayaran:</span>
                    <span class="text-lg font-extrabold text-primary-700">Rp {{ number_format($sale->total, 0, ',', '.') }}</span>
                </div>
                @if($sale->payment_method === 'Tunai')
                <div class="flex justify-between items-center text-xs text-gray-500 border-t border-dashed border-gray-200 pt-2">
                    <span>Uang Diterima:</span>
                    <span class="font-semibold text-gray-800">Rp {{ number_format($sale->cash_received, 0, ',', '.') }}</span>
                </div>
                <div class="flex justify-between items-center text-xs text-gray-500">
                    <span>Kembalian:</span>
                    <span class="font-bold text-emerald-600">Rp {{ number_format($sale->change_amount, 0, ',', '.') }}</span>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

{{-- ─── Pay Modal ──────────────────────────────────────────────── --}}
@if($sale->payment_method === 'Invoice' && $sale->payment_status === 'unpaid' && $sale->status !== 'cancelled')
<div id="pay-modal" class="hidden fixed inset-0 z-[9999] flex items-center justify-center p-4 bg-gray-900/60 backdrop-blur-sm">
    <div class="bg-white rounded-2xl max-w-md w-full shadow-2xl border border-gray-100 overflow-hidden animate-in fade-in zoom-in-95 duration-200">
        {{-- Header --}}
        <div class="px-6 py-4 border-b border-gray-100 bg-gradient-to-r from-orange-50 to-amber-50 flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-orange-100 text-orange-600 flex items-center justify-center flex-shrink-0 text-xl">💰</div>
            <div>
                <h3 class="text-sm font-extrabold text-gray-900">Pelunasan Invoice</h3>
                <p class="text-xs text-gray-500">{{ $sale->invoice_no }} &mdash; Rp {{ number_format($sale->total, 0, ',', '.') }}</p>
            </div>
            <button type="button" onclick="document.getElementById('pay-modal').classList.add('hidden')"
                    class="ml-auto p-1.5 rounded-lg text-gray-400 hover:text-gray-600 hover:bg-gray-100 transition-colors cursor-pointer">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

        {{-- Body --}}
        <form action="{{ route('sales.pay', $sale->id) }}" method="POST" class="p-6">
            @csrf

            {{-- Invoice Info --}}
            <div class="bg-gray-50 rounded-xl p-4 mb-5 flex flex-col gap-2">
                <div class="flex justify-between text-xs">
                    <span class="text-gray-500 font-medium">Pelanggan</span>
                    <span class="font-bold text-gray-800">{{ $sale->customer_name }}</span>
                </div>
                <div class="flex justify-between text-xs">
                    <span class="text-gray-500 font-medium">Jatuh Tempo</span>
                    <span class="font-bold {{ $sale->due_date && $sale->due_date->isPast() ? 'text-red-600' : 'text-gray-800' }}">
                        {{ $sale->due_date ? $sale->due_date->format('d M Y') : '-' }}
                        @if($sale->due_date && $sale->due_date->isPast())
                            <span class="ml-1 px-1 py-0.5 rounded text-[9px] bg-red-100 text-red-700 font-extrabold">OVERDUE</span>
                        @endif
                    </span>
                </div>
                <div class="flex justify-between items-center pt-2 border-t border-gray-200">
                    <span class="text-gray-500 font-medium text-xs">Total Tagihan</span>
                    <span class="text-lg font-extrabold text-gray-900">Rp {{ number_format($sale->total, 0, ',', '.') }}</span>
                </div>
            </div>

            {{-- Method selection --}}
            <div class="mb-5">
                <label class="text-xs font-bold text-gray-700 block mb-3">Pilih Metode Pelunasan</label>
                <div class="grid grid-cols-2 gap-3">
                    <label class="relative cursor-pointer">
                        <input type="radio" name="settlement_method" value="cash" class="sr-only peer" checked>
                        <div class="p-3.5 rounded-xl border-2 border-gray-200 peer-checked:border-emerald-500 peer-checked:bg-emerald-50 transition-all text-center">
                            <div class="text-2xl mb-1">💵</div>
                            <p class="text-xs font-extrabold text-gray-800">Tunai</p>
                            <p class="text-[10px] text-gray-400 mt-0.5">Bayar cash</p>
                        </div>
                    </label>
                    <label class="relative cursor-pointer">
                        <input type="radio" name="settlement_method" value="transfer" class="sr-only peer">
                        <div class="p-3.5 rounded-xl border-2 border-gray-200 peer-checked:border-blue-500 peer-checked:bg-blue-50 transition-all text-center">
                            <div class="text-2xl mb-1">🏦</div>
                            <p class="text-xs font-extrabold text-gray-800">Transfer</p>
                            <p class="text-[10px] text-gray-400 mt-0.5">Via bank / m-banking</p>
                        </div>
                    </label>
                </div>
            </div>

            {{-- Buttons --}}
            <div class="flex gap-3">
                <button type="button" onclick="document.getElementById('pay-modal').classList.add('hidden')"
                        class="flex-1 px-4 py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-700 font-bold rounded-xl text-sm transition-colors cursor-pointer">
                    Batal
                </button>
                <button type="submit"
                        class="flex-1 px-4 py-2.5 bg-orange-500 hover:bg-orange-600 text-white font-extrabold rounded-xl text-sm transition-colors shadow-sm cursor-pointer">
                    ✓ Konfirmasi Lunas
                </button>
            </div>
        </form>
    </div>
</div>
@endif
