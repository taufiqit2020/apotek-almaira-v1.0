<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    @php
        // ================================================================
        // ENTITAS BRANDING — 2 kategori resmi
        // 'pt'     = PT. Nur Madani Farma  (hijau #047857)
        // 'apotek' = Apotek Almaira         (biru  #1b6ca8)
        // ================================================================
        $isPT     = ($entity ?? 'apotek') === 'pt';
        $accentColor  = $isPT ? '#047857' : '#1b6ca8';
        $accentDark   = $isPT ? '#064e3b' : '#1e3a5f';
        $accentLight  = $isPT ? '#d1fae5' : '#dbeafe';
        $accentText   = $isPT ? '#065f46' : '#1e40af';
        $summaryBg    = $isPT ? 'linear-gradient(135deg,#f0fdf4,#ecfdf5)' : 'linear-gradient(135deg,#eff6ff,#dbeafe)';
        $summaryBorder= $isPT ? '#a7f3d0' : '#bfdbfe';
        $summaryTotal = $isPT ? '#047857' : '#1d4ed8';

        // ================================================================
        // ORIENTASI KERTAS — otomatis portrait / landscape
        // ================================================================
        $landscapeTypes = [
            'penjualan_harian','pembelian','stok_saat_ini',
            'stok_menipis','stok_opname','log_aktivitas',
            'diskon','gaji_karyawan','transaksi_qris','ppn_pajak',
        ];
        $isLandscape  = in_array($type, $landscapeTypes);
        $orientation  = $isLandscape ? 'landscape' : 'portrait';
        $paperMaxWidth= $isLandscape ? '1060px' : '760px';
        $paperPadding = $isLandscape ? '26px 34px 38px' : '30px 40px 42px';
    @endphp

    <title>{{ $report_name }} · Almaira</title>

    <style>
        /* ========== PRINT MEDIA ========== */
        @media print {
            @page {
                size: A4 {{ $orientation }};
                margin: 1.0cm 1.2cm 1.0cm 1.2cm;
            }
            body { 
                -webkit-print-color-adjust: exact; 
                print-color-adjust: exact;
                background: white !important;
                margin: 0 !important;
                padding: 0 !important;
                color: #1e293b !important;
            }
            .no-print { display: none !important; }
            .watermark { display: flex !important; }
            
            /* Reset wrapper spacings so content is not squeezed by screen layout constraints */
            .page-wrapper {
                margin-top: 0 !important;
                padding: 0 !important;
                display: block !important;
            }
            .paper {
                box-shadow: none !important;
                border-radius: 0 !important;
                padding: 0 !important;
                margin: 0 !important;
                max-width: 100% !important;
                width: 100% !important;
                background: transparent !important;
            }
            /* Watermark tetap absolute di area kolom — jangan fixed ke halaman */
            .report-body {
                position: relative !important;
                min-height: 280px !important;
            }
            .watermark {
                position: absolute !important;
                z-index: 0 !important;
                opacity: 0.08 !important;
                /* left/top di-set JS agar mengikuti tengah kolom tabel */
            }
            .watermark img { width: min(400px, 58%) !important; }
            
            .report-table { 
                table-layout: auto !important; 
                width: 100% !important; 
            }
            .report-table th {
                padding: 5px 6px !important;
                font-size: 7.5px !important;
                white-space: normal !important; /* Allow header wrap to prevent horizontal stretch */
            }
            .report-table td {
                padding: 5px 6px !important;
                font-size: 8.5px !important;
                word-break: break-word !important; 
            }
            
            .summary-box {
                margin-top: 14px !important;
                padding: 10px 12px !important;
                width: 320px !important;
                font-size: 8.5px !important;
            }
            .summary-row {
                margin-bottom: 5px !important;
            }
            .signature-section {
                margin-top: 20px !important;
                padding-top: 10px !important;
            }
        }

        /* ========== RESET ========== */
        * { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            font-size: 10.5px;
            color: #1a2433;
            line-height: 1.45;
            background: #e5e7eb;
        }

        /* ========== TOOLBAR (screen only) ========== */
        .print-toolbar {
            position: fixed;
            top: 0; left: 0; right: 0;
            z-index: 9999;
            background: linear-gradient(135deg, {{ $accentDark }}, {{ $accentColor }});
            color: white;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 10px 24px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.25);
            gap: 12px;
            height: 52px;
        }
        .print-toolbar .brand {
            display: flex; align-items: center; gap: 10px;
            font-size: 13px; font-weight: bold;
        }
        .print-toolbar .brand .brand-sub {
            opacity: 0.75; font-size: 10.5px; font-weight: normal;
            display: block; margin-top: 1px;
        }
        .toolbar-actions { display: flex; gap: 8px; align-items: center; }
        .btn-print {
            background: #ffffff; color: {{ $accentText }};
            border: none; border-radius: 8px; padding: 8px 18px;
            font-size: 12px; font-weight: bold; cursor: pointer;
            display: flex; align-items: center; gap: 6px;
            transition: all 0.15s;
        }
        .btn-print:hover { background: {{ $accentLight }}; transform: translateY(-1px); }
        .btn-close {
            background: rgba(255,255,255,0.15); color: white;
            border: 1px solid rgba(255,255,255,0.35);
            border-radius: 8px; padding: 8px 16px;
            font-size: 12px; cursor: pointer; transition: all 0.15s;
        }
        .btn-close:hover { background: rgba(255,255,255,0.28); }
        .print-info {
            font-size: 10.5px; opacity: 0.82; max-width: 480px;
            white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
        }

        /* ========== PAGE WRAPPER ========== */
        .page-wrapper {
            margin-top: 52px; padding: 24px 20px 32px;
            display: flex; justify-content: center;
        }

        /* ========== A4 PAPER ========== */
        .paper {
            background: white; width: 100%;
            max-width: {{ $paperMaxWidth }};
            padding: {{ $paperPadding }};
            box-shadow: 0 4px 30px rgba(0,0,0,0.14);
            border-radius: 3px; position: relative; overflow: hidden;
        }

        /* ========== WATERMARK — ikut area kolom tabel, selalu di tengah ========== */
        .report-body {
            position: relative;
            width: 100%;
            min-height: 300px; /* data sedikit → area kolom tetap ada, watermark tetap tengah */
            z-index: 0;
        }
        .watermark {
            position: absolute;
            left: 50%;
            top: 50%;
            transform: translate(-50%, -50%);
            display: flex;
            align-items: center;
            justify-content: center;
            opacity: 0.08;
            pointer-events: none;
            z-index: 0;
            text-align: center;
            width: auto;
            max-width: 70%;
        }
        .watermark img {
            width: 370px;
            max-width: 100%;
            height: auto;
            display: block;
        }
        .report-body-inner {
            position: relative;
            z-index: 1;
            width: 100%;
        }

        .paper-content { position: relative; z-index: 1; }

        /* ========== KOP SURAT ========== */
        .kop-wrapper {
            width: 100%; margin-bottom: 14px;
            padding-bottom: 10px;
            border-bottom: 3px solid {{ $accentColor }};
        }
        .kop-inner { display: flex; align-items: center; gap: 14px; }
        .kop-logo img { height: 60px; width: auto; display: block; }
        .kop-divider {
            width: 2px; height: 58px; flex-shrink: 0;
            background: linear-gradient(to bottom, {{ $accentColor }}, {{ $accentLight }}, transparent);
        }
        .kop-text { flex: 1; text-align: center; }
        .kop-title {
            font-size: 19px; font-weight: 900;
            color: {{ $accentColor }}; text-transform: uppercase;
            letter-spacing: 0.6px; line-height: 1.1; margin-bottom: 3px;
        }
        .kop-subtitle {
            font-size: 10px; font-weight: 700;
            color: {{ $isPT ? '#059669' : '#2563eb' }};
            text-transform: uppercase; letter-spacing: 0.4px; margin-bottom: 5px;
        }
        .kop-address { font-size: 8.5px; color: #4b5563; line-height: 1.65; }
        .kop-address strong { color: #1e293b; }
        .kop-spacer { width: 74px; flex-shrink: 0; }

        /* ========== REPORT HEADER META ========== */
        .report-header {
            margin-bottom: 16px; width: 100%;
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            border-left: 4px solid {{ $accentColor }};
            border-radius: 6px; padding: 11px 16px;
        }
        .report-header-title {
            font-size: 13px; font-weight: 900; text-align: center;
            margin: 0 0 10px 0; text-transform: uppercase; color: #0f172a;
            letter-spacing: 0.6px; border-bottom: 1px solid #e2e8f0; padding-bottom: 8px;
        }
        .meta-table { width: 100%; font-size: 9.5px; margin-bottom: 0; color: #475569; }
        .meta-table td { border: none !important; padding: 2.5px 0 !important; }

        /* ========== FOOTER ========== */
        .print-footer {
            margin-top: 20px; border-top: 1.5px solid #e5e7eb; padding-top: 7px;
        }
        .footer-table { width: 100%; font-size: 8.5px; color: #6b7280; }
        .footer-table td { border: none !important; padding: 0 !important; }
        .footer-left { font-weight: 700; color: {{ $accentColor }}; }

        /* ========== DATA TABLES ========== */
        .report-table {
            width: 100%; border-collapse: collapse;
            margin-top: 14px; font-size: 9.5px; table-layout: auto;
        }
        .report-table th {
            background-color: {{ $isPT ? '#f0fdf4' : '#eff6ff' }};
            color: {{ $accentDark }}; font-weight: 800;
            text-transform: uppercase; font-size: 8px; letter-spacing: 0.5px;
            border-top: 2px solid {{ $accentColor }};
            border-bottom: 2px solid {{ $accentColor }};
            padding: 7px 9px; text-align: left; white-space: nowrap;
        }
        .report-table td {
            border-bottom: 1px solid #f1f5f9; padding: 7px 9px;
            vertical-align: middle; color: #334155;
        }
        .report-table tr:nth-child(even) td { background-color: {{ $isPT ? '#f8fdfb' : '#f8faff' }}; }
        .report-table tr:hover td { background-color: {{ $accentLight }}; }

        .text-right  { text-align: right !important; }
        .text-center { text-align: center !important; }
        .font-mono   { font-family: 'Courier New', Courier, monospace; }
        .font-bold   { font-weight: 700; }
        .text-xs     { font-size: 8px; }
        .text-gray   { color: #94a3b8; }

        /* ========== SUMMARY BOX ========== */
        .summary-box {
            margin-top: 18px; padding: 12px 16px;
            width: 48%; margin-left: auto; border-radius: 8px;
            border: 1px solid {{ $summaryBorder }};
            background: {{ $summaryBg }};
            box-shadow: 0 2px 6px rgba(0,0,0,0.06);
        }
        .summary-row {
            display: table; width: 100%; margin-bottom: 5px;
            font-size: 9.5px; color: #374151;
        }
        .summary-row:last-child {
            margin-bottom: 0; border-top: 1.5px solid {{ $summaryBorder }};
            padding-top: 7px; margin-top: 6px;
            font-weight: 800; font-size: 11px; color: {{ $summaryTotal }};
        }
        .summary-label { display: table-cell; text-align: left; vertical-align: middle; }
        .summary-value { display: table-cell; text-align: right; vertical-align: middle; font-weight: 600; }

        /* ========== BADGES ========== */
        .badge {
            display: inline-block; padding: 2px 6px; font-size: 7.5px;
            font-weight: 800; border-radius: 4px;
            text-transform: uppercase; letter-spacing: 0.3px;
        }
        .badge-success { background-color: #d1fae5; color: #065f46; }
        .badge-danger  { background-color: #fee2e2; color: #991b1b; }
        .badge-warning { background-color: #fef3c7; color: #92400e; }
        .badge-info    { background-color: #e0f2fe; color: #0369a1; }
        .badge-purple  { background-color: #ede9fe; color: #5b21b6; }
        .badge-pink    { background-color: #fce7f3; color: #9d174d; }
        .badge-dark    { background-color: #f1f5f9; color: #334155; }
        .badge-green   { background-color: #d1fae5; color: #047857; }

        /* ========== STAT CARDS (log aktivitas) ========== */
        .stat-card { border-radius: 8px; padding: 10px 14px; color: white; }
        .stat-card-label { font-size: 7.5px; text-transform: uppercase; letter-spacing: 0.8px; opacity: 0.85; margin-bottom: 4px; }
        .stat-card-value { font-size: 20px; font-weight: 800; line-height: 1; }
        .stat-card-sub   { font-size: 7.5px; opacity: 0.75; margin-top: 3px; }

        /* ========== PROGRESS BARS ========== */
        .progress-bar-track { flex: 1; background: #f1f5f9; border-radius: 10px; height: 8px; overflow: hidden; }
        .progress-bar-fill  { height: 100%; border-radius: 10px; }
    </style>

</head>
<body>

    {{-- ========== TOOLBAR (screen only) ========== --}}
    <div class="print-toolbar no-print">
        <div class="brand">
            <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
            </svg>
            <div>
                Preview Laporan
                <span class="brand-sub">{{ $report_name }}</span>
            </div>
        </div>
        <div class="print-info">
            Gunakan tombol "🖨️ Cetak / Simpan PDF" untuk mencetak atau menyimpan sebagai PDF
        </div>
        <div class="toolbar-actions">
            <button class="btn-print" onclick="window.print()">
                <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                </svg>
                🖨️ Cetak / Simpan PDF
            </button>
            <button class="btn-close" onclick="closeOrRedirect()">✕ Tutup</button>
        </div>
    </div>

    {{-- ========== PAPER SHEET ========== --}}
    <div class="page-wrapper">
        <div class="paper">

            <div class="paper-content">

                {{-- ===== KOP SURAT sesuai entitas ===== --}}
                <div class="kop-wrapper">
                    <div class="kop-inner">

                        {{-- Logo --}}
                        <div class="kop-logo">
                            @if($isPT)
                                @if(file_exists(public_path('assets/images/logo-ptnmf.png')))
                                    <img src="{{ asset('assets/images/logo-ptnmf.png') }}" alt="Logo PT. Nur Madani Farma">
                                @else
                                    <div style="width:60px;height:60px;background:#047857;border-radius:8px;display:flex;align-items:center;justify-content:center;color:white;font-weight:900;font-size:14px;text-align:center;">NMF</div>
                                @endif
                            @else
                                @if(file_exists(public_path('assets/images/logo-apotek.png')))
                                    <img src="{{ asset('assets/images/logo-apotek.png') }}" alt="Logo Apotek Almaira">
                                @else
                                    <div style="width:60px;height:60px;background:#1b6ca8;border-radius:8px;display:flex;align-items:center;justify-content:center;color:white;font-weight:900;font-size:11px;text-align:center;">APOTEK</div>
                                @endif
                            @endif
                        </div>

                        {{-- Garis Pemisah Vertikal --}}
                        <div class="kop-divider"></div>

                        {{-- Teks Kop Surat --}}
                        <div class="kop-text">
                            @if($isPT)
                                {{-- === PT. NUR MADANI FARMA === --}}
                                <div class="kop-title">PT. Nur Madani Farma</div>
                                <div class="kop-subtitle">Distributor &amp; Mitra Pengadaan Alat Kesehatan &amp; Farmasi</div>
                                <div class="kop-address">
                                    Jl. Panglima Batur No. 16, Kel. Komet, Kec. Banjarbaru Utara, Kota Banjarbaru, Kalsel 70714<br>
                                    WhatsApp: 0851-6665-7070 &nbsp;|&nbsp; Email: ptnurmadanifarma@gmail.com &nbsp;|&nbsp; Instagram: @apotekalmaira
                                </div>
                            @else
                                {{-- === APOTEK ALMAIRA === --}}
                                <div class="kop-title">Apotek Almaira</div>
                                <div class="kop-subtitle" style="color:#2563eb;">Pelayanan Kesehatan &amp; Kefarmasian Terpercaya</div>
                                <div class="kop-address">
                                    Jl. Nuri No. 14 RT/RW 001/005, Kel. Komet, Kec. Banjarbaru Utara, Kota Banjarbaru, Kalsel 70714<br>
                                    <strong>Telepon/WA:</strong> 0851-6665-7070 &nbsp;|&nbsp;
                                    <strong>Instagram:</strong> @apotekalmaira
                                </div>
                            @endif
                        </div>

                        {{-- Spacer kanan (menyeimbangkan logo) --}}
                        <div class="kop-spacer"></div>
                    </div>
                </div>

                {{-- ===== REPORT HEADER METADATA ===== --}}
                <div class="report-header">
                    <div class="report-header-title">{{ $report_name }}</div>
                    <table class="meta-table">
                        <tr>
                            <td style="width: 14%; border: none !important; color: #64748b;">Periode Laporan</td>
                            <td style="width: 46%; border: none !important; font-weight: 600; color: #1e293b;">:&nbsp;
                                @if($start_date && $end_date)
                                    {{ \Carbon\Carbon::parse($start_date)->format('d/m/Y') }} s.d. {{ \Carbon\Carbon::parse($end_date)->format('d/m/Y') }}
                                @elseif($start_date)
                                    Mulai {{ \Carbon\Carbon::parse($start_date)->format('d/m/Y') }}
                                @elseif($end_date)
                                    s.d. {{ \Carbon\Carbon::parse($end_date)->format('d/m/Y') }}
                                @elseif(isset($month) && isset($year))
                                    {{ \Carbon\Carbon::createFromDate($year, $month, 1)->locale('id')->isoFormat('MMMM Y') }}
                                @else
                                    Semua Periode
                                @endif
                            </td>
                            <td style="width: 40%; text-align: right; border: none !important; padding-right: 0; color: #64748b;">
                                Dicetak Oleh:&nbsp;<span style="font-weight: 700; color: {{ $accentColor }};">{{ auth()->user()->name }}</span>
                            </td>
                        </tr>
                        <tr>
                            <td style="border: none !important; color: #64748b;">Tanggal Cetak</td>
                            <td style="border: none !important; font-weight: 600; color: #1e293b;">:&nbsp;{{ now()->locale('id')->isoFormat('dddd, D MMMM Y HH:mm') }} WITA</td>
                            <td style="text-align: right; border: none !important; padding-right: 0; color: #64748b;">
                                Status Sistem:&nbsp;<span style="font-weight: 700; color: #059669;">Aktif (Normal)</span>
                            </td>
                        </tr>
                    </table>
                </div>

                {{-- ===== MAIN CONTENT + WATERMARK di tengah area kolom ===== --}}
                <div class="report-body">
                    @if($isPT)
                        @if(file_exists(public_path('assets/images/watermark-ptnmf.png')))
                        <div class="watermark" aria-hidden="true">
                            <img src="{{ asset('assets/images/watermark-ptnmf.png') }}" alt="">
                        </div>
                        @endif
                    @else
                        @if(file_exists(public_path('assets/images/watermark-apotek.png')))
                        <div class="watermark" aria-hidden="true">
                            <img src="{{ asset('assets/images/watermark-apotek.png') }}" alt="">
                        </div>
                        @endif
                    @endif
                    <div class="report-body-inner">
                        @include('reports.templates.' . $type)
                    </div>
                </div>

                {{-- ===== FOOTER sesuai entitas ===== --}}
                <div class="print-footer" style="margin-top: 25px; border-top: 2px solid {{ $accentColor }}; padding-top: 10px;">
                    <table class="footer-table" style="width: 100%; border-collapse: collapse; font-size: 8px; color: #475569;">
                        <tr>
                            <td class="footer-left" style="font-weight: 700; color: {{ $accentColor }}; font-size: 9px; border: none !important; padding: 0 0 3px 0 !important;">
                                @if($isPT)
                                    © {{ date('Y') }} PT Nur Madani Farma - Apotek Almaira, Banjarbaru
                                @else
                                    © {{ date('Y') }} Apotek Almaira, Banjarbaru
                                @endif
                            </td>
                            <td style="text-align: right; font-weight: 600; color: #64748b; border: none !important; padding: 0 0 3px 0 !important;">
                                Halaman Cetak Laporan Terpadu
                            </td>
                        </tr>
                        <tr>
                            <td style="color: #64748b; font-size: 8px; border: none !important; padding: 0 !important; line-height: 1.4;">
                                @if($isPT)
                                    <strong>Kantor:</strong> Jl. Panglima Batur No. 16, Kel. Komet, Kec. Banjarbaru Utara, Kota Banjarbaru, Kalsel 70714 &nbsp;•&nbsp; WhatsApp: 0851-6665-7070 &nbsp;|&nbsp; Email: ptnurmadanifarma@gmail.com &nbsp;|&nbsp; Instagram: @apotekalmaira
                                @else
                                    <strong>Apotek:</strong> Jl. Nuri No. 14 RT/RW 001/005, Banjarbaru &nbsp;•&nbsp; <strong>Telepon/WA:</strong> 0851-6665-7070
                                @endif
                            </td>
                            <td style="text-align: right; color: #94a3b8; font-size: 7.5px; border: none !important; padding: 0 !important;">
                                Waktu Cetak: {{ now()->locale('id')->isoFormat('D MMMM Y HH:mm') }} WITA
                            </td>
                        </tr>
                    </table>
                </div>

            </div>{{-- /paper-content --}}
        </div>{{-- /paper --}}
    </div>{{-- /page-wrapper --}}

    {{-- ========== SCRIPTS ========== --}}
    <script>
        function closeOrRedirect() {
            if (window.opener) {
                window.close();
            } else {
                window.location.href = "{{ route('reports.index') }}";
            }
        }

        /**
         * Watermark di tengah area kolom tabel (ikut lebar/tinggi tabel).
         * Data sedikit → tetap di tengah kolom, tidak mengambang di halaman.
         */
        function placeWatermarkOnColumns() {
            var body = document.querySelector('.report-body');
            var wm = document.querySelector('.watermark');
            var table = document.querySelector('.report-body .report-table');
            if (!body || !wm) return;

            var bodyRect = body.getBoundingClientRect();
            var target = table || body;
            var targetRect = target.getBoundingClientRect();

            // Tengah horizontal & vertikal relatif ke .report-body
            var cx = (targetRect.left - bodyRect.left) + (targetRect.width / 2);
            var cy = (targetRect.top - bodyRect.top) + (Math.max(targetRect.height, 160) / 2);

            wm.style.left = cx + 'px';
            wm.style.top = cy + 'px';
            wm.style.transform = 'translate(-50%, -50%)';
        }

        window.addEventListener('load', function () {
            placeWatermarkOnColumns();
            setTimeout(placeWatermarkOnColumns, 200);
            setTimeout(function () {
                window.print();
            }, 900);
        });

        window.addEventListener('resize', placeWatermarkOnColumns);
        window.addEventListener('beforeprint', placeWatermarkOnColumns);

        // NOTE: afterprint event dihapus secara sengaja.
        // Saat klik "Cancel" di dialog cetak → halaman preview tetap tampil.
        // Gunakan tombol "Tutup" untuk kembali ke halaman laporan.
    </script>

</body>
</html>
