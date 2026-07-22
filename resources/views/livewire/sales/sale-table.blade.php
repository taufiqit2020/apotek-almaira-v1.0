<div>
    {{-- ── Toolbar ────────────────────────────────────────────────── --}}
    <div class="card p-4 mb-5">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-3">
            {{-- Search --}}
            <div class="relative">
                <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                <input wire:model.live.debounce.500ms="search" type="text"
                       placeholder="Cari no. faktur / invoice / pelanggan..."
                       class="input pl-9 w-full">
                <div wire:loading wire:target="search" class="absolute right-3 top-1/2 -translate-y-1/2">
                    <svg class="w-4 h-4 text-primary-500 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
                </div>
            </div>

            {{-- Tanggal Mulai --}}
            <input wire:model.live="startDate" type="date" class="input" title="Dari Tanggal">

            {{-- Tanggal Akhir --}}
            <input wire:model.live="endDate" type="date" class="input" title="Sampai Tanggal">

            {{-- Metode Bayar --}}
            <select wire:model.live="paymentMethod" class="input">
                <option value="">Semua Metode</option>
                <option value="Tunai">Tunai</option>
                <option value="QRIS">QRIS</option>
                <option value="Transfer">Transfer</option>
                <option value="Split">Split Payment</option>
            </select>
        </div>

        <div class="flex items-center justify-between mt-3">
            <div class="flex items-center gap-2">
                @can('viewAny', \App\Models\User::class)
                <select wire:model.live="userId" class="input text-sm">
                    <option value="">Semua Kasir</option>
                    @foreach($kasirList as $kasir)
                    <option value="{{ $kasir->id }}">{{ $kasir->name }}</option>
                    @endforeach
                </select>
                @endcan

                <select wire:model.live="status" class="input text-sm">
                    <option value="">Semua Status</option>
                    <option value="completed">Selesai</option>
                    <option value="pending">Pending</option>
                    <option value="cancelled">Dibatalkan</option>
                </select>

                <button wire:click="clearFilters" class="btn btn-secondary text-sm flex items-center gap-1.5">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 1121.21 8H18.5"/></svg>
                    Reset
                </button>
            </div>

            {{-- Total Revenue --}}
            <div class="text-right">
                <p class="text-xs text-gray-400">Total periode ini</p>
                <p class="text-lg font-extrabold text-emerald-600">Rp {{ number_format($totalRevenue, 0, ',', '.') }}</p>
            </div>
        </div>
    </div>

    {{-- ── Tabel ───────────────────────────────────────────────────── --}}
    <div class="card overflow-hidden">
        <div wire:loading.class="opacity-60" class="transition-opacity duration-200 overflow-x-auto">
            <table class="data-table w-full">
                <thead>
                    <tr>
                        <th>No. Dokumen</th>
                        <th>Pelanggan</th>
                        <th>Kasir</th>
                        <th class="text-center">Waktu</th>
                        <th class="text-center">Metode</th>
                        <th class="text-right">Total</th>
                        <th class="text-center">Status</th>
                        <th class="text-center w-24">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($sales as $sale)
                    <tr>
                        <td>
                            <span class="block text-[9px] font-bold text-gray-400 uppercase tracking-wide">{{ $sale->document_label }}</span>
                            <a wire:navigate href="{{ route('sales.show', $sale) }}" class="font-mono text-xs font-semibold text-primary-600 hover:underline">{{ $sale->invoice_no }}</a>
                        </td>
                        <td class="text-sm text-gray-700">{{ $sale->customer_name ?: 'Umum' }}</td>
                        <td class="text-xs text-gray-500">{{ $sale->user?->name ?? '-' }}</td>
                        <td class="text-center text-xs text-gray-500">
                            {{ $sale->sold_at->format('d/m/y') }}<br>
                            <span class="text-gray-400">{{ $sale->sold_at->format('H:i') }}</span>
                        </td>
                        <td class="text-center">
                            @if($sale->payment_method === 'Tunai')
                            <span class="badge bg-emerald-50 text-emerald-700 text-xs">{{ $sale->payment_method }}</span>
                            @elseif($sale->payment_method === 'QRIS')
                            <span class="badge bg-violet-50 text-violet-750 text-xs">{{ $sale->payment_method }}</span>
                            @elseif($sale->payment_method === 'Transfer')
                            <span class="badge bg-blue-50 text-blue-700 text-xs">{{ $sale->payment_method }}</span>
                            @elseif($sale->payment_method === 'Invoice')
                            <span class="badge bg-orange-50 text-orange-700 text-xs">{{ $sale->payment_method }}</span>
                            @else
                            <span class="badge bg-gray-50 text-gray-700 text-xs">{{ $sale->payment_method }}</span>
                            @endif
                        </td>
                        <td class="text-right font-semibold text-gray-800">Rp {{ number_format($sale->total, 0, ',', '.') }}</td>
                        <td class="text-center">
                            <div class="flex flex-col items-center gap-0.5">
                                <span class="badge {{ $sale->status === 'completed' ? 'bg-green-50 text-green-700 border-green-200' : 'bg-red-50 text-red-700 border-red-200' }} border text-xs">
                                    {{ $sale->status === 'completed' ? 'Selesai' : 'Batal' }}
                                </span>
                                @if($sale->status === 'completed')
                                    @if($sale->payment_status === 'paid')
                                    <span class="text-[9px] font-bold text-emerald-600 uppercase tracking-wide">Lunas</span>
                                    @else
                                    <span class="text-[9px] font-black text-red-500 uppercase tracking-wide">
                                        Tempo 
                                        @if($sale->due_date)
                                            @php
                                                $daysOverdue = now()->diffInDays($sale->due_date, false);
                                            @endphp
                                            @if($daysOverdue < 0)
                                                (Overdue {{ abs($daysOverdue) }}d)
                                            @else
                                                (H-{{ $daysOverdue }})
                                            @endif
                                        @endif
                                    </span>
                                    @endif
                                @endif
                            </div>
                        </td>
                        <td class="text-center">
                            <div class="flex items-center justify-center gap-1">
                                <a wire:navigate href="{{ route('sales.show', $sale) }}" class="btn-icon" title="Detail">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                </a>
                                <a href="{{ route('sales.print', $sale) }}" class="btn-icon" title="Struk" target="_blank">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                </a>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center py-12">
                            <div class="flex flex-col items-center gap-2 text-gray-400">
                                <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                                <p class="text-sm">Tidak ada transaksi ditemukan</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($sales->hasPages())
        <div class="px-4 py-3 border-t border-gray-100">
            {{ $sales->links() }}
        </div>
        @endif
    </div>

    <div class="mt-3 text-xs text-gray-400 text-right">
        {{ $sales->total() }} transaksi ditemukan
    </div>
</div>
