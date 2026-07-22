<table class="report-table">
    <thead>
        <tr>
            <th>Kode</th>
            <th>Nama Produk / Obat</th>
            <th>Kategori</th>
            <th>Satuan</th>
            <th>Tanggal Kadaluarsa</th>
            <th class="text-center font-bold">Stok</th>
            <th class="text-center">Status Kadaluarsa</th>
        </tr>
    </thead>
    <tbody>
        @forelse($data as $prod)
        @php
            $isExpired = $prod->isExpired();
            $daysLeft = today()->diffInDays($prod->expired_date, false);
        @endphp
        <tr>
            <td class="font-mono text-xs">{{ $prod->code }}</td>
            <td class="font-bold">{{ $prod->name }}</td>
            <td>{{ $prod->category?->name ?? '-' }}</td>
            <td class="text-center">{{ $prod->unit?->name ?? '-' }}</td>
            <td class="font-bold {{ $isExpired ? 'text-red-600' : ($daysLeft <= 30 ? 'text-amber-600' : 'text-orange-600') }}">
                {{ $prod->expired_date->format('d/m/Y') }}
            </td>
            <td class="text-center font-bold">{{ $prod->stock }}</td>
            <td class="text-center">
                @if($isExpired)
                    <span class="badge badge-danger">Kadaluarsa</span>
                @elseif($daysLeft <= 30)
                    <span class="badge badge-danger" style="background-color: #fef3c7; color: #92400e;">≤ 30 Hari</span>
                @else
                    <span class="badge badge-warning">≤ 60 Hari</span>
                @endif
            </td>
        </tr>
        @empty
        <tr>
            <td colspan="7" class="text-center text-gray-400">Tidak ada produk kadaluarsa dalam kriteria filter ini</td>
        </tr>
        @endforelse
    </tbody>
</table>

@if($data->count() > 0)
<div class="summary-box">
    <div class="summary-row">
        <span class="summary-label">Total Produk Terdampak</span>
        <span class="summary-value font-bold">{{ $data->count() }} produk</span>
    </div>
</div>
@endif

@include('reports.partials.signature')
