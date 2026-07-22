<table class="report-table">
    <thead>
        <tr>
            <th>Waktu & Tanggal Opname</th>
            <th>Kode Produk</th>
            <th>Nama Produk</th>
            <th>Petugas Opname</th>
            <th class="text-center">Stok Catatan (Sistem)</th>
            <th class="text-center">Stok Fisik Aktual</th>
            <th class="text-center">Selisih Opname</th>
            <th>Keterangan / Alasan</th>
        </tr>
    </thead>
    <tbody>
        @forelse($data as $op)
        <tr>
            <td>{{ $op->created_at->format('d/m/Y H:i') }}</td>
            <td class="font-mono text-xs">{{ $op->product?->code ?? '-' }}</td>
            <td class="font-bold">{{ $op->product?->name ?? '-' }}</td>
            <td>{{ $op->user?->name ?? '-' }}</td>
            <td class="text-center">{{ $op->system_stock }}</td>
            <td class="text-center font-bold">{{ $op->physical_stock }}</td>
            <td class="text-center font-bold {{ $op->difference < 0 ? 'text-red-600' : ($op->difference > 0 ? 'text-green-600' : 'text-gray-500') }}">
                {{ $op->difference > 0 ? '+' : '' }}{{ $op->difference }}
            </td>
            <td class="text-gray-500">{{ $op->notes ?? '-' }}</td>
        </tr>
        @empty
        <tr>
            <td colspan="8" class="text-center text-gray-400">Tidak ada riwayat stok opname dalam kriteria filter ini</td>
        </tr>
        @endforelse
    </tbody>
</table>

@if($data->count() > 0)
@php
    $selisihKurang = $data->filter(fn($o) => $o->difference < 0)->sum('difference');
    $selisihLebih = $data->filter(fn($o) => $o->difference > 0)->sum('difference');
@endphp
<div class="summary-box" style="width: 50%;">
    <div class="summary-row">
        <span class="summary-label">Total Aktivitas Opname</span>
        <span class="summary-value font-bold">{{ $data->count() }} kali</span>
    </div>
    <div class="summary-row">
        <span class="summary-label">Total Selisih Kurang (Penyusutan)</span>
        <span class="summary-value text-red-600 font-bold">{{ $selisihKurang }} item</span>
    </div>
    <div class="summary-row">
        <span class="summary-label">Total Selisih Lebih</span>
        <span class="summary-value text-green-600 font-bold">+{{ $selisihLebih }} item</span>
    </div>
</div>
@endif

@include('reports.partials.signature')
