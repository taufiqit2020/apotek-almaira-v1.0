<table class="report-table">
    <thead>
        <tr>
            <th>Faktur Ref</th>
            <th>Tanggal Pembelian</th>
            <th>Supplier</th>
            <th>Petugas Penerima</th>
            <th class="text-right">Total Nominal Faktur</th>
            <th>Catatan</th>
        </tr>
    </thead>
    <tbody>
        @forelse($data as $purchase)
        <tr>
            <td class="font-mono font-bold">{{ $purchase->reference_no ?? '-' }}</td>
            <td>{{ Carbon\Carbon::parse($purchase->purchase_date)->format('d/m/Y') }}</td>
            <td class="font-bold">{{ $purchase->supplier?->name ?? 'Supplier Umum/Bebas' }}</td>
            <td>{{ $purchase->user?->name ?? '-' }}</td>
            <td class="text-right font-bold text-emerald-600">Rp {{ number_format($purchase->total_amount, 0, ',', '.') }}</td>
            <td class="text-gray-500">{{ $purchase->notes ?? '-' }}</td>
        </tr>
        @empty
        <tr>
            <td colspan="6" class="text-center text-gray-400">Tidak ada data pembelian barang masuk</td>
        </tr>
        @endforelse
    </tbody>
</table>

@if($data->count() > 0)
<div class="summary-box">
    <div class="summary-row">
        <span class="summary-label">Total Transaksi Masuk</span>
        <span class="summary-value font-bold">{{ $data->count() }} Faktur</span>
    </div>
    <div class="summary-row">
        <span class="summary-label">Total Nilai Pembelian</span>
        <span class="summary-value font-bold text-emerald-600">Rp {{ number_format($data->sum('total_amount'), 0, ',', '.') }}</span>
    </div>
</div>
@endif

@include('reports.partials.signature')
