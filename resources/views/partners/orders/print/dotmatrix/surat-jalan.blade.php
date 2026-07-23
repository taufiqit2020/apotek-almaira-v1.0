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

    $docDate = $order->fulfilled_at ?? $order->confirmed_at ?? $order->created_at;
    $qtyTotal = (int) $order->items->sum('quantity');
    $itemCount = $order->items->count();

    if ($isPT) {
        $kopName = 'PT. NUR MADANI FARMA';
        $kopTag = 'Distributor & Mitra Pengadaan Alat Kesehatan & Farmasi';
    } else {
        $kopName = 'APOTEK ALMAIRA';
        $kopTag = 'Pelayanan Kesehatan & Kefarmasian Terpercaya';
    }
    $addrLine = trim(preg_replace('/\s+/u', ' ', str_replace(["\r\n", "\n", "\r"], ' ', (string) $address)) ?? '');
    $picLine = trim((string) ($order->pic_name ?? '—'));
    $shipAddr = trim((string) ($order->shipping_address ?? '—'));

    $W = $dm::WIDTH;
    $L = 8;
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
    $lines[] = $dm::pad('SURAT JALAN', $W, 'center');
    $lines[] = $dm::rule($W, '=');

    // ── Info ──
    foreach ($dm::fieldWrap('No. PO', (string) $order->order_no, $L, $W) as $row) {
        $lines[] = $row;
    }
    $lines[] = $dm::fieldPair(
        'Tanggal',
        $docDate?->timezone('Asia/Makassar')->format('d/m/Y H:i') ?? '—',
        'PIC',
        $picLine,
        $L,
        $W,
        34
    );
    foreach ($dm::fieldWrap('Mitra', (string) ($order->partner?->name ?? '—'), $L, $W) as $row) {
        $lines[] = $row;
    }
    foreach ($dm::fieldWrap('Alamat', $shipAddr, $L, $W) as $row) {
        $lines[] = $row;
    }
    $lines[] = $dm::rule($W, '-');

    // ── Tabel barang ──
    // NO(3)+1+KODE(10)+1+NAMA(32)+1+SATUAN(9)+1+QTY(5) = 63 ≈ WIDTH 64
    $lines[] = $dm::row([
        ['NO', 3, 'left'],
        [' ', 1, 'left'],
        ['KODE', 10, 'left'],
        [' ', 1, 'left'],
        ['NAMA BARANG', 32, 'left'],
        [' ', 1, 'left'],
        ['SATUAN', 9, 'left'],
        [' ', 1, 'left'],
        ['QTY', 5, 'right'],
    ]);
    $lines[] = $dm::rule($W, '-');

    foreach ($order->items as $i => $item) {
        $meta = $item->catalogDisplay();
        $lines[] = $dm::row([
            [(string) ($i + 1), 3, 'left'],
            [' ', 1, 'left'],
            [(string) $meta['code'], 10, 'left'],
            [' ', 1, 'left'],
            [(string) $item->product_name, 32, 'left'],
            [' ', 1, 'left'],
            [(string) $meta['unit'], 9, 'left'],
            [' ', 1, 'left'],
            [(string) $item->quantity, 5, 'right'],
        ]);
        foreach ($dm::fieldWrap('Kategori', (string) $meta['category'], 9, $W - 4) as $row) {
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
    // Sejajarkan angka TOTAL QTY dengan kolom QTY di header
    $lines[] = $dm::row([
        ['', 3, 'left'],
        [' ', 1, 'left'],
        ['', 10, 'left'],
        [' ', 1, 'left'],
        [$itemCount.' jenis produk', 32, 'left'],
        [' ', 1, 'left'],
        ['TOTAL QTY', 9, 'right'],
        [' ', 1, 'left'],
        [(string) $qtyTotal, 5, 'right'],
    ]);
    $lines[] = $dm::rule($W, '=');
    $lines[] = '';

    if ($order->notes) {
        foreach ($dm::fieldWrap('Catatan', (string) $order->notes, $L, $W) as $row) {
            $lines[] = $row;
        }
        $lines[] = '';
    }

    // ── Tanda tangan: nama 1 baris, font mengecil jika panjang ──
    $lines[] = '';
    $penerimaName = \App\Models\Salary::formatPersonName(
        $order->pic_name ?? $order->partner?->name ?? '....................'
    );
    $halfChars = (int) floor($W / 2);
    $pengirimPt = $dm::fitFontPt('( .................... )', $halfChars, 15.0, 8.0);
    $penerimaPt = $dm::fitFontPt('( '.$penerimaName.' )', $halfChars, 15.0, 8.0);

    $footerLines = [];
    $footerLines[] = '';
    $footerLines[] = $dm::pad('Barang diserahkan dalam kondisi baik.', $W, 'center');
    $footerLines[] = $dm::pad('Dokumen bukti serah terima.', $W, 'center');
    $document = implode("\n", $lines);
    $footer = implode("\n", $footerLines);
@endphp

<div class="page-wrapper">
<div class="container">
<pre class="dm-pre">{{ $document }}</pre>
<div class="dm-sig">
    <div class="dm-sig-col">
        <div class="dm-sig-label">Pengirim,</div>
        <div class="dm-sig-space"></div>
        <div class="dm-sig-name" style="font-size: {{ $pengirimPt }}pt">( .................... )</div>
    </div>
    <div class="dm-sig-col">
        <div class="dm-sig-label">Penerima,</div>
        <div class="dm-sig-space"></div>
        <div class="dm-sig-name" style="font-size: {{ $penerimaPt }}pt">( {{ $penerimaName }} )</div>
    </div>
</div>
<pre class="dm-pre">{{ $footer }}</pre>
</div>
</div>
</body>
</html>
