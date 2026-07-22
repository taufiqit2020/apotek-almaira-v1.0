<table class="report-table">
    <thead>
        <tr>
            <th>Kode Produk</th>
            <th>Nama Produk</th>
            <th>Satuan</th>
            <th class="text-center">Total Terjual</th>
            <th class="text-right">Rata-rata Harga Jual</th>
            <th class="text-right">Total Diskon Item</th>
            <th class="text-right">Total Omzet</th>
        </tr>
    </thead>
    <tbody>
        @forelse($data as $row)
        <tr>
            <td class="font-mono text-gray-600 font-semibold">{{ $row->product_code }}</td>
            <td class="font-bold">{{ $row->product_name }}</td>
            <td class="text-center">{{ $row->unit_name ?? '-' }}</td>
            <td class="text-center font-bold text-emerald-600">{{ $row->total_qty }}</td>
            <td class="text-right">Rp {{ number_format($row->avg_price, 0, ',', '.') }}</td>
            <td class="text-right text-red-600">Rp {{ number_format($row->total_discount, 0, ',', '.') }}</td>
            <td class="text-right font-bold text-gray-900">Rp {{ number_format($row->total_revenue, 0, ',', '.') }}</td>
        </tr>
        @empty
        <tr>
            <td colspan="7" class="text-center text-gray-400">Tidak ada data penjualan produk</td>
        </tr>
        @endforelse
    </tbody>
</table>

@if($data->count() > 0)
<div class="summary-box">
    <div class="summary-row">
        <span class="summary-label">Total Item Terjual</span>
        <span class="summary-value font-bold">{{ $data->sum('total_qty') }}</span>
    </div>
    <div class="summary-row">
        <span class="summary-label">Total Omzet Bersih</span>
        <span class="summary-value font-bold">Rp {{ number_format($data->sum('total_revenue'), 0, ',', '.') }}</span>
    </div>
</div>
@endif

@include('reports.partials.signature')
