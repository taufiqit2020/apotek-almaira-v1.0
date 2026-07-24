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

    $kopLines = $dm::kopHeaderLines(
        $kopName,
        $kopTag,
        (string) $address,
        (string) $phone,
        (string) ($website ?? 'www.ptnurmadanifarma.com'),
        (string) ($instagram ?? '@apotekalmaira'),
        $W
    );
    $kopLines[] = '';
    $kopLines[] = $dm::oneLineCentered('FAKTUR PENJUALAN - '.$payLabel, $W);
    $kopLines[] = '';
    $kopText = implode("\n", $kopLines);

    $lines = [];

    $lines[] = $dm::fieldPair(
        'Kepada',
        (string) ($order->partner?->name ?? '—'),
        'No. PO',
        (string) $order->order_no,
        $L,
        $W,
        46
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

    // Tabel lebar 96 - Kolom rata tengah
    $lines[] = $dm::row([
        ['NO', 2, 'center'],
        [' ', 1, 'left'],
        ['KODE', 9, 'center'],
        [' ', 1, 'left'],
        ['NAMA BARANG', 30, 'center'],
        [' ', 1, 'left'],
        ['SATUAN', 7, 'center'],
        [' ', 1, 'left'],
        ['QTY', 4, 'center'],
        [' ', 1, 'left'],
        ['BENTUK', 10, 'center'],
        [' ', 1, 'left'],
        ['HARGA', 12, 'center'],
        [' ', 1, 'left'],
        ['SUBTOTAL', 14, 'center'],
    ]);
    $lines[] = '';

    // Format ringkasan: label rata kiri, Rp + angka rata kanan presisi (padRaw agar spasi tidak di-trim)
    $fmtSummaryRow = static function (string $label, $val) use ($dm, $fmt) {
        $labelPart = $dm::padRaw(str_pad($label, 8).' :', 10, 'left'); // 10 chars
        $amtPart   = $dm::padRaw($fmt($val), 10, 'right');             // 10 chars rata kanan
        // 44 + 10(label) + 24(gap) + 3(Rp ) + 10(amount) + 5(trail) = 96
        return str_repeat(' ', 44)
            .$labelPart
            .str_repeat(' ', 24)
            .'Rp '
            .$amtPart
            .str_repeat(' ', 5);
    };

    foreach ($order->items as $i => $item) {
        $meta = $item->catalogDisplay();
        $bentuk = $meta['bentuk'] === '—' ? '-' : $meta['bentuk'];
        $satuan = $meta['unit'] === '—' ? '-' : $meta['unit'];
        $lines[] = $dm::row([
            [(string) ($i + 1), 2, 'center'],
            [' ', 1, 'left'],
            [(string) $meta['code'], 9, 'center'],
            [' ', 1, 'left'],
            [(string) $item->product_name, 30, 'center'],
            [' ', 1, 'left'],
            [mb_strtoupper((string) $satuan, 'UTF-8'), 7, 'center'],
            [' ', 1, 'left'],
            [(string) $item->quantity, 4, 'center'],
            [' ', 1, 'left'],
            [(string) $bentuk, 10, 'center'],
            [' ', 1, 'left'],
            [$fmt($item->unit_price), 12, 'center'],
            [' ', 1, 'left'],
            [$fmt($item->subtotal), 14, 'center'],
        ]);
    }

    $lines[] = '';

    // Ringkasan sesuai gambar referensi: label rata kiri, Rp + angka rata kanan sejajar SUBTOTAL
    $lines[] = $fmtSummaryRow('Subtotal', $totals['subtotal']);
    $lines[] = $fmtSummaryRow('Diskon', $totals['discount_amount'] ?? 0);
    if (($totals['ppn_amount'] ?? 0) > 0) {
        $lines[] = $fmtSummaryRow('PPN', $totals['ppn_amount']);
    }
    $lines[] = $fmtSummaryRow('TOTAL', $totals['grand_total']);

    // Terbilang di bawah TOTAL
    $grandTotal = (int) round((float) ($totals['grand_total'] ?? 0));
    $terbilangStr = $dm::terbilang($grandTotal);
    $terbilangLine = $dm::pad('Terbilang : '.$terbilangStr.' Rupiah', $W, 'left');
    $lines[] = '';
    $lines[] = $terbilangLine;
    $lines[] = '';
    $lines[] = '';

    $sigLeftName = null;
    $sigRightName = null;
    $sigLeftPt = 10.5;
    $sigRightPt = 10.5;
    if ($showSignature) {
        $sigLeftName = $directorName;
        $sigRightName = $penerimaName;
        $halfChars = (int) floor($W / 2);
        $sigLeftPt = $dm::fitFontPt($sigLeftName, $halfChars, 10.5, 8.0);
        $sigRightPt = $dm::fitFontPt($sigRightName, $halfChars, 10.5, 8.0);
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
<pre class="dm-kop">{{ $kopText }}</pre>
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
<pre class="dm-foot">{{ $footer }}</pre>
</div>
</div>
</body>
</html>
