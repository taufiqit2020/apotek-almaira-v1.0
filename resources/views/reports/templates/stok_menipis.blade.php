<table class="report-table">
    <thead>
        <tr>
            <th>Kode</th>
            <th>Nama Produk / Obat</th>
            <th>Kategori</th>
            <th>Satuan</th>
            <th>Supplier</th>
            <th class="text-center">Batas Min. Stok</th>
            <th class="text-center">Stok Saat Ini</th>
            <th class="text-center">Status</th>
        </tr>
    </thead>
    <tbody>
        @forelse($data as $prod)
        <tr>
            <td class="font-mono text-xs">{{ $prod->code }}</td>
            <td class="font-bold text-red-800">{{ $prod->name }}</td>
            <td>{{ $prod->category?->name ?? '-' }}</td>
            <td class="text-center">{{ $prod->unit?->name ?? '-' }}</td>
            <td>{{ $prod->supplier?->name ?? '-' }}</td>
            <td class="text-center font-bold">{{ $prod->stock_min }}</td>
            <td class="text-center font-bold text-red-600">{{ $prod->stock }}</td>
            <td class="text-center">
                <span class="badge {{ $prod->stock === 0 ? 'badge-danger' : 'badge-warning' }}">
                    {{ $prod->stock === 0 ? 'Habis' : 'Kritis' }}
                </span>
            </td>
        </tr>
        @empty
        <tr>
            <td colspan="8" class="text-center text-gray-400">Semua produk dalam stok aman</td>
        </tr>
        @endforelse
    </tbody>
</table>

@if($data->count() > 0)
<div class="summary-box">
    <div class="summary-row">
        <span class="summary-label">Total Produk Kritis</span>
        <span class="summary-value font-bold text-red-600">{{ $data->count() }} produk</span>
    </div>
</div>
@endif

@include('reports.partials.signature')
