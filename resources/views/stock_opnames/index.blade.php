@extends('layouts.app')
@section('title', 'Stok Opname')
@section('page-title', 'Stok Opname')

@section('breadcrumb')
<svg class="w-3 h-3 text-gray-400" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"/></svg>
<span class="text-gray-600 font-medium">Stok Opname</span>
@endsection

@section('content')
<div class="animate-in">
    {{-- Header --}}
    <div class="page-header mb-6">
        <div>
            <h2 class="page-title text-2xl font-bold text-gray-800">Riwayat Stok Opname (Penyesuaian)</h2>
            <p class="page-subtitle text-gray-500">Mencatat riwayat audit stok fisik vs stok sistem untuk menjaga keakuratan inventori</p>
        </div>
        <a wire:navigate href="{{ route('stock-opnames.create') }}" class="btn btn-primary flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Mulai Audit Stok Opname
        </a>
    </div>

    {{-- Filters --}}
    <form method="GET" class="card p-4 mb-6 flex flex-wrap items-center gap-4 bg-white border border-gray-100 rounded-2xl shadow-sm">
        <div class="flex-1 min-w-[200px] relative">
            <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama, kode, atau indikasi/fungsi..." class="form-input pl-9">
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
        @if(request()->hasAny(['search','start_date','end_date']))
        <a wire:navigate href="{{ route('stock-opnames.index') }}" class="btn btn-secondary btn-sm">Reset</a>
        @endif
    </form>

    {{-- Table --}}
    <div class="card overflow-hidden bg-white border border-gray-100 rounded-2xl shadow-sm">
        <div class="overflow-x-auto">
            <table class="data-table w-full">
                <thead>
                    <tr>
                        <th class="w-12 text-center">#</th>
                        <th>Tanggal Audit</th>
                        <th>Kode Produk</th>
                        <th>Nama Produk</th>
                        <th class="text-center w-28">Stok Sistem</th>
                        <th class="text-center w-28">Stok Fisik</th>
                        <th class="text-center w-28">Selisih</th>
                        <th>Petugas</th>
                        <th>Keterangan</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($stockOpnames as $i => $s)
                    <tr>
                        <td class="text-center text-gray-400 text-sm">{{ $stockOpnames->firstItem() + $i }}</td>
                        <td class="text-gray-600">{{ $s->created_at->format('d M Y H:i') }}</td>
                        <td class="font-mono text-xs font-semibold text-gray-500">{{ $s->product?->code ?: '-' }}</td>
                        <td class="font-semibold text-gray-800">{{ $s->product?->name ?? 'Produk Dihapus' }}</td>
                        <td class="text-center text-gray-700 font-semibold">{{ $s->system_stock }}</td>
                        <td class="text-center text-gray-700 font-bold">{{ $s->physical_stock }}</td>
                        <td class="text-center">
                            @if($s->difference > 0)
                            <span class="text-green-600 font-bold">+{{ $s->difference }}</span>
                            @elseif($s->difference < 0)
                            <span class="text-red-600 font-bold">{{ $s->difference }}</span>
                            @else
                            <span class="text-gray-400 font-semibold">0</span>
                            @endif
                        </td>
                        <td class="text-gray-600 text-sm">{{ $s->user?->name ?? '-' }}</td>
                        <td class="text-gray-500 text-xs truncate max-w-[200px]" title="{{ $s->notes }}">{{ $s->notes ?: '-' }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="text-center text-gray-400 py-12">
                            <div class="flex flex-col items-center justify-center gap-2">
                                <svg class="w-12 h-12 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2"/></svg>
                                <p>Belum ada data stok opname.</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($stockOpnames->hasPages())
        <div class="px-6 py-4 border-t border-gray-100 bg-gray-50/50">
            {{ $stockOpnames->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
