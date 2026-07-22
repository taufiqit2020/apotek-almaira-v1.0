<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    @php
        $branding = $branding ?? $salary->branding();
        $accent = $branding['accent'];
        $accentDark = $branding['accent_dark'];
        $totalPendapatan = $salary->basic_salary + $salary->overtime + $salary->allowance;
        $totalPotongan = $salary->bpjs_kesehatan + $salary->bpjs_ketenagakerjaan + $salary->deduction;
        $wmPath = null;
        if (file_exists(public_path($branding['watermark']))) {
            $wmPath = $branding['watermark'];
        } elseif (file_exists(public_path($branding['logo']))) {
            $wmPath = $branding['logo'];
        }
        $wmSrc = $wmPath
            ? asset($wmPath).'?v='.filemtime(public_path($wmPath))
            : null;
        $wmInitial = $salary->isApotek() ? 'AA' : 'NMF';
        $directorName = \App\Models\Salary::formatPersonName(
            \App\Models\Setting::get('pimpinan_name', 'Hj. Nor Maulida, S.H.')
        );
    @endphp
    <title>Cetak Slip Gaji - {{ $salary->employee_name }} · {{ $salary->entity_label }}</title>
    <style>
        @page {
            size: A4 portrait;
            margin: 10mm 12mm;
        }
        * { box-sizing: border-box; }
        body {
            font-family: Arial, Helvetica, sans-serif;
            background-color: #f3f4f6;
            margin: 0;
            padding: 10px;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }
        .toolbar {
            max-width: 186mm;
            margin: 10px auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
            background-color: #0f172a;
            color: #ffffff;
            padding: 10px 20px;
            border-radius: 12px;
            box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1);
        }
        .toolbar-title { font-size: 14px; font-weight: bold; }
        .toolbar-subtitle { font-size: 11px; color: #94a3b8; margin-top: 2px; }
        .btn-print {
            background-color: #2563eb;
            color: #ffffff;
            border: none;
            padding: 8px 16px;
            font-size: 12px;
            font-weight: bold;
            cursor: pointer;
            border-radius: 6px;
        }
        .btn-print:hover { background-color: #1d4ed8; }
        .btn-back {
            background-color: #475569;
            color: #ffffff;
            border: none;
            padding: 8px 16px;
            font-size: 12px;
            font-weight: bold;
            cursor: pointer;
            border-radius: 6px;
            text-decoration: none;
        }
        .btn-back:hover { background-color: #334155; }

        .page-container {
            width: 186mm;
            margin: 0 auto;
            background-color: #ffffff;
            box-shadow: 0 10px 15px -3px rgba(0,0,0,0.1);
        }
        .slip-wrapper {
            border: 1px solid #cbd5e1;
            padding: 12px 14px 8px;
            position: relative;
            overflow: hidden;
            background: #fff;
        }
        .slip-body { position: relative; z-index: 1; }

        .tables-zone {
            position: relative;
            z-index: 1;
        }
        /* Watermark rata tengah area tabel — di atas grid, transparan lembut */
        .watermark {
            position: absolute;
            left: 0;
            right: 0;
            top: 12%;
            bottom: 18%;
            z-index: 5;
            pointer-events: none;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .watermark img {
            width: 230px;
            max-width: 68%;
            height: auto;
            object-fit: contain;
            opacity: 0.34;
            transform: rotate(-12deg);
        }
        .watermark-fallback {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            border: 2px solid {{ $accent }};
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 26px;
            font-weight: 800;
            letter-spacing: 1px;
            color: {{ $accent }};
            background: {{ $accent }}14;
            opacity: 0.4;
            transform: rotate(-12deg);
        }
        .tables-zone > table {
            position: relative;
            z-index: 1;
            background-color: transparent !important;
        }
        .tables-zone > .net-salary-box {
            position: relative;
            z-index: 6;
        }

        .kop {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 14px;
            margin-bottom: 6px;
        }
        .kop-brand {
            display: flex;
            align-items: center;
            gap: 10px;
            min-width: 0;
            flex: 1 1 auto;
        }
        .kop-logo {
            flex: 0 0 auto;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .kop-logo img {
            display: block;
            height: 50px;
            width: auto;
            max-width: 54px;
            object-fit: contain;
        }
        /* Logo apotek sudah berisi teks APOTEK ALMAIRA */
        .kop-brand.is-apotek {
            flex-direction: column;
            align-items: flex-start;
            gap: 4px;
        }
        .kop-brand.is-apotek .kop-logo {
            width: auto;
            height: auto;
            background: transparent;
            border: none;
            padding: 0;
            border-radius: 0;
        }
        .kop-brand.is-apotek .kop-logo img {
            height: 58px;
            width: auto;
            max-width: 220px;
            object-fit: contain;
        }
        .kop-text {
            min-width: 0;
            padding-top: 1px;
        }
        .kop-name {
            font-size: 17px;
            font-weight: 800;
            color: {{ $accent }};
            line-height: 1.15;
            letter-spacing: 0.15px;
        }
        .kop-tagline {
            font-size: 8.5px;
            color: #64748b;
            font-weight: 600;
            margin-top: 3px;
            line-height: 1.35;
            max-width: 280px;
        }
        .kop-brand.is-apotek .kop-tagline {
            margin-top: 0;
            padding-left: 2px;
            color: #0369a1;
            opacity: 0.88;
            font-size: 8.5px;
            max-width: 230px;
        }
        .kop-contact {
            text-align: right;
            flex: 0 0 auto;
            max-width: 48%;
            font-size: 8px;
            color: #64748b;
            line-height: 1.4;
        }
        .kop-contact strong {
            color: #334155;
            font-weight: 700;
        }
        .divider {
            border: none;
            border-top: 2.5px solid {{ $accent }};
            margin: 4px 0 2px;
            position: relative;
        }
        .divider::after {
            content: '';
            display: block;
            border-top: 1px solid {{ $accent }};
            opacity: 0.35;
            margin-top: 2px;
        }
        .slip-header-title {
            text-align: center;
            font-size: 13px;
            font-weight: bold;
            letter-spacing: 0.8px;
            text-transform: uppercase;
            margin-bottom: 8px;
        }
        .meta-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 8px;
        }
        .meta-table td {
            font-size: 10.5px;
            padding: 2px 0;
            border: none !important;
        }
        .detail-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 6px;
        }
        .detail-table th, .detail-table td {
            border: 1px solid #4b5563 !important;
            padding: 5px 9px;
            font-size: 10px;
            background-color: transparent;
        }
        .detail-table th {
            background-color: rgba(243, 244, 246, 0.55);
            font-weight: bold;
            text-align: left;
        }
        .section-row td {
            font-weight: bold;
            background-color: rgba(249, 250, 251, 0.5) !important;
        }
        .text-right { text-align: right; }
        .net-salary-box {
            background-color: {{ $accent }};
            color: #ffffff;
            font-size: 11px;
            font-weight: bold;
            padding: 7px 10px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 5px;
            border-radius: 2px;
        }
        .terbilang-box {
            font-size: 9px;
            font-style: italic;
            color: #374151;
            margin-bottom: 8px;
            padding: 5px 8px;
            background-color: #f3f4f6;
            border-left: 3px solid {{ $accent }};
        }
        .terbilang-text {
            font-weight: bold;
            color: {{ $accent }};
        }
        .signatures-table {
            width: 100%;
            border-collapse: collapse;
            margin: 0 0 6px;
        }
        .signatures-table td {
            width: 50%;
            text-align: center;
            font-size: 10px;
            border: none !important;
            vertical-align: top;
        }
        .sig-space { height: 38px; }
        .sig-name {
            font-weight: bold;
            text-decoration: underline;
            text-transform: none;
        }
        .green-footer {
            background-color: {{ $accentDark }};
            color: #ffffff;
            padding: 5px 8px;
            text-align: center;
            font-size: 8px;
            line-height: 1.35;
            margin-top: 0;
            border-radius: 2px;
            position: relative;
            z-index: 1;
        }

        @media print {
            body {
                background-color: #ffffff;
                padding: 0;
                margin: 0;
            }
            .toolbar { display: none !important; }
            .page-container {
                width: 100%;
                box-shadow: none;
                margin: 0;
            }
            .slip-wrapper {
                border: 1px solid #000000;
            }
        }
    </style>
</head>
<body>

    <div class="toolbar no-print">
        <div>
            <div class="toolbar-title">Cetak Slip Gaji — A4 Portrait</div>
            <div class="toolbar-subtitle">Nomor: {{ $salary->slip_number }} • Perusahaan: {{ $salary->entity_label }}</div>
        </div>
        <div style="display: flex; gap: 8px;">
            <a href="{{ route('salaries.index') }}" class="btn-back">← Kembali</a>
            <button class="btn-print" onclick="window.print()">🖨️ Cetak (Ctrl+P)</button>
        </div>
    </div>

    <div class="page-container">
        <div class="slip-wrapper">
            <div class="slip-body">
                <div class="kop">
                    <div class="kop-brand {{ $salary->isApotek() ? 'is-apotek' : '' }}">
                        <div class="kop-logo">
                            @if(file_exists(public_path($branding['logo'])))
                                <img src="{{ asset($branding['logo']) }}?v={{ filemtime(public_path($branding['logo'])) }}" alt="{{ $branding['name'] }}">
                            @endif
                        </div>
                        @if($salary->isApotek())
                            <div class="kop-tagline">{{ $branding['tagline'] }}</div>
                        @else
                            <div class="kop-text">
                                <div class="kop-name">{{ $branding['name'] }}</div>
                                <div class="kop-tagline">{{ $branding['tagline'] }}</div>
                            </div>
                        @endif
                    </div>
                    <div class="kop-contact">
                        {!! nl2br(e($branding['address'])) !!}<br>
                        <strong>WA</strong> {{ $branding['phone'] }}
                        &nbsp;·&nbsp; <strong>Email</strong> {{ $branding['email'] }}<br>
                        <strong>IG</strong> {{ $branding['ig'] }}
                    </div>
                </div>
                <div class="divider"></div>

                <div class="slip-header-title">SLIP GAJI KARYAWAN</div>

                <table class="meta-table">
                    <tr>
                        <td style="width: 14%;"><strong>No. Slip</strong></td>
                        <td style="width: 36%;">: {{ $salary->slip_number }}</td>
                        <td style="width: 14%;"><strong>Nama</strong></td>
                        <td style="width: 36%;">: {{ $salary->employee_name }}</td>
                    </tr>
                    <tr>
                        <td><strong>Periode</strong></td>
                        <td>: {{ \Carbon\Carbon::create(null, $salary->period_month)->locale('id')->isoFormat('MMMM') }} {{ $salary->period_year }}</td>
                        <td><strong>Jabatan</strong></td>
                        <td>: {{ $salary->employee_position }}</td>
                    </tr>
                    <tr>
                        <td><strong>Entitas</strong></td>
                        <td colspan="3">: {{ $salary->entity_label }}</td>
                    </tr>
                </table>

                <div class="tables-zone">
                    <div class="watermark" aria-hidden="true">
                        @if($wmSrc)
                            <img src="{{ $wmSrc }}" alt="">
                        @else
                            <div class="watermark-fallback">{{ $wmInitial }}</div>
                        @endif
                    </div>

                    <table class="detail-table">
                        <thead>
                            <tr>
                                <th style="width: 70%;">I. PENDAPATAN (EARNINGS)</th>
                                <th style="width: 30%; text-align: right;">JUMLAH</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>Gaji Pokok</td>
                                <td class="text-right">Rp {{ number_format($salary->basic_salary, 0, ',', '.') }}</td>
                            </tr>
                            <tr>
                                <td>Lembur</td>
                                <td class="text-right">Rp {{ number_format($salary->overtime, 0, ',', '.') }}</td>
                            </tr>
                            <tr>
                                <td>Tunjangan / Bonus</td>
                                <td class="text-right">Rp {{ number_format($salary->allowance, 0, ',', '.') }}</td>
                            </tr>
                            <tr class="section-row">
                                <td>Total Pendapatan (A)</td>
                                <td class="text-right">Rp {{ number_format($totalPendapatan, 0, ',', '.') }}</td>
                            </tr>
                        </tbody>
                    </table>

                    <table class="detail-table">
                        <thead>
                            <tr>
                                <th style="width: 70%;">II. POTONGAN (DEDUCTIONS)</th>
                                <th style="width: 30%; text-align: right;">JUMLAH</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>BPJS Kesehatan</td>
                                <td class="text-right">Rp {{ number_format($salary->bpjs_kesehatan, 0, ',', '.') }}</td>
                            </tr>
                            <tr>
                                <td>BPJS Ketenagakerjaan</td>
                                <td class="text-right">Rp {{ number_format($salary->bpjs_ketenagakerjaan, 0, ',', '.') }}</td>
                            </tr>
                            @if($salary->deduction > 0)
                            <tr>
                                <td>Potongan Lainnya</td>
                                <td class="text-right">Rp {{ number_format($salary->deduction, 0, ',', '.') }}</td>
                            </tr>
                            @endif
                            <tr class="section-row">
                                <td>Total Potongan (B)</td>
                                <td class="text-right">Rp {{ number_format($totalPotongan, 0, ',', '.') }}</td>
                            </tr>
                        </tbody>
                    </table>

                    <div class="net-salary-box">
                        <span>GAJI BERSIH DITERIMA (NET) = A − B</span>
                        <span>Rp {{ number_format($salary->net_salary, 0, ',', '.') }}</span>
                    </div>
                </div>

                <div class="terbilang-box">
                    Terbilang: <span class="terbilang-text"># {{ $salary->terbilang }} #</span>
                </div>

                <table class="signatures-table">
                    <tr>
                        <td>
                            Direktur,
                            <div class="sig-space"></div>
                            <div class="sig-name">{{ $directorName }}</div>
                        </td>
                        <td>
                            Penerima / Karyawan,
                            <div class="sig-space"></div>
                            <div class="sig-name">{{ \App\Models\Salary::formatPersonName($salary->employee_name) }}</div>
                        </td>
                    </tr>
                </table>

                <div class="green-footer">
                    {{ $branding['address'] }}
                    &nbsp;·&nbsp; WA: {{ $branding['phone'] }}
                    &nbsp;·&nbsp; {{ $branding['email'] }}
                    &nbsp;·&nbsp; {{ $branding['ig'] }}
                </div>
            </div>
        </div>
    </div>
</body>
</html>
