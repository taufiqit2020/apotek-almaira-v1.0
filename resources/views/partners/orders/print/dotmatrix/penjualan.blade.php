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

    $totals = $order->totalsBreakdown();
    $isPaid = $order->payment_status === \App\Models\PartnerOrder::PAYMENT_PAID;
    $isInvoice = $order->payment_method === \App\Models\PartnerOrder::PAY_INVOICE;
    $showSignature = $isInvoice;
    $payLabel = match ($order->payment_method) {
        'transfer' => 'TRANSFER BANK',
        'invoice' => 'INVOICE TEMPO',
        default => 'COD / TUNAI',
    };
    $directorName = \App\Models\Salary::formatPersonName(
        \App\Models\Setting::get('pimpinan_name', 'Hj. Nor Maulida, S.H.')
    );

    if ($isPT) {
        $kopName = 'PT. NUR MADANI FARMA';
        $kopTag = 'Distributor & Mitra Pengadaan Alat Kesehatan & Farmasi';
    } else {
        $kopName = 'APOTEK ALMAIRA';
        $kopTag = 'Pelayanan Kesehatan & Kefarmasian Terpercaya';
    }
    $addrLine = trim(preg_replace('/\s+/u', ' ', str_replace(["\r\n", "\n", "\r"], ' ', (string) $address)) ?? '');
    $W = $dm::WIDTH;
    $L = 9;
    $fmt = static fn ($n) => number_format((float) $n, 0, ',', '.');

    $lines = [];
    // Kop: alamat/tagline panjang dibungkus, tidak dipotong
    $lines[] = $dm::pad($kopName, $W, 'center');
    foreach ($dm::wrap($kopTag, $W, 'center') as $row) {
        $lines[] = $row;
    }
    foreach ($dm::wrap($addrLine, $W, 'center') as $row) {
        $lines[] = $row;
    }
    $lines[] = $dm::pad('Telp/WA: '.$phone, $W, 'center');
    $lines[] = '';
    $lines[] = $dm::pad('FAKTUR PENJUALAN - '.$payLabel, $W, 'center');
    $lines[] = '';

    $lines[] = $dm::fieldPair(
        'No. PO',
        (string) $order->order_no,
        'Tanggal',
        $order->created_at?->timezone('Asia/Makassar')->format('d/m/Y H:i') ?? '—',
        $L,
        $W
    );
    $lines[] = $dm::fieldPair(
        'Kepada',
        (string) ($order->partner?->name ?? '—'),
        'Metode',
        (string) $order->payment_method_label,
        $L,
        $W
    );
    $lines[] = $dm::fieldPair(
        'Status',
        $isPaid ? 'LUNAS' : strtoupper((string) $order->payment_status_label),
        'Tempo',
        ($isInvoice && $order->due_date) ? $order->due_date->format('d/m/Y') : '—',
        $L,
        $W
    );
    // Keterangan sejajar titik dua dengan field di atas
    if ($order->payment_method === 'transfer' && ! $isInvoice) {
        foreach ($dm::fieldWrap('Ket', $bankName.' a/n '.$bankHolder.' Rek '.$bankAccount, $L, $W) as $row) {
            $lines[] = $row;
        }
    } elseif ($isInvoice) {
        foreach ($dm::fieldWrap('Ket', 'Invoice tempo jatuh tempo '.($order->due_date?->format('d/m/Y') ?? '—'), $L, $W) as $row) {
            $lines[] = $row;
        }
    } elseif ($order->payment_method === 'cod') {
        foreach ($dm::fieldWrap('Ket', 'COD / tunai saat barang diterima', $L, $W) as $row) {
            $lines[] = $row;
        }
    }
    $lines[] = '';

    // NO(3)+1+KODE(8)+1+NAMA(22)+1+QTY(4)+1+HARGA(10)+1+SUBTOTAL(11) = 63
    $lines[] = $dm::row([
        ['NO', 3, 'left'],
        [' ', 1, 'left'],
        ['KODE', 8, 'left'],
        [' ', 1, 'left'],
        ['NAMA BARANG', 22, 'left'],
        [' ', 1, 'left'],
        ['QTY', 4, 'right'],
        [' ', 1, 'left'],
        ['HARGA', 10, 'right'],
        [' ', 1, 'left'],
        ['SUBTOTAL', 11, 'right'],
    ]);
    $lines[] = '';

    foreach ($order->items as $i => $item) {
        $meta = $item->catalogDisplay();
        $lines[] = $dm::row([
            [(string) ($i + 1), 3, 'left'],
            [' ', 1, 'left'],
            [(string) $meta['code'], 8, 'left'],
            [' ', 1, 'left'],
            [(string) $item->product_name, 22, 'left'],
            [' ', 1, 'left'],
            [(string) $item->quantity, 4, 'right'],
            [' ', 1, 'left'],
            [$fmt($item->unit_price), 10, 'right'],
            [' ', 1, 'left'],
            [$fmt($item->subtotal), 11, 'right'],
        ]);
        $lines[] = $dm::pad(
            '    Kat: '.$meta['category'].' | Sat: '.$meta['unit'],
            $W,
            'left'
        );
        $lines[] = $dm::pad(
            '    Kand: '.$meta['kandungan'].' | Bentuk: '.$meta['bentuk'],
            $W,
            'left'
        );
    }

    $lines[] = '';
    // Label + nominal sejajar kolom HARGA / SUBTOTAL di header
    $moneyRow = static function (string $label, string $amount) use ($dm): string {
        return $dm::row([
            ['', 3, 'left'],
            [' ', 1, 'left'],
            ['', 8, 'left'],
            [' ', 1, 'left'],
            ['', 22, 'left'],
            [' ', 1, 'left'],
            ['', 4, 'left'],
            [' ', 1, 'left'],
            [$label, 10, 'right'],
            [' ', 1, 'left'],
            [$amount, 11, 'right'],
        ]);
    };
    $lines[] = $moneyRow('Subtotal', 'Rp '.$fmt($totals['subtotal']));
    $lines[] = $moneyRow('Diskon', (($totals['discount_amount'] ?? 0) > 0 ? '- ' : '').'Rp '.$fmt($totals['discount_amount'] ?? 0));
    if (($totals['ppn_amount'] ?? 0) > 0) {
        $ppnLabel = 'PPN '.rtrim(rtrim(number_format($totals['ppn_percent'], 2, ',', '.'), '0'), ',');
        $lines[] = $moneyRow($ppnLabel, 'Rp '.$fmt($totals['ppn_amount']));
    }
    $lines[] = $moneyRow('TOTAL', 'Rp '.$fmt($totals['grand_total']));
    $lines[] = '';

    $sigLeftName = null;
    $sigRightName = null;
    $sigLeftPt = 15.0;
    $sigRightPt = 15.0;
    if ($showSignature) {
        $lines[] = '';
        $sigLeftName = \App\Models\Salary::formatPersonName($order->partner?->name ?? '....................');
        $sigRightName = $directorName;
        $halfChars = (int) floor($W / 2);
        $sigLeftPt = $dm::fitFontPt('( '.$sigLeftName.' )', $halfChars, 15.0, 8.0);
        $sigRightPt = $dm::fitFontPt('( '.$sigRightName.' )', $halfChars, 15.0, 8.0);
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
        <div class="dm-sig-label">Penerima / Pembeli,</div>
        <div class="dm-sig-space"></div>
        <div class="dm-sig-name" style="font-size: {{ $sigLeftPt }}pt">( {{ $sigLeftName }} )</div>
    </div>
    <div class="dm-sig-col">
        <div class="dm-sig-label">Direktur,</div>
        <div class="dm-sig-space"></div>
        <div class="dm-sig-name" style="font-size: {{ $sigRightPt }}pt">( {{ $sigRightName }} )</div>
    </div>
</div>
@endif
<pre class="dm-pre">{{ $footer }}</pre>
</div>
</div>
</body>
</html>
