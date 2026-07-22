<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Preview Laporan</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        body {
            background-color: #ffffff;
            padding: 16px;
            font-family: 'Inter', ui-sans-serif, system-ui, sans-serif;
            -webkit-font-smoothing: antialiased;
        }
        /* Custom Table Styling for Live Preview */
        .report-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 1px 3px rgba(0,0,0,0.02);
            background-color: #ffffff;
            margin-top: 8px;
        }
        .report-table th {
            background-color: #f8fafc;
            color: #475569;
            font-weight: 700;
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            padding: 12px 16px;
            border-bottom: 1px solid #e2e8f0;
            text-align: left;
        }
        .report-table td {
            padding: 12px 16px;
            font-size: 13px;
            color: #1e293b;
            border-bottom: 1px solid #f1f5f9;
            vertical-align: middle;
        }
        .report-table tr:hover td {
            background-color: #f8fafc;
        }
        .report-table tr:last-child td {
            border-bottom: none;
        }
        .text-right {
            text-align: right !important;
        }
        .text-center {
            text-align: center !important;
        }
        .font-mono {
            font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
            font-size: 12px;
        }
        .font-bold {
            font-weight: 700;
        }
        /* Badges */
        .badge {
            display: inline-flex;
            align-items: center;
            padding: 4px 10px;
            border-radius: 9999px;
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }
        .badge-success { background-color: #dcfce7; color: #166534; }
        .badge-danger { background-color: #fee2e2; color: #991b1b; }
        .badge-warning { background-color: #fef3c7; color: #92400e; }
        .badge-info { background-color: #e0f2fe; color: #0369a1; }
        .badge-gray { background-color: #f1f5f9; color: #475569; }

        /* Summary Box */
        .summary-box {
            margin-top: 24px;
            border: 1px solid #e2e8f0;
            background-color: #f8fafc;
            padding: 16px;
            width: 380px;
            margin-left: auto;
            border-radius: 12px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.02);
        }
        .summary-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 8px;
            font-size: 13px;
            color: #475569;
        }
        .summary-row:last-child {
            margin-bottom: 0;
            border-top: 1px solid #e2e8f0;
            padding-top: 8px;
            font-weight: 700;
            font-size: 14px;
            color: #0f172a;
        }
        .summary-label {
            text-align: left;
        }
        .summary-value {
            text-align: right;
            color: #0f172a;
            font-weight: 600;
        }
    </style>
</head>
<body>
    @yield('content')
</body>
</html>
