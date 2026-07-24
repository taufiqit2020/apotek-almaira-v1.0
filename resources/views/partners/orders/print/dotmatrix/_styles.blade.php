{{-- CSS Epson LX-310 — dokumen monospasi, font kompak agar 1 baris --}}
<style>
    @page {
        size: 250mm 285mm;
        margin: 6mm 10mm 6mm 2mm; /* Geser 3,5 cm ke kiri */
    }

    * {
        box-sizing: border-box;
        -webkit-font-smoothing: none !important;
        -moz-osx-font-smoothing: grayscale !important;
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
        font-family: "Courier New", Courier, monospace;
        font-size: 9.5pt;
        font-weight: bold;
        line-height: 1.15;
    }

    .container {
        width: 100%;
        text-align: left;
        margin: 0;
        padding: 0;
        display: flex;
        flex-direction: column;
        align-items: flex-start; /* Geser seluruh template ke kiri 3,5 cm */
    }

    /* Kop: baris tepat 96 karakter, rata tengah di dalam box 96ch, tepi kiri-kanan sejajar dokumen */
    pre.dm-kop {
        display: block;
        box-sizing: content-box;
        width: 96ch;
        max-width: 100%;
        margin: 0 0 0.4em 0;
        padding: 0;
        text-align: center !important;
        font-family: "Courier New", Courier, monospace !important;
        font-size: 9.5pt !important;
        font-weight: bold !important;
        line-height: 1.15 !important;
        letter-spacing: 0 !important;
        white-space: pre !important;
        overflow: hidden;
        color: #000 !important;
    }

    pre.dm-pre {
        display: block;
        box-sizing: content-box;
        width: 96ch;
        max-width: 100%;
        margin: 0;
        padding: 0;
        text-align: left;
        font-family: "Courier New", Courier, monospace !important;
        font-size: 9.5pt !important;
        font-weight: bold !important;
        line-height: 1.15 !important;
        letter-spacing: 0 !important;
        white-space: pre !important;
        overflow: visible;
        color: #000 !important;
        tab-size: 4;
    }

    /* Ringkasan + footer */
    pre.dm-foot {
        display: block;
        box-sizing: content-box;
        width: 96ch;
        max-width: 100%;
        margin: 0;
        padding: 0;
        text-align: center !important;
        font-family: "Courier New", Courier, monospace !important;
        font-size: 9.5pt !important;
        font-weight: bold !important;
        line-height: 1.15 !important;
        letter-spacing: 0 !important;
        white-space: pre !important;
        color: #000 !important;
    }

    /* Tanda tangan: jarak dari ringkasan + ruang paraf */
    .dm-sig {
        display: flex;
        box-sizing: content-box;
        width: 96ch;
        max-width: 100%;
        font-family: "Courier New", Courier, monospace;
        font-weight: bold;
        color: #000;
        margin: 1.4em 0 1.2em 0;
        padding: 0;
    }
    .dm-sig + pre.dm-foot,
    .dm-sig + pre.dm-pre {
        max-width: 96ch;
        margin-top: 0;
    }
    .dm-sig-col {
        width: 50%;
        text-align: center;
        padding: 0 4px;
    }
    .dm-sig-label {
        font-size: 9.5pt;
        line-height: 1.2;
        white-space: nowrap;
    }
    .dm-sig-space {
        height: 4.5em; /* jarak untuk tanda tangan */
    }
    .dm-sig-name {
        font-size: 9.5pt;
        font-weight: bold;
        line-height: 1.15;
        white-space: nowrap;
        overflow: visible;
    }

    @media print {
        .no-print { display: none !important; }
        html, body { width: 250mm; background: #fff !important; }
        .page-wrapper { margin: 0 !important; padding: 0 !important; }
        .container { width: 100%; text-align: left; align-items: flex-start; margin: 0; }
        pre.dm-kop { font-size: 9.5pt !important; width: 96ch; text-align: center !important; white-space: pre !important; margin: 0 0 0.4em 0; }
        pre.dm-pre { page-break-inside: avoid; font-size: 9.5pt !important; width: 96ch; margin: 0; }
        pre.dm-foot { font-size: 9.5pt !important; width: 96ch; text-align: center !important; margin: 0; }
        .dm-sig { width: 96ch; margin-left: 0; margin-right: 0; }
        .dm-sig-space { height: 4.5em; }
    }

    .print-toolbar {
        position: fixed; top: 0; left: 0; right: 0; z-index: 9999;
        background: linear-gradient(135deg, #1e293b, #334155);
        color: #fff;
        display: flex; align-items: center; justify-content: space-between;
        padding: 8px 16px; gap: 10px; min-height: 50px;
        font-family: system-ui, -apple-system, sans-serif;
        box-shadow: 0 2px 12px rgba(0,0,0,.2);
        text-align: left;
    }
    .print-toolbar .title { font-size: 12px; font-weight: 700; }
    .print-toolbar .sub   { font-size: 10px; opacity: .75; }
    .print-toolbar .hint {
        font-size: 10px; opacity: .9; line-height: 1.35; max-width: 420px; display: none;
    }
    @media (min-width: 900px) {
        .print-toolbar .hint { display: block; }
    }
    .toolbar-group { display: flex; gap: 5px; align-items: center; flex-wrap: wrap; }
    .tb-btn {
        border: 1px solid rgba(255,255,255,.3);
        border-radius: 7px;
        padding: 6px 11px;
        font-size: 11px;
        font-weight: 700;
        cursor: pointer;
        text-decoration: none;
        color: #fff;
        background: rgba(255,255,255,.1);
    }
    .tb-btn:hover { background: rgba(255,255,255,.22); }
    .tb-btn.active, .tb-btn.primary { background: #fff; color: #1e293b; border-color: #fff; }
    .page-wrapper { margin-top: 12px; padding: 8px 10px 24px; text-align: left; }
    .print-tips {
        margin: 58px 12px 0;
        padding: 10px 12px;
        border: 1px dashed #94a3b8;
        border-radius: 8px;
        background: #f8fafc;
        font-family: system-ui, -apple-system, sans-serif;
        font-size: 11px;
        font-weight: 500;
        color: #334155;
        line-height: 1.45;
        text-align: left;
    }
    .print-tips strong { color: #0f172a; }
    @media print {
        .page-wrapper { margin-top: 0; padding: 0; }
        .print-tips { display: none !important; }
    }
</style>
