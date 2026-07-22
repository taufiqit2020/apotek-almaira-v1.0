{{-- Toolbar thermal (layar saja) --}}
@php
    $tbTitle = $toolbarTitle ?? 'Thermal 80mm';
    $tbSub   = $toolbarSub ?? $order->order_no;
    $backUrl = route('partner-orders.show', $order);
@endphp
<div class="screen-toolbar no-print">
    <div>
        <div class="tb-title">{{ $tbTitle }}</div>
        <div class="tb-sub">{{ $tbSub }}</div>
    </div>
    <div class="tb-group">
        @if(!empty($entitySwitchUrls))
        <a href="{{ $entitySwitchUrls['pt'] ?? '#' }}" class="tb-btn {{ ($entity ?? 'pt') === 'pt' ? 'active' : '' }}">PT</a>
        <a href="{{ $entitySwitchUrls['apotek'] ?? '#' }}" class="tb-btn {{ ($entity ?? 'pt') === 'apotek' ? 'active' : '' }}">Apotek</a>
        @endif
        @if(!empty($printerSwitchUrls))
        <a href="{{ $printerSwitchUrls['dotmatrix'] ?? '#' }}" class="tb-btn">LX-310</a>
        <a href="{{ $printerSwitchUrls['a4'] ?? '#' }}" class="tb-btn">A4</a>
        @endif
        <button type="button" class="tb-btn primary" onclick="window.print()">🖨 Cetak</button>
        <button type="button" class="tb-btn" onclick="goBackThermal()">← Kembali</button>
    </div>
</div>
<script>
    function goBackThermal() {
        try { window.close(); } catch (e) {}
        setTimeout(function () {
            var ref = document.referrer;
            if (ref) {
                try {
                    var u = new URL(ref);
                    if (u.origin === window.location.origin && u.href !== window.location.href) {
                        window.location.href = ref;
                        return;
                    }
                } catch (e) {}
            }
            if (window.history.length > 1) {
                window.history.back();
                return;
            }
            window.location.href = @json($backUrl);
        }, 120);
    }
</script>
