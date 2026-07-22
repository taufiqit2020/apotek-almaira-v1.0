<table class="report-table">
    <thead>
        <tr>
            <th class="w-12 text-center">#</th>
            <th>Karyawan</th>
            <th>Entitas</th>
            <th>Periode Gaji</th>
            <th class="text-right">Gaji Pokok</th>
            <th class="text-right">Lembur</th>
            <th class="text-right">Tunjangan</th>
            <th class="text-right">BPJS Kes</th>
            <th class="text-right">BPJS Ket</th>
            <th class="text-right">Potongan Lain</th>
            <th class="text-right">Gaji Bersih</th>
            <th class="text-center">Tanggal Bayar</th>
        </tr>
    </thead>
    <tbody>
        @forelse($data as $i => $s)
        <tr>
            <td class="text-center text-gray-400">{{ $i + 1 }}</td>
            <td>
                <strong>{{ $s->employee_name }}</strong><br>
                <span style="font-size: 10px; color: #6b7280;">{{ $s->employee?->code ?? '—' }} · {{ $s->employee_position }}</span>
            </td>
            <td>
                <strong>{{ $s->entity_label }}</strong>
            </td>
            <td>
                {{ \Carbon\Carbon::create(null, $s->period_month)->locale('id')->isoFormat('MMMM') }} {{ $s->period_year }}
            </td>
            <td class="text-right">Rp {{ number_format($s->basic_salary, 0, ',', '.') }}</td>
            <td class="text-right" style="color: #047857;">+ Rp {{ number_format($s->overtime, 0, ',', '.') }}</td>
            <td class="text-right" style="color: #047857;">+ Rp {{ number_format($s->allowance, 0, ',', '.') }}</td>
            <td class="text-right" style="color: #b91c1c;">- Rp {{ number_format($s->bpjs_kesehatan, 0, ',', '.') }}</td>
            <td class="text-right" style="color: #b91c1c;">- Rp {{ number_format($s->bpjs_ketenagakerjaan, 0, ',', '.') }}</td>
            <td class="text-right" style="color: #b91c1c;">- Rp {{ number_format($s->deduction, 0, ',', '.') }}</td>
            <td class="text-right font-bold text-emerald-600">Rp {{ number_format($s->net_salary, 0, ',', '.') }}</td>
            <td class="text-center">{{ $s->payment_date->format('d/m/Y') }}</td>
        </tr>
        @empty
        <tr>
            <td colspan="12" class="text-center text-gray-400" style="padding: 30px;">Tidak ada riwayat pembayaran gaji dalam kriteria filter ini</td>
        </tr>
        @endforelse
    </tbody>
</table>

@if($data->count() > 0)
@php
    $totalBasic = $data->sum('basic_salary');
    $totalOvertime = $data->sum('overtime');
    $totalAllowance = $data->sum('allowance');
    $totalBpjsKes = $data->sum('bpjs_kesehatan');
    $totalBpjsKet = $data->sum('bpjs_ketenagakerjaan');
    $totalDeduction = $data->sum('deduction');
    $totalNet = $data->sum('net_salary');
@endphp
<div class="summary-box">
    <div class="summary-row">
        <span class="summary-label">Total Gaji Pokok</span>
        <span class="summary-value">Rp {{ number_format($totalBasic, 0, ',', '.') }}</span>
    </div>
    <div class="summary-row">
        <span class="summary-label">Total Lembur</span>
        <span class="summary-value" style="color: #047857;">+ Rp {{ number_format($totalOvertime, 0, ',', '.') }}</span>
    </div>
    <div class="summary-row">
        <span class="summary-label">Total Tunjangan</span>
        <span class="summary-value" style="color: #047857;">+ Rp {{ number_format($totalAllowance, 0, ',', '.') }}</span>
    </div>
    <div class="summary-row">
        <span class="summary-label">Total BPJS Kesehatan</span>
        <span class="summary-value" style="color: #b91c1c;">- Rp {{ number_format($totalBpjsKes, 0, ',', '.') }}</span>
    </div>
    <div class="summary-row">
        <span class="summary-label">Total BPJS Ketenagakerjaan</span>
        <span class="summary-value" style="color: #b91c1c;">- Rp {{ number_format($totalBpjsKet, 0, ',', '.') }}</span>
    </div>
    <div class="summary-row">
        <span class="summary-label">Total Potongan Lainnya</span>
        <span class="summary-value" style="color: #b91c1c;">- Rp {{ number_format($totalDeduction, 0, ',', '.') }}</span>
    </div>
    <div class="summary-row">
        <span class="summary-label">Total Gaji Bersih</span>
        <span class="summary-value" style="color: #10b981;">Rp {{ number_format($totalNet, 0, ',', '.') }}</span>
    </div>
</div>
@endif
