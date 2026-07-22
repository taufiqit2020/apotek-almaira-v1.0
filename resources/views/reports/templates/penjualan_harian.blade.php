<table class="report-table">
    <thead>
        <tr>
            <th>No. Faktur</th>
            <th>Tanggal / Waktu</th>
            <th>Kasir</th>
            <th>Metode Bayar</th>
            <th class="text-right">Subtotal</th>
            <th class="text-right">Diskon</th>
            <th class="text-right">PPN</th>
            <th class="text-right">Grand Total</th>
        </tr>
    </thead>
    <tbody>
        @forelse($data as $sale)
        <tr>
            <td class="font-mono font-bold">{{ $sale->invoice_no }}</td>
            <td>{{ $sale->sold_at->format('d/m/Y H:i') }}</td>
            <td>{{ $sale->user?->name ?? '-' }}</td>
            <td class="text-center">
                <span class="badge {{ $sale->payment_method === 'Tunai' ? 'badge-success' : 'badge-info' }}">
                    {{ $sale->payment_method }}
                </span>
            </td>
            <td class="text-right">Rp {{ number_format($sale->subtotal, 0, ',', '.') }}</td>
            <td class="text-right">Rp {{ number_format($sale->discount_amount, 0, ',', '.') }}</td>
            <td class="text-right">Rp {{ number_format($sale->ppn_amount, 0, ',', '.') }}</td>
            <td class="text-right font-bold">Rp {{ number_format($sale->total, 0, ',', '.') }}</td>
        </tr>
        @empty
        <tr>
            <td colspan="8" class="text-center text-gray-400">Tidak ada data transaksi ditemukan</td>
        </tr>
        @endforelse
    </tbody>
</table>

@if($data->count() > 0)
<div class="summary-box">
    <div class="summary-row">
        <span class="summary-label">Total Subtotal</span>
        <span class="summary-value">Rp {{ number_format($data->sum('subtotal'), 0, ',', '.') }}</span>
    </div>
    <div class="summary-row">
        <span class="summary-label">Total Diskon</span>
        <span class="summary-value">Rp {{ number_format($data->sum('discount_amount'), 0, ',', '.') }}</span>
    </div>
    <div class="summary-row">
        <span class="summary-label">Total PPN</span>
        <span class="summary-value">Rp {{ number_format($data->sum('ppn_amount'), 0, ',', '.') }}</span>
    </div>
    <div class="summary-row">
        <span class="summary-label">Grand Total Penjualan</span>
        <span class="summary-value">Rp {{ number_format($data->sum('total'), 0, ',', '.') }}</span>
    </div>
</div>
@endif

@include('reports.partials.signature')
