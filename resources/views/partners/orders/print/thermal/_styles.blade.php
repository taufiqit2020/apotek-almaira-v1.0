{{-- CSS thermal 80mm --}}
<style>
    @page { size: 80mm auto; margin: 0; }
    * { box-sizing: border-box; margin: 0; padding: 0; }
    body {
        font-family: 'Courier New', Courier, monospace;
        font-size: 11px;
        line-height: 1.35;
        color: #000;
        background: #fff;
    }
    .text-center { text-align: center; }
    .text-right  { text-align: right; }
    .bold        { font-weight: bold; }
    .small       { font-size: 9px; }
    .xs          { font-size: 8px; }
    .divider     { border: none; border-top: 1px dashed #000; margin: 5px 0; }
    .solid       { border: none; border-top: 1px solid #000; margin: 5px 0; }
    .double      { border: none; border-top: 3px double #000; margin: 5px 0; }

    .receipt {
        width: 80mm;
        padding: 4mm 3.5mm;
        background: #fff;
    }

    .header { text-align: center; margin-bottom: 4px; }
    .header .main { font-size: 14px; font-weight: bold; letter-spacing: 0.6px; }
    .header .sub  { font-size: 9px; font-weight: bold; margin: 2px 0; }
    .header .addr { font-size: 8.5px; line-height: 1.35; margin-top: 2px; }

    .doc-badge {
        text-align: center;
        font-size: 12px;
        font-weight: bold;
        margin: 4px 0 2px;
        letter-spacing: 0.5px;
    }
    .doc-sub { text-align: center; font-size: 9px; margin-bottom: 2px; }

    .meta-row {
        display: grid;
        grid-template-columns: 72px 1fr;
        gap: 4px;
        font-size: 10px;
        padding: 2px 0;
        align-items: start;
    }
    .meta-row .lbl { color: #333; }
    .meta-row .val { text-align: right; word-break: break-word; font-weight: 600; }

    .col-hdr {
        display: grid;
        grid-template-columns: 1fr 28px 58px;
        gap: 2px;
        font-size: 9px;
        font-weight: bold;
        border-bottom: 1px dashed #000;
        padding-bottom: 3px;
        margin-bottom: 3px;
        text-transform: uppercase;
    }
    .col-hdr .c-qty { text-align: center; }
    .col-hdr .c-amt { text-align: right; }

    .item-block { margin-bottom: 4px; }
    .item-name  { font-size: 10px; font-weight: bold; word-break: break-word; line-height: 1.25; margin-bottom: 1px; }
    .item-row {
        display: grid;
        grid-template-columns: 1fr 28px 58px;
        gap: 2px;
        font-size: 9px;
        align-items: baseline;
    }
    .item-row .c-harga { color: #444; }
    .item-row .c-qty   { text-align: center; font-weight: bold; }
    .item-row .c-sub   { text-align: right; font-weight: bold; font-size: 10px; }

    .sum-box { margin-top: 2px; }
    .sum-row {
        display: grid;
        grid-template-columns: 1fr auto;
        gap: 8px;
        font-size: 10px;
        padding: 2px 0;
        align-items: baseline;
    }
    .sum-row .sum-val { text-align: right; white-space: nowrap; min-width: 72px; }
    .sum-row.grand { font-size: 12px; font-weight: bold; padding: 4px 0; }
    .sum-row.grand .sum-val { font-size: 12px; }

    .note-box {
        font-size: 8.5px;
        text-align: center;
        padding: 4px 2px;
        margin-top: 4px;
        border: 1px dashed #666;
        line-height: 1.35;
    }

    .footer { margin-top: 8px; text-align: center; }
    .footer .thanks { font-size: 11px; font-weight: bold; letter-spacing: 0.5px; }

    @media print {
        .no-print { display: none !important; }
        body { background: #fff !important; }
        .receipt { width: 80mm; margin: 0; padding: 2mm 3mm; box-shadow: none !important; border: none !important; }
    }

    @media screen {
        body { background: #e8ecf0; min-height: 100vh; }
        .screen-toolbar {
            position: fixed; top: 0; left: 0; right: 0; z-index: 9999;
            background: linear-gradient(135deg, #1e293b, #334155);
            color: #fff;
            padding: 10px 16px;
            display: flex; align-items: center; justify-content: space-between;
            gap: 10px; flex-wrap: wrap;
            box-shadow: 0 2px 12px rgba(0,0,0,.2);
            font-family: system-ui, -apple-system, sans-serif;
        }
        .screen-toolbar .tb-title { font-size: 12px; font-weight: 700; }
        .screen-toolbar .tb-sub   { font-size: 10px; opacity: .75; margin-top: 1px; }
        .tb-group { display: flex; gap: 6px; flex-wrap: wrap; align-items: center; }
        .tb-btn {
            border: 1px solid rgba(255,255,255,.35);
            border-radius: 8px;
            padding: 7px 12px;
            font-size: 11px;
            font-weight: 700;
            cursor: pointer;
            text-decoration: none;
            color: #fff;
            background: rgba(255,255,255,.1);
            font-family: inherit;
            display: inline-flex;
            align-items: center;
            gap: 4px;
        }
        .tb-btn:hover { background: rgba(255,255,255,.22); }
        .tb-btn.active { background: #fff; color: #1e293b; border-color: #fff; }
        .tb-btn.primary { background: #10b981; border-color: #10b981; color: #fff; }
        .tb-btn.primary:hover { background: #059669; }
        .receipt-outer { padding: 68px 12px 32px; display: flex; justify-content: center; }
        .receipt {
            box-shadow: 0 4px 24px rgba(0,0,0,.12);
            border: 1px solid #d1d5db;
            border-radius: 2px;
        }
    }
</style>
