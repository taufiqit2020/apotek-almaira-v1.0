<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Surat Jalan Thermal — {{ $order->order_no }}</title>
    @include('partners.orders.print.thermal._styles')
</head>
<body>
@php
    $docDate  = $order->fulfilled_at ?? $order->confirmed_at ?? $order->created_at;
    $qtyTotal = $order->items->sum('quantity');
@endphp

@include('partners.orders.print.thermal._toolbar', [
    'toolbarTitle' => 'Surat Jalan · Thermal',
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
    <div class="doc-badge">SURAT JALAN</div>
    <hr class="divider">

    <div class="meta-row"><span class="lbl">No. PO</span><span class="val">{{ $order->order_no }}</span></div>
    <div class="meta-row"><span class="lbl">Tanggal</span><span class="val">{{ $docDate?->timezone('Asia/Makassar')->format('d/m/Y H:i') }}</span></div>
    <div class="meta-row"><span class="lbl">Mitra</span><span class="val">{{ $order->partner?->name }}</span></div>
    <div class="meta-row"><span class="lbl">PIC</span><span class="val">{{ $order->pic_name ?? '—' }}</span></div>
    <div class="meta-row"><span class="lbl">Alamat</span><span class="val small">{{ Str::limit($order->shipping_address ?? '—', 90) }}</span></div>

    <hr class="divider">

    <div class="col-hdr">
        <span>Barang</span>
        <span class="c-qty">Qty</span>
        <span class="c-amt">Sat</span>
    </div>
    @foreach($order->items as $item)
    @php $meta = $item->catalogDisplay(); @endphp
    <div class="item-block">
        <div class="item-name">{{ $item->product_name }}</div>
        <div class="xs" style="color:#555;margin:1px 0 2px;">{{ $meta['code'] }} · {{ $meta['category'] }} · {{ $meta['unit'] }}</div>
        <div class="xs" style="color:#666;margin:0 0 3px;">{{ $meta['kandungan'] }} · {{ $meta['bentuk'] }}</div>
        <div class="item-row">
            <span class="c-harga">Qty</span>
            <span class="c-qty">{{ $item->quantity }}</span>
            <span class="c-sub">{{ $meta['unit'] }}</span>
        </div>
    </div>
    @endforeach

    <hr class="divider">
    <div class="sum-row grand">
        <span>TOTAL QTY · {{ $order->items->count() }} item</span>
        <span class="sum-val">{{ $qtyTotal }}</span>
    </div>

    <hr class="double">
    <div class="footer">
        <div class="thanks">SERAH TERIMA BARANG</div>
        <div class="xs" style="margin-top:8px;">Pengirim: ___________________</div>
        <div class="xs" style="margin-top:4px;">Penerima: {{ $order->pic_name ?? '___________________' }}</div>
    </div>
</div>
</div>
</body>
</html>
