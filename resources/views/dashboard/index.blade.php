@extends('layouts.app')

@section('title', 'Dashboard')
@section('page-title', 'Dashboard')

@section('content')
@if(!auth()->user()->isKasir())
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        // --- 1. Sales Trend Bar Chart ---
        const dailyLabels = @json($dailyLabels ?? []);
        const dailyValues = @json($dailyValues ?? []);
        const weeklyLabels = @json($weeklyLabels ?? []);
        const weeklyValues = @json($weeklyValues ?? []);
        const monthlyLabels = @json($monthlyLabels ?? []);
        const monthlyValues = @json($monthlyValues ?? []);

        const ctx = document.getElementById('salesTrendChart').getContext('2d');
        let salesChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: dailyLabels,
                datasets: [{
                    label: 'Total Penjualan (Rp)',
                    data: dailyValues,
                    backgroundColor: 'rgba(16, 185, 129, 0.65)',
                    borderColor: 'rgb(16, 185, 129)',
                    borderWidth: 1.5,
                    borderRadius: 8,
                    borderSkipped: false
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                let label = context.dataset.label || '';
                                if (label) {
                                    label += ': ';
                                }
                                if (context.parsed.y !== null) {
                                    label += new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(context.parsed.y);
                                }
                                return label;
                            }
                        }
                    }
                },
                scales: {
                    x: {
                        grid: {
                            display: false
                        },
                        ticks: {
                            font: {
                                size: 11,
                                weight: '500'
                            }
                        }
                    },
                    y: {
                        beginAtZero: true,
                        grid: {
                            color: 'rgba(0, 0, 0, 0.04)'
                        },
                        ticks: {
                            font: {
                                size: 10,
                                weight: '500'
                            },
                            callback: function(value) {
                                if (value >= 1000000) {
                                    return 'Rp ' + (value / 1000000).toFixed(1) + 'jt';
                                }
                                return 'Rp ' + new Intl.NumberFormat('id-ID', { maximumFractionDigits: 0 }).format(value / 1000) + 'rb';
                            }
                        }
                    }
                }
            }
        });

        // Expose update chart globally so Alpine can trigger it
        window.updateChart = function(type) {
            let labels, values;
            if (type === 'harian') {
                labels = dailyLabels;
                values = dailyValues;
            } else if (type === 'mingguan') {
                labels = weeklyLabels;
                values = weeklyValues;
            } else if (type === 'bulanan') {
                labels = monthlyLabels;
                values = monthlyValues;
            }

            salesChart.data.labels = labels;
            salesChart.data.datasets[0].data = values;
            salesChart.update();
        };

        // --- 2. Payment Method Pie Chart ---
        const canvas = document.getElementById('paymentMethodChart');
        if (canvas) {
            const ctxPie = canvas.getContext('2d');
            new Chart(ctxPie, {
                type: 'doughnut',
                data: {
                    labels: ['Tunai', 'QRIS'],
                    datasets: [{
                        data: [{{ $cashSalesTotal ?? 0 }}, {{ $qrisSalesTotal ?? 0 }}],
                        backgroundColor: [
                            'rgba(16, 185, 129, 0.8)', // Emerald
                            'rgba(59, 130, 246, 0.8)'  // Blue
                        ],
                        borderColor: [
                            'rgb(16, 185, 129)',
                            'rgb(59, 130, 246)'
                        ],
                        borderWidth: 1.5
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: {
                                boxWidth: 12,
                                font: {
                                    size: 11,
                                    weight: '600'
                                },
                                padding: 15
                            }
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    let value = context.raw;
                                    return ' ' + context.label + ': Rp ' + new Intl.NumberFormat('id-ID', { maximumFractionDigits: 0 }).format(value);
                                }
                            }
                        }
                    },
                    cutout: '70%'
                }
            });
        }
    });
</script>
@endif

