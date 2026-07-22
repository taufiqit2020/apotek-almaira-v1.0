<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Faktur Pembelian - {{ $purchase->reference_no }}</title>
    <style>
        @page {
            margin: 1.5cm 1.5cm 1.8cm 1.5cm;
        }
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            font-size: 11px;
            color: #1a2433;
            line-height: 1.4;
        }
        /* Kop Surat Ganda */
        .kop-table {
            width: 100%;
            border-collapse: collapse;
            border-bottom: 2.5px solid #1b6ca8;
            padding-bottom: 8px;
            margin-bottom: 15px;
        }
        .kop-table td {
            border: none !important;
            padding: 0 !important;
            vertical-align: middle;
        }
        .logo-left {
            width: 70px;
            text-align: left;
        }
        .logo-right {
            width: 75px;
            text-align: right;
        }
        .kop-center {
            text-align: center;
        }
        .kop-title {
            font-size: 16px;
            font-weight: bold;
            color: #1b6ca8;
            margin: 0;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .kop-subtitle {
            font-size: 12px;
            font-weight: bold;
            color: #2d9c5a;
            margin: 2px 0 0 0;
            text-transform: uppercase;
        }
        .kop-address {
            font-size: 9px;
            color: #4b5563;
            margin: 4px 0 0 0;
            font-weight: normal;
        }
        
        /* Report Meta */
        .report-header {
            margin-bottom: 20px;
            width: 100%;
        }
        .report-header-title {
            font-size: 13px;
            font-weight: bold;
            text-align: center;
            margin: 0 0 10px 0;
            text-transform: uppercase;
            color: #1a2433;
            border-bottom: 1px dashed #d1d5db;
            padding-bottom: 5px;
        }
        .meta-table {
            width: 100%;
            font-size: 10px;
            margin-bottom: 10px;
        }
        .meta-table td {
            border: none !important;
            padding: 3px 0 !important;
            vertical-align: top;
        }

        /* Watermark */
        .watermark {
            position: fixed;
            top: 25%;
            left: 20%;
            width: 60%;
            opacity: 0.1;
            z-index: -1000;
            text-align: center;
        }
        .watermark img {
            width: 320px;
            height: auto;
        }

        /* Footer */
        footer {
            position: fixed;
            bottom: -40px;
            left: 0px;
            right: 0px;
            height: 30px;
            border-top: 1px solid #e5e7eb;
            padding-top: 8px;
        }
        .footer-table {
            width: 100%;
            font-size: 9px;
            color: #6b7280;
        }
        .footer-table td {
            border: none !important;
            padding: 0 !important;
        }
        .page-number:after {
            content: counter(page);
        }

        /* Data Tables Styling */
        .report-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
            font-size: 10px;
        }
        .report-table th {
            background-color: #f3f4f6;
            color: #374151;
            font-weight: bold;
            text-transform: uppercase;
            font-size: 9px;
            border: 1px solid #d1d5db;
            padding: 6px 8px;
            text-align: left;
        }
        .report-table td {
            border: 1px solid #e5e7eb;
            padding: 5px 8px;
            vertical-align: middle;
        }
        .report-table tr:nth-child(even) {
            background-color: #f9fafb;
        }
        .text-right {
            text-align: right;
        }
        .text-center {
            text-align: center;
        }
        .font-mono {
            font-family: Courier, monospace;
        }
        .font-bold {
            font-weight: bold;
        }

        /* Summary / Total Box */
        .summary-box {
            margin-top: 15px;
            border: 1px solid #d1d5db;
            background-color: #f9fafb;
            padding: 8px 12px;
            width: 45%;
            margin-left: auto;
            border-radius: 6px;
        }
        .summary-row {
            display: table;
            width: 100%;
            margin-bottom: 4px;
            font-size: 10px;
        }
        .summary-row:last-child {
            margin-bottom: 0;
            border-top: 1px solid #d1d5db;
            padding-top: 4px;
            font-weight: bold;
            font-size: 11px;
            color: #1b6ca8;
        }
        .summary-label {
            display: table-cell;
            text-align: left;
        }
        .summary-value {
            display: table-cell;
            text-align: right;
        }
        
        /* Badges */
        .badge {
            display: inline-block;
            padding: 2px 5px;
            font-size: 8px;
            font-weight: bold;
            border-radius: 3px;
            text-transform: uppercase;
        }
        .badge-success { background-color: #d1fae5; color: #065f46; }
        .badge-danger { background-color: #fee2e2; color: #991b1b; }
        .badge-warning { background-color: #fef3c7; color: #92400e; }
        .badge-info { background-color: #e0f2fe; color: #0369a1; }

        /* Signature Area */
        .signature-section {
            margin-top: 50px;
            width: 100%;
            page-break-inside: avoid;
        }
        .signature-table {
            width: 100%;
            border-collapse: collapse;
        }
        .signature-table td {
            border: none !important;
            width: 50%;
            text-align: center;
            vertical-align: top;
            padding: 0 20px;
        }
        .signature-title {
            font-weight: bold;
            margin-bottom: 60px;
            font-size: 10px;
            text-transform: uppercase;
        }
        .signature-name {
            font-weight: bold;
            text-decoration: underline;
            font-size: 10px;
        }
        .signature-role {
            font-size: 9px;
            color: #4b5563;
        }
    </style>
</head>
<body>

    {{-- Watermark Dinamis --}}
    @if(($entity ?? 'pt') === 'pt')
        @if(file_exists(public_path('assets/images/watermark-ptnmf.png')))
        <div class="watermark">
            <img src="{{ public_path('assets/images/watermark-ptnmf.png') }}" alt="Watermark PT">
        </div>
        @endif
    @else
        @if(file_exists(public_path('assets/images/watermark-apotek.png')))
        <div class="watermark">
            <img src="{{ public_path('assets/images/watermark-apotek.png') }}" alt="Watermark Apotek">
        </div>
        @endif
    @endif

    {{-- Kop Surat Dinamis --}}
    <table class="kop-table" style="border-bottom: 2.5px solid {{ ($entity ?? 'pt') === 'pt' ? '#10b981' : '#1b6ca8' }};">
        <tr>
            <td class="logo-left" style="width: 70px;">
                @if(($entity ?? 'pt') === 'pt')
                    @if(file_exists(public_path('assets/images/logo-ptnmf.png')))
                        <img src="{{ public_path('assets/images/logo-ptnmf.png') }}" alt="Logo PT" style="height: 50px;">
                    @endif
                @else
                    @if(file_exists(public_path('assets/images/logo-apotek.png')))
                        <img src="{{ public_path('assets/images/logo-apotek.png') }}" alt="Logo Apotek" style="height: 50px;">
                    @endif
                @endif
            </td>
            <td class="kop-center" style="text-align: center;">
                @if(($entity ?? 'pt') === 'pt')
                    <div class="kop-title" style="color: #047857;">PT Nur Madani Farma</div>
                    <div class="kop-subtitle" style="color: #10b981; font-size: 11px;">Distributor &amp; Mitra Pengadaan Alat Kesehatan &amp; Farmasi</div>
                    <div class="kop-address" style="font-size: 9px; color: #4b5563; margin-top: 4px;">
                        Jl. Panglima Batur No. 16, Kel. Komet, Kec. Banjarbaru Utara, Kota Banjarbaru, Kalsel 70714<br>
                        WhatsApp: 0851-6665-7070 &nbsp;|&nbsp; Email: ptnurmadanifarma@gmail.com &nbsp;|&nbsp; Instagram: @apotekalmaira
                    </div>
                @else
                    <div class="kop-title" style="color: #1b6ca8;">Apotek Almaira</div>
                    <div class="kop-address" style="font-size: 9px; color: #4b5563; margin-top: 4px;">
                        Jl. Nuri No. 14 RT/RW 001/005, Kel. Komet, Kec. Banjarbaru Utara, Kota Banjarbaru, Kalsel 70714<br>
                        Telepon/WA: 0851-6665-7070 &nbsp;|&nbsp; Instagram: @apotekalmaira
                    </div>
                @endif
            </td>
            <td style="width: 70px;"></td> {{-- Balance space --}}
        </tr>
    </table>

    {{-- Report Header Metadata --}}
    <div class="report-header">
        <div class="report-header-title">Faktur Barang Masuk / Purchase Order (PO)</div>
        <table class="meta-table">
            <tr>
                <td style="width: 15%; font-weight: bold;">No. FAKTUR Pembelian</td>
                <td style="width: 35%;">: {{ $purchase->reference_no }}</td>
                <td style="width: 15%; font-weight: bold;">Nama Supplier</td>
                <td style="width: 35%;">: {{ $purchase->supplier->name }}</td>
            </tr>
            <tr>
                <td style="font-weight: bold;">Tanggal PO</td>
                <td>: {{ $purchase->purchase_date->format('d/m/Y') }}</td>
                <td style="font-weight: bold;">Alamat Supplier</td>
                <td>: {{ $purchase->supplier->address ?? '—' }}</td>
            </tr>
            <tr>
                <td style="font-weight: bold;">Status PO</td>
                <td>: 
                    @if($purchase->status === 'received')
                        <span class="badge badge-success">Diterima</span>
                    @elseif($purchase->status === 'sent')
                        <span class="badge badge-info">Dikirim</span>
                    @else
                        <span class="badge badge-warning">Draft</span>
                    @endif
                </td>
                <td style="font-weight: bold;">No. Telp Supplier</td>
                <td>: {{ $purchase->supplier->phone ?? '—' }}</td>
            </tr>
            <tr>
                <td style="font-weight: bold;">Pembuat PO</td>
                <td>: {{ $purchase->user->name }}</td>
                <td style="font-weight: bold;">Tanggal Cetak</td>
                <td>: {{ now()->locale('id')->isoFormat('D MMMM Y H:i') }} WITA</td>
            </tr>
        </table>
    </div>

    {{-- Items Table --}}
    <table class="report-table">
        <thead>
            <tr>
                <th style="width: 5%;" class="text-center">#</th>
                <th style="width: 35%;">Nama Produk / Obat</th>
                <th style="width: 10%;" class="text-center">Satuan</th>
                <th style="width: 10%;" class="text-center">Kuantitas</th>
                <th style="width: 15%;" class="text-right">Harga Beli</th>
                <th style="width: 10%;" class="text-center">Kadaluarsa</th>
                <th style="width: 15%;" class="text-right">Subtotal</th>
            </tr>
        </thead>
        <tbody>
            @foreach($purchase->items as $idx => $item)
            <tr>
                <td class="text-center">{{ $idx + 1 }}</td>
                <td>
                    <div class="font-bold">{{ $item->product_name }}</div>
                    @if($item->batch_no)
                    <div style="font-size: 8px; color: #6b7280; font-family: monospace;">Batch: {{ $item->batch_no }}</div>
                    @endif
                </td>
                <td class="text-center">{{ $item->product?->unit?->name ?? 'pcs' }}</td>
                <td class="text-center">{{ $item->quantity }}</td>
                <td class="text-right">Rp {{ number_format($item->purchase_price, 0, ',', '.') }}</td>
                <td class="text-center">{{ $item->expired_date ? \Carbon\Carbon::parse($item->expired_date)->format('d/m/Y') : '—' }}</td>
                <td class="text-right">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    {{-- Summary Box --}}
    <div class="summary-box">
        <div class="summary-row">
            <span class="summary-label">Total Item</span>
            <span class="summary-value">{{ $purchase->items->sum('quantity') }} obat</span>
        </div>
        <div class="summary-row">
            <span class="summary-label">Total Subtotal</span>
            <span class="summary-value">Rp {{ number_format($purchase->total_amount, 0, ',', '.') }}</span>
        </div>
        <div class="summary-row">
            <span class="summary-label">Grand Total</span>
            <span class="summary-value">Rp {{ number_format($purchase->total_amount, 0, ',', '.') }}</span>
        </div>
    </div>

    @if($purchase->notes)
    <div style="margin-top: 15px; border-left: 3px solid #1b6ca8; padding-left: 10px; font-style: italic; color: #4b5563;">
        <strong>Catatan PO:</strong> {{ $purchase->notes }}
    </div>
    @endif

    {{-- Signature Section --}}
    <div class="signature-section">
        <table class="signature-table">
            <tr>
                <td>
                    <div class="signature-title">Diserahkan Oleh / Supplier</div>
                    <div style="height: 50px;"></div>
                    <div class="signature-name">....................................</div>
                    <div class="signature-role">Tanda Tangan & Cap Supplier</div>
                </td>
                <td>
                    @if(($entity ?? 'pt') === 'pt')
                        <div class="signature-title">Diterima Oleh / Direktur</div>
                        <div style="height: 50px;"></div>
                        <div class="signature-name">Hj. Nor Maulida, S.H.</div>
                        <div class="signature-role">Direktur PT Nur Madani Farma</div>
                    @else
                        <div class="signature-title">Diterima Oleh / Apoteker</div>
                        <div style="height: 50px;"></div>
                        <div class="signature-name">Apt. Wulan Ageng Sujatmiko, S.Farm., M.M.</div>
                        <div class="signature-role">Apoteker Penanggung Jawab</div>
                        <div style="font-size: 8px; color: #4b5563; margin-top: 1px;">SIP: NR63722606010965</div>
                    @endif
                </td>
            </tr>
        </table>
    </div>

    {{-- Footer --}}
    <footer>
        <table class="footer-table">
            <tr>
                <td style="text-align: left; width: 60%;">
                    © {{ date('Y') }} PT Nur Madani Farma - Apotek Almaira, Banjarbaru
                </td>
                <td style="text-align: right; width: 40%;">
                    Halaman <span class="page-number"></span>
                </td>
            </tr>
        </table>
    </footer>

</body>
</html>
