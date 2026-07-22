<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>{{ $report_name }}</title>
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
            font-size: 14px;
            font-weight: bold;
            text-align: center;
            margin: 0 0 10px 0;
            text-transform: uppercase;
            color: #1a2433;
        }
        .meta-table {
            width: 100%;
            font-size: 10px;
            margin-bottom: 10px;
        }
        .meta-table td {
            border: none !important;
            padding: 2px 0 !important;
        }

        /* Watermark — di tengah area kolom laporan */
        .report-body {
            position: relative;
            width: 100%;
            min-height: 280px;
        }
        .watermark {
            position: absolute;
            top: 50%;
            left: 50%;
            width: 60%;
            transform: translate(-50%, -50%);
            opacity: 0.08;
            z-index: 0;
            text-align: center;
        }
        .watermark img {
            width: 320px;
            height: auto;
        }
        .report-body-inner {
            position: relative;
            z-index: 1;
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

        /* Data Tables Styling (Shared for all templates) */
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
            padding: 10px;
            width: 40%;
            margin-left: auto;
            border-radius: 4px;
        }
        .summary-row {
            display: table;
            width: 100%;
            margin-bottom: 4px;
        }
        .summary-row:last-child {
            margin-bottom: 0;
            border-top: 1px solid #e5e7eb;
            padding-top: 4px;
            font-weight: bold;
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
    </style>
</head>
<body>

    {{-- Kop Surat Ganda --}}
    <table class="kop-table">
        <tr>
            <td class="logo-left">
                @if(file_exists(public_path('assets/images/logodashboard.jpeg')))
                    <img src="{{ public_path('assets/images/logodashboard.jpeg') }}" alt="Logo Apotek" style="height: 50px; border-radius: 6px;">
                @endif
            </td>
            <td class="kop-center">
                <div class="kop-title">Apotek Almaira</div>
                <div class="kop-subtitle">PT. Nur Madani Farma</div>
                <div class="kop-address">
                    Jl. Panglima Batur No. 16, Kel. Komet, Kec. Banjarbaru Utara, Kota Banjarbaru, Kalsel 70714<br>
                    WhatsApp: 0851-6665-7070 &nbsp;|&nbsp; Email: ptnurmadanifarma@gmail.com &nbsp;|&nbsp; Instagram: @apotekalmaira
                </div>
            </td>
            <td class="logo-right">
                @if(file_exists(public_path('assets/images/logo-ptnmf.png')))
                    <img src="{{ public_path('assets/images/logo-ptnmf.png') }}" alt="Logo NMF" style="height: 50px;">
                @endif
            </td>
        </tr>
    </table>

    {{-- Report Header Metadata --}}
    <div class="report-header">
        <div class="report-header-title">{{ $report_name }}</div>
        <table class="meta-table">
            <tr>
                <td style="width: 15%; text-align: left; border: none !important;">Periode Laporan</td>
                <td style="width: 45%; text-align: left; border: none !important;">:
                    @if($start_date && $end_date)
                        {{ Carbon\Carbon::parse($start_date)->format('d/m/Y') }} s.d. {{ Carbon\Carbon::parse($end_date)->format('d/m/Y') }}
                    @elseif($start_date)
                        Mulai {{ Carbon\Carbon::parse($start_date)->format('d/m/Y') }}
                    @elseif($end_date)
                        s.d. {{ Carbon\Carbon::parse($end_date)->format('d/m/Y') }}
                    @elseif(isset($month) && isset($year))
                        {{ Carbon\Carbon::createFromDate($year, $month, 1)->locale('id')->isoFormat('MMMM Y') }}
                    @else
                        Semua Periode
                    @endif
                </td>
                <td style="width: 40%; text-align: right; border: none !important; padding-right: 0;">Dicetak Oleh: <span style="font-weight: bold; color: #1a2433;">{{ auth()->user()->name }}</span></td>
            </tr>
            <tr>
                <td style="text-align: left; border: none !important;">Tanggal Cetak</td>
                <td style="text-align: left; border: none !important;">: {{ now()->locale('id')->isoFormat('dddd, D MMMM Y HH:mm') }} WITA</td>
                <td style="text-align: right; border: none !important; padding-right: 0;">Status Sistem: <span style="font-weight: bold; color: #059669;">Aktif (Normal)</span></td>
            </tr>
        </table>
    </div>

    {{-- Main Table Content + watermark di tengah kolom --}}
    <div class="report-body">
        @if(file_exists(public_path('assets/images/watermark-ptnmf.png')))
        <div class="watermark">
            <img src="{{ public_path('assets/images/watermark-ptnmf.png') }}" alt="Watermark">
        </div>
        @endif
        <div class="report-body-inner">
            @include('reports.templates.' . $type)
        </div>
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
