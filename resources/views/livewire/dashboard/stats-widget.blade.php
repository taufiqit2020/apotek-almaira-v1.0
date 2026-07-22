<div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
    {{-- Penjualan Hari Ini --}}
    <div class="stat-card border-l-4 border-emerald-500 relative overflow-hidden">
        <div wire:loading.delay.longer.class="opacity-50" class="transition-opacity duration-300">
            <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Penjualan Hari Ini</p>
            <h3 class="text-2xl font-extrabold text-emerald-600 mt-1">Rp {{ number_format($salesTodayAmount, 0, ',', '.') }}</h3>
            <div class="flex items-center gap-1 mt-2">
                <span class="inline-flex items-center gap-1 text-xs text-gray-500 font-medium">
                    Data hari ini
                </span>
            </div>
        </div>
        <div class="absolute top-3 right-3 w-10 h-10 bg-emerald-50 rounded-xl flex items-center justify-center">
            <svg class="w-5 h-5 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        </div>
    </div>

    {{-- Jumlah Transaksi --}}
    <div class="stat-card border-l-4 border-blue-500 relative overflow-hidden">
        <div wire:loading.delay.longer.class="opacity-50" class="transition-opacity duration-300">
            <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Transaksi Hari Ini</p>
            <h3 class="text-2xl font-extrabold text-blue-600 mt-1">{{ $salesTodayCount }}</h3>
            <p class="text-xs text-gray-400 mt-2">transaksi selesai</p>
        </div>
        <div class="absolute top-3 right-3 w-10 h-10 bg-blue-50 rounded-xl flex items-center justify-center">
            <svg class="w-5 h-5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
        </div>
    </div>

    @if($isAdmin)
    {{-- Keuntungan --}}
    <div class="stat-card border-l-4 border-violet-500 relative overflow-hidden">
        <div wire:loading.delay.longer.class="opacity-50" class="transition-opacity duration-300">
            <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Estimasi Profit</p>
            <h3 class="text-2xl font-extrabold text-violet-600 mt-1">Rp {{ number_format($profitToday, 0, ',', '.') }}</h3>
            <p class="text-xs text-gray-400 mt-2">keuntungan kotor hari ini</p>
        </div>
        <div class="absolute top-3 right-3 w-10 h-10 bg-violet-50 rounded-xl flex items-center justify-center">
            <svg class="w-5 h-5 text-violet-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg>
        </div>
    </div>

    {{-- Stok Kritis --}}
    <div class="stat-card border-l-4 {{ $lowStockCount > 0 ? 'border-red-500' : 'border-gray-200' }} relative overflow-hidden">
        <div wire:loading.delay.longer.class="opacity-50" class="transition-opacity duration-300">
            <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Stok Kritis</p>
            <h3 class="text-2xl font-extrabold {{ $lowStockCount > 0 ? 'text-red-600' : 'text-gray-400' }} mt-1">{{ $lowStockCount }}</h3>
            <p class="text-xs {{ $lowStockCount > 0 ? 'text-red-500' : 'text-gray-400' }} mt-2">produk perlu reorder</p>
        </div>
        <div class="absolute top-3 right-3 w-10 h-10 {{ $lowStockCount > 0 ? 'bg-red-50' : 'bg-gray-50' }} rounded-xl flex items-center justify-center">
            <svg class="w-5 h-5 {{ $lowStockCount > 0 ? 'text-red-500' : 'text-gray-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
        </div>
    </div>
    @endif
</div>
