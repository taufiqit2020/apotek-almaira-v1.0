@extends('layouts.app')
@section('title', 'Dashboard Analitik')
@section('page-title', 'Analitik Lanjutan')

@section('breadcrumb')
<svg class="w-3 h-3 text-gray-400" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"/></svg>
<span class="text-gray-600 font-medium">Analitik</span>
@endsection

@section('content')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener("DOMContentLoaded", function() {
        const ctx = document.getElementById('topSellersChart').getContext('2d');
        const labels = [@foreach($topSellers as $ts) '{{ $ts->product_name }}', @endforeach];
        const data = [@foreach($topSellers as $ts) {{ $ts->total_qty }}, @endforeach];

        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Kuantitas Terjual',
                    data: data,
                    backgroundColor: 'rgba(16, 185, 129, 0.7)',
                    borderColor: 'rgba(16, 185, 129, 1)',
                    borderWidth: 1.5,
                    borderRadius: 8,
                    hoverBackgroundColor: 'rgba(16, 185, 129, 0.95)'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: {
                            color: '#f3f4f6'
                        }
                    },
                    x: {
                        grid: {
                            display: false
                        }
                    }
                }
            }
        });
    });
</script>

<div class="animate-in space-y-6">
    {{-- Header --}}
    <div class="page-header mb-6 flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h2 class="page-title text-2xl font-bold text-gray-800">Analitik & Performa Bisnis</h2>
            <p class="page-subtitle text-gray-500">Pantau performa penjualan produk, efisiensi margin keuntungan, dan deadstock apotek secara real-time</p>
        </div>
        <a wire:navigate href="{{ route('analytics.shift-report') }}" class="btn btn-primary flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
            Laporan Shift Kasir
        </a>
    </div>

    {{-- Trends Cards --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        {{-- Revenue Compare --}}
        <div class="card p-6 bg-white border border-gray-100 rounded-2xl shadow-sm flex items-center justify-between">
            <div class="space-y-2">
                <span class="text-xs font-bold text-gray-400 uppercase tracking-wider block">Omzet Bulan Ini vs Lalu</span>
                <div class="flex items-baseline gap-2">
                    <span class="text-2xl font-black text-slate-800">Rp {{ number_format($thisMonthRevenue, 0, ',', '.') }}</span>
                    <span class="text-xs text-gray-400">vs Rp {{ number_format($lastMonthRevenue, 0, ',', '.') }}</span>
                </div>
                <div class="text-xs">
                    @if($thisMonthRevenue >= $lastMonthRevenue)
                    <span class="text-emerald-600 font-bold">▲ Naik {{ $lastMonthRevenue > 0 ? round((($thisMonthRevenue - $lastMonthRevenue)/$lastMonthRevenue)*100, 1) : 100 }}%</span>
                    @else
                    <span class="text-red-650 font-bold">▼ Turun {{ $lastMonthRevenue > 0 ? round((($lastMonthRevenue - $thisMonthRevenue)/$lastMonthRevenue)*100, 1) : 0 }}%</span>
                    @endif
                </div>
            </div>
            <div class="p-4 bg-emerald-50 text-emerald-600 rounded-2xl shadow-inner">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
        </div>

        {{-- Transactions Compare --}}
        <div class="card p-6 bg-white border border-gray-100 rounded-2xl shadow-sm flex items-center justify-between">
            <div class="space-y-2">
                <span class="text-xs font-bold text-gray-400 uppercase tracking-wider block">Transaksi Bulan Ini vs Lalu</span>
                <div class="flex items-baseline gap-2">
                    <span class="text-2xl font-black text-slate-800">{{ number_format($thisMonthTx) }} Tx</span>
                    <span class="text-xs text-gray-400">vs {{ number_format($lastMonthTx) }} Tx</span>
                </div>
                <div class="text-xs">
                    @if($thisMonthTx >= $lastMonthTx)
                    <span class="text-emerald-600 font-bold">▲ Naik {{ $lastMonthTx > 0 ? round((($thisMonthTx - $lastMonthTx)/$lastMonthTx)*100, 1) : 100 }}%</span>
                    @else
                    <span class="text-red-650 font-bold">▼ Turun {{ $lastMonthTx > 0 ? round((($lastMonthTx - $thisMonthTx)/$lastMonthTx)*100, 1) : 0 }}%</span>
                    @endif
                </div>
            </div>
            <div class="p-4 bg-blue-50 text-blue-600 rounded-2xl shadow-inner">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
            </div>
        </div>
    </div>

    {{-- Top Sellers Chart --}}
    <div class="grid grid-cols-1 gap-6">
        <div class="card p-6 bg-white border border-gray-100 rounded-2xl shadow-sm">
            <h3 class="text-lg font-bold text-gray-800 mb-4 border-b border-gray-50 pb-2">Top 10 Produk Terlaris (30 Hari Terakhir)</h3>
            <div class="h-80">
                <canvas id="topSellersChart"></canvas>
            </div>
        </div>
    </div>

    {{-- Deadstock & Margin Table --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        {{-- Deadstock Alert --}}
        <div class="card p-6 bg-white border border-gray-100 rounded-2xl shadow-sm flex flex-col">
            <div class="border-b border-gray-50 pb-2 mb-4 flex items-center justify-between">
                <h3 class="text-base font-bold text-gray-850">Deadstock Alert (Tidak Terjual 30 Hari)</h3>
                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800">Deadstock</span>
            </div>
            <div class="overflow-x-auto flex-1">
                <table class="w-full text-left border-collapse text-xs">
                    <thead>
                        <tr class="border-b border-gray-100 text-gray-400 font-bold uppercase">
                            <th class="py-2.5 px-2">Nama Produk</th>
                            <th class="py-2.5 px-2">Kategori</th>
                            <th class="py-2.5 px-2 text-center">Stok Fisik</th>
                            <th class="py-2.5 px-2 text-right">Nilai Barang (HPP)</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @forelse($deadstock as $ds)
                        <tr>
                            <td class="py-2.5 px-2 font-semibold text-gray-850">{{ $ds->name }}</td>
                            <td class="py-2.5 px-2 text-gray-500">{{ $ds->category?->name ?? '-' }}</td>
                            <td class="py-2.5 px-2 text-center font-bold text-amber-600">{{ $ds->stock }} {{ $ds->unit?->name }}</td>
                            <td class="py-2.5 px-2 text-right font-semibold text-gray-600">Rp {{ number_format($ds->stock * $ds->purchase_price, 0, ',', '.') }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="text-center py-6 text-gray-400">Tidak ada deadstock terdeteksi. Semua produk laku!</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Margin Analysis --}}
        <div class="card p-6 bg-white border border-gray-100 rounded-2xl shadow-sm flex flex-col">
            <h3 class="text-base font-bold text-gray-850 mb-4 border-b border-gray-50 pb-2">Analisis Margin Keuntungan Produk</h3>
            <div class="overflow-x-auto flex-1">
                <table class="w-full text-left border-collapse text-xs">
                    <thead>
                        <tr class="border-b border-gray-100 text-gray-400 font-bold uppercase">
                            <th class="py-2.5 px-2">Nama Produk</th>
                            <th class="py-2.5 px-2 text-right">Harga Beli</th>
                            <th class="py-2.5 px-2 text-right">Harga Jual</th>
                            <th class="py-2.5 px-2 text-right">Profit Margin</th>
                            <th class="py-2.5 px-2 text-center">Margin %</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @foreach($productsWithMargin as $pwm)
                        @php
                            $profit = $pwm->sell_price - $pwm->purchase_price;
                            $marginPercent = $pwm->sell_price > 0 ? round(($profit / $pwm->sell_price) * 100, 1) : 0;
                            $isNegative = $profit < 0;
                        @endphp
                        <tr class="{{ $isNegative ? 'bg-red-50 text-red-700' : 'hover:bg-gray-50/50' }}">
                            <td class="py-2.5 px-2 font-semibold text-gray-850">{{ $pwm->name }}</td>
                            <td class="py-2.5 px-2 text-right">Rp {{ number_format($pwm->purchase_price, 0, ',', '.') }}</td>
                            <td class="py-2.5 px-2 text-right">Rp {{ number_format($pwm->sell_price, 0, ',', '.') }}</td>
                            <td class="py-2.5 px-2 text-right font-bold {{ $isNegative ? 'text-red-700' : 'text-emerald-700' }}">
                                Rp {{ number_format($profit, 0, ',', '.') }}
                            </td>
                            <td class="py-2.5 px-2 text-center font-bold">
                                <span class="px-2 py-0.5 rounded-full {{ $isNegative ? 'bg-red-200 text-red-800' : 'bg-emerald-50 text-emerald-800' }}">
                                    {{ $marginPercent }}%
                                </span>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @if($productsWithMargin->hasPages())
            <div class="pt-4 border-t border-gray-50 mt-4 text-[10px]">
                {{ $productsWithMargin->links() }}
            </div>
            @endif
        </div>
    </div>
</div>


@endsection
