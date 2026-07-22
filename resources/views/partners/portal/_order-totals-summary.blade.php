{{-- Ringkasan subtotal / disc / PPN / total — $totals array dari Partner::calculateOrderTotals atau PartnerOrder::totalsBreakdown --}}
@php
    $totals = $totals ?? [];
    $ppnEnabled = !empty($totals['ppn_enabled']);
    $ppnBearerLabel = $totals['ppn_bearer_label'] ?? null;
@endphp
<div class="space-y-2 {{ $class ?? '' }}">
    <div class="flex items-center justify-between text-sm">
        <span class="text-slate-500">Subtotal</span>
        <span class="font-semibold text-slate-700 {{ $subtotalId ?? '' }}" @if(!empty($subtotalId)) id="{{ $subtotalId }}" @endif>
            Rp {{ number_format($totals['subtotal'] ?? 0, 0, ',', '.') }}
        </span>
    </div>
    <div class="flex items-center justify-between text-sm">
        <span class="text-slate-500">Disc</span>
        <span class="font-semibold {{ ($totals['discount_amount'] ?? 0) > 0 ? 'text-red-600' : 'text-slate-700' }} {{ $discId ?? '' }}" @if(!empty($discId)) id="{{ $discId }}" @endif>
            {{ ($totals['discount_amount'] ?? 0) > 0 ? '-' : '' }}Rp {{ number_format($totals['discount_amount'] ?? 0, 0, ',', '.') }}
        </span>
    </div>
    @if($ppnEnabled)
    <div class="flex items-start justify-between text-sm gap-3 {{ $ppnRowClass ?? '' }}" @if(!empty($ppnRowId)) id="{{ $ppnRowId }}" @endif>
        <div>
            <span class="text-slate-500">PPN</span>
            @if($ppnBearerLabel)
            <p class="text-[10px] text-slate-400 mt-0.5 leading-snug">{{ $ppnBearerLabel }}</p>
            @endif
        </div>
        <span class="font-semibold text-slate-700 shrink-0 {{ $ppnId ?? '' }}" @if(!empty($ppnId)) id="{{ $ppnId }}" @endif>
            Rp {{ number_format($totals['ppn_amount'] ?? 0, 0, ',', '.') }}
        </span>
    </div>
    @elseif(!empty($showPpnDisabledHint))
    <p class="text-[11px] text-slate-400 italic">PPN tidak berlaku untuk akun mitra ini.</p>
    @endif
    <div class="flex items-end justify-between pt-2 border-t border-slate-100">
        <div>
            <p class="text-xs font-bold uppercase tracking-wider text-slate-400">{{ $totalLabel ?? 'Estimasi Total' }}</p>
            @if($ppnEnabled && ($totals['ppn_percent'] ?? 0) > 0)
            <p class="text-[10px] text-slate-400 mt-0.5">Termasuk PPN {{ rtrim(rtrim(number_format($totals['ppn_percent'], 2, ',', '.'), '0'), ',') }}%</p>
            @endif
        </div>
        <p class="text-2xl font-extrabold text-emerald-700 {{ $grandId ?? '' }}" @if(!empty($grandId)) id="{{ $grandId }}" @endif>
            Rp {{ number_format($totals['grand_total'] ?? 0, 0, ',', '.') }}
        </p>
    </div>
</div>
