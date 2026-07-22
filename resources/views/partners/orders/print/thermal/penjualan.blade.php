<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Faktur Thermal — {{ $order->order_no }}</title>
    @include('partners.orders.print.thermal._styles')
</head>
<body>
@php
    $totals = $order->totalsBreakdown();
    $isPaid = $order->payment_status === \App\Models\PartnerOrder::PAYMENT_PAID;
    $disc   = (float) ($totals['discount_amount'] ?? 0);
    $payLabel = match($order->payment_method) {
        'transfer' => 'TRANSFER BANK',
        'invoice'  => 'INVOICE TEMPO',
        default    => 'COD / TUNAI',
    };
@endphp

@include('partners.orders.print.thermal._toolbar', [
    'toolbarTitle' => 'Faktur Penjualan · Thermal',
    'toolbarSub'   => $order->order_no . ' · ' . ($order->partner?->name ?? ''),
])

<div class="receipt-outer">
<div class="receipt">
    <div class="header">
        <div class="main">{{ $headerName }}</div>
        <div class="sub">{{ $subName }}</div>
        <div class="addr xs">{{ $addressLine }}</div>
        <div class="xs">Telp/WA: {{ $phone }}</div>
    </div>

    <hr class="solid">
    <div class="doc-badge">FAKTUR PENJUALAN</div>
    <div class="doc-sub">{{ $payLabel }}</div>
    <hr class="divider">

    <div class="meta-row"><span class="lbl">No. PO</span><span class="val">{{ $order->order_no }}</span></div>
    <div class="meta-row"><span class="lbl">Tanggal</span><span class="val">{{ $order->created_at?->timezone('Asia/Makassar')->format('d/m/Y H:i') }}</span></div>
    <div class="meta-row"><span class="lbl">Mitra</span><span class="val">{{ $order->partner?->name }}</span></div>
    <div class="meta-row"><span class="lbl">Bayar</span><span class="val">{{ $order->payment_method_label }} · {{ $isPaid ? 'LUNAS' : 'BELUM' }}</span></div>
    @if($order->payment_method === 'invoice' && $order->due_date)
    <div class="meta-row"><span class="lbl">Jatuh Tempo</span><span class="val">{{ $order->due_date->format('d/m/Y') }}</span></div>
    @endif

    <hr class="divider">

    <div class="col-hdr">
        <span>Item</span>
        <span class="c-qty">Qty</span>
        <span class="c-amt">Total</span>
    </div>
    @foreach($order->items as $item)
    <div class="item-block">
        <div class="item-name">{{ $item->product_name }}</div>
        <div class="item-row">
            <span class="c-harga">@ Rp {{ number_format($item->unit_price, 0, ',', '.') }}</span>
            <span class="c-qty">{{ $item->quantity }}</span>
            <span class="c-sub">{{ number_format($item->subtotal, 0, ',', '.') }}</span>
        </div>
    </div>
    @endforeach

    <hr class="divider">

    <div class="sum-box">
        <div class="sum-row">
            <span>Subtotal</span>
            <span class="sum-val">Rp {{ number_format($totals['subtotal'], 0, ',', '.') }}</span>
        </div>
        <div class="sum-row">
            <span>Diskon</span>
            <span class="sum-val">{{ $disc > 0 ? '- ' : '' }}Rp {{ number_format($disc, 0, ',', '.') }}</span>
        </div>
        @if(($totals['ppn_amount'] ?? 0) > 0)
        <div class="sum-row">
            <span>PPN {{ rtrim(rtrim(number_format($totals['ppn_percent'], 2, ',', '.'), '0'), ',') }}%</span>
            <span class="sum-val">Rp {{ number_format($totals['ppn_amount'], 0, ',', '.') }}</span>
        </div>
        @endif
        <hr class="double">
        <div class="sum-row grand">
            <span>TOTAL</span>
            <span class="sum-val">Rp {{ number_format($totals['grand_total'], 0, ',', '.') }}</span>
        </div>
    </div>

    @if($order->payment_method === 'transfer')
    <div class="note-box">{{ $bankName }} · {{ $bankAccount }}<br>a/n {{ $bankHolder }}</div>
    @elseif($order->payment_method === 'invoice')
    <div class="note-box">Invoice tempo — jatuh tempo {{ $order->due_date?->format('d/m/Y') ?? '—' }}</div>
    @endif

    <hr class="double">
    <div class="footer">
        <div class="thanks">TERIMA KASIH</div>
        <div class="xs" style="margin-top:6px;color:#444;">Barang sudah dibeli tidak dapat<br>ditukar / dikembalikan.</div>
    </div>
</div>
</div>
</body>
</html>
