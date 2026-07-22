<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cetak Invoice - {{ $sale->invoice_no }}</title>
    <style>
        /*
         * Invoice Epson LX-310 — continuous 25.00 × 28.50 cm
         */
        @page {
            size: 250mm 285mm;
            /* Geser 1 cm ke kiri (21mm → 11mm) */
            margin: 4mm 6mm 4mm 11mm;
        }
        * {
            box-sizing: border-box;
            -webkit-font-smoothing: none !important;
            -moz-osx-font-smoothing: grayscale !important;
            text-rendering: geometricPrecision !important;
            color: #000 !important;
            background: transparent !important;
        }
        html, body {
            width: 250mm;
            margin: 0;
            padding: 0;
            background: #fff !important;
        }
        body {
            font-family: "Courier New", Courier, "Lucida Console", monospace;
            font-size: 16pt;
            font-weight: bold;
            line-height: 1.2;
            color: #000;
        }
        .container {
            width: 100%;
            max-width: 238mm;
            margin: 0;
            padding: 0;
            text-align: left;
        }
        h1, h2, h3, h4, h5, h6, p {
            margin: 0;
            padding: 0;
        }
        .text-center { text-align: center !important; }
        .text-right { text-align: right !important; }
        .text-left { text-align: left !important; }
        .font-bold { font-weight: bold !important; }
        
        .header {
            text-align: center;
            padding-bottom: 4px;
            margin-bottom: 6px;
            border: none;
        }
        .header h1 {
            font-size: 20pt;
            margin-bottom: 3px;
            text-transform: uppercase;
            font-weight: 900;
            letter-spacing: 0.5px;
        }
        .header p {
            font-size: 14pt;
            font-weight: bold;
        }
        .sep-line {
            margin: 4px 0 6px;
            font-size: 14pt;
            font-weight: bold;
            letter-spacing: -0.5px;
            white-space: nowrap;
            overflow: hidden;
        }

        .invoice-title {
            text-align: center;
            font-size: 16pt;
            text-decoration: underline;
            margin-bottom: 8px;
            text-transform: uppercase;
            font-weight: bold;
        }

        .info-table {
            width: 100%;
            margin-bottom: 8px;
        }
        .info-table td {
            vertical-align: top;
            padding: 2px 2px 2px 0;
            font-size: 14pt;
            font-weight: bold;
        }

        .item-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 8px;
        }
        .item-table th, .item-table td {
            padding: 3px 2px;
            vertical-align: top;
            font-size: 14pt;
            font-weight: bold;
        }
        .item-table th,
        .item-table td {
            border: none !important;
        }
        .item-table th {
            text-transform: uppercase;
            font-size: 12pt;
        }

        .summary-table {
            width: 100%;
        }
        .summary-table td {
            padding: 2px 0;
            vertical-align: top;
            font-size: 14pt;
            font-weight: bold;
        }
        .summary-table .total-row {
            font-weight: bold;
            font-size: 16pt;
        }

        .signature-area {
            width: 100%;
            margin-top: 14px;
        }
        .signature-col {
            width: 32%;
            display: inline-block;
            text-align: center;
            vertical-align: top;
            font-size: 14pt;
            font-weight: bold;
        }
        .signature-space {
            height: 36px;
        }
        .signature-name {
            text-decoration: underline;
            font-weight: bold;
            font-size: 14pt;
        }

        .footer {
            margin-top: 10px;
            text-align: center;
            font-size: 12pt;
            padding-top: 5px;
            border-top: none;
            font-weight: bold;
        }

        .print-tips {
            margin: 68px 16px 0;
            padding: 10px 12px;
            border: 1px dashed #94a3b8;
            border-radius: 8px;
            background: #f8fafc;
            font-family: system-ui, -apple-system, sans-serif;
            font-size: 11px;
            font-weight: 500;
            color: #334155;
            line-height: 1.45;
        }
        .print-tips strong { color: #0f172a; }

        @media print {
            .no-print { display: none !important; }
            html, body { width: 250mm; background: #fff !important; }
            .container { max-width: none; }
            .print-tips { display: none !important; }
        }

        /* ========== TOOLBAR (layar saja) ========== */
        .print-toolbar {
            position: fixed;
            top: 0; left: 0; right: 0;
            z-index: 9999;
            background: linear-gradient(135deg, #064e3b, #047857);
            color: #fff;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 10px 20px;
            box-shadow: 0 2px 12px rgba(0,0,0,0.2);
            gap: 12px;
            min-height: 56px;
            font-family: system-ui, -apple-system, "Segoe UI", Roboto, sans-serif;
        }
        .print-toolbar .brand {
            display: flex;
            align-items: center;
            gap: 10px;
            min-width: 0;
        }
        .print-toolbar .brand-icon {
            width: 36px;
            height: 36px;
            border-radius: 10px;
            background: rgba(255,255,255,0.15);
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }
        .print-toolbar .brand-title {
            font-size: 14px;
            font-weight: 700;
            line-height: 1.2;
        }
        .print-toolbar .brand-sub {
            font-size: 11px;
            opacity: 0.8;
            font-weight: 500;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            max-width: 280px;
        }
        .print-toolbar .print-hint {
            font-size: 11px;
            opacity: 0.85;
            text-align: center;
            flex: 1;
            padding: 0 12px;
            display: none;
        }
        @media (min-width: 768px) {
            .print-toolbar .print-hint { display: block; }
        }
        .toolbar-actions {
            display: flex;
            gap: 8px;
            align-items: center;
            flex-shrink: 0;
        }
        .btn-print {
            background: #fff;
            color: #065f46;
            border: none;
            border-radius: 10px;
            padding: 9px 16px;
            font-size: 13px;
            font-weight: 700;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 7px;
            transition: all 0.15s ease;
            box-shadow: 0 1px 3px rgba(0,0,0,0.12);
        }
        .btn-print:hover {
            background: #ecfdf5;
            transform: translateY(-1px);
        }
        .btn-back {
            background: rgba(255,255,255,0.12);
            color: #fff;
            border: 1px solid rgba(255,255,255,0.35);
            border-radius: 10px;
            padding: 9px 14px;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            transition: all 0.15s ease;
        }
        .btn-back:hover {
            background: rgba(255,255,255,0.22);
        }
        .page-wrapper {
            margin-top: 12px;
            padding: 8px 12px 32px;
        }
        @media print {
            .page-wrapper { margin-top: 0; padding: 0; }
        }
    </style>
</head>
<body>
    <div class="print-toolbar no-print">
        <div class="brand">
            <div class="brand-icon">
                <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
            </div>
            <div>
                <div class="brand-title">Invoice · Epson LX-310 · 25 × 28,5 cm</div>
                <div class="brand-sub">{{ $sale->invoice_no }} — {{ $sale->customer_name }}</div>
            </div>
        </div>
        <div class="print-hint">
            Scale <strong>100%</strong> · matikan Fit/Shrink · kertas driver <strong>25 × 28,5 cm</strong>
        </div>
        <div class="toolbar-actions">
            <button type="button" class="btn-print" onclick="printInvoice()">
                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                </svg>
                Cetak LX-310
            </button>
            <button type="button" class="btn-back" onclick="closePrintPage()">
                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
                Tutup
            </button>
        </div>
    </div>

    <div class="print-tips no-print">
        <strong>Tips LX-310:</strong>
        Driver Continuous <strong>25,00 × 28,50 cm</strong> · dialog cetak Scale <strong>100%</strong>
        (jangan Fit to page) · Margins Minimum · kualitas Draft/LQ.
    </div>

    <div class="page-wrapper">
    <div class="container">
        <!-- HEADER -->
        <div class="header">
            <h1>APOTEK ALMAIRA</h1>
            <p class="font-bold">Pelayanan Kesehatan & Kefarmasian Terpercaya</p>
            <p>Jl. Nuri No.14 RT/RW 001/005, Kel. Komet, Banjarbaru Utara, Kalsel 70714</p>
            <p>Telp/WA: 0851-6665-7070</p>
        </div>
        <!-- TITLE -->
        <div class="invoice-title font-bold">INVOICE PENJUALAN</div>

        <!-- INFO -->
        <table class="info-table">
            <tr>
                <td width="15%">PENJUALAN INVOICE</td>
                <td width="2%">:</td>
                <td width="33%" class="font-bold">{{ $sale->invoice_no }}</td>
                <td width="15%">Kepada</td>
                <td width="2%">:</td>
                <td width="33%" class="font-bold">{{ $sale->customer_name }}</td>
            </tr>
            <tr>
                <td>Tanggal</td>
                <td>:</td>
                <td>{{ \Carbon\Carbon::parse($sale->sold_at)->format('d-m-Y H:i') }}</td>
                <td>Jatuh Tempo</td>
                <td>:</td>
                <td class="font-bold">{{ \Carbon\Carbon::parse($sale->due_date)->format('d-m-Y') }}</td>
            </tr>
            <tr>
                <td>Kasir</td>
                <td>:</td>
                <td>{{ $sale->user->name }}</td>
                <td>Status</td>
                <td>:</td>
                <td>{{ $sale->payment_status === 'paid' ? 'LUNAS (' . \Carbon\Carbon::parse($sale->settled_at)->format('d/m/Y') . ')' : 'BELUM LUNAS' }}</td>
            </tr>
        </table>

        <!-- ITEMS -->
        <table class="item-table">
            <thead>
                <tr>
                    <th width="5%">No.</th>
                    <th width="45%">Nama Barang</th>
                    <th width="10%" class="text-center">Qty</th>
                    <th width="15%" class="text-right">Harga</th>
                    <th width="10%" class="text-right">Disc</th>
                    <th width="15%" class="text-right">Subtotal</th>
                </tr>
            </thead>
            <tbody>
                @foreach($sale->items as $index => $item)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $item->product_name }}</td>
                    <td class="text-center">{{ $item->quantity }} {{ $item->unit_name }}</td>
                    <td class="text-right">{{ number_format($item->unit_price, 0, ',', '.') }}</td>
                    <td class="text-right">{{ $item->discount_percent > 0 ? $item->discount_percent.'%' : '-' }}</td>
                    <td class="text-right">{{ number_format($item->subtotal, 0, ',', '.') }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <!-- SUMMARY -->
        <table class="summary-table">
            <tr>
                <td width="60%"></td>
                <td width="20%">Subtotal</td>
                <td width="5%">: Rp</td>
                <td width="15%" class="text-right">{{ number_format($sale->subtotal, 0, ',', '.') }}</td>
            </tr>
            @if($sale->discount_amount > 0)
            <tr>
                <td></td>
                <td>Diskon Transaksi</td>
                <td>: Rp</td>
                <td class="text-right">{{ number_format($sale->discount_amount, 0, ',', '.') }}</td>
            </tr>
            @endif
            <tr>
                <td></td>
                <td>PPN ({{ $sale->ppn_percent + 0 }}%)</td>
                <td>: Rp</td>
                <td class="text-right">{{ number_format($sale->ppn_amount, 0, ',', '.') }}</td>
            </tr>
            <tr class="total-row">
                <td></td>
                <td>TOTAL TAGIHAN</td>
                <td>: Rp</td>
                <td class="text-right">{{ number_format($sale->total, 0, ',', '.') }}</td>
            </tr>
        </table>

        <!-- SIGNATURES -->
        <div class="signature-area">
            <div class="signature-col">
                <p>Penerima / Pembeli,</p>
                <div class="signature-space"></div>
                <p class="signature-name">( {{ $sale->customer_name }} )</p>
            </div>
            <div class="signature-col">
            </div>
            <div class="signature-col">
                <p>Direktur,</p>
                <div class="signature-space"></div>
                <p class="signature-name">( {{ \App\Models\Salary::formatPersonName(\App\Models\Setting::get('pimpinan_name', 'Hj. Nor Maulida, S.H.')) }} )</p>
            </div>
        </div>

        <!-- FOOTER -->
        <div class="footer">
            <p>Terima kasih atas kepercayaan Anda.</p>
            <p>Dicetak pada: {{ now()->format('d-m-Y H:i:s') }}</p>
        </div>
    </div>
    </div>

    <script>
        const INVOICES_URL = @json(route('invoices.index'));

        function printInvoice() {
            window.print();
        }

        function closePrintPage() {
            // Tab dari target="_blank" — window.close() sering diblokir browser modern
            try { window.close(); } catch (e) {}

            setTimeout(function () {
                if (document.visibilityState === 'hidden') return;

                const ref = document.referrer;
                if (ref) {
                    try {
                        const refUrl = new URL(ref);
                        if (refUrl.origin === window.location.origin && refUrl.href !== window.location.href) {
                            window.location.href = ref;
                            return;
                        }
                    } catch (e) {}
                }

                if (window.history.length > 1) {
                    window.history.back();
                    return;
                }

                window.location.href = INVOICES_URL;
            }, 120);
        }

        window.addEventListener('load', function () {
            setTimeout(printInvoice, 800);
        });
    </script>
</body>
</html>
