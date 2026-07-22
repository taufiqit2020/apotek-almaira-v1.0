<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    @php
        $isPT = ($entity ?? 'pt') === 'pt';
        $accentColor   = $isPT ? '#047857' : '#1b6ca8';
        $accentDark    = $isPT ? '#064e3b' : '#1e3a5f';
        $accentLight   = $isPT ? '#d1fae5' : '#dbeafe';
        $accentText    = $isPT ? '#065f46' : '#1e40af';
        $summaryBg     = $isPT ? '#f0fdf4' : '#eff6ff';
        $summaryBorder = $isPT ? '#a7f3d0' : '#bfdbfe';
        $entityLabel   = $isPT ? 'PT. Nur Madani Farma' : 'Apotek Almaira';
    @endphp
    <title>{{ $docTitle ?? 'Dokumen PO' }} — {{ $order->order_no }} — {{ $entityLabel }}</title>
    <style>
        @media print {
            @page { size: A4 portrait; margin: 1.0cm 1.2cm; }
            body { -webkit-print-color-adjust: exact; print-color-adjust: exact; background: white !important; margin: 0 !important; }
            .no-print { display: none !important; }
            .watermark { display: block !important; }
            .page-wrapper { margin-top: 0 !important; padding: 0 !important; }
            .paper { box-shadow: none !important; border-radius: 0 !important; padding: 0 !important; max-width: 100% !important; width: 100% !important; }
        }
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; font-size: 10.5px; color: #1a2433; line-height: 1.45; background: #e5e7eb; }
        .print-toolbar {
            position: fixed; top: 0; left: 0; right: 0; z-index: 9999;
            background: linear-gradient(135deg, {{ $accentDark }}, {{ $accentColor }});
            color: white; display: flex; align-items: center; justify-content: space-between;
            padding: 10px 20px; box-shadow: 0 2px 10px rgba(0,0,0,0.25); gap: 12px; min-height: 52px;
        }
        .print-toolbar .brand { display: flex; align-items: center; gap: 10px; font-size: 13px; font-weight: bold; }
        .print-toolbar .brand-sub { opacity: 0.8; font-size: 10px; font-weight: normal; display: block; }
        .toolbar-actions { display: flex; gap: 8px; align-items: center; flex-wrap: wrap; }
        .btn-print { background: #fff; color: {{ $accentText }}; border: none; border-radius: 8px; padding: 8px 16px; font-size: 12px; font-weight: bold; cursor: pointer; }
        .btn-print:hover { background: {{ $accentLight }}; }
        .btn-entity { background: rgba(255,255,255,0.15); color: white; border: 1px solid rgba(255,255,255,0.35); border-radius: 8px; padding: 6px 12px; font-size: 11px; font-weight: bold; text-decoration: none; }
        .btn-entity.active { background: white; color: {{ $accentText }}; }
        .btn-close { background: rgba(255,255,255,0.12); color: white; border: 1px solid rgba(255,255,255,0.35); border-radius: 8px; padding: 8px 14px; font-size: 12px; cursor: pointer; }
        .page-wrapper { margin-top: 52px; padding: 24px 16px 32px; display: flex; justify-content: center; }
        .paper { background: white; width: 100%; max-width: 760px; padding: 30px 38px 40px; box-shadow: 0 4px 30px rgba(0,0,0,0.14); border-radius: 3px; position: relative; overflow: hidden; }
        .watermark { position: absolute; top: 38%; left: 50%; transform: translate(-50%, -50%); opacity: 0.07; pointer-events: none; z-index: 0; }
        .watermark img { width: 340px; height: auto; }
        .paper-content { position: relative; z-index: 1; }
        .kop-wrapper { width: 100%; margin-bottom: 14px; padding-bottom: 10px; border-bottom: 3px solid {{ $accentColor }}; }
        .kop-inner { display: flex; align-items: center; gap: 14px; }
        .kop-logo img { height: 58px; width: auto; display: block; }
        .kop-divider { width: 2px; height: 54px; flex-shrink: 0; background: linear-gradient(to bottom, {{ $accentColor }}, {{ $accentLight }}, transparent); }
        .kop-text { flex: 1; text-align: center; }
        .kop-title { font-size: 18px; font-weight: 900; color: {{ $accentColor }}; text-transform: uppercase; letter-spacing: 0.5px; line-height: 1.1; margin-bottom: 3px; }
        .kop-subtitle { font-size: 9.5px; font-weight: 700; color: {{ $isPT ? '#059669' : '#2563eb' }}; text-transform: uppercase; letter-spacing: 0.4px; margin-bottom: 5px; }
        .kop-address { font-size: 8.5px; color: #4b5563; line-height: 1.65; }
        .kop-spacer { width: 70px; flex-shrink: 0; }
        .doc-header { margin-bottom: 16px; background: #f8fafc; border: 1px solid #e2e8f0; border-left: 4px solid {{ $accentColor }}; border-radius: 6px; padding: 11px 16px; }
        .doc-header-title { font-size: 14px; font-weight: 900; text-align: center; margin: 0 0 10px; text-transform: uppercase; color: #0f172a; letter-spacing: 0.8px; border-bottom: 1px solid #e2e8f0; padding-bottom: 8px; }
        .meta-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 8px 20px; font-size: 9.5px; }
        .meta-item label { display: block; font-size: 8px; font-weight: 800; text-transform: uppercase; letter-spacing: 0.5px; color: #94a3b8; margin-bottom: 2px; }
        .meta-item span { font-weight: 600; color: #1e293b; }
        .meta-item .mono { font-family: 'Courier New', Courier, monospace; font-weight: 700; color: {{ $accentColor }}; }
        .data-table { width: 100%; border-collapse: collapse; margin-top: 14px; font-size: 9.5px; }
        .data-table th { background: {{ $isPT ? '#f0fdf4' : '#eff6ff' }}; color: {{ $accentDark }}; font-weight: 800; text-transform: uppercase; font-size: 8px; letter-spacing: 0.5px; border-top: 2px solid {{ $accentColor }}; border-bottom: 2px solid {{ $accentColor }}; padding: 7px 8px; text-align: left; }
        .data-table td { border-bottom: 1px solid #f1f5f9; padding: 7px 8px; vertical-align: middle; color: #334155; }
        .data-table tr:nth-child(even) td { background: {{ $isPT ? '#f8fdfb' : '#f8faff' }}; }
        .text-right { text-align: right !important; }
        .text-center { text-align: center !important; }
        .font-mono { font-family: 'Courier New', Courier, monospace; }
        .font-bold { font-weight: 700; }
        .summary-box { margin-top: 14px; margin-left: auto; width: 300px; background: {{ $summaryBg }}; border: 1px solid {{ $summaryBorder }}; border-radius: 8px; padding: 12px 14px; font-size: 9.5px; }
        .summary-row { display: flex; justify-content: space-between; margin-bottom: 5px; color: #475569; }
        .summary-row.total { margin-top: 8px; padding-top: 8px; border-top: 2px solid {{ $accentColor }}; font-size: 11px; font-weight: 900; color: {{ $accentColor }}; }
        .payment-box { margin-top: 14px; padding: 12px 14px; border-radius: 8px; border: 1px solid #e2e8f0; background: #f8fafc; font-size: 9.5px; }
        .payment-box h4 { font-size: 9px; font-weight: 800; text-transform: uppercase; letter-spacing: 0.5px; color: {{ $accentColor }}; margin-bottom: 8px; }
        .payment-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 8px; }
        .payment-cell { background: white; border: 1px solid #e2e8f0; border-radius: 6px; padding: 8px 10px; }
        .payment-cell label { display: block; font-size: 7.5px; font-weight: 800; text-transform: uppercase; color: #94a3b8; margin-bottom: 2px; }
        .payment-cell span { font-weight: 700; color: #1e293b; }
        .badge { display: inline-block; padding: 2px 8px; border-radius: 999px; font-size: 8px; font-weight: 800; text-transform: uppercase; }
        .badge-paid { background: #d1fae5; color: #047857; }
        .badge-unpaid { background: #fef3c7; color: #b45309; }
        .badge-overdue { background: #fee2e2; color: #b91c1c; }
        .notes-box { margin-top: 12px; padding: 10px 12px; border-radius: 6px; background: #fffbeb; border: 1px solid #fde68a; font-size: 9px; color: #92400e; }
    </style>
</head>
<body>

<div class="print-toolbar no-print">
    <div class="brand">
        <div>
            {{ $docTitle ?? 'Dokumen PO' }}
            <span class="brand-sub">{{ $order->order_no }} · {{ $entityLabel }}</span>
        </div>
    </div>
    <div class="toolbar-actions">
        @if(!empty($entitySwitchUrls))
            <a href="{{ $entitySwitchUrls['pt'] ?? '#' }}" class="btn-entity {{ $isPT ? 'active' : '' }}">PT NMF</a>
            <a href="{{ $entitySwitchUrls['apotek'] ?? '#' }}" class="btn-entity {{ !$isPT ? 'active' : '' }}">Apotek Almaira</a>
        @endif
        @if(!empty($printerSwitchUrls))
            <a href="{{ $printerSwitchUrls['a4'] ?? '#' }}" class="btn-entity {{ ($printer ?? 'a4') === 'a4' ? 'active' : '' }}">A4</a>
            <a href="{{ $printerSwitchUrls['thermal'] ?? '#' }}" class="btn-entity {{ ($printer ?? '') === 'thermal' ? 'active' : '' }}">Thermal</a>
            <a href="{{ $printerSwitchUrls['dotmatrix'] ?? '#' }}" class="btn-entity {{ ($printer ?? '') === 'dotmatrix' ? 'active' : '' }}">LX-310</a>
        @endif
        <button type="button" class="btn-print" onclick="window.print()">🖨️ Cetak / PDF</button>
        <button type="button" class="btn-close" onclick="window.close()">✕ Tutup</button>
    </div>
</div>

<div class="page-wrapper">
    <div class="paper">
        @if($isPT && file_exists(public_path('assets/images/watermark-ptnmf.png')))
        <div class="watermark"><img src="{{ asset('assets/images/watermark-ptnmf.png') }}" alt=""></div>
        @elseif(!$isPT && file_exists(public_path('assets/images/watermark-apotek.png')))
        <div class="watermark"><img src="{{ asset('assets/images/watermark-apotek.png') }}" alt=""></div>
        @endif

        <div class="paper-content">
            {{-- Kop Surat --}}
            <div class="kop-wrapper">
                <div class="kop-inner">
                    <div class="kop-logo">
                        @if($isPT && file_exists(public_path('assets/images/logo-ptnmf.png')))
                            <img src="{{ asset('assets/images/logo-ptnmf.png') }}" alt="PT NMF">
                        @elseif(!$isPT && file_exists(public_path('assets/images/logo-apotek.png')))
                            <img src="{{ asset('assets/images/logo-apotek.png') }}" alt="Apotek Almaira">
                        @else
                            <div style="width:58px;height:58px;background:{{ $accentColor }};border-radius:8px;display:flex;align-items:center;justify-content:center;color:white;font-weight:900;font-size:11px;text-align:center;">{{ $isPT ? 'NMF' : 'APOTEK' }}</div>
                        @endif
                    </div>
                    <div class="kop-divider"></div>
                    <div class="kop-text">
                        @if($isPT)
                            <div class="kop-title">{{ $branding['pt_name'] ?? 'PT. Nur Madani Farma' }}</div>
                            <div class="kop-subtitle">{{ $branding['pt_tagline'] ?? 'Distributor & Mitra Pengadaan Alat Kesehatan & Farmasi' }}</div>
                            <div class="kop-address">{!! nl2br(e($branding['pt_address'] ?? '')) !!}</div>
                        @else
                            <div class="kop-title">{{ $branding['apotek_name'] ?? 'Apotek Almaira' }}</div>
                            <div class="kop-subtitle">Pelayanan Kesehatan & Kefarmasian Terpercaya</div>
                            <div class="kop-address">{!! nl2br(e($branding['apotek_address'] ?? '')) !!}<br>
                                <strong>Telepon/WA:</strong> {{ $branding['apotek_phone'] ?? '-' }}
                            </div>
                        @endif
                    </div>
                    <div class="kop-spacer"></div>
                </div>
            </div>

            @yield('content')
        </div>
    </div>
</div>

</body>
</html>
