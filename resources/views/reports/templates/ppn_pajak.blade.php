<table class="report-table">
    <thead>
        <tr>
            <th>No. Faktur</th>
            <th>Waktu</th>
            <th>Pelanggan</th>
            <th>Pihak Penanggung</th>
            <th class="text-right">Grand Total</th>
            <th class="text-center">Persentase</th>
            <th class="text-right">Nominal PPN</th>
        </tr>
    </thead>
    <tbody>
        @forelse($data as $sale)
        <tr>
            <td class="font-mono font-bold">{{ $sale->invoice_no }}</td>
            <td>{{ $sale->sold_at->format('d/m/Y H:i') }}</td>
            <td>{{ $sale->customer_name ?? 'Umum' }}</td>
            <td class="text-center font-bold">
                <span class="badge {{ $sale->ppn_bearer === 'Ditanggung Pembeli' ? 'badge-success' : 'badge-warning' }}">
                    {{ $sale->ppn_bearer }}
                </span>
            </td>
            <td class="text-right">Rp {{ number_format($sale->total, 0, ',', '.') }}</td>
            <td class="text-center">{{ number_format($sale->ppn_percent, 0) }}%</td>
            <td class="text-right font-bold text-gray-900">Rp {{ number_format($sale->ppn_amount, 0, ',', '.') }}</td>
        </tr>
        @empty
        <tr>
            <td colspan="7" class="text-center text-gray-400">Tidak ada transaksi yang memungut PPN</td>
        </tr>
        @endforelse
    </tbody>
</table>

@if($data->count() > 0)
@php
    $buyerPpn = $data->filter(fn($s) => $s->ppn_bearer === 'Ditanggung Pembeli')->sum('ppn_amount');
    $sellerPpn = $data->filter(fn($s) => $s->ppn_bearer === 'Ditanggung Penjual')->sum('ppn_amount');
@endphp
<div class="summary-box">
    <div class="summary-row">
        <span class="summary-label">PPN Ditanggung Pembeli</span>
        <span class="summary-value text-emerald-700">Rp {{ number_format($buyerPpn, 0, ',', '.') }}</span>
    </div>
    <div class="summary-row">
        <span class="summary-label">PPN Ditanggung Penjual</span>
        <span class="summary-value text-amber-700">Rp {{ number_format($sellerPpn, 0, ',', '.') }}</span>
    </div>
    <div class="summary-row">
        <span class="summary-label">Total PPN Terkumpul</span>
        <span class="summary-value font-bold text-gray-900">Rp {{ number_format($data->sum('ppn_amount'), 0, ',', '.') }}</span>
    </div>
</div>
@endif

@include('reports.partials.signature')
