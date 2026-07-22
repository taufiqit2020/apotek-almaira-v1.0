<table class="report-table">
    <thead>
        <tr>
            <th>No. Faktur</th>
            <th>Waktu</th>
            <th>Kasir</th>
            <th>Detail Diskon Produk / Global</th>
            <th class="text-right">Total Transaksi</th>
            <th class="text-right">Nominal Diskon Transaksi</th>
        </tr>
    </thead>
    <tbody>
        @forelse($data as $sale)
        <tr>
            <td class="font-mono font-bold">{{ $sale->invoice_no }}</td>
            <td>{{ $sale->sold_at->format('d/m/Y H:i') }}</td>
            <td>{{ $sale->user?->name ?? '-' }}</td>
            <td>
                @if($sale->discount_amount > 0)
                <div style="font-weight: bold; color: #b45309;">Global: Rp {{ number_format($sale->discount_amount, 0, ',', '.') }} ({{ $sale->discount_percent }}%)</div>
                @endif
                
                @php
                    $itemDiscounts = $sale->items->filter(fn($it) => $it->discount_amount > 0);
                @endphp
                @if($itemDiscounts->count() > 0)
                <div style="margin-top: 3px; font-size: 8px; color: #4b5563;">
                    @foreach($itemDiscounts as $it)
                    • {{ $it->product_name }}: -Rp {{ number_format($it->discount_amount, 0, ',', '.') }} ({{ $it->discount_percent }}%)<br>
                    @endforeach
                </div>
                @endif
            </td>
            <td class="text-right">Rp {{ number_format($sale->total, 0, ',', '.') }}</td>
            <td class="text-right font-bold text-red-600">
                @php
                    $itemDiscSum = $sale->items->sum('discount_amount');
                    $totalDisc = $sale->discount_amount + $itemDiscSum;
                @endphp
                Rp {{ number_format($totalDisc, 0, ',', '.') }}
            </td>
        </tr>
        @empty
        <tr>
            <td colspan="6" class="text-center text-gray-400">Tidak ada penggunaan diskon dalam kriteria filter ini</td>
        </tr>
        @endforelse
    </tbody>
</table>

@if($data->count() > 0)
@php
    $globalDiscSum = $data->sum('discount_amount');
    $itemsDiscSum = $data->sum(fn($s) => $s->items->sum('discount_amount'));
    $grandTotalDisc = $globalDiscSum + $itemsDiscSum;
@endphp
<div class="summary-box">
    <div class="summary-row">
        <span class="summary-label">Total Diskon Global</span>
        <span class="summary-value">Rp {{ number_format($globalDiscSum, 0, ',', '.') }}</span>
    </div>
    <div class="summary-row">
        <span class="summary-label">Total Diskon Per Item</span>
        <span class="summary-value">Rp {{ number_format($itemsDiscSum, 0, ',', '.') }}</span>
    </div>
    <div class="summary-row">
        <span class="summary-label">Total Pengeluaran Diskon</span>
        <span class="summary-value font-bold text-red-600">Rp {{ number_format($grandTotalDisc, 0, ',', '.') }}</span>
    </div>
</div>
@endif

@include('reports.partials.signature')
