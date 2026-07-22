<table class="report-table">
    <thead>
        <tr>
            <th>Kode</th>
            <th>Nama Produk / Obat</th>
            <th>Kategori</th>
            <th>Satuan</th>
            <th>Supplier Utama</th>
            <th class="text-right">Harga Beli (HPP)</th>
            <th class="text-right">Harga Jual</th>
            <th class="text-center">Stok</th>
            <th class="text-right">Total Nilai Stok (HPP)</th>
        </tr>
    </thead>
    <tbody>
        @forelse($data as $prod)
        <tr>
            <td class="font-mono text-xs">{{ $prod->code }}</td>
            <td class="font-bold">
                {{ $prod->name }}
                @if($prod->isLowStock())
                <span class="badge badge-danger">Kritis</span>
                @endif
            </td>
            <td>{{ $prod->category?->name ?? '-' }}</td>
            <td class="text-center">{{ $prod->unit?->name ?? '-' }}</td>
            <td>{{ $prod->supplier?->name ?? '-' }}</td>
            <td class="text-right">Rp {{ number_format($prod->purchase_price, 0, ',', '.') }}</td>
            <td class="text-right">Rp {{ number_format($prod->sell_price, 0, ',', '.') }}</td>
            <td class="text-center font-bold {{ $prod->isLowStock() ? 'text-red-600' : '' }}">{{ $prod->stock }}</td>
            <td class="text-right font-bold">Rp {{ number_format($prod->stock * $prod->purchase_price, 0, ',', '.') }}</td>
        </tr>
        @empty
        <tr>
            <td colspan="9" class="text-center text-gray-400">Tidak ada produk ditemukan</td>
        </tr>
        @endforelse
    </tbody>
</table>

@if($data->count() > 0)
@php
    $totalStockVal = $data->sum(fn($p) => $p->stock * $p->purchase_price);
@endphp
<div class="summary-box">
    <div class="summary-row">
        <span class="summary-label">Total Jenis Produk</span>
        <span class="summary-value font-bold">{{ $data->count() }}</span>
    </div>
    <div class="summary-row">
        <span class="summary-label">Total Kuantitas Stok</span>
        <span class="summary-value font-bold">{{ $data->sum('stock') }} item</span>
    </div>
    <div class="summary-row">
        <span class="summary-label">Total Aset HPP Stok</span>
        <span class="summary-value font-bold text-emerald-800">Rp {{ number_format($totalStockVal, 0, ',', '.') }}</span>
    </div>
</div>
@endif

@include('reports.partials.signature')
