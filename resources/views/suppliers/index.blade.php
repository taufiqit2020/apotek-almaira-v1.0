@extends('layouts.app')
@section('title', 'Supplier')
@section('page-title', 'Manajemen Supplier')

@section('breadcrumb')
<svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"/></svg>
<span class="text-gray-600 font-medium">Supplier</span>
@endsection

@section('content')
<div class="animate-in">
    <div class="page-header">
        <div>
            <h2 class="page-title">Data Supplier</h2>
            <p class="page-subtitle">Kelola supplier / distributor produk apotek</p>
        </div>
        <a wire:navigate href="{{ route('suppliers.create') }}" class="btn btn-primary">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Tambah Supplier
        </a>
    </div>

    <div class="card overflow-hidden">
        <div class="overflow-x-auto">
            <table class="data-table">
                <thead>
                    <tr>
                        <th class="w-12">#</th>
                        <th>Nama Supplier</th>
                        <th>Contact Person</th>
                        <th>Telepon</th>
                        <th>Email</th>
                        <th class="text-center">Produk</th>
                        <th class="text-center">Status</th>
                        <th class="text-center w-24">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($suppliers as $i => $s)
                    <tr>
                        <td class="text-gray-400">{{ $suppliers->firstItem() + $i }}</td>
                        <td>
                            <p class="font-semibold text-gray-800">{{ $s->name }}</p>
                            @if($s->address)
                            <p class="text-xs text-gray-400 truncate max-w-[200px]">{{ $s->address }}</p>
                            @endif
                        </td>
                        <td class="text-gray-600">{{ $s->contact_person ?? '—' }}</td>
                        <td class="text-gray-600">{{ $s->phone ?? '—' }}</td>
                        <td class="text-gray-600 text-sm">{{ $s->email ?? '—' }}</td>
                        <td class="text-center">
                            <span class="badge badge-info">{{ $s->products_count }}</span>
                        </td>
                        <td class="text-center">
                            @if($s->is_active)
                            <span class="badge badge-success">Aktif</span>
                            @else
                            <span class="badge badge-gray">Nonaktif</span>
                            @endif
                        </td>
                        <td>
                            <div class="flex items-center justify-center gap-1.5">
                                <a wire:navigate href="{{ route('suppliers.edit', $s) }}" class="btn btn-icon btn-sm btn-secondary" title="Edit">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                </a>
                                <form id="del-s-{{ $s->id }}" method="POST" action="{{ route('suppliers.destroy', $s) }}">
                                    @csrf @method('DELETE')
                                </form>
                                <button type="button" @click="confirm('del-s-{{ $s->id }}')" class="btn btn-icon btn-sm bg-red-50 text-red-500 hover:bg-red-100">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center py-12 text-gray-400">Belum ada supplier</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($suppliers->hasPages())
        <div class="px-5 py-4 border-t border-gray-100">
            {{ $suppliers->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
