<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Surat Jalan LX-310 — {{ $order->order_no }}</title>
    @include('partners.orders.print.dotmatrix._styles')
</head>
<body>
@include('partners.orders.print.dotmatrix._toolbar', [
    'toolbarTitle' => 'Surat Jalan · Epson LX-310',
    'toolbarSub'   => $order->order_no . ' · ' . ($order->partner?->name ?? ''),
])

@php
    $dm = \App\Support\DotMatrixText::class;
    $W = $dm::WIDTH;
    $L = 8;

    $docDate = $order->fulfilled_at ?? $order->confirmed_at ?? $order->created_at;
    $qtyTotal = (int) $order->items->sum('quantity');
    $itemCount = $order->items->count();
    $picLine = trim((string) ($order->pic_name ?? '—')) ?: '—';
    $shipAddr = trim((string) ($order->shipping_address ?: $order->partner?->address ?: '—')) ?: '—';
    $tanggalText = $docDate?->timezone('Asia/Makassar')->format('d/m/Y H:i') ?? '—';
    $statusText = (string) ($order->status_label ?? $order->status ?? '—');

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
    $kopLines[] = $dm::oneLineCentered('SURAT JALAN', $W);
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
    foreach ($dm::fieldWrap('Alamat', $shipAddr, $L, $W) as $row) {
        $lines[] = $row;
    }
    $lines[] = $dm::fieldTriple(
        'TANGGAL',
        $tanggalText,
        'PIC',
        $picLine,
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
        ['NAMA BARANG', 42, 'center'],
        [' ', 1, 'left'],
        ['SATUAN', 7, 'center'],
        [' ', 1, 'left'],
        ['QTY', 5, 'center'],
        [' ', 1, 'left'],
        ['BENTUK', 26, 'center'],
    ]);
    $lines[] = '';

    foreach ($order->items as $i => $item) {
        $meta = $item->catalogDisplay();
        $bentuk = $meta['bentuk'] === '—' ? '-' : $meta['bentuk'];
        $satuan = $meta['unit'] === '—' ? '-' : $meta['unit'];
        $lines[] = $dm::row([
            [(string) ($i + 1), 2, 'center'],
            [' ', 1, 'left'],
            [(string) $meta['code'], 9, 'center'],
            [' ', 1, 'left'],
            [(string) $item->product_name, 42, 'center'],
            [' ', 1, 'left'],
            [mb_strtoupper((string) $satuan, 'UTF-8'), 7, 'center'],
            [' ', 1, 'left'],
            [(string) $item->quantity, 5, 'center'],
            [' ', 1, 'left'],
            [(string) $bentuk, 26, 'center'],
        ]);
    }

    $lines[] = '';
    // TOTAL QTY sejajar kolom SATUAN + angka di kolom QTY
    $lines[] = $dm::row([
        ['', 2, 'center'],
        [' ', 1, 'left'],
        ['', 9, 'center'],
        [' ', 1, 'left'],
        ['', 42, 'center'],
        [' ', 1, 'left'],
        ['TOTAL QTY', 7, 'center'],
        [' ', 1, 'left'],
        [(string) $qtyTotal, 5, 'center'],
        [' ', 1, 'left'],
        ['('.$itemCount.' jenis)', 26, 'left'],
    ]);
    $lines[] = '';
    $lines[] = '';
    $lines[] = '';

    if ($order->notes) {
        foreach ($dm::fieldWrap('Catatan', (string) $order->notes, $L, $W) as $row) {
            $lines[] = $row;
        }
        $lines[] = '';
    }

    $pengirimName = \App\Models\Salary::formatPersonName(
        \App\Models\Setting::get('pimpinan_name', 'Hj. Nor Maulida, S.H.')
    );
    $penerimaName = \App\Models\Salary::formatPersonName(
        $order->pic_name ?: ($order->partner?->name ?? '....................')
    );
    $halfChars = (int) floor($W / 2);
    $pengirimPt = $dm::fitFontPt($pengirimName, $halfChars, 10.5, 8.0);
    $penerimaPt = $dm::fitFontPt($penerimaName, $halfChars, 10.5, 8.0);

    $footerLines = [];
    $footerLines[] = '';
    $footerLines[] = $dm::pad('Barang diserahkan dalam kondisi baik.', $W, 'center');
    $footerLines[] = $dm::pad('Dokumen bukti serah terima.', $W, 'center');
    $document = implode("\n", $lines);
    $footer = implode("\n", $footerLines);
@endphp

<div class="page-wrapper">
<div class="container">
<pre class="dm-kop">{{ $kopText }}</pre>
<pre class="dm-pre">{{ $document }}</pre>
<div class="dm-sig">
    <div class="dm-sig-col">
        <div class="dm-sig-label">Pengirim</div>
        <div class="dm-sig-space"></div>
        <div class="dm-sig-name" style="font-size: {{ $pengirimPt }}pt">{{ $pengirimName }}</div>
    </div>
    <div class="dm-sig-col">
        <div class="dm-sig-label">Penerima</div>
        <div class="dm-sig-space"></div>
        <div class="dm-sig-name" style="font-size: {{ $penerimaPt }}pt">{{ $penerimaName }}</div>
    </div>
</div>
<pre class="dm-foot">{{ $footer }}</pre>
</div>
</div>
</body>
</html>
