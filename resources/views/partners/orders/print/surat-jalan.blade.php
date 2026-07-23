@extends('partners.orders.print.layout')

@section('content')
@php
    $docDate = $order->fulfilled_at ?? $order->confirmed_at ?? $order->created_at;
    $qtyTotal = $order->items->sum('quantity');
@endphp

<div class="doc-header">
    <div class="doc-header-title">Surat Jalan</div>
    <div class="meta-grid">
        <div class="meta-item">
            <label>No. Surat Jalan / PO</label>
            <span class="mono">{{ $order->order_no }}</span>
        </div>
        <div class="meta-item">
            <label>Tanggal</label>
            <span>{{ $docDate?->timezone('Asia/Makassar')->locale('id')->isoFormat('dddd, D MMMM Y') }}</span>
        </div>
        <div class="meta-item">
            <label>Mitra / Penerima</label>
            <span>{{ $order->partner?->name }} ({{ $order->partner?->code }})</span>
        </div>
        <div class="meta-item">
            <label>PIC Penerima</label>
            <span>{{ $order->pic_name ?? '—' }} · {{ $order->pic_phone ?? '—' }}</span>
        </div>
        <div class="meta-item" style="grid-column: 1 / -1;">
            <label>Alamat Pengiriman</label>
            <span>{{ $order->shipping_address ?? '—' }}</span>
        </div>
        @if($order->notes)
        <div class="meta-item" style="grid-column: 1 / -1;">
            <label>Catatan</label>
            <span>{{ $order->notes }}</span>
        </div>
        @endif
    </div>
</div>

<table class="data-table">
    <thead>
        <tr>
            <th class="text-center" style="width:36px">No</th>
            <th style="width:72px">Kode</th>
            <th>Nama Produk</th>
            <th style="width:78px">Kategori</th>
            <th class="text-center" style="width:56px">Satuan</th>
            <th style="width:110px">Kandungan</th>
            <th style="width:90px">Bentuk Sediaan</th>
            <th class="text-center" style="width:52px">Qty</th>
        </tr>
    </thead>
    <tbody>
        @foreach($order->items as $i => $item)
        @php $meta = $item->catalogDisplay(); @endphp
        <tr>
            <td class="text-center">{{ $i + 1 }}</td>
            <td class="font-mono text-xs">{{ $meta['code'] }}</td>
            <td>{{ $item->product_name }}</td>
            <td>{{ $meta['category'] }}</td>
            <td class="text-center">{{ $meta['unit'] }}</td>
            <td>{{ $meta['kandungan'] }}</td>
            <td>{{ $meta['bentuk'] }}</td>
            <td class="text-center font-bold">{{ $item->quantity }}</td>
        </tr>
        @endforeach
    </tbody>
    <tfoot>
        <tr>
            <td colspan="7" class="text-right font-bold" style="border-top:2px solid #e2e8f0;padding-top:8px;">Total Qty</td>
            <td class="text-center font-bold" style="border-top:2px solid #e2e8f0;padding-top:8px;">{{ $qtyTotal }}</td>
        </tr>
    </tfoot>
</table>

<p style="margin-top:14px;font-size:9px;color:#64748b;line-height:1.6;">
    Barang tersebut telah diserahkan dalam kondisi baik. Dokumen ini dijadikan bukti serah terima barang antara pengirim dan penerima.
</p>

{{-- Tanda tangan serah terima --}}
<div style="margin-top:32px;page-break-inside:avoid;">
    <table style="width:100%;border-collapse:collapse;font-size:9.5px;">
        <tr>
            <td style="width:50%;text-align:center;vertical-align:top;padding:0 12px;">
                <p style="font-weight:800;text-transform:uppercase;color:#1e293b;margin-bottom:4px;">Pengirim</p>
                <p style="color:#64748b;margin-bottom:52px;">{{ ($entity ?? 'pt') === 'pt' ? 'Gudang PT Nur Madani Farma' : 'Apotek Almaira' }}</p>
                <div style="border-bottom:1px solid #374151;width:75%;margin:0 auto 4px;"></div>
                <p style="font-weight:700;color:#1e293b;">(&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;)</p>
            </td>
            <td style="width:50%;text-align:center;vertical-align:top;padding:0 12px;">
                <p style="font-weight:800;text-transform:uppercase;color:#1e293b;margin-bottom:4px;">Penerima</p>
                <p style="color:#64748b;margin-bottom:52px;">{{ $order->partner?->name }}</p>
                <div style="border-bottom:1px solid #374151;width:75%;margin:0 auto 4px;"></div>
                <p style="font-weight:700;color:#1e293b;">{{ $order->pic_name ?? '( __________________ )' }}</p>
            </td>
        </tr>
    </table>
</div>
@endsection
