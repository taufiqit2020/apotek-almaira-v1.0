<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Faktur LX-310 — {{ $order->order_no }}</title>
    @include('partners.orders.print.dotmatrix._styles')
</head>
<body>
@php
    $dm = \App\Support\DotMatrixText::class;
    $W = $dm::WIDTH;
    $L = 8;
    $fmt = static fn ($n) => number_format((float) $n, 0, ',', '.');

    $totals = $order->totalsBreakdown();
    $isPaid = $order->payment_status === \App\Models\PartnerOrder::PAYMENT_PAID;
    $isInvoice = $order->payment_method === \App\Models\PartnerOrder::PAY_INVOICE;
    $showSignature = $isInvoice;

    $payLabel = match ($order->payment_method) {
        'transfer' => 'TRANSFER BANK',
        'invoice' => 'INVOICE TEMPO',
        default => 'COD / TUNAI',
    };
    $statusText = $isPaid
        ? 'Lunas'
        : ($order->payment_status === \App\Models\PartnerOrder::PAYMENT_UNPAID
            ? 'Belum Lunas'
            : (string) $order->payment_status_label);
    $tempoText = ($isInvoice && $order->due_date) ? $order->due_date->format('d/m/Y') : '—';
    $tanggalText = $order->created_at?->timezone('Asia/Makassar')->format('d/m/Y H:i') ?? '—';
    $alamatText = trim((string) ($order->shipping_address ?: $order->partner?->address ?: '—')) ?: '—';

    $directorName = \App\Models\Salary::formatPersonName(
        \App\Models\Setting::get('pimpinan_name', 'Hj. Nor Maulida, S.H.')
    );
    $penerimaName = \App\Models\Salary::formatPersonName($order->partner?->name ?? '....................');

    $kopName = $isPT ? 'PT NUR MADANI FARMA' : 'APOTEK ALMAIRA';
    $kopTag = $isPT
        ? (string) ($kopTagline ?? 'Distributor & Mitra Pengadaan Alat Kesehatan & Farmasi')
        : 'Pelayanan Kesehatan & Kefarmasian Terpercaya';

    $lines = $dm::kopLines(
        $kopName,
        $kopTag,
        (string) $address,
        (string) $phone,
        (string) ($website ?? 'www.ptnurmadanifarma.com'),
        (string) ($instagram ?? '@apotekalmaira'),
        'FAKTUR PENJUALAN - '.$payLabel,
        $W
    );

    // Meta sesuai contoh:
    // Kepada .............. No. PO
    // Alamat  (wrap)
    // TANGGAL .... Tempo .... Status
    $lines[] = $dm::fieldPair(
        'Kepada',
        (string) ($order->partner?->name ?? '—'),
        'No. PO',
        (string) $order->order_no,
        $L,
        $W,
        34
    );
    foreach ($dm::fieldWrap('Alamat', $alamatText, $L, $W) as $row) {
        $lines[] = $row;
    }
    $lines[] = $dm::fieldTriple(
        'TANGGAL',
        $tanggalText,
        'Tempo',
        $tempoText,
        'Status',
        $statusText,
        $L,
        $W
    );
    $lines[] = '';

    // NO KODE NAMA BARANG SATUAN QTY BENTUK HARGA SUBTOTAL = 72
    $lines[] = $dm::row([
        ['NO', 2, 'left'],
        [' ', 1, 'left'],
        ['KODE', 8, 'left'],
        [' ', 1, 'left'],
        ['NAMA BARANG', 19, 'left'],
        [' ', 1, 'left'],
        ['SATUAN', 6, 'left'],
        [' ', 1, 'left'],
        ['QTY', 3, 'right'],
        [' ', 1, 'left'],
        ['BENTUK', 8, 'left'],
        [' ', 1, 'left'],
        ['HARGA', 9, 'right'],
        [' ', 1, 'left'],
        ['SUBTOTAL', 10, 'right'],
    ]);
    $lines[] = '';

    foreach ($order->items as $i => $item) {
        $meta = $item->catalogDisplay();
        $bentuk = $meta['bentuk'] === '—' ? '-' : $meta['bentuk'];
        $satuan = $meta['unit'] === '—' ? '-' : $meta['unit'];
        $lines[] = $dm::row([
            [(string) ($i + 1), 2, 'left'],
            [' ', 1, 'left'],
            [(string) $meta['code'], 8, 'left'],
            [' ', 1, 'left'],
            [(string) $item->product_name, 19, 'left'],
            [' ', 1, 'left'],
            [mb_strtoupper((string) $satuan, 'UTF-8'), 6, 'left'],
            [' ', 1, 'left'],
            [(string) $item->quantity, 3, 'right'],
            [' ', 1, 'left'],
            [(string) $bentuk, 8, 'left'],
            [' ', 1, 'left'],
            [$fmt($item->unit_price), 9, 'right'],
            [' ', 1, 'left'],
            [$fmt($item->subtotal), 10, 'right'],
        ]);
    }

    $lines[] = '';

    // Ringkasan sejajar (titik dua + Rp + angka)
    $lines[] = $dm::moneySummaryLine('Subtotal', $fmt($totals['subtotal']), 8, 10, 1, $W);
    $lines[] = $dm::moneySummaryLine('Diskon', $fmt($totals['discount_amount'] ?? 0), 8, 10, 1, $W);
    if (($totals['ppn_amount'] ?? 0) > 0) {
        $lines[] = $dm::moneySummaryLine('PPN', $fmt($totals['ppn_amount']), 8, 10, 1, $W);
    }
    $lines[] = $dm::moneySummaryLine('TOTAL', $fmt($totals['grand_total']), 8, 10, 1, $W);
    $lines[] = '';

    $sigLeftName = null;
    $sigRightName = null;
    $sigLeftPt = 15.0;
    $sigRightPt = 15.0;
    if ($showSignature) {
        $sigLeftName = $directorName;
        $sigRightName = $penerimaName;
        $halfChars = (int) floor($W / 2);
        $sigLeftPt = $dm::fitFontPt($sigLeftName, $halfChars, 15.0, 8.0);
        $sigRightPt = $dm::fitFontPt($sigRightName, $halfChars, 15.0, 8.0);
    }

    $footerLines = [];
    if ($showSignature) {
        $footerLines[] = '';
    }
    $footerLines[] = $dm::pad('Terima kasih atas kepercayaan Anda.', $W, 'center');
    $document = implode("\n", $lines);
    $footer = implode("\n", $footerLines);
@endphp

@include('partners.orders.print.dotmatrix._toolbar', [
    'toolbarTitle' => 'Faktur Penjualan · Epson LX-310',
    'toolbarSub'   => $order->order_no . ' · ' . ($order->partner?->name ?? '') . ' · ' . $payLabel,
])

<div class="page-wrapper">
<div class="container">
<pre class="dm-pre">{{ $document }}</pre>
@if($showSignature)
<div class="dm-sig">
    <div class="dm-sig-col">
        <div class="dm-sig-label">Direktur</div>
        <div class="dm-sig-space"></div>
        <div class="dm-sig-name" style="font-size: {{ $sigLeftPt }}pt">{{ $sigLeftName }}</div>
    </div>
    <div class="dm-sig-col">
        <div class="dm-sig-label">Penerima/Pembeli</div>
        <div class="dm-sig-space"></div>
        <div class="dm-sig-name" style="font-size: {{ $sigRightPt }}pt">{{ $sigRightName }}</div>
    </div>
</div>
@endif
<pre class="dm-pre">{{ $footer }}</pre>
</div>
</div>
</body>
</html>
