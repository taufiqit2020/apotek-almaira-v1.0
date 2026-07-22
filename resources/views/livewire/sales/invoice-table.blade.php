<div>
    {{-- ─── Stats Cards ─────────────────────────────────────────────── --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
        {{-- Belum Lunas --}}
        <div class="card p-4 bg-white border border-gray-100 rounded-2xl shadow-sm flex items-center gap-4 cursor-pointer transition-all hover:shadow-md hover:border-amber-200 {{ $filterStatus === 'unpaid' ? 'ring-2 ring-amber-400' : '' }}"
             wire:click="$set('filterStatus', 'unpaid')">
            <div class="w-12 h-12 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center flex-shrink-0">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <div>
                <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Belum Lunas</p>
                <p class="text-2xl font-extrabold text-amber-600">{{ $stats['total_unpaid'] }}</p>
                <p class="text-xs text-gray-500 font-medium">Rp {{ number_format($stats['amount_unpaid'], 0, ',', '.') }}</p>
            </div>
        </div>

        {{-- Overdue --}}
        <div class="card p-4 bg-white border border-gray-100 rounded-2xl shadow-sm flex items-center gap-4 cursor-pointer transition-all hover:shadow-md hover:border-red-200 {{ $filterStatus === 'overdue' ? 'ring-2 ring-red-400' : '' }}"
             wire:click="$set('filterStatus', 'overdue')">
            <div class="w-12 h-12 rounded-xl bg-red-50 text-red-600 flex items-center justify-center flex-shrink-0">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                </svg>
            </div>
            <div>
                <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Jatuh Tempo / Overdue</p>
                <p class="text-2xl font-extrabold text-red-600">{{ $stats['total_overdue'] }}</p>
                <p class="text-xs text-gray-500 font-medium">Rp {{ number_format($stats['amount_overdue'], 0, ',', '.') }}</p>
            </div>
        </div>

        {{-- Lunas Bulan Ini --}}
        <div class="card p-4 bg-white border border-gray-100 rounded-2xl shadow-sm flex items-center gap-4 cursor-pointer transition-all hover:shadow-md hover:border-emerald-200 {{ $filterStatus === 'paid' ? 'ring-2 ring-emerald-400' : '' }}"
             wire:click="$set('filterStatus', 'paid')">
            <div class="w-12 h-12 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center flex-shrink-0">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <div>
                <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Lunas Bulan Ini</p>
                <p class="text-2xl font-extrabold text-emerald-600">{{ $stats['total_paid_month'] }}</p>
                <p class="text-xs text-gray-500 font-medium">bulan ini · POS + Mitra</p>
            </div>
        </div>
    </div>

    {{-- ─── Filter Bar ──────────────────────────────────────────────── --}}
    <div class="card p-4 bg-white border border-gray-100 rounded-2xl shadow-sm mb-4">
        <div class="flex flex-wrap gap-3 items-end">
            {{-- Search --}}
            <div class="flex-1 min-w-[160px]">
                <label class="text-[10px] font-bold text-gray-400 uppercase tracking-widest block mb-1">Cari Invoice / PO / Mitra</label>
                <div class="relative">
                    <input type="text" wire:model.live.debounce.300ms="search"
                           placeholder="No. invoice, PO mitra, atau nama..."
                           class="form-input text-sm pl-8 w-full rounded-xl border-gray-200">
                    <svg class="absolute left-2.5 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                </div>
            </div>

            {{-- Status Filter --}}
            <div class="min-w-[130px]">
                <label class="text-[10px] font-bold text-gray-400 uppercase tracking-widest block mb-1">Status</label>
                <select wire:model.live="filterStatus" class="form-input text-sm w-full rounded-xl border-gray-200">
                    <option value="all">Semua</option>
                    <option value="unpaid">Belum Lunas</option>
                    <option value="overdue">Overdue</option>
                    <option value="paid">Sudah Lunas</option>
                </select>
            </div>

            {{-- Start Date --}}
            <div>
                <label class="text-[10px] font-bold text-gray-400 uppercase tracking-widest block mb-1">Dari Tanggal</label>
                <input type="date" wire:model.live="startDate" class="form-input text-sm rounded-xl border-gray-200">
            </div>

            {{-- End Date --}}
            <div>
                <label class="text-[10px] font-bold text-gray-400 uppercase tracking-widest block mb-1">Sampai Tanggal</label>
                <input type="date" wire:model.live="endDate" class="form-input text-sm rounded-xl border-gray-200">
            </div>

            {{-- Reset --}}
            <button wire:click="clearFilters" class="flex items-center gap-1.5 px-3 py-2 text-xs font-bold text-gray-500 hover:text-red-600 border border-gray-200 rounded-xl hover:border-red-200 transition-all cursor-pointer">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                </svg>
                Reset
            </button>
        </div>
    </div>

    {{-- ─── Table ───────────────────────────────────────────────────── --}}
    <div class="card bg-white border border-gray-100 rounded-2xl shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead>
                    <tr class="text-[10px] font-bold text-gray-400 uppercase tracking-wider border-b border-gray-100 bg-gray-50/80">
                        <th class="py-3 px-4">No Invoice / PO</th>
                        <th class="py-3 px-4">Sumber</th>
                        <th class="py-3 px-4">Pelanggan / Mitra</th>
                        <th class="py-3 px-4">Kasir</th>
                        <th class="py-3 px-4">Tgl Transaksi</th>
                        <th class="py-3 px-4">Jatuh Tempo</th>
                        <th class="py-3 px-4 text-right">Total</th>
                        <th class="py-3 px-4 text-center">Status</th>
                        <th class="py-3 px-4 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse($invoices as $inv)
                    @php
                        $isOverdue = !empty($inv['is_overdue']);
                        $dueDate = $inv['due_date'] ?? null;
                        $isDueToday = !$isOverdue && ($inv['payment_status'] ?? '') === 'unpaid' && $dueDate && $dueDate->isToday();
                        $isDueSoon = !$isOverdue && ($inv['payment_status'] ?? '') === 'unpaid' && $dueDate && $dueDate->isFuture()
                            && (int) now()->diffInDays($dueDate, false) <= 3;
                        $isMitra = ($inv['source'] ?? '') === 'mitra';
                    @endphp
                    <tr class="hover:bg-gray-50/60 transition-colors {{ $isOverdue ? 'bg-red-50/30' : '' }}">
                        <td class="py-3.5 px-4">
                            <a wire:navigate href="{{ $inv['detail_url'] }}" class="text-sm font-bold text-emerald-700 hover:text-emerald-900 hover:underline font-mono">
                                {{ $inv['ref'] }}
                            </a>
                        </td>
                        <td class="py-3.5 px-4">
                            <span class="inline-flex px-2 py-0.5 rounded-md text-[10px] font-bold border
                                {{ $isMitra ? 'bg-sky-50 text-sky-700 border-sky-200' : 'bg-blue-50 text-blue-700 border-blue-200' }}">
                                {{ $isMitra ? 'Mitra PO' : 'POS' }}
                            </span>
                        </td>
                        <td class="py-3.5 px-4">
                            <span class="text-sm font-semibold text-gray-800">{{ $inv['customer_name'] }}</span>
                        </td>
                        <td class="py-3.5 px-4">
                            <span class="text-xs text-gray-500">{{ $inv['cashier'] ?? '-' }}</span>
                        </td>
                        <td class="py-3.5 px-4">
                            @if($inv['transacted_at'])
                                <span class="text-xs text-gray-600">{{ $inv['transacted_at']->format('d M Y') }}</span>
                                <span class="block text-[10px] text-gray-400">{{ $inv['transacted_at']->format('H:i') }} WIB</span>
                            @else
                                <span class="text-gray-400 text-xs">-</span>
                            @endif
                        </td>
                        <td class="py-3.5 px-4">
                            @if($dueDate)
                                <span class="text-xs font-bold {{ $isOverdue ? 'text-red-600' : ($isDueToday ? 'text-amber-600' : ($isDueSoon ? 'text-orange-500' : 'text-gray-700')) }}">
                                    {{ $dueDate->format('d M Y') }}
                                </span>
                                @if($isOverdue)
                                    <span class="block text-[9px] font-extrabold text-red-600 uppercase tracking-wide">
                                        Lewat {{ $dueDate->diffForHumans(null, true) }}
                                    </span>
                                @elseif($isDueToday)
                                    <span class="block text-[9px] font-extrabold text-amber-600 uppercase tracking-wide">Jatuh tempo hari ini!</span>
                                @elseif($isDueSoon)
                                    <span class="block text-[9px] font-extrabold text-orange-500 uppercase tracking-wide">{{ (int) now()->diffInDays($dueDate, false) }} hari lagi</span>
                                @endif
                            @else
                                <span class="text-gray-400 text-xs">-</span>
                            @endif
                        </td>
                        <td class="py-3.5 px-4 text-right">
                            <span class="text-sm font-extrabold text-gray-900">Rp {{ number_format($inv['total'], 0, ',', '.') }}</span>
                        </td>
                        <td class="py-3.5 px-4 text-center">
                            @if(($inv['payment_status'] ?? '') === 'paid')
                                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-[10px] font-extrabold bg-emerald-50 text-emerald-700 ring-1 ring-emerald-200">
                                    ✓ LUNAS
                                </span>
                                @if(!empty($inv['settlement_method']))
                                    <span class="block text-[9px] text-gray-400 mt-0.5">{{ $inv['settlement_method'] === 'cash' ? 'Tunai' : 'Transfer' }}</span>
                                @endif
                            @elseif($isOverdue)
                                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-[10px] font-extrabold bg-red-50 text-red-700 ring-1 ring-red-200 animate-pulse">
                                    ⚠ OVERDUE
                                </span>
                            @elseif($isDueToday)
                                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-[10px] font-extrabold bg-amber-50 text-amber-700 ring-1 ring-amber-200">
                                    ⏰ HARI INI
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-[10px] font-extrabold bg-slate-50 text-slate-600 ring-1 ring-slate-200">
                                    ○ BELUM LUNAS
                                </span>
                            @endif
                        </td>
                        <td class="py-3.5 px-4 text-center">
                            <div class="flex items-center justify-center gap-1.5">
                                <a wire:navigate href="{{ $inv['detail_url'] }}" class="p-1.5 rounded-lg text-gray-400 hover:text-emerald-600 hover:bg-emerald-50 transition-colors" title="Detail">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                    </svg>
                                </a>
                                @if(!$isMitra && !empty($inv['print_url']))
                                <a href="{{ $inv['print_url'] }}" target="_blank" rel="noopener noreferrer" class="p-1.5 rounded-lg text-gray-400 hover:text-blue-600 hover:bg-blue-50 transition-colors" title="Cetak Invoice">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path>
                                    </svg>
                                </a>
                                <a href="{{ $inv['export_url'] }}" class="p-1.5 rounded-lg text-gray-400 hover:text-green-600 hover:bg-green-50 transition-colors" title="Unduh Excel">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                                    </svg>
                                </a>
                                @endif
                                @if(!empty($inv['can_pay']))
                                    @if($isMitra)
                                    <button wire:click="openMitraPayModal({{ $inv['id'] }})"
                                            class="flex items-center gap-1 px-2.5 py-1.5 rounded-lg text-[10px] font-extrabold bg-orange-500 text-white hover:bg-orange-600 transition-colors shadow-sm cursor-pointer">
                                        💰 Lunasi
                                    </button>
                                    @else
                                    <button wire:click="openPayModal({{ $inv['id'] }})"
                                            class="flex items-center gap-1 px-2.5 py-1.5 rounded-lg text-[10px] font-extrabold bg-orange-500 text-white hover:bg-orange-600 transition-colors shadow-sm cursor-pointer">
                                        💰 Lunasi
                                    </button>
                                    @endif
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="py-16 text-center">
                            <div class="flex flex-col items-center gap-3 text-gray-400">
                                <div class="w-14 h-14 rounded-2xl bg-gray-50 flex items-center justify-center">
                                    <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                    </svg>
                                </div>
                                <p class="font-semibold text-sm">Tidak ada tagihan invoice ditemukan</p>
                                <p class="text-xs">Coba ubah filter atau rentang tanggal</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($invoices->hasPages())
        <div class="px-4 py-3 border-t border-gray-100 bg-gray-50/50">
            {{ $invoices->links() }}
        </div>
        @endif
    </div>

    {{-- ─── Pay Modal ───────────────────────────────────────────────── --}}
    @if($showPayModal && $payingSaleData)
    <div class="fixed inset-0 z-[9999] flex items-center justify-center p-4 bg-gray-900/60 backdrop-blur-sm">
        <div class="bg-white rounded-2xl max-w-md w-full shadow-2xl border border-gray-100 overflow-hidden animate-in fade-in zoom-in-95 duration-200">
            {{-- Header --}}
            <div class="px-6 py-4 border-b border-gray-100 bg-gradient-to-r from-orange-50 to-amber-50 flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-orange-100 text-orange-600 flex items-center justify-center flex-shrink-0">
                    💰
                </div>
                <div>
                    <h3 class="text-sm font-extrabold text-gray-900">Pelunasan Invoice</h3>
                    <p class="text-xs text-gray-500">{{ $payingSaleData['invoice_no'] }}</p>
                </div>
                <button wire:click="closePayModal" class="ml-auto p-1.5 rounded-lg text-gray-400 hover:text-gray-600 hover:bg-gray-100 transition-colors cursor-pointer">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            {{-- Body --}}
            <div class="p-6">
                {{-- Invoice Info --}}
                <div class="bg-gray-50 rounded-xl p-4 mb-5 flex flex-col gap-2">
                    <div class="flex justify-between text-xs">
                        <span class="text-gray-500 font-medium">Pelanggan</span>
                        <span class="font-bold text-gray-800">{{ $payingSaleData['customer'] }}</span>
                    </div>
                    <div class="flex justify-between text-xs">
                        <span class="text-gray-500 font-medium">Jatuh Tempo</span>
                        <span class="font-bold {{ $payingSaleData['is_overdue'] ? 'text-red-600' : 'text-gray-800' }}">
                            {{ $payingSaleData['due_date'] }}
                            @if($payingSaleData['is_overdue'])
                                <span class="ml-1 px-1 py-0.5 rounded text-[9px] bg-red-100 text-red-700 font-extrabold">OVERDUE</span>
                            @endif
                        </span>
                    </div>
                    <div class="flex justify-between items-center pt-2 border-t border-gray-200">
                        <span class="text-gray-500 font-medium text-xs">Total Tagihan</span>
                        <span class="text-lg font-extrabold text-gray-900">Rp {{ number_format($payingSaleData['total'], 0, ',', '.') }}</span>
                    </div>
                </div>

                {{-- Settlement method --}}
                <div class="mb-5">
                    <label class="text-xs font-bold text-gray-700 block mb-3">Pilih Metode Pelunasan</label>
                    <div class="grid grid-cols-2 gap-3">
                        <label class="relative cursor-pointer">
                            <input type="radio" wire:model="settlementMethod" value="cash" class="sr-only peer">
                            <div class="p-3.5 rounded-xl border-2 border-gray-200 peer-checked:border-emerald-500 peer-checked:bg-emerald-50 transition-all text-center">
                                <div class="text-2xl mb-1">💵</div>
                                <p class="text-xs font-extrabold text-gray-800">Tunai</p>
                                <p class="text-[10px] text-gray-400 mt-0.5">Bayar cash</p>
                            </div>
                        </label>
                        <label class="relative cursor-pointer">
                            <input type="radio" wire:model="settlementMethod" value="transfer" class="sr-only peer">
                            <div class="p-3.5 rounded-xl border-2 border-gray-200 peer-checked:border-blue-500 peer-checked:bg-blue-50 transition-all text-center">
                                <div class="text-2xl mb-1">🏦</div>
                                <p class="text-xs font-extrabold text-gray-800">Transfer</p>
                                <p class="text-[10px] text-gray-400 mt-0.5">Via bank / m-banking</p>
                            </div>
                        </label>
                    </div>
                </div>

                {{-- Buttons --}}
                <div class="flex gap-3">
                    <button wire:click="closePayModal" class="flex-1 px-4 py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-700 font-bold rounded-xl text-sm transition-colors cursor-pointer">
                        Batal
                    </button>
                    <button wire:click="processPayment" wire:loading.attr="disabled" wire:loading.class="opacity-75 cursor-wait"
                            class="flex-1 px-4 py-2.5 bg-orange-500 hover:bg-orange-600 text-white font-extrabold rounded-xl text-sm transition-colors shadow-sm cursor-pointer flex items-center justify-center gap-2">
                        <span wire:loading.remove wire:target="processPayment">✓ Konfirmasi Lunas</span>
                        <span wire:loading wire:target="processPayment">
                            <svg class="w-4 h-4 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 12a8 8 0 018-8v4l3-3-3-3v4a8 8 0 100 16 8 8 0 01-8-8z"/>
                            </svg>
                            Memproses...
                        </span>
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- ─── Mitra PO Pay Modal (form POST — bukti transfer) ─────────── --}}
    @if($showMitraPayModal && $payingMitraData)
    <div class="fixed inset-0 z-[9999] flex items-center justify-center p-4 bg-gray-900/60 backdrop-blur-sm">
        <div class="bg-white rounded-2xl max-w-md w-full shadow-2xl border border-gray-100 overflow-hidden animate-in fade-in zoom-in-95 duration-200">
            <div class="px-6 py-4 border-b border-gray-100 bg-gradient-to-r from-sky-50 to-emerald-50 flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-sky-100 text-sky-600 flex items-center justify-center flex-shrink-0">
                    🏥
                </div>
                <div>
                    <h3 class="text-sm font-extrabold text-gray-900">Pelunasan PO Mitra</h3>
                    <p class="text-xs text-gray-500 font-mono">{{ $payingMitraData['order_no'] }}</p>
                </div>
                <button wire:click="closeMitraPayModal" type="button" class="ml-auto p-1.5 rounded-lg text-gray-400 hover:text-gray-600 hover:bg-gray-100 transition-colors cursor-pointer">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            <form action="{{ $payingMitraData['pay_url'] }}" method="POST" enctype="multipart/form-data"
                  class="p-6" id="mitra-invoice-pay-form"
                  onsubmit="return confirmMitraInvoicePay(this)">
                @csrf
                <input type="hidden" name="redirect" value="invoices">

                <div class="bg-gray-50 rounded-xl p-4 mb-5 flex flex-col gap-2">
                    <div class="flex justify-between text-xs">
                        <span class="text-gray-500 font-medium">Mitra</span>
                        <span class="font-bold text-gray-800">{{ $payingMitraData['customer'] }}</span>
                    </div>
                    <div class="flex justify-between text-xs">
                        <span class="text-gray-500 font-medium">Jatuh Tempo</span>
                        <span class="font-bold {{ $payingMitraData['is_overdue'] ? 'text-red-600' : 'text-gray-800' }}">
                            {{ $payingMitraData['due_date'] }}
                            @if($payingMitraData['is_overdue'])
                                <span class="ml-1 px-1 py-0.5 rounded text-[9px] bg-red-100 text-red-700 font-extrabold">OVERDUE</span>
                            @endif
                        </span>
                    </div>
                    <div class="flex justify-between items-center pt-2 border-t border-gray-200">
                        <span class="text-gray-500 font-medium text-xs">Total Tagihan</span>
                        <span class="text-lg font-extrabold text-gray-900">Rp {{ number_format($payingMitraData['total'], 0, ',', '.') }}</span>
                    </div>
                </div>

                <div class="mb-4">
                    <label class="text-xs font-bold text-gray-700 block mb-2">Metode Pelunasan</label>
                    <select name="settlement_method" id="mitra-settlement-method" required
                            onchange="toggleMitraProof(this)"
                            class="w-full form-input py-2.5 text-sm rounded-xl border-gray-200">
                        <option value="transfer">Transfer</option>
                        <option value="cash">Tunai</option>
                    </select>
                </div>

                <div id="mitra-proof-wrap" class="mb-5 rounded-xl border border-sky-200 bg-sky-50/90 p-3">
                    <label class="flex items-center gap-1.5 text-[10px] font-bold uppercase tracking-wider text-sky-800 mb-2">
                        Bukti Transfer
                    </label>
                    <input type="file" name="settlement_proof" id="mitra-settlement-proof"
                           accept=".jpg,.jpeg,.png,.webp,.pdf"
                           class="block w-full text-xs text-gray-600 file:mr-2 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:bg-sky-600 file:text-white file:font-bold file:text-[10px]">
                    <p class="text-[10px] text-sky-700/80 mt-1.5">Wajib jika Transfer · JPG, PNG, WEBP, PDF · maks. 4 MB</p>
                </div>

                <div class="flex gap-3">
                    <button wire:click="closeMitraPayModal" type="button"
                            class="flex-1 px-4 py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-700 font-bold rounded-xl text-sm transition-colors cursor-pointer">
                        Batal
                    </button>
                    <button type="submit"
                            class="flex-1 px-4 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white font-extrabold rounded-xl text-sm transition-colors shadow-sm cursor-pointer">
                        ✓ Konfirmasi Lunas
                    </button>
                </div>
            </form>
        </div>
    </div>
    <script>
        function toggleMitraProof(select) {
            const wrap = document.getElementById('mitra-proof-wrap');
            const input = document.getElementById('mitra-settlement-proof');
            if (!wrap || !input) return;
            const isTransfer = select.value === 'transfer';
            wrap.style.display = isTransfer ? 'block' : 'none';
            input.required = isTransfer;
            if (!isTransfer) input.value = '';
        }
        function confirmMitraInvoicePay(form) {
            const method = form.querySelector('[name="settlement_method"]')?.value;
            const proof = form.querySelector('[name="settlement_proof"]');
            if (method === 'transfer' && proof && !proof.files.length) {
                alert('Bukti transfer wajib diunggah untuk metode Transfer.');
                return false;
            }
            return confirm('Konfirmasi pelunasan PO mitra ini?');
        }
        document.addEventListener('DOMContentLoaded', () => {
            const sel = document.getElementById('mitra-settlement-method');
            if (sel) toggleMitraProof(sel);
        });
    </script>
    @endif

    {{-- Toast event dispatch to global toastManager --}}
    <script>
        window.addEventListener('toast', e => {
            window.dispatchEvent(new CustomEvent('toast', { detail: { type: e.detail.type, message: e.detail.message } }));
        });
    </script>
</div>
