<table class="report-table">
    <thead>
        <tr>
            <th>Nama Kasir / User</th>
            <th>Email</th>
            <th class="text-center">Jumlah Transaksi</th>
            <th class="text-right">Subtotal</th>
            <th class="text-right">Total Diskon</th>
            <th class="text-right">Total PPN</th>
            <th class="text-right">Total Penjualan</th>
        </tr>
    </thead>
    <tbody>
        @forelse($data as $row)
        <tr>
            <td class="font-bold">{{ $row->user?->name ?? 'Kasir Terhapus' }}</td>
            <td>{{ $row->user?->email ?? '-' }}</td>
            <td class="text-center font-bold">{{ $row->count }}</td>
            <td class="text-right">Rp {{ number_format($row->subtotal, 0, ',', '.') }}</td>
            <td class="text-right text-red-600">Rp {{ number_format($row->discount_amount, 0, ',', '.') }}</td>
            <td class="text-right text-blue-600">Rp {{ number_format($row->ppn_amount, 0, ',', '.') }}</td>
            <td class="text-right font-bold text-emerald-600">Rp {{ number_format($row->total, 0, ',', '.') }}</td>
        </tr>
        @empty
        <tr>
            <td colspan="7" class="text-center text-gray-400">Tidak ada data transaksi per kasir</td>
        </tr>
        @endforelse
    </tbody>
</table>

@if($data->count() > 0)
<div class="summary-box">
    <div class="summary-row">
        <span class="summary-label">Total Semua Transaksi</span>
        <span class="summary-value font-bold">{{ $data->sum('count') }}</span>
    </div>
    <div class="summary-row">
        <span class="summary-label">Total Omzet Bersih</span>
        <span class="summary-value font-bold">Rp {{ number_format($data->sum('total'), 0, ',', '.') }}</span>
    </div>
</div>
@endif

@include('reports.partials.signature')
