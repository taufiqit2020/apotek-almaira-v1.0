@extends('layouts.app')
@section('title', 'Daftar Reorder (Stok Menipis)')
@section('page-title', 'Daftar Reorder')

@section('breadcrumb')
<svg class="w-3 h-3 text-gray-400" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"/></svg>
<a wire:navigate href="{{ route('purchases.index') }}" class="hover:text-primary-600 transition-colors">Barang Masuk</a>
<svg class="w-3 h-3 text-gray-400" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"/></svg>
<span class="text-gray-600 font-medium">Reorder Alert</span>
@endsection

@section('content')
<script>
    window.reorderList = function() {
        return {
            selectedProducts: [],
            toggleSelectAll(checked) {
                if (checked) {
                    this.selectedProducts = [@foreach($products as $p) '{{ $p->id }}', @endforeach];
                } else {
                    this.selectedProducts = [];
                }
            },
            createBulkPO() {
                if (this.selectedProducts.length > 0) {
                    const ids = this.selectedProducts.join(',');
                    window.location.href = `{{ route('purchases.create') }}?reorder_products=${ids}`;
                }
            }
        }
    }
</script>

<div class="animate-in" x-data="reorderList()">
    <div class="page-header mb-6 flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h2 class="page-title text-2xl font-bold text-gray-800">Daftar Reorder Alert</h2>
            <p class="page-subtitle text-gray-500">Daftar produk dengan jumlah stok di bawah batas minimum. Buat Purchase Order (PO) langsung secara massal.</p>
        </div>
        
        <div class="flex gap-3">
            <button type="button" @click="createBulkPO()" 
                    class="btn btn-primary flex items-center gap-2"
                    :disabled="selectedProducts.length === 0">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/></svg>
                Buat PO Massal (<span x-text="selectedProducts.length"></span>)
            </button>
        </div>
    </div>

    <div class="card p-6 bg-white border border-gray-100 rounded-2xl shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="border-b border-gray-100 text-xs font-bold text-gray-400 uppercase tracking-wider bg-gray-50/50">
                        <th class="py-3 px-4 w-12 text-center">
                            <input type="checkbox" @change="toggleSelectAll($el.checked)" class="rounded border-gray-300 text-emerald-600 focus:ring-emerald-500 w-4 h-4 cursor-pointer">
                        </th>
                        <th class="py-3 px-4">Nama Produk</th>
                        <th class="py-3 px-4">Kode Produk</th>
                        <th class="py-3 px-4">Supplier</th>
                        <th class="py-3 px-4 text-center">Stok Saat Ini</th>
                        <th class="py-3 px-4 text-center">Batas Min</th>
                        <th class="py-3 px-4 text-center">Saran Reorder Qty</th>
                        <th class="py-3 px-4 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse($products as $p)
                    <tr class="hover:bg-gray-50/50 transition-colors">
                        <td class="py-3 px-4 text-center">
                            <input type="checkbox" value="{{ $p->id }}" 
                                   x-model="selectedProducts" 
                                   class="rounded border-gray-300 text-emerald-600 focus:ring-emerald-500 w-4 h-4 cursor-pointer">
                        </td>
                        <td class="py-3 px-4">
                            <span class="font-semibold text-gray-800 text-sm">{{ $p->name }}</span>
                        </td>
                        <td class="py-3 px-4">
                            <span class="font-mono text-xs text-gray-500 bg-gray-100 px-2 py-1 rounded">{{ $p->code }}</span>
                        </td>
                        <td class="py-3 px-4">
                            <span class="text-xs text-gray-600">{{ $p->supplier ? $p->supplier->name : 'Tidak ada supplier' }}</span>
                        </td>
                        <td class="py-3 px-4 text-center">
                            <span class="px-2 py-1 rounded-full text-xs font-bold bg-red-100 text-red-700">
                                {{ $p->stock }} {{ $p->unit ? $p->unit->name : 'pcs' }}
                            </span>
                        </td>
                        <td class="py-3 px-4 text-center">
                            <span class="text-xs text-gray-500 font-medium">{{ $p->stock_min }}</span>
                        </td>
                        <td class="py-3 px-4 text-center">
                            <span class="text-xs text-emerald-700 font-bold bg-emerald-50 px-2 py-1 rounded">
                                {{ max(1, ($p->stock_min * 2) - $p->stock) }} {{ $p->unit ? $p->unit->name : 'pcs' }}
                            </span>
                        </td>
                        <td class="py-3 px-4 text-right">
                            <a wire:navigate href="{{ route('purchases.create', ['reorder_products' => $p->id]) }}" 
                               class="btn btn-secondary py-1 px-3 text-xs flex items-center gap-1 w-fit ml-auto">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                Buat PO
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="py-12 text-center">
                            <div class="flex flex-col items-center justify-center text-gray-400">
                                <svg class="w-12 h-12 mb-3 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                <p class="text-sm font-semibold">Semua stok produk aman!</p>
                                <p class="text-xs text-gray-400 mt-1">Tidak ada produk dengan stok di bawah batas minimum.</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>


@endsection
