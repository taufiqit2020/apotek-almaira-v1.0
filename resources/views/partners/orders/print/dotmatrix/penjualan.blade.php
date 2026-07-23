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
    $L = 8;
    $fmt = static fn ($n) => number_format((float) $n, 0, ',', '.');

    $statusText = $isPaid ? 'LUNAS' : strtoupper((string) $order->payment_status_label);
    $tempoText = ($isInvoice && $order->due_date) ? $order->due_date->format('d/m/Y') : '—';
    $tanggalText = $order->created_at?->timezone('Asia/Makassar')->format('d/m/Y H:i') ?? '—';

    $lines = [];

    // ── Kop ──
    $lines[] = $dm::pad($kopName, $W, 'center');
    foreach ($dm::wrap($kopTag, $W, 'center') as $row) {
        $lines[] = $row;
    }
    foreach ($dm::wrap($addrLine, $W, 'center') as $row) {
        $lines[] = $row;
    }
    $lines[] = $dm::pad('Telp/WA: '.$phone, $W, 'center');
    $lines[] = $dm::rule($W, '=');
    $lines[] = $dm::pad('FAKTUR PENJUALAN - '.$payLabel, $W, 'center');
    $lines[] = $dm::rule($W, '=');

    // ── Meta: nilai panjang full-width; pasangan pendek 2 kolom ──
    foreach ($dm::fieldWrap('No. PO', (string) $order->order_no, $L, $W) as $row) {
        $lines[] = $row;
    }
    $lines[] = $dm::fieldPair(
        'Tanggal',
        $tanggalText,
        'Metode',
        (string) $order->payment_method_label,
        $L,
        $W,
        34
    );
    foreach ($dm::fieldWrap('Kepada', (string) ($order->partner?->name ?? '—'), $L, $W) as $row) {
        $lines[] = $row;
    }
    $lines[] = $dm::fieldPair(
        'Status',
        $statusText,
        'Tempo',
        $tempoText,
        $L,
        $W,
        34
    );

    if ($order->payment_method === 'transfer' && ! $isInvoice) {
        foreach ($dm::fieldWrap('Ket', $bankName.' a/n '.$bankHolder.' · Rek '.$bankAccount, $L, $W) as $row) {
            $lines[] = $row;
        }
    } elseif ($order->payment_method === 'cod') {
        foreach ($dm::fieldWrap('Ket', 'COD / tunai saat barang diterima', $L, $W) as $row) {
            $lines[] = $row;
        }
    }
    $lines[] = $dm::rule($W, '-');

    // ── Header tabel ──
    // NO(3)+1+KODE(9)+1+NAMA(24)+1+QTY(4)+1+HARGA(9)+1+SUBTOTAL(10) = 64
    $lines[] = $dm::row([
        ['NO', 3, 'left'],
        [' ', 1, 'left'],
        ['KODE', 9, 'left'],
        [' ', 1, 'left'],
        ['NAMA BARANG', 24, 'left'],
        [' ', 1, 'left'],
        ['QTY', 4, 'right'],
        [' ', 1, 'left'],
        ['HARGA', 9, 'right'],
        [' ', 1, 'left'],
        ['SUBTOTAL', 10, 'right'],
    ]);
    $lines[] = $dm::rule($W, '-');

    foreach ($order->items as $i => $item) {
        $meta = $item->catalogDisplay();
        $lines[] = $dm::row([
            [(string) ($i + 1), 3, 'left'],
            [' ', 1, 'left'],
            [(string) $meta['code'], 9, 'left'],
            [' ', 1, 'left'],
            [(string) $item->product_name, 24, 'left'],
            [' ', 1, 'left'],
            [(string) $item->quantity, 4, 'right'],
            [' ', 1, 'left'],
            [$fmt($item->unit_price), 9, 'right'],
            [' ', 1, 'left'],
            [$fmt($item->subtotal), 10, 'right'],
        ]);

        // Detail produk — 2 kolom jika muat, wrap jika panjang
        foreach ($dm::fieldPairWrap(
            'Kategori',
            (string) $meta['category'],
            'Satuan',
            (string) $meta['unit'],
            9,
            $W - 4,
            36
        ) as $row) {
            $lines[] = '    '.rtrim($row);
        }
        foreach ($dm::fieldWrap('Kandungan', (string) $meta['kandungan'], 9, $W - 4) as $row) {
            $lines[] = '    '.rtrim($row);
        }
        foreach ($dm::fieldWrap('Bentuk', (string) $meta['bentuk'], 9, $W - 4) as $row) {
            $lines[] = '    '.rtrim($row);
        }
        $lines[] = '';
    }

    $lines[] = $dm::rule($W, '-');

    $moneyRow = static function (string $label, string $amount) use ($dm): string {
        return $dm::row([
            ['', 3, 'left'],
            [' ', 1, 'left'],
            ['', 9, 'left'],
            [' ', 1, 'left'],
            ['', 24, 'left'],
            [' ', 1, 'left'],
            ['', 4, 'left'],
            [' ', 1, 'left'],
            [$label, 9, 'right'],
            [' ', 1, 'left'],
            [$amount, 10, 'right'],
        ]);
    };
    $lines[] = $moneyRow('Subtotal', 'Rp '.$fmt($totals['subtotal']));
    $lines[] = $moneyRow('Diskon', (($totals['discount_amount'] ?? 0) > 0 ? '- ' : '').'Rp '.$fmt($totals['discount_amount'] ?? 0));
    if (($totals['ppn_amount'] ?? 0) > 0) {
        $ppnLabel = 'PPN '.rtrim(rtrim(number_format($totals['ppn_percent'], 2, ',', '.'), '0'), ',');
        $lines[] = $moneyRow($ppnLabel, 'Rp '.$fmt($totals['ppn_amount']));
    }
    $lines[] = $dm::rule($W, '=');
    $lines[] = $moneyRow('TOTAL', 'Rp '.$fmt($totals['grand_total']));
    $lines[] = $dm::rule($W, '=');
    $lines[] = '';

    $sigLeftName = null;
    $sigRightName = null;
    $sigLeftPt = 15.0;
    $sigRightPt = 15.0;
    if ($showSignature) {
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
