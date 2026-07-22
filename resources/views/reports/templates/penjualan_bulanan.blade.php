<table class="report-table">
    <thead>
        <tr>
            <th>Tanggal</th>
            <th class="text-center">Jumlah Transaksi</th>
            <th class="text-right">Subtotal</th>
            <th class="text-right">Diskon</th>
            <th class="text-right">PPN</th>
            <th class="text-right">Total Pendapatan</th>
        </tr>
    </thead>
    <tbody>
        @forelse($data as $day)
        <tr>
            <td class="font-bold">{{ Carbon\Carbon::parse($day->date_label)->format('d/m/Y') }}</td>
            <td class="text-center">{{ $day->count }}</td>
            <td class="text-right">Rp {{ number_format($day->subtotal, 0, ',', '.') }}</td>
            <td class="text-right">Rp {{ number_format($day->discount_amount, 0, ',', '.') }}</td>
            <td class="text-right">Rp {{ number_format($day->ppn_amount, 0, ',', '.') }}</td>
            <td class="text-right font-bold">Rp {{ number_format($day->total, 0, ',', '.') }}</td>
        </tr>
        @empty
        <tr>
            <td colspan="6" class="text-center text-gray-400">Tidak ada data penjualan pada bulan ini</td>
        </tr>
        @endforelse
    </tbody>
</table>

@if($data->count() > 0)
<div class="summary-box">
    <div class="summary-row">
        <span class="summary-label">Total Transaksi</span>
        <span class="summary-value">{{ $data->sum('count') }}</span>
    </div>
    <div class="summary-row">
        <span class="summary-label">Total Pendapatan Bersih</span>
        <span class="summary-value">Rp {{ number_format($data->sum('total'), 0, ',', '.') }}</span>
    </div>
</div>
@endif

@include('reports.partials.signature')
