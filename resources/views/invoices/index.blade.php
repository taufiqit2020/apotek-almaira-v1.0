@extends('layouts.app')

@section('title', 'Invoice')
@section('page-title', 'Manajemen Invoice')

@section('breadcrumb')
<svg class="w-3 h-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
<span class="text-gray-600 font-medium">Manajemen Invoice</span>
@endsection

@section('content')
<div class="animate-in flex flex-col gap-6">

    {{-- Page Header --}}
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div>
            <h2 class="text-xl font-extrabold text-gray-900">Tagihan Invoice (Tempo 30 Hari)</h2>
            <p class="text-xs text-gray-400 font-medium mt-0.5">Kelola tagihan invoice tempo — POS pelanggan CRM &amp; PO Mitra — pantau, lunasi, dan lacak statusnya</p>
        </div>
        <div class="flex gap-2 flex-shrink-0">
            <a wire:navigate href="{{ route('pos.index') }}" class="inline-flex items-center gap-1.5 px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-xl text-xs shadow-sm transition-all">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Transaksi Baru
            </a>
        </div>
    </div>

    {{-- Alert: overdue warning --}}
    @if($totalOverdue > 0)
    <div class="p-4 bg-red-50 border border-red-200 rounded-2xl flex items-center gap-3 text-red-800 shadow-sm">
        <div class="w-10 h-10 rounded-xl bg-red-100 text-red-600 flex items-center justify-center flex-shrink-0">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
            </svg>
        </div>
        <div class="flex-1">
            <strong class="font-extrabold text-sm block">⚠ Perhatian: Ada {{ $totalOverdue }} Tagihan Overdue!</strong>
            <p class="text-xs text-red-700 mt-0.5">
                Total tunggakan: <strong>Rp {{ number_format($amountOverdue, 0, ',', '.') }}</strong> —
                Pelanggan/mitra dengan tagihan overdue tidak dapat menggunakan metode Invoice untuk transaksi baru.
            </p>
        </div>
    </div>
    @endif

    {{-- Livewire Component --}}
    @livewire('sales.invoice-table')

</div>
@endsection
