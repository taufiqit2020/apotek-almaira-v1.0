@extends('layouts.app')
@section('title', 'Laporan Shift Kasir')
@section('page-title', 'Laporan Kas Harian')

@section('breadcrumb')
<svg class="w-3 h-3 text-gray-400" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"/></svg>
<a wire:navigate href="{{ route('analytics.index') }}" class="hover:text-primary-600 transition-colors">Analitik</a>
<svg class="w-3 h-3 text-gray-400" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"/></svg>
<span class="text-gray-600 font-medium">Laporan Shift</span>
@endsection

@section('content')
<style>
    @media print {
        body * {
            visibility: hidden;
        }
        #printable-shift, #printable-shift * {
            visibility: visible;
        }
        #printable-shift {
            position: absolute;
            left: 0;
            top: 0;
            width: 80mm;
            border: none !important;
            box-shadow: none !important;
            padding: 0 !important;
            margin: 0 !important;
        }
        .no-print {
            display: none !important;
        }
    }
</style>

<div class="animate-in space-y-6">
    {{-- Header --}}
    <div class="page-header mb-6 no-print">
        <div>
            <h2 class="page-title text-2xl font-bold text-gray-800">Laporan Kas Harian (Shift Kasir)</h2>
            <p class="page-subtitle text-gray-500">Rekapitulasi penjualan per kasir dan per tanggal untuk audit buku harian</p>
        </div>
        <div class="flex gap-3">
            <button onclick="window.print()" class="btn btn-primary flex items-center gap-2">
                🖨️ Cetak Rekap Shift
            </button>
            <a wire:navigate href="{{ route('analytics.index') }}" class="btn btn-secondary flex items-center gap-2">
                Kembali
            </a>
        </div>
    </div>

    {{-- Filter Form --}}
    <form method="GET" class="card p-4 mb-6 flex flex-wrap items-center gap-4 bg-white border border-gray-100 rounded-2xl shadow-sm no-print">
        <div class="w-52">
            <label class="block text-[10px] font-bold text-gray-400 uppercase mb-1">Kasir</label>
            <select name="user_id" class="form-input text-xs py-2">
                @foreach($cashiers as $c)
                <option value="{{ $c->id }}" {{ $cashierId == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="w-48">
            <label class="block text-[10px] font-bold text-gray-400 uppercase mb-1">Tanggal</label>
            <input type="date" name="date" value="{{ $date }}" class="form-input text-xs py-2">
        </div>
        <button type="submit" class="btn btn-primary btn-sm mt-5 flex items-center gap-1">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            Filter
        </button>
    </form>

    {{-- Stats cards --}}
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6 no-print">
        <div class="card p-5 bg-white border border-gray-100 rounded-2xl shadow-sm flex items-center gap-4">
            <div class="p-3 bg-emerald-50 text-emerald-600 rounded-xl">💵</div>
            <div>
                <span class="text-[10px] font-bold text-gray-400 uppercase tracking-wider block">Total Tunai</span>
                <span class="text-lg font-black text-gray-800">Rp {{ number_format($totalTunai, 0, ',', '.') }}</span>
            </div>
        </div>
        <div class="card p-5 bg-white border border-gray-100 rounded-2xl shadow-sm flex items-center gap-4">
            <div class="p-3 bg-violet-50 text-violet-600 rounded-xl">📱</div>
            <div>
                <span class="text-[10px] font-bold text-gray-400 uppercase tracking-wider block">Total QRIS</span>
                <span class="text-lg font-black text-gray-800">Rp {{ number_format($totalQris, 0, ',', '.') }}</span>
            </div>
        </div>
        <div class="card p-5 bg-white border border-gray-100 rounded-2xl shadow-sm flex items-center gap-4">
            <div class="p-3 bg-orange-50 text-orange-600 rounded-xl">🏷️</div>
            <div>
                <span class="text-[10px] font-bold text-gray-400 uppercase tracking-wider block">Total Diskon</span>
                <span class="text-lg font-black text-gray-800">Rp {{ number_format($totalDiscount, 0, ',', '.') }}</span>
            </div>
        </div>
        <div class="card p-5 bg-white border border-gray-100 rounded-2xl shadow-sm flex items-center gap-4">
            <div class="p-3 bg-blue-50 text-blue-600 rounded-xl">📈</div>
            <div>
                <span class="text-[10px] font-bold text-gray-400 uppercase tracking-wider block">Grand Total Omzet</span>
                <span class="text-lg font-black text-primary-600">Rp {{ number_format($totalAmount, 0, ',', '.') }}</span>
            </div>
        </div>
    </div>

    {{-- Printable Thermal Struk Container --}}
    <div class="card p-8 bg-white border border-gray-100 rounded-2xl shadow-sm max-w-sm mx-auto font-mono text-xs text-slate-800 relative" id="printable-shift">
        <div class="text-center space-y-1 mb-4">
            <h1 class="font-bold text-sm uppercase tracking-tight">Apotek Almaira</h1>
            <p class="text-[10px]">PT Nur Madani Farma</p>
            <p class="text-[9px]">Banjarbaru, Kalsel</p>
            <p class="border-b border-dashed border-slate-300 py-1.5"></p>
            <h2 class="font-bold text-xs uppercase pt-1">Laporan Shift Kasir</h2>
            <p class="text-[9px]">Tanggal: {{ date('d-M-Y', strtotime($date)) }}</p>
        </div>

        <div class="space-y-1.5 text-[10px]">
            <div class="flex justify-between">
                <span>Nama Kasir:</span>
                <span class="font-bold">{{ $cashierName }}</span>
            </div>
            <div class="flex justify-between">
                <span>Total Transaksi:</span>
                <span class="font-bold">{{ $totalTxCount }} Tx</span>
            </div>
            <p class="border-b border-dashed border-slate-200 my-2"></p>
            <div class="flex justify-between">
                <span>Total Tunai:</span>
                <span class="font-bold">Rp {{ number_format($totalTunai, 0, ',', '.') }}</span>
            </div>
            <div class="flex justify-between">
                <span>Total QRIS:</span>
                <span class="font-bold">Rp {{ number_format($totalQris, 0, ',', '.') }}</span>
            </div>
            <div class="flex justify-between">
                <span>Total Diskon:</span>
                <span class="font-bold">Rp {{ number_format($totalDiscount, 0, ',', '.') }}</span>
            </div>
            <p class="border-b border-dashed border-slate-300 my-2"></p>
            <div class="flex justify-between text-xs font-black">
                <span>TOTAL OMZET:</span>
                <span>Rp {{ number_format($totalAmount, 0, ',', '.') }}</span>
            </div>
            <p class="border-b border-dashed border-slate-300 my-2"></p>
        </div>

        {{-- Signature Blocks --}}
        <div class="grid grid-cols-2 gap-4 mt-8 text-[9px] text-center">
            <div>
                <p>Kasir,</p>
                <br><br><br>
                <p class="font-bold underline">{{ $cashierName }}</p>
            </div>
            <div>
                <p>Pimpinan,</p>
                <br><br><br>
                <p class="font-bold underline">Hj. Nor Maulida, S.H.</p>
            </div>
        </div>
    </div>
</div>


@endsection