<div class="animate-in">

    @if(auth()->user()->isKasir())
    {{-- ═══════════════════════════════════════════════════════
         KASIR VIEW DASHBOARD
         ══════════════════════════════════════════════════════ --}}
    {{-- Welcome Banner with POS Shortcut --}}
    <div class="relative overflow-hidden rounded-2xl bg-gradient-to-r from-emerald-800 via-emerald-600 to-teal-500 p-8 shadow-lg shadow-emerald-500/15 flex flex-col justify-between min-h-[200px] mb-6">
        <div class="absolute -right-16 -top-16 w-64 h-64 bg-white/5 rounded-full"></div>
        <div class="absolute -right-4 -bottom-16 w-48 h-48 bg-white/5 rounded-full"></div>
        <div class="relative z-10 flex justify-between items-start gap-4">
            <div>
                <h2 class="text-3xl font-extrabold text-white">
                    Selamat datang, {{ explode(',', auth()->user()->name)[0] }}! 👋
                </h2>
                <p class="text-emerald-100/90 text-sm mt-2 font-medium">
                    {{ now()->locale('id')->isoFormat('dddd, D MMMM Y') }} — Apotek Almaira · PT Nur Madani Farma
                </p>
                <p class="text-emerald-200/70 text-xs mt-1">
                    Kelola transaksi obat hari ini dengan cepat dan teliti.
                </p>
            </div>
            <div class="flex-shrink-0">
                <img src="{{ asset('assets/images/logodashboard.jpeg') }}" alt="Logo" class="h-16 w-16 rounded-xl object-contain bg-white p-1.5 shadow-md">
            </div>
        </div>
        <div class="relative z-10 mt-6">
            <a wire:navigate href="{{ route('pos.index') }}" class="inline-flex items-center gap-2 px-6 py-3.5 bg-white text-emerald-800 font-bold rounded-xl shadow-lg hover:bg-emerald-50 transition-all duration-200 hover:-translate-y-0.5 hover:shadow-xl active:translate-y-0 text-sm">
                <svg class="w-5 h-5 text-emerald-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/>
                </svg>
                Mulai Transaksi Baru
            </a>
        </div>
    </div>

    {{-- Livewire Stats Widget --}}
    <div class="mb-6">
        <livewire:dashboard.stats-widget />
    </div>

    {{-- Kasir Bottom Grid --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Recent Transactions for this Kasir (Livewire) --}}
        <div class="lg:col-span-2 card p-5 bg-white border border-gray-100 rounded-2xl shadow-sm">
            <livewire:dashboard.recent-transactions />
        </div>

        {{-- Quick Actions / System Status for Kasir --}}
        <div class="card p-5 bg-white border border-gray-100 rounded-2xl shadow-sm flex flex-col justify-between">
            <div>
                <h3 class="font-bold text-gray-800 text-lg">Menu Cepat</h3>
                <p class="text-xs text-gray-400 mt-0.5 mb-4">Navigasi pintas menu kasir</p>
                <div class="flex flex-col gap-2.5">
                    <a wire:navigate href="{{ route('pos.index') }}" class="btn btn-primary w-full flex items-center justify-center gap-2">
                        <span>🛒 Kasir (POS)</span>
                    </a>
                    <a wire:navigate href="{{ route('sales.index') }}" class="btn btn-secondary w-full flex items-center justify-center gap-2">
                        <span>📄 Riwayat Struk</span>
                    </a>
                </div>
            </div>
            <div class="mt-6 pt-4 border-t border-gray-100 text-center text-xs text-gray-400">
                Apotek Almaira v1.0
            </div>
        </div>
    </div>

    @else
    {{-- ═══════════════════════════════════════════════════════
         ADMIN & IT ADMIN VIEW DASHBOARD
         ══════════════════════════════════════════════════════ --}}
    {{-- Welcome Banner --}}
    <div class="relative overflow-hidden rounded-2xl bg-gradient-to-r from-emerald-800 via-emerald-600 to-teal-500 p-6 mb-6 shadow-lg shadow-emerald-500/15">
        <div class="absolute -right-16 -top-16 w-64 h-64 bg-white/5 rounded-full"></div>
        <div class="absolute -right-4 -bottom-16 w-48 h-48 bg-white/5 rounded-full"></div>
        <div class="relative z-10 flex items-center justify-between flex-wrap gap-4">
            <div>
                <h2 class="text-2xl font-bold text-white">
                    Selamat datang, {{ explode(',', auth()->user()->name)[0] }}! 👋
                </h2>
                <p class="text-emerald-100/80 text-sm mt-1">
                    {{ now()->locale('id')->isoFormat('dddd, D MMMM Y') }} — Apotek Almaira · PT Nur Madani Farma
                </p>
            </div>
            <div class="flex items-center gap-2">
                <button type="button"
                        onclick="Livewire.dispatch('dashboard-refresh')"
                        class="inline-flex items-center gap-2 px-3 py-2 bg-white/15 hover:bg-white/25 text-white text-xs font-bold rounded-xl border border-white/20 transition-colors"
                        title="Perbarui statistik dashboard">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 1121.21 8H18.5"/></svg>
                    Refresh Data
                </button>
                <img src="{{ asset('assets/images/logodashboard.jpeg') }}" alt="Logo" class="h-12 rounded-xl object-contain">
            </div>
        </div>
    </div>

    {{-- Livewire Stats Widget (Live) --}}
    <div class="mb-6">
        <livewire:dashboard.stats-widget />
    </div>

    {{-- Charts Section --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
        {{-- Trend Chart (Left & Mid) --}}
        <div class="lg:col-span-2 card p-5 bg-white border border-gray-100 rounded-2xl shadow-sm" x-data="{ activeTab: 'harian' }">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
                <div>
                    <h3 class="font-bold text-gray-800 text-lg">Grafik Penjualan Trend</h3>
                    <p class="text-xs text-gray-400 mt-0.5 font-medium">Pantau pertumbuhan omzet apotek</p>
                </div>
                <div class="flex bg-gray-100 p-1 rounded-xl self-start sm:self-center">
                    <button @click="activeTab = 'harian'; updateChart('harian')" :class="activeTab === 'harian' ? 'bg-white text-emerald-800 shadow-sm font-bold' : 'text-gray-500 hover:text-gray-800 font-medium'" class="px-4 py-1.5 text-xs rounded-lg transition-all cursor-pointer">
                        Harian
                    </button>
                    <button @click="activeTab = 'mingguan'; updateChart('mingguan')" :class="activeTab === 'mingguan' ? 'bg-white text-emerald-800 shadow-sm font-bold' : 'text-gray-500 hover:text-gray-800 font-medium'" class="px-4 py-1.5 text-xs rounded-lg transition-all cursor-pointer">
                        Mingguan
                    </button>
                    <button @click="activeTab = 'bulanan'; updateChart('bulanan')" :class="activeTab === 'bulanan' ? 'bg-white text-emerald-800 shadow-sm font-bold' : 'text-gray-500 hover:text-gray-800 font-medium'" class="px-4 py-1.5 text-xs rounded-lg transition-all cursor-pointer">
                        Bulanan
                    </button>
                </div>
            </div>
            <div class="relative h-[280px]">
                <canvas id="salesTrendChart"></canvas>
            </div>
        </div>

        {{-- Payment Method (Right) --}}
        <div class="card p-5 bg-white border border-gray-100 rounded-2xl shadow-sm">
            <h3 class="font-bold text-gray-800 text-lg">Metode Pembayaran</h3>
            <p class="text-xs text-gray-400 mt-0.5 mb-6 font-medium">Breakdown transaksi hari ini</p>
            <div class="relative h-[180px] flex items-center justify-center">
                @if($cashSalesCount === 0 && $qrisSalesCount === 0)
                    <div class="text-center text-gray-400 py-6">
                        <svg class="w-12 h-12 mx-auto text-gray-300 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <p class="text-sm font-medium">Belum ada transaksi</p>
                    </div>
                @else
                    <canvas id="paymentMethodChart" class="max-w-[180px] max-h-[180px]"></canvas>
                @endif
            </div>
            @if($cashSalesCount > 0 || $qrisSalesCount > 0)
            <div class="grid grid-cols-2 gap-4 mt-6 pt-4 border-t border-gray-100 text-center">
                <div>
                    <p class="text-xs font-semibold text-gray-400">Tunai</p>
                    <p class="text-base font-extrabold text-emerald-600">Rp {{ number_format($cashSalesTotal, 0, ',', '.') }}</p>
                    <p class="text-[10px] text-gray-400 font-medium">{{ $cashSalesCount }} Transaksi</p>
                </div>
                <div>
                    <p class="text-xs font-semibold text-gray-400">QRIS</p>
                    <p class="text-base font-extrabold text-blue-600">Rp {{ number_format($qrisSalesTotal, 0, ',', '.') }}</p>
                    <p class="text-[10px] text-gray-400 font-medium">{{ $qrisSalesCount }} Transaksi</p>
                </div>
            </div>
            @endif
        </div>
    </div>

    {{-- Bottom Grid: Alerts, Transactions, Top Products --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Left & Mid: 10 Transactions and Alerts --}}
        <div class="lg:col-span-2 flex flex-col gap-6">
            {{-- Alerts & Warnings Panel --}}
            <div class="card p-5 bg-white border border-gray-100 rounded-2xl shadow-sm">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h3 class="font-bold text-gray-800 text-lg">Pemberitahuan Sistem (Alerts)</h3>
                        <p class="text-xs text-gray-400 mt-0.5">Status dan masa aktif obat farmasi</p>
                    </div>
                    <span class="px-2.5 py-1 text-xs font-bold bg-amber-50 text-amber-700 rounded-lg ring-1 ring-amber-500/20">
                        {{ $lowStockProducts->count() + $expiredSoon30Count + $expiredSoon60Count }} Isu
                    </span>
                </div>
                
                <div class="flex flex-col gap-3">
                    <!-- Stok Kritis Alert -->
                    @if($lowStockProducts->count() > 0)
                    <div class="p-4 rounded-xl bg-red-50/50 border border-red-100/80 flex gap-3 items-start sm:items-center">
                        <span class="w-10 h-10 flex items-center justify-center bg-red-100 text-red-600 rounded-lg flex-shrink-0">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                            </svg>
                        </span>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-bold text-red-800">Ada {{ $lowStockProducts->count() }} Produk dengan Stok Kritis</p>
                            <div class="mt-1 flex flex-wrap gap-1">
                                @foreach($lowStockProducts->take(3) as $prod)
                                <span class="inline-flex items-center px-2 py-0.5 text-[10px] font-medium bg-white text-red-700 rounded border border-red-200">
                                    {{ $prod->name }} (Sisa: {{ $prod->stock }} {{ $prod->unit?->name ?? 'pcs' }})
                                </span>
                                @endforeach
                                @if($lowStockProducts->count() > 3)
                                <span class="text-[10px] text-red-600 font-medium self-center ml-1">+{{ $lowStockProducts->count() - 3 }} lainnya</span>
                                @endif
                            </div>
                        </div>
                        <a wire:navigate href="{{ route('products.index') }}" class="text-xs text-red-600 hover:text-red-800 font-bold self-center whitespace-nowrap ml-2">Restock</a>
                    </div>
                    @endif

                    <!-- Kadaluarsa <= 30 Hari Alert -->
                    @if($expiredSoon30Count > 0)
                    <div class="p-4 rounded-xl bg-amber-50/50 border border-amber-100/80 flex gap-3 items-start sm:items-center">
                        <span class="w-10 h-10 flex items-center justify-center bg-amber-100 text-amber-600 rounded-lg flex-shrink-0">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </span>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-bold text-amber-800">Ada {{ $expiredSoon30Count }} Produk Mendekati Kadaluarsa (≤ 30 Hari)</p>
                            <div class="mt-1 flex flex-wrap gap-1">
                                @foreach($expiredSoon30->take(3) as $prod)
                                <span class="inline-flex items-center px-2 py-0.5 text-[10px] font-medium bg-white text-amber-700 rounded border border-amber-200">
                                    {{ $prod->name }} (Kdl: {{ $prod->expired_date->format('d/m/Y') }})
                                </span>
                                @endforeach
                                @if($expiredSoon30Count > 3)
                                <span class="text-[10px] text-amber-600 font-medium self-center ml-1">+{{ $expiredSoon30Count - 3 }} lainnya</span>
                                @endif
                            </div>
                        </div>
                        <a wire:navigate href="{{ route('products.index') }}" class="text-xs text-amber-600 hover:text-amber-800 font-bold self-center whitespace-nowrap ml-2">Periksa</a>
                    </div>
                    @endif

                    <!-- Kadaluarsa <= 60 Hari Alert -->
                    @if($expiredSoon60Count > 0)
                    <div class="p-4 rounded-xl bg-orange-50/50 border border-orange-100/80 flex gap-3 items-start sm:items-center">
                        <span class="w-10 h-10 flex items-center justify-center bg-orange-100 text-orange-600 rounded-lg flex-shrink-0">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </span>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-bold text-orange-800">Ada {{ $expiredSoon60Count }} Produk Mendekati Kadaluarsa (31 - 60 Hari)</p>
                            <div class="mt-1 flex flex-wrap gap-1">
                                @foreach($expiredSoon60->take(3) as $prod)
                                <span class="inline-flex items-center px-2 py-0.5 text-[10px] font-medium bg-white text-orange-700 rounded border border-orange-200">
                                    {{ $prod->name }} (Kdl: {{ $prod->expired_date->format('d/m/Y') }})
                                </span>
                                @endforeach
                                @if($expiredSoon60Count > 3)
                                <span class="text-[10px] text-orange-600 font-medium self-center ml-1">+{{ $expiredSoon60Count - 3 }} lainnya</span>
                                @endif
                            </div>
                        </div>
                        <a wire:navigate href="{{ route('products.index') }}" class="text-xs text-orange-600 hover:text-orange-800 font-bold self-center whitespace-nowrap ml-2">Periksa</a>
                    </div>
                    @endif

                    @if($lowStockProducts->count() === 0 && $expiredSoon30Count === 0 && $expiredSoon60Count === 0)
                    <div class="flex flex-col items-center justify-center py-10 text-center">
                        <div class="w-12 h-12 rounded-full bg-emerald-50 flex items-center justify-center text-emerald-500 mb-3">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                        </div>
                        <p class="text-sm font-semibold text-gray-800">Sistem Bersih dari Isu Stok & Kadaluarsa</p>
                        <p class="text-xs text-gray-400 mt-1 font-medium">Semua produk dalam kondisi stok aman dan belum mendekati kadaluarsa.</p>
                    </div>
                    @endif
                </div>
            </div>

            {{-- 10 Recent Transactions (Livewire) --}}
            <div class="card p-5 bg-white border border-gray-100 rounded-2xl shadow-sm">
                <livewire:dashboard.recent-transactions />
            </div>
        </div>

        {{-- Right Column: Top Products & Quick Actions --}}
        <div class="flex flex-col gap-6">
            {{-- Top Products --}}
            <div class="card p-5 bg-white border border-gray-100 rounded-2xl shadow-sm">
                <h3 class="font-bold text-gray-800 text-lg mb-4">Top 5 Produk Terlaris Bulan Ini</h3>
                @if($topProducts->count() > 0)
                <div class="flex flex-col gap-4">
                    @foreach($topProducts as $index => $prod)
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-xl bg-gradient-to-br {{ $index == 0 ? 'from-amber-400 to-amber-500 text-white' : ($index == 1 ? 'from-slate-300 to-slate-400 text-white' : ($index == 2 ? 'from-amber-600 to-amber-700 text-white' : 'from-gray-100 to-gray-200 text-gray-500')) }} flex items-center justify-center font-extrabold text-sm shadow-sm flex-shrink-0">
                            {{ $index + 1 }}
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-bold text-gray-800 truncate" title="{{ $prod->product_name }}">{{ $prod->product_name }}</p>
                            <p class="text-[10px] text-gray-400">Kode: {{ $prod->product_code }}</p>
                        </div>
                        <div class="text-right">
                            <p class="text-sm font-bold text-emerald-600">{{ $prod->total_qty }} terjual</p>
                            <p class="text-[10px] text-gray-400">Rp {{ number_format($prod->total_revenue, 0, ',', '.') }}</p>
                        </div>
                    </div>
                    @endforeach
                </div>
                @else
                <div class="flex flex-col items-center justify-center py-10 text-center">
                    <svg class="w-10 h-10 text-gray-300 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
                    </svg>
                    <p class="text-sm font-semibold text-gray-400">Belum ada penjualan</p>
                </div>
                @endif
            </div>

            {{-- Quick Actions --}}
            <div class="card p-5 bg-white border border-gray-100 rounded-2xl shadow-sm">
                <h3 class="font-bold text-gray-800 text-lg mb-4">Aksi Cepat</h3>
                <div class="grid grid-cols-2 gap-3">
                    @php
                    $actions = [
                        ['icon' => 'M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z', 'label' => 'Kasir / POS', 'color' => 'bg-blue-50 text-blue-700 hover:bg-blue-100', 'url' => route('pos.index')],
                        ['icon' => 'M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4', 'label' => 'Barang Masuk', 'color' => 'bg-green-50 text-green-700 hover:bg-green-100', 'url' => route('purchases.index')],
                        ['icon' => 'M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z', 'label' => 'Riwayat', 'color' => 'bg-purple-50 text-purple-700 hover:bg-purple-100', 'url' => route('sales.index')],
                        ['icon' => 'M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4', 'label' => 'Tambah Produk', 'color' => 'bg-amber-50 text-amber-700 hover:bg-amber-100', 'url' => route('products.create')],
                    ];
                    @endphp

                    @foreach($actions as $action)
                    <a href="{{ $action['url'] }}"
                       class="flex flex-col items-center gap-2 p-4 rounded-xl {{ $action['color'] }} transition-all duration-200 hover:-translate-y-0.5 hover:shadow-md text-center">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $action['icon'] }}"/>
                        </svg>
                        <span class="text-sm font-bold">{{ $action['label'] }}</span>
                    </a>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- System Info Banner --}}
    <div class="mt-6 bg-emerald-50/50 border border-emerald-100 rounded-xl p-4 flex items-start gap-3">
        <svg class="w-5 h-5 text-emerald-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
        <div>
            <p class="text-sm font-bold text-emerald-800">Sistem berjalan normal</p>
            <p class="text-xs text-emerald-600 mt-0.5 font-medium">
                <span class="font-bold text-emerald-700">Apotek Almaira</span> v1.0 · Laravel {{ app()->version() }} · PHP {{ phpversion() }}
            </p>
        </div>
    </div>

</div>
@endsection


