@extends('layouts.app')
@section('title', 'Barang Masuk')
@section('page-title', 'Barang Masuk / PO')

@section('breadcrumb')
<svg class="w-3 h-3 text-gray-400" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"/></svg>
<span class="text-gray-600 font-medium">Barang Masuk / PO</span>
@endsection

@section('content')
<div class="animate-in">
    {{-- Header --}}
    <div class="page-header mb-6">
        <div>
            <h2 class="page-title text-2xl font-bold text-gray-800">Riwayat Barang Masuk & PO</h2>
            <p class="page-subtitle text-gray-500">Kelola dan pantau stok barang masuk serta purchase order dari supplier</p>
        </div>
        <div class="flex gap-3">
            <a wire:navigate href="{{ route('purchases.reorder') }}" class="btn btn-warning flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                Reorder Alert
            </a>
            <a wire:navigate href="{{ route('purchases.create') }}" class="btn btn-primary flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Tambah Barang Masuk / PO
            </a>
        </div>
    </div>

    {{-- Stats Cards --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
        <div class="card p-6 bg-white border border-gray-100 rounded-2xl shadow-sm flex items-center gap-4">
            <div class="p-3 bg-emerald-50 text-emerald-600 rounded-xl">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
            </div>
            <div>
                <span class="text-xs font-semibold text-gray-400 uppercase tracking-wider block">Total Belanja Diterima</span>
                <span class="text-xl font-extrabold text-gray-800">Rp {{ number_format($totalReceived, 0, ',', '.') }}</span>
            </div>
        </div>
        <div class="card p-6 bg-white border border-gray-100 rounded-2xl shadow-sm flex items-center gap-4">
            <div class="p-3 bg-amber-50 text-amber-600 rounded-xl">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
            <div>
                <span class="text-xs font-semibold text-gray-400 uppercase tracking-wider block">Total PO Berjalan</span>
                <span class="text-xl font-extrabold text-gray-800">Rp {{ number_format($totalPending, 0, ',', '.') }}</span>
            </div>
        </div>
        <div class="card p-6 bg-white border border-gray-100 rounded-2xl shadow-sm flex items-center gap-4">
            <div class="p-3 bg-primary-50 text-primary-600 rounded-xl">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg>
            </div>
            <div>
                <span class="text-xs font-semibold text-gray-400 uppercase tracking-wider block">Grand Total PO & Belanja</span>
                <span class="text-xl font-extrabold text-primary-600">Rp {{ number_format($totalAmount, 0, ',', '.') }}</span>
            </div>
        </div>
    </div>

    {{-- Filters --}}
    <form method="GET" class="card p-4 mb-6 flex flex-wrap items-center gap-4 bg-white border border-gray-100 rounded-2xl shadow-sm">
        <div class="flex-1 min-w-[200px] relative">
            <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari No. Faktur Pembelian..." class="form-input pl-9">
        </div>
        <div class="w-40">
            <select name="status" class="form-input">
                <option value="">Semua Status</option>
                <option value="received" {{ request('status') === 'received' ? 'selected' : '' }}>Diterima (Stok+)</option>
                <option value="sent" {{ request('status') === 'sent' ? 'selected' : '' }}>Dikirim (PO)</option>
                <option value="draft" {{ request('status') === 'draft' ? 'selected' : '' }}>Draft</option>
            </select>
        </div>
        <div class="w-48">
            <select name="supplier_id" class="form-input">
                <option value="">Semua Supplier</option>
                @foreach($suppliers as $supplier)
                <option value="{{ $supplier->id }}" {{ request('supplier_id') == $supplier->id ? 'selected' : '' }}>{{ $supplier->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="flex items-center gap-2">
            <input type="date" name="start_date" value="{{ request('start_date') }}" class="form-input text-sm">
            <span class="text-xs text-gray-400">s/d</span>
            <input type="date" name="end_date" value="{{ request('end_date') }}" class="form-input text-sm">
        </div>
        <button type="submit" class="btn btn-primary btn-sm flex items-center gap-1">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2a1 1 0 01-.293.707L13 13.414V19a1 1 0 01-.553.894l-4 2A1 1 0 017 21v-7.586L3.293 6.707A1 1 0 013 6V4z"/></svg>
            Filter
        </button>
        @if(request()->hasAny(['search','supplier_id','status','start_date','end_date']))
        <a wire:navigate href="{{ route('purchases.index') }}" class="btn btn-secondary btn-sm">Reset</a>
        @endif
    </form>

    {{-- Table --}}
    <div class="card overflow-hidden bg-white border border-gray-100 rounded-2xl shadow-sm">
        <div class="overflow-x-auto">
            <table class="data-table w-full">
                <thead>
                    <tr>
                        <th class="w-12 text-center">#</th>
                        <th>No. Faktur Pembelian</th>
                        <th>Supplier</th>
                        <th>Tanggal Masuk</th>
                        <th class="text-center w-28">Status</th>
                        <th class="text-right">Total Transaksi</th>
                        <th>Petugas</th>
                        <th>Catatan</th>
                        <th class="text-center w-28">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($purchases as $i => $p)
                    <tr>
                        <td class="text-center text-gray-400 text-sm">{{ $purchases->firstItem() + $i }}</td>
                        <td class="font-mono text-sm font-semibold text-gray-700">{{ $p->reference_no }}</td>
                        <td class="font-semibold text-gray-800">{{ $p->supplier?->name ?? '-' }}</td>
                        <td class="text-gray-600">{{ $p->purchase_date->format('d M Y') }}</td>
                        <td class="text-center">
                            @if($p->status === 'received')
                            <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800 ring-1 ring-emerald-500/20">Diterima</span>
                            @elseif($p->status === 'sent')
                            <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800 ring-1 ring-amber-500/20">Dikirim (PO)</span>
                            @else
                            <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-gray-100 text-gray-800 ring-1 ring-gray-500/20">Draft PO</span>
                            @endif
                        </td>
                        <td class="text-right font-bold text-primary-600">Rp {{ number_format($p->total_amount, 0, ',', '.') }}</td>
                        <td class="text-gray-600 text-sm">{{ $p->user?->name ?? '-' }}</td>
                        <td class="text-gray-400 text-xs truncate max-w-[150px]" title="{{ $p->notes }}">{{ $p->notes ?: '-' }}</td>
                        <td class="text-center">
                            <a wire:navigate href="{{ route('purchases.show', $p->id) }}" class="btn btn-secondary btn-sm flex items-center justify-center gap-1">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                Detail
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="text-center text-gray-400 py-12">
                            <div class="flex flex-col items-center justify-center gap-2">
                                <svg class="w-12 h-12 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                                <p>Belum ada data barang masuk.</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($purchases->hasPages())
        <div class="px-6 py-4 border-t border-gray-100 bg-gray-50/50">
            {{ $purchases->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
