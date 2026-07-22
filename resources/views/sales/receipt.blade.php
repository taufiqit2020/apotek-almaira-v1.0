<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Struk Pembayaran {{ $sale->invoice_no }}</title>
    <style>
        /* ====== PRINT SIZE ====== */
        @page {
            size: 80mm auto;
            margin: 0;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Courier New', Courier, monospace;
            font-size: 11px;
            line-height: 1.5;
            color: #000;
            width: 80mm;
            padding: 5mm 4mm;
            background-color: #fff;
        }

        /* ====== TYPOGRAPHY ====== */
        .text-center { text-align: center; }
        .text-right  { text-align: right; }
        .text-left   { text-align: left; }
        .bold        { font-weight: bold; }
        .italic      { font-style: italic; }
        .small       { font-size: 9px; }
        .very-small  { font-size: 8px; }

        /* ====== DIVIDERS ====== */
        .divider {
            border: none;
            border-top: 1px dashed #000;
            margin: 5px 0;
        }
        .solid-divider {
            border: none;
            border-top: 1px solid #000;
            margin: 5px 0;
        }
        .double-divider {
            border: none;
            border-top: 3px double #000;
            margin: 5px 0;
        }

        /* ====== HEADER ====== */
        .header {
            text-align: center;
            margin-bottom: 4px;
        }
        .header .apotek-name {
            font-size: 15px;
            font-weight: bold;
            letter-spacing: 1px;
            margin-bottom: 1px;
        }
        .header .company-name {
            font-size: 9px;
            font-weight: bold;
            margin-bottom: 2px;
        }
        .header .contact {
            font-size: 9px;
            margin-bottom: 1px;
        }
        .header .address {
            font-size: 8px;
            line-height: 1.4;
        }

        /* ====== META INFO ====== */
        .meta-row {
            display: flex;
            justify-content: space-between;
            font-size: 10px;
            padding: 1px 0;
        }
        .meta-row .label { flex: 0 0 auto; }
        .meta-row .value { flex: 0 0 auto; text-align: right; }

        /* ====== ITEMS TABLE ====== */
        .items-header {
            display: flex;
            font-size: 10px;
            font-weight: bold;
            border-bottom: 1px dashed #000;
            padding-bottom: 3px;
            margin-bottom: 2px;
        }
        .items-header .col-item  { flex: 1; }
        .items-header .col-qty   { width: 20px; text-align: center; }
        .items-header .col-price { width: 55px; text-align: right; }

        .item-block {
            margin-bottom: 4px;
        }
        .item-name {
            font-size: 10px;
            font-weight: bold;
            word-break: break-word;
            line-height: 1.3;
        }
        .item-detail-row {
            display: flex;
            align-items: baseline;
            font-size: 10px;
        }
        .item-detail-row .col-harga {
            flex: 1;
            color: #333;
        }
        .item-detail-row .col-disc {
            font-size: 8px;
            color: #666;
            margin-left: 2px;
        }
        .item-detail-row .col-qty {
            width: 20px;
            text-align: center;
        }
        .item-detail-row .col-subtotal {
            width: 55px;
            text-align: right;
            font-weight: bold;
        }

        /* ====== SUMMARY TABLE ====== */
        .summary-row {
            display: flex;
            justify-content: space-between;
            font-size: 10px;
            padding: 1.5px 0;
        }
        .summary-row .s-label { flex: 1; }
        .summary-row .s-value { text-align: right; white-space: nowrap; padding-left: 8px; }
        .summary-row.grand-total {
            font-size: 13px;
            font-weight: bold;
            padding: 3px 0;
        }
        .summary-row.discount { color: #333; }
        .summary-row.ppn-info {
            font-size: 8px;
            font-style: italic;
            color: #555;
            padding: 0 0 2px 0;
        }

        /* ====== PAYMENT SECTION ====== */
        .payment-row {
            display: flex;
            justify-content: space-between;
            font-size: 10px;
            padding: 1.5px 0;
        }
        .payment-row .p-label { flex: 1; }
        .payment-row .p-value { text-align: right; white-space: nowrap; padding-left: 8px; }
        .payment-row.method   { font-weight: bold; }
        .payment-row.kembalian .p-value { font-weight: bold; }

        .qris-info {
            text-align: center;
            font-size: 8px;
            padding: 2px 0;
            color: #333;
        }

        /* ====== FOOTER ====== */
        .footer {
            margin-top: 10px;
            text-align: center;
        }
        .footer .terima-kasih {
            font-size: 12px;
            font-weight: bold;
            letter-spacing: 1px;
            margin-bottom: 2px;
        }
        .footer .pesan {
            font-size: 10px;
            margin-bottom: 2px;
        }
        .footer .disclaimer {
            font-size: 8px;
            color: #555;
            line-height: 1.4;
        }

        /* ====== PRINT BUTTON (web only) ====== */
        @media screen {
            .print-wrapper {
                width: 80mm;
                margin: 16px auto;
            }
            .btn-cetak {
                display: block;
                width: 100%;
                padding: 10px;
                background-color: #10b981;
                color: #fff;
                border: none;
                border-radius: 6px;
                font-family: sans-serif;
                font-weight: bold;
                cursor: pointer;
                text-align: center;
                font-size: 13px;
                margin-bottom: 10px;
                letter-spacing: 0.5px;
            }
            .btn-cetak:hover { background-color: #059669; }
            body {
                margin: 0 auto;
                box-shadow: 0 2px 16px rgba(0,0,0,0.15);
                border: 1px solid #ddd;
                min-height: 100vh;
            }
        }
        @media print {
            .btn-cetak { display: none !important; }
        }
    </style>
</head>
<body>

    <!-- ===== CETAK BUTTON (web preview only) ===== -->
    <button onclick="window.print()" class="btn-cetak">🖨️ Cetak Struk</button>

    <!-- ===== HEADER ===== -->
    <div class="header">
        <div class="apotek-name">APOTEK ALMAIRA</div>
        <div class="company-name">PT NUR MADANI FARMA</div>
        <div class="contact">Telp/WA: 0851-6665-7070</div>
        <div class="address">Jl. Nuri No.14 RT/RW 001/005, Kel. Komet<br>Banjarbaru Utara, Kalsel 70714</div>
    </div>

    <hr class="solid-divider">

    <!-- ===== META INFO ===== -->
    <div class="meta-row">
        <span class="label">{{ $sale->document_label }}</span>
        <span class="value bold">{{ $sale->invoice_no }}</span>
    </div>
    <div class="meta-row">
        <span class="label">Tanggal</span>
        <span class="value">{{ $sale->sold_at->format('d/m/Y  H:i') }}</span>
    </div>
    <div class="meta-row">
        <span class="label">Kasir</span>
        <span class="value">{{ $sale->user?->name ?? 'Kasir' }}</span>
    </div>
    <div class="meta-row">
        <span class="label">Pelanggan</span>
        <span class="value">{{ $sale->customer_name ?: 'Umum' }}</span>
    </div>

    <hr class="divider">

    <!-- ===== ITEMS ===== -->
    <div class="items-header">
        <span class="col-item">Item</span>
        <span class="col-qty">Qty</span>
        <span class="col-price">Total</span>
    </div>

    @foreach($sale->items as $item)
    <div class="item-block">
        <div class="item-name">{{ $item->product_name }}</div>
        <div class="item-detail-row">
            <span class="col-harga">
                @php
                    $hargaLabel = number_format($item->unit_price, 0, ',', '.');
                    $priceType = isset($item->price_type) && $item->price_type === 'wholesale' ? 'Grosir' : 'Eceran';
                @endphp
                Rp {{ $hargaLabel }}
                @if($item->discount_percent > 0)
                    <span class="col-disc">(-{{ number_format($item->discount_percent, 1, ',', '.') }}%)</span>
                @endif
            </span>
            <span class="col-qty">{{ $item->quantity }}</span>
            <span class="col-subtotal">{{ number_format($item->subtotal, 0, ',', '.') }}</span>
        </div>
    </div>
    @endforeach

    <hr class="divider">

    <!-- ===== SUMMARY ===== -->
    <div class="summary-row">
        <span class="s-label">Subtotal</span>
        <span class="s-value">Rp {{ number_format($sale->subtotal, 0, ',', '.') }}</span>
    </div>

    @if(isset($sale->discount_amount) && $sale->discount_amount > 0)
    <div class="summary-row discount">
        <span class="s-label">Diskon ({{ number_format($sale->discount_percent ?? 0, 1, ',', '.') }}%)</span>
        <span class="s-value">- Rp {{ number_format($sale->discount_amount, 0, ',', '.') }}</span>
    </div>
    @endif

    @if(isset($sale->ppn_active) && $sale->ppn_active)
    <div class="summary-row">
        <span class="s-label">PPN 11%</span>
        <span class="s-value">Rp {{ number_format($sale->ppn_amount, 0, ',', '.') }}</span>
    </div>

    @endif

    <hr class="double-divider">

    <div class="summary-row grand-total">
        <span class="s-label">TOTAL</span>
        <span class="s-value">Rp {{ number_format($sale->total, 0, ',', '.') }}</span>
    </div>

    <hr class="divider">

    <!-- ===== PAYMENT ===== -->
    <div class="payment-row method">
        <span class="p-label">Metode Bayar</span>
        <span class="p-value">{{ $sale->payment_method }}</span>
    </div>

    @if($sale->payment_method === 'Tunai')
    <div class="payment-row">
        <span class="p-label">Tunai Diterima</span>
        <span class="p-value">Rp {{ number_format($sale->cash_received, 0, ',', '.') }}</span>
    </div>
    <div class="payment-row kembalian">
        <span class="p-label">Kembalian</span>
        <span class="p-value">Rp {{ number_format($sale->change_amount, 0, ',', '.') }}</span>
    </div>
    @elseif($sale->payment_method === 'Transfer')
    <div class="qris-info" style="font-weight: bold; text-transform: uppercase;">
        Transfer Bank (LUNAS)
    </div>
    @elseif($sale->payment_method === 'Invoice')
    <div class="payment-row">
        <span class="p-label">Status Bayar</span>
        <span class="p-value bold">{{ $sale->payment_status === 'paid' ? 'LUNAS' : 'BELUM LUNAS' }}</span>
    </div>
    <div class="payment-row">
        <span class="p-label">Jatuh Tempo</span>
        <span class="p-value">{{ $sale->due_date ? $sale->due_date->format('d/m/Y') : '-' }}</span>
    </div>
    @else
    <div class="qris-info">
        QRIS BNI Wondr — NMID: ID1026522359276
    </div>
    @endif

    @if($sale->notes)
    <hr class="divider">
    <div style="font-size: 9px; line-height: 1.4;">
        <span class="bold">Catatan:</span> {{ $sale->notes }}
    </div>
    @endif

    <hr class="double-divider">

    <!-- ===== FOOTER ===== -->
    <div class="footer">
        <div class="terima-kasih">TERIMA KASIH</div>
        <div class="pesan">Semoga Lekas Sembuh 🙏</div>
        <div class="disclaimer">
            Barang yang sudah dibeli tidak dapat<br>ditukar atau dikembalikan.
        </div>
        <div style="font-size: 7.5px; color: #555; margin-top: 10px; font-family: monospace; letter-spacing: 0px;">
            ©Copyright Apotek Almaira v1.0<br>PT Nur Madani Farma
        </div>
    </div>

    <script>
        // Auto print when opened from POS (not when user manually visits)
        const urlParams = new URLSearchParams(window.location.search);
        if (urlParams.get('autoprint') === '1') {
            window.onload = function() { window.print(); }
        }
    </script>
</body>
</html>
