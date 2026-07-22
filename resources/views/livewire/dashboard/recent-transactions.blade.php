<div>
    <div class="flex items-center justify-between mb-4">
        <h3 class="text-base font-bold text-gray-800">Transaksi Terakhir</h3>
        <button type="button"
                onclick="Livewire.dispatch('dashboard-refresh')"
                class="inline-flex items-center gap-1.5 px-2.5 py-1.5 text-xs font-semibold text-gray-500 hover:text-emerald-700 hover:bg-emerald-50 rounded-lg transition-colors"
                title="Perbarui data dashboard">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 1121.21 8H18.5"/></svg>
            Refresh
        </button>
    </div>

    <div wire:loading.delay.longer.class="opacity-60" class="transition-opacity duration-300 overflow-x-auto">
        <table class="data-table w-full">
            <thead>
                <tr>
                    <th>No. Dokumen</th>
                    <th>Pelanggan</th>
                    <th>Kasir</th>
                    <th>Metode</th>
                    <th class="text-right">Total</th>
                    <th class="text-center">Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse($transactions as $sale)
                <tr>
                    <td>
                        <span class="block text-[9px] font-bold text-gray-400 uppercase">{{ $sale->document_label }}</span>
                        <span class="font-mono text-xs font-semibold text-primary-600">{{ $sale->invoice_no }}</span>
                    </td>
                    <td class="text-sm text-gray-700">{{ $sale->customer_name ?: 'Umum' }}</td>
                    <td class="text-xs text-gray-500">{{ $sale->user?->name ?? '-' }}</td>
                    <td>
                        <span class="badge {{ $sale->payment_method === 'Tunai' ? 'bg-emerald-50 text-emerald-700' : 'bg-violet-50 text-violet-700' }} text-xs">
                            {{ $sale->payment_method }}
                        </span>
                    </td>
                    <td class="text-right font-semibold text-gray-800">Rp {{ number_format($sale->total, 0, ',', '.') }}</td>
                    <td class="text-center">
                        <span class="badge {{ $sale->status === 'completed' ? 'bg-green-50 text-green-700' : 'bg-yellow-50 text-yellow-700' }} text-xs">
                            {{ $sale->status === 'completed' ? 'Selesai' : ucfirst($sale->status) }}
                        </span>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="text-center text-gray-400 py-8">Belum ada transaksi hari ini</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
