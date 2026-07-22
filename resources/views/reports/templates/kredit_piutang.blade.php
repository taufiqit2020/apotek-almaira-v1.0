<table class="report-table">
    <thead>
        <tr>
            <th class="w-12 text-center">#</th>
            <th>Sumber</th>
            <th>Nomor</th>
            <th>Nama</th>
            <th>Tanggal</th>
            <th>Jatuh Tempo</th>
            <th class="text-right">Total Piutang</th>
        </tr>
    </thead>
    <tbody>
        @forelse($data as $i => $row)
        <tr>
            <td class="text-center text-gray-400">{{ $i + 1 }}</td>
            <td>{{ $row->sumber }}</td>
            <td class="font-mono text-xs font-bold">{{ $row->nomor }}</td>
            <td>{{ $row->nama ?? '—' }}</td>
            <td>{{ $row->tanggal ? \Carbon\Carbon::parse($row->tanggal)->format('d/m/Y') : '—' }}</td>
            <td>{{ $row->jatuh_tempo ? \Carbon\Carbon::parse($row->jatuh_tempo)->format('d/m/Y') : '—' }}</td>
            <td class="text-right font-bold">Rp {{ number_format($row->total, 0, ',', '.') }}</td>
        </tr>
        @empty
        <tr><td colspan="7" class="text-center text-gray-400" style="padding:30px;">Tidak ada kredit/piutang dalam periode ini</td></tr>
        @endforelse
    </tbody>
</table>
@if($data->count() > 0)
<div class="summary-box">
    <div class="summary-row">
        <span class="summary-label">Total Piutang</span>
        <span class="summary-value">Rp {{ number_format($data->sum('total'), 0, ',', '.') }}</span>
    </div>
</div>
@endif
