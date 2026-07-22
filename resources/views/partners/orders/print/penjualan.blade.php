@extends('partners.orders.print.layout')

@section('content')
@php
    $totals = $order->totalsBreakdown();
    $isPaid = $order->payment_status === \App\Models\PartnerOrder::PAYMENT_PAID;
    $isOverdue = $order->isCreditOverdue();
    $payMethod = $order->payment_method;
@endphp

<div class="doc-header">
    <div class="doc-header-title">
        @if($payMethod === 'invoice')
            Faktur Penjualan — Invoice Tempo
        @elseif($payMethod === 'transfer')
            Faktur Penjualan — Transfer Bank
        @else
            Faktur Penjualan — COD / Tunai
        @endif
    </div>
    <div class="meta-grid">
        <div class="meta-item">
            <label>{{ $order->payment_method === 'invoice' ? 'No. Invoice' : 'No. PO / Faktur' }}</label>
            <span class="mono">{{ $order->order_no }}</span>
        </div>
        <div class="meta-item">
            <label>Tgl Transaksi</label>
            <span>{{ $order->created_at?->timezone('Asia/Makassar')->format('d/m/Y H:i') }} WITA</span>
        </div>
        <div class="meta-item">
            <label>Mitra / Pelanggan</label>
            <span>{{ $order->partner?->name }} ({{ $order->partner?->code }})</span>
        </div>
        <div class="meta-item">
            <label>Metode Bayar</label>
            <span>{{ $order->payment_method_label }}</span>
        </div>
        <div class="meta-item">
            <label>Status Pembayaran</label>
            <span>
                @if($isPaid)
                    <span class="badge badge-paid">Lunas</span>
                    @if($order->settlement_method)
                        · {{ $order->settlement_method === 'cash' ? 'Tunai' : 'Transfer' }}
                    @endif
                @elseif($isOverdue)
                    <span class="badge badge-overdue">Overdue</span>
                @else
                    <span class="badge badge-unpaid">{{ $order->payment_status_label }}</span>
                @endif
            </span>
        </div>
        @if($payMethod === 'invoice' && $order->due_date)
        <div class="meta-item">
            <label>Jatuh Tempo</label>
            <span style="{{ $isOverdue ? 'color:#b91c1c;font-weight:800;' : '' }}">
                {{ $order->due_date->locale('id')->isoFormat('D MMMM Y') }}
            </span>
        </div>
        @endif
        @if($isPaid && $order->settled_at)
        <div class="meta-item">
            <label>Tgl Pelunasan</label>
            <span>{{ $order->settled_at->timezone('Asia/Makassar')->format('d/m/Y H:i') }} WITA</span>
        </div>
        @endif
        <div class="meta-item">
            <label>Skema Harga</label>
            <span class="capitalize">{{ str_replace('_', ' ', $order->price_mode_snapshot ?? '-') }}</span>
        </div>
        <div class="meta-item">
            <label>PIC</label>
            <span>{{ $order->pic_name ?? '—' }} · {{ $order->pic_phone ?? '—' }}</span>
        </div>
    </div>
</div>

<table class="data-table">
    <thead>
        <tr>
            <th class="text-center" style="width:32px">No</th>
            <th>Produk</th>
            <th class="text-center" style="width:56px">Tipe</th>
            <th class="text-center" style="width:48px">Qty</th>
            <th class="text-right" style="width:90px">Harga</th>
            <th class="text-right" style="width:100px">Subtotal</th>
        </tr>
    </thead>
    <tbody>
        @foreach($order->items as $i => $item)
        <tr>
            <td class="text-center">{{ $i + 1 }}</td>
            <td>
                <span class="font-bold">{{ $item->product_name }}</span>
                @if($item->product_code)
                <br><span class="font-mono text-xs" style="color:#94a3b8">{{ $item->product_code }}</span>
                @endif
            </td>
            <td class="text-center capitalize">{{ $item->price_type ?? '—' }}</td>
            <td class="text-center font-bold">{{ $item->quantity }}</td>
            <td class="text-right font-mono">{{ number_format($item->unit_price, 0, ',', '.') }}</td>
            <td class="text-right font-mono font-bold">{{ number_format($item->subtotal, 0, ',', '.') }}</td>
        </tr>
        @endforeach
    </tbody>
</table>

<div class="summary-box">
    <div class="summary-row">
        <span>Subtotal</span>
        <span>Rp {{ number_format($totals['subtotal'], 0, ',', '.') }}</span>
    </div>
    <div class="summary-row">
        <span>Diskon</span>
        <span style="{{ ($totals['discount_amount'] ?? 0) > 0 ? 'color:#dc2626' : '' }}">{{ ($totals['discount_amount'] ?? 0) > 0 ? '- ' : '' }}Rp {{ number_format($totals['discount_amount'] ?? 0, 0, ',', '.') }}</span>
    </div>
    @if(($totals['ppn_amount'] ?? 0) > 0)
    <div class="summary-row">
        <span>PPN {{ rtrim(rtrim(number_format($totals['ppn_percent'], 2, ',', '.'), '0'), ',') }}%
            @if($totals['ppn_bearer_label'])
            <br><span style="font-size:7.5px;color:#94a3b8;font-weight:500">{{ $totals['ppn_bearer_label'] }}</span>
            @endif
        </span>
        <span>Rp {{ number_format($totals['ppn_amount'], 0, ',', '.') }}</span>
    </div>
    @endif
    <div class="summary-row total">
        <span>TOTAL</span>
        <span>Rp {{ number_format($totals['grand_total'], 0, ',', '.') }}</span>
    </div>
</div>

@if($payMethod === 'transfer')
<div class="payment-box">
    <h4>Informasi Transfer Bank</h4>
    <div class="payment-grid">
        <div class="payment-cell">
            <label>Bank</label>
            <span>{{ $bankName }}</span>
        </div>
        <div class="payment-cell">
            <label>No. Rekening</label>
            <span class="font-mono">{{ $bankAccount }}</span>
        </div>
        <div class="payment-cell">
            <label>A/N</label>
            <span>{{ $bankHolder }}</span>
        </div>
    </div>
    @if($order->transfer_proof)
    <p style="margin-top:8px;font-size:8.5px;color:#64748b;">Bukti transfer diunggah: {{ $order->transfer_proof_at?->format('d/m/Y H:i') ?? '—' }}</p>
    @endif
</div>
@elseif($payMethod === 'invoice')
<div class="payment-box">
    <h4>Invoice Tempo 30 Hari</h4>
    <p style="font-size:9px;color:#475569;line-height:1.6;">
        Pembayaran ditagihkan ke mitra dengan jatuh tempo
        <strong>{{ $order->due_date?->locale('id')->isoFormat('D MMMM Y') ?? '—' }}</strong>.
        @if(!$isPaid)
        Harap lunasi sebelum tanggal tersebut untuk menghindari status overdue.
        @endif
    </p>
</div>
@else
<div class="payment-box">
    <h4>Pembayaran COD / Tunai</h4>
    <p style="font-size:9px;color:#475569;">Pembayaran dilakukan saat barang diterima mitra (Cash on Delivery).</p>
</div>
@endif

@if($order->notes)
<div class="notes-box">
    <strong>Catatan:</strong> {{ $order->notes }}
</div>
@endif

@if($order->payment_method === 'invoice')
@include('reports.partials.signature', ['entity' => 'pt'])
@endif
@endsection
