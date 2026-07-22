<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            font-size: 11px;
            color: #1e293b;
        }
        .kop-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }
        .kop-table td {
            border: none !important;
            padding: 0 !important;
        }
        .kop-title {
            font-size: 16px;
            font-weight: bold;
            text-transform: uppercase;
        }
        .kop-subtitle {
            font-size: 11px;
            font-weight: bold;
            text-transform: uppercase;
        }
        .kop-address {
            font-size: 9px;
            color: #4b5563;
            margin-top: 3px;
        }
        .report-header-title {
            font-size: 13px;
            font-weight: bold;
            text-align: center;
            margin: 15px 0 10px 0;
            text-transform: uppercase;
            color: #0f172a;
            border-bottom: 1px dashed #cbd5e1;
            padding-bottom: 5px;
        }
        .meta-table {
            width: 100%;
            font-size: 9px;
            margin-bottom: 15px;
            border-collapse: collapse;
        }
        .meta-table td {
            border: none !important;
            padding: 3px 0 !important;
        }
        .report-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
            font-size: 10px;
        }
        .report-table th {
            background-color: #f1f5f9;
            color: #334155;
            font-weight: bold;
            text-transform: uppercase;
            font-size: 9px;
            border: 1px solid #cbd5e1;
            padding: 8px;
            text-align: left;
        }
        .report-table td {
            border: 1px solid #e2e8f0;
            padding: 6px 8px;
            vertical-align: middle;
        }
        .text-center { text-align: center !important; }
        .text-right { text-align: right !important; }
        .font-bold { font-weight: bold !important; }
        .text-red-600 { color: #dc2626 !important; }
        .text-blue-600 { color: #2563eb !important; }
        .text-emerald-600 { color: #059669 !important; }
        
        .summary-box {
            margin-top: 15px;
            border: 1px solid #cbd5e1;
            background-color: #f8fafc;
            padding: 10px;
            width: 320px;
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
            border-top: 1px solid #cbd5e1;
            padding-top: 4px;
            font-weight: bold;
            font-size: 11px;
        }
        .summary-label {
            display: table-cell;
            text-align: left;
        }
        .summary-value {
            display: table-cell;
            text-align: right;
        }
    </style>
</head>
<body>

    <!-- KOP SURAT (Centering using Colspan 7 for clean alignment) -->
    <table class="kop-table" style="width: 100%; margin-bottom: 15px;">
        <tr>
            <td colspan="7" style="text-align: center; border: none !important; padding: 10px 0 !important;">
                @if(($entity ?? 'apotek') === 'pt')
                    <span style="font-size: 16px; font-weight: bold; color: #047857;">PT. NUR MADANI FARMA</span><br>
                    <span style="font-size: 11px; font-weight: bold; color: #10b981;">Distributor & Mitra Pengadaan Alat Kesehatan & Farmasi</span><br>
                    <span style="font-size: 9px; color: #4b5563;">Jl. Panglima Batur No. 16, Kel. Komet, Kec. Banjarbaru Utara, Kota Banjarbaru, Kalsel 70714</span><br>
                    <span style="font-size: 9px; color: #4b5563;">WhatsApp: 0851-6665-7070 &nbsp;|&nbsp; Email: ptnurmadanifarma@gmail.com &nbsp;|&nbsp; Instagram: @apotekalmaira</span>
                @else
                    <span style="font-size: 16px; font-weight: bold; color: #1b6ca8;">APOTEK ALMAIRA</span><br>
                    <span style="font-size: 9px; color: #4b5563;">Jl. Nuri No. 14 RT/RW 001/005, Kel. Komet, Kec. Banjarbaru Utara, Kota Banjarbaru, Kalsel 70714</span><br>
                    <span style="font-size: 9px; color: #4b5563;">Telepon/WA: 0851-6665-7070</span>
                @endif
            </td>
        </tr>
        <tr>
            <td colspan="7" style="border-bottom: 2.5px solid {{ ($entity ?? 'apotek') === 'pt' ? '#10b981' : '#1b6ca8' }}; height: 5px; border-left: none !important; border-right: none !important; border-top: none !important; padding: 0 !important;">&nbsp;</td>
        </tr>
    </table>

    <!-- TITLE (Centered) -->
    <table style="width: 100%; border-collapse: collapse; margin-bottom: 10px;">
        <tr>
            <td colspan="7" style="text-align: center; font-size: 13px; font-weight: bold; text-transform: uppercase; color: #0f172a; border: none !important; padding: 10px 0 !important;">
                {{ $report_name }}
            </td>
        </tr>
    </table>

    <!-- METADATA (Grid layout using colspan) -->
    <table class="meta-table" style="width: 100%;">
        <tr>
            <td colspan="2" style="font-weight: bold; border: none !important; padding: 3px 0 !important;">Periode Laporan</td>
            <td colspan="2" style="border: none !important; padding: 3px 0 !important;">: 
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
            <td colspan="3" style="text-align: right; border: none !important; padding: 3px 0 !important; font-weight: bold;">Dicetak Oleh: {{ auth()->user()->name }}</td>
        </tr>
        <tr>
            <td colspan="2" style="font-weight: bold; border: none !important; padding: 3px 0 !important;">Tanggal Cetak</td>
            <td colspan="2" style="border: none !important; padding: 3px 0 !important;">: {{ now()->locale('id')->isoFormat('dddd, D MMMM Y HH:mm') }} WITA</td>
            <td colspan="3" style="text-align: right; border: none !important; padding: 3px 0 !important; font-weight: bold; color: #059669;">Status Sistem: Aktif (Normal)</td>
        </tr>
    </table>

    <!-- MAIN TEMPLATE CONTENT -->
    @include('reports.templates.' . $type)

</body>
</html>
