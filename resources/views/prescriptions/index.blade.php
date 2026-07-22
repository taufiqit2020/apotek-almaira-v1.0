@extends('layouts.app')
@section('title', 'Resep')
@section('page-title', 'Resep Dokter')

@section('breadcrumb')
<svg class="w-3 h-3 text-gray-400" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"/></svg>
<span class="text-gray-600 font-medium">Resep Dokter</span>
@endsection

@section('content')
<div class="animate-in">
    {{-- Header --}}
    <div class="page-header mb-6">
        <div>
            <h2 class="page-title text-2xl font-bold text-gray-800">Manajemen Resep Dokter</h2>
            <p class="page-subtitle text-gray-500">Kelola berkas resep masuk dan konversikan langsung ke kasir penjualan POS</p>
        </div>
        <a wire:navigate href="{{ route('prescriptions.create') }}" class="btn btn-primary flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
            Catat Resep Baru
        </a>
    </div>

    {{-- Filter Search --}}
    <form method="GET" class="card p-4 mb-6 flex flex-wrap items-center gap-4 bg-white border border-gray-100 rounded-2xl shadow-sm">
        <div class="flex-1 min-w-[200px] relative">
            <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama pasien atau dokter..." class="form-input pl-9">
        </div>
        <div class="w-40">
            <select name="status" class="form-input">
                <option value="">Semua Status</option>
                <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending (Belum Diproses)</option>
                <option value="processed" {{ request('status') === 'processed' ? 'selected' : '' }}>Processed (Selesai)</option>
            </select>
        </div>
        <button type="submit" class="btn btn-primary btn-sm flex items-center gap-1">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2a1 1 0 01-.293.707L13 13.414V19a1 1 0 01-.553.894l-4 2A1 1 0 017 21v-7.586L3.293 6.707A1 1 0 013 6V4z"/></svg>
            Filter
        </button>
        @if(request()->hasAny(['search', 'status']))
        <a wire:navigate href="{{ route('prescriptions.index') }}" class="btn btn-secondary btn-sm">Reset</a>
        @endif
    </form>

    {{-- Table --}}
    <div class="card overflow-hidden bg-white border border-gray-100 rounded-2xl shadow-sm">
        <div class="overflow-x-auto">
            <table class="data-table w-full">
                <thead>
                    <tr>
                        <th class="w-12 text-center">#</th>
                        <th>Tanggal Resep</th>
                        <th>Nama Dokter</th>
                        <th>No. SIP Dokter</th>
                        <th>Nama Pasien</th>
                        <th class="text-center">Jumlah Item</th>
                        <th class="text-center w-28">Status</th>
                        <th class="text-center w-28">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($prescriptions as $i => $pr)
                    <tr class="hover:bg-gray-50/50">
                        <td class="text-center text-gray-400 text-sm">{{ $prescriptions->firstItem() + $i }}</td>
                        <td class="font-semibold text-gray-700">{{ $pr->prescription_date->format('d M Y') }}</td>
                        <td class="font-bold text-gray-800 text-sm">Dr. {{ $pr->doctor_name }}</td>
                        <td class="text-gray-500 font-mono text-xs">{{ $pr->doctor_sip ?: '-' }}</td>
                        <td class="font-semibold text-gray-800 text-sm">{{ $pr->patient_name }}</td>
                        <td class="text-center text-gray-600 font-semibold text-sm">{{ $pr->items->count() }} item</td>
                        <td class="text-center">
                            @if($pr->status === 'processed')
                            <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800 ring-1 ring-emerald-500/20">Selesai (Kasir)</span>
                            @else
                            <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800 ring-1 ring-amber-500/20">Pending</span>
                            @endif
                        </td>
                        <td class="text-center">
                            <div class="flex items-center justify-center gap-1.5">
                                <a wire:navigate href="{{ route('prescriptions.show', $pr->id) }}" class="btn btn-secondary btn-sm p-1.5" title="Detail">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                </a>
                                @if($pr->status === 'pending')
                                <a wire:navigate href="{{ route('prescriptions.edit', $pr->id) }}" class="btn btn-secondary btn-sm p-1.5 text-blue-600 hover:text-blue-800" title="Edit">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                </a>
                                <form action="{{ route('prescriptions.destroy', $pr->id) }}" method="POST" class="inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus resep dokter ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-secondary btn-sm p-1.5 text-red-650 hover:text-red-800" title="Hapus">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    </button>
                                </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center text-gray-400 py-12">
                            <div class="flex flex-col items-center justify-center gap-2">
                                <svg class="w-12 h-12 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                <p>Belum ada data resep dokter.</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($prescriptions->hasPages())
        <div class="px-6 py-4 border-t border-gray-100 bg-gray-50/50">
            {{ $prescriptions->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
