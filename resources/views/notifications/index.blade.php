@extends('layouts.app')
@section('title', 'Notifikasi')
@section('page-title', 'Pusat Notifikasi')

@section('breadcrumb')
<svg class="w-3 h-3 text-gray-400" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"/></svg>
<span class="text-gray-600 font-medium">Notifikasi</span>
@endsection

@section('content')
<div class="animate-in space-y-6">
    {{-- Header --}}
    <div class="page-header mb-6">
        <div>
            <h2 class="page-title text-2xl font-bold text-gray-800">Pusat Notifikasi & Alert</h2>
            <p class="page-subtitle text-gray-500">Pantau seluruh notifikasi otomatis sistem, stok di bawah limit, dan obat mendekati kedaluwarsa</p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        {{-- Low Stock Alerts Card --}}
        <div class="card p-6 bg-white border border-gray-100 rounded-2xl shadow-sm flex flex-col">
            <div class="border-b border-gray-50 pb-2.5 mb-4 flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <span class="p-2 bg-red-50 text-red-650 rounded-xl">⚠️</span>
                    <h3 class="text-base font-bold text-gray-850">Stok Kritis (<span x-text="{{ $lowStock->count() }}"></span>)</h3>
                </div>
                <a wire:navigate href="{{ route('purchases.reorder') }}" class="btn btn-secondary btn-sm py-1.5 px-3 text-[10px] font-bold flex items-center gap-1">
                    🛒 Buat PO Massal
                </a>
            </div>
            
            <div class="overflow-y-auto max-h-96 flex-1 space-y-2.5 pr-1" style="scrollbar-width: thin;">
                @forelse($lowStock as $ls)
                <div class="p-3 bg-red-50/40 border border-red-100/50 rounded-xl flex items-center justify-between text-xs transition-all hover:bg-red-50">
                    <div class="space-y-1">
                        <span class="font-bold text-gray-800 text-sm block">{{ $ls->name }}</span>
                        <div class="flex items-center gap-2 text-[10px] text-gray-400 font-mono">
                            <span>Kode: {{ $ls->code }}</span>
                            <span>•</span>
                            <span>Batas Min: {{ $ls->stock_min }}</span>
                        </div>
                    </div>
                    <div class="text-right">
                        <span class="px-2.5 py-1 rounded-full text-xs font-black bg-red-100 text-red-800" title="Stok Saat Ini">
                            {{ $ls->stock }} Pcs
                        </span>
                    </div>
                </div>
                @empty
                <div class="text-center py-12 text-gray-400">
                    <p>Tidak ada stok kritis terdeteksi saat ini.</p>
                </div>
                @endforelse
            </div>
        </div>

        {{-- Expiring Soon Alerts Card --}}
        <div class="card p-6 bg-white border border-gray-100 rounded-2xl shadow-sm flex flex-col">
            <div class="border-b border-gray-50 pb-2.5 mb-4 flex items-center gap-2">
                <span class="p-2 bg-amber-50 text-amber-650 rounded-xl">📅</span>
                <h3 class="text-base font-bold text-gray-850">Mendekati Kedaluwarsa (<span x-text="{{ count($expiringData) }}"></span>)</h3>
            </div>
            
            <div class="overflow-y-auto max-h-96 flex-1 space-y-2.5 pr-1" style="scrollbar-width: thin;">
                @forelse($expiringData as $exp)
                <div class="p-3 bg-amber-50/40 border border-amber-100/50 rounded-xl flex items-center justify-between text-xs transition-all hover:bg-amber-50">
                    <div class="space-y-1">
                        <span class="font-bold text-gray-800 text-sm block">{{ $exp['name'] }}</span>
                        <div class="flex items-center gap-2 text-[10px] text-gray-400 font-mono">
                            <span>Kode: {{ $exp['code'] }}</span>
                            <span>•</span>
                            <span>Expired: {{ $exp['expired_date'] }}</span>
                        </div>
                    </div>
                    <div class="text-right">
                        <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-amber-100 text-amber-900">
                            {{ $exp['days_left'] }} Hari Lagi
                        </span>
                    </div>
                </div>
                @empty
                <div class="text-center py-12 text-gray-400">
                    <p>Tidak ada produk mendekati kedaluwarsa (dalam 30 hari).</p>
                </div>
                @endforelse
            </div>
        </div>
    </div>
    
    {{-- Webhook Simulation & System Status Log --}}
    <div class="card p-6 bg-white border border-gray-100 rounded-2xl shadow-sm">
        <h3 class="text-lg font-bold text-gray-800 mb-3 border-b border-gray-50 pb-2.5">Simulasi Webhook Notifikasi</h3>
        <p class="text-xs text-gray-400 mb-4">Integrasi notifikasi Multi-Channel (Email & WhatsApp) disimulasikan langsung dengan menulis detail log pengiriman ke berkas log Laravel.</p>
        
        <div class="bg-slate-900 rounded-xl p-4 text-xs font-mono text-emerald-400 shadow-inner overflow-x-auto max-h-40">
            <span class="text-slate-500">// Lokasi berkas simulasi notifikasi:</span><br>
            <span class="text-white">storage/logs/laravel.log</span><br><br>
            <span class="text-slate-500">// Contoh log simulasi WhatsApp:</span><br>
            <span class="text-emerald-300">[2026-06-27 08:35:12] local.INFO: 🔔 [SIMULATION WA WEBHOOK] Sending to 0851-6665-7070:</span><br>
            <span class="text-emerald-300">⚠️ [ALERT STOK KRITIS] Produk 'Amoxicillin 500mg' saat ini tersisa 4 pcs (Batas minimum: 10 pcs).</span>
        </div>
    </div>
</div>
@endsection
