@extends('layouts.app')
@section('title', 'Pelanggan')
@section('page-title', 'Pelanggan')

@section('breadcrumb')
<svg class="w-3 h-3 text-gray-400" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"/></svg>
<span class="text-gray-600 font-medium">Pelanggan</span>
@endsection

@section('content')
<div class="animate-in">
    {{-- Header --}}
    <div class="page-header mb-6">
        <div>
            <h2 class="page-title text-2xl font-bold text-gray-800">Master Data Pelanggan (CRM)</h2>
            <p class="page-subtitle text-gray-500">Kelola informasi pelanggan, alamat, serta pantau perolehan poin loyalitas mereka</p>
        </div>
        <a wire:navigate href="{{ route('customers.create') }}" class="btn btn-primary flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/></svg>
            Daftar Pelanggan Baru
        </a>
    </div>

    {{-- Filter Search --}}
    <form method="GET" class="card p-4 mb-6 flex items-center gap-4 bg-white border border-gray-100 rounded-2xl shadow-sm">
        <div class="flex-1 relative">
            <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama atau nomor HP pelanggan..." class="form-input pl-9">
        </div>
        <button type="submit" class="btn btn-primary btn-sm flex items-center gap-1">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2a1 1 0 01-.293.707L13 13.414V19a1 1 0 01-.553.894l-4 2A1 1 0 017 21v-7.586L3.293 6.707A1 1 0 013 6V4z"/></svg>
            Cari
        </button>
        @if(request('search'))
        <a wire:navigate href="{{ route('customers.index') }}" class="btn btn-secondary btn-sm">Reset</a>
        @endif
    </form>

    {{-- Table --}}
    <div class="card overflow-hidden bg-white border border-gray-100 rounded-2xl shadow-sm">
        <div class="overflow-x-auto">
            <table class="data-table w-full">
                <thead>
                    <tr>
                        <th class="w-12 text-center">#</th>
                        <th>Nama Pelanggan</th>
                        <th>No. HP / WhatsApp</th>
                        <th>Alamat</th>
                        <th>Tanggal Lahir</th>
                        <th class="text-center w-28">Poin Loyalitas</th>
                        <th class="text-center w-24">Status</th>
                        <th class="text-center w-36">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($customers as $i => $c)
                    <tr class="hover:bg-gray-50/50">
                        <td class="text-center text-gray-400 text-sm">{{ $customers->firstItem() + $i }}</td>
                        <td class="font-semibold text-gray-800 text-sm">{{ $c->name }}</td>
                        <td class="font-semibold text-gray-600 text-sm">{{ $c->phone }}</td>
                        <td class="text-gray-500 text-xs truncate max-w-[200px]" title="{{ $c->address }}">{{ $c->address ?: '-' }}</td>
                        <td class="text-gray-500 text-xs">{{ $c->dob ? $c->dob->format('d M Y') : '-' }}</td>
                        <td class="text-center font-bold text-emerald-600 text-sm">
                            <span class="bg-emerald-50 px-2.5 py-1 rounded-full border border-emerald-100">{{ number_format($c->points) }} Pts</span>
                        </td>
                        <td class="text-center">
                            @if($c->is_active)
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800">Aktif</span>
                            @else
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-red-100 text-red-800">Nonaktif</span>
                            @endif
                        </td>
                        <td class="text-center flex justify-center gap-1.5 py-3">
                            <a wire:navigate href="{{ route('customers.show', $c->id) }}" class="btn btn-secondary btn-sm py-1 px-2.5 flex items-center gap-1">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                Detail
                            </a>
                            <a wire:navigate href="{{ route('customers.edit', $c->id) }}" class="btn btn-secondary btn-sm py-1 px-2.5 flex items-center gap-1 text-amber-600 hover:text-amber-800 border-amber-200">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                Edit
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center text-gray-400 py-12">
                            <div class="flex flex-col items-center justify-center gap-2">
                                <svg class="w-12 h-12 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                                <p>Belum ada data pelanggan terdaftar.</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($customers->hasPages())
        <div class="px-6 py-4 border-t border-gray-100 bg-gray-50/50">
            {{ $customers->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
