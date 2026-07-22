{{-- Toolbar LX-310 --}}
@php
    $tbTitle = $toolbarTitle ?? 'Epson LX-310 · Continuous 25 × 28,5 cm';
    $tbSub   = $toolbarSub ?? $order->order_no;
@endphp
<div class="print-toolbar no-print">
    <div>
        <div class="title">{{ $tbTitle }}</div>
        <div class="sub">{{ $tbSub }}</div>
    </div>
    <div class="hint">
        Dialog cetak: Scale <strong>100%</strong> · matikan Fit/Shrink · kertas driver <strong>25 × 28,5 cm</strong>
    </div>
    <div class="toolbar-group">
        @if(!empty($entitySwitchUrls))
        <a href="{{ $entitySwitchUrls['pt'] ?? '#' }}" class="tb-btn {{ ($entity ?? 'pt') === 'pt' ? 'active' : '' }}">PT NMF</a>
        <a href="{{ $entitySwitchUrls['apotek'] ?? '#' }}" class="tb-btn {{ ($entity ?? 'pt') === 'apotek' ? 'active' : '' }}">Apotek</a>
        @endif
        @if(!empty($printerSwitchUrls))
        <a href="{{ $printerSwitchUrls['thermal'] ?? '#' }}" class="tb-btn">Thermal</a>
        <a href="{{ $printerSwitchUrls['a4'] ?? '#' }}" class="tb-btn">A4</a>
        @endif
        <button type="button" class="tb-btn primary" onclick="window.print()">🖨 Cetak</button>
        <button type="button" class="tb-btn" onclick="window.close()">✕ Tutup</button>
    </div>
</div>
<div class="print-tips no-print">
    <strong>Tips LX-310:</strong>
    Layout sudah <strong>tanpa kotak/kolom</strong> (teks polos).
    Driver Continuous <strong>25,00 × 28,50 cm</strong> · Chrome Scale <strong>100%</strong>
    (matikan Fit to page) · Margins <strong>Minimum</strong> · kualitas <strong>Draft</strong> dulu (lebih tajam di 9-pin).
    Pastikan kertas continuous sejajar (tractor kiri-kanan) agar tidak miring.
</div>
