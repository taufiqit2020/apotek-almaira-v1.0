<table class="report-table">
    <thead>
        <tr>
            <th>No. Faktur</th>
            <th>Tanggal / Waktu</th>
            <th>Kasir / Petugas</th>
            <th>NMID QRIS</th>
            <th class="text-right">Grand Total</th>
            <th>Status Transaksi</th>
        </tr>
    </thead>
    <tbody>
        @forelse($data as $sale)
        <tr>
            <td class="font-mono font-bold">{{ $sale->invoice_no }}</td>
            <td>{{ $sale->sold_at->format('d/m/Y H:i') }}</td>
            <td>{{ $sale->user?->name ?? '-' }}</td>
            <td class="text-center font-semibold text-gray-500">ID1026522359276 (BNI Wondr)</td>
            <td class="text-right font-extrabold text-blue-600">Rp {{ number_format($sale->total, 0, ',', '.') }}</td>
            <td class="text-center">
                <span class="badge badge-success">Lunas</span>
            </td>
        </tr>
        @empty
        <tr>
            <td colspan="6" class="text-center text-gray-400">Tidak ada transaksi QRIS pada periode ini</td>
        </tr>
        @endforelse
    </tbody>
</table>

@if($data->count() > 0)
<div class="summary-box">
    <div class="summary-row">
        <span class="summary-label">Total Transaksi QRIS</span>
        <span class="summary-value font-bold">{{ $data->count() }} transaksi</span>
    </div>
    <div class="summary-row">
        <span class="summary-label">Total Nominal BNI Wondr</span>
        <span class="summary-value font-bold text-blue-600">Rp {{ number_format($data->sum('total'), 0, ',', '.') }}</span>
    </div>
</div>
@endif

@include('reports.partials.signature')
