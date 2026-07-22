@extends('layouts.app')
@section('title', 'Catat Barang Keluar')
@section('page-title', 'Barang Keluar')

@section('breadcrumb')
<svg class="w-3 h-3 text-gray-400" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"/></svg>
<a wire:navigate href="{{ route('stock-outs.index') }}" class="hover:text-primary-600 transition-colors">Barang Keluar</a>
<svg class="w-3 h-3 text-gray-400" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"/></svg>
<span class="text-gray-600 font-medium">Catat</span>
@endsection

@section('content')
<script>
    window.stockOutForm = function() {
        return {
            productId: '',
            productName: '',
            currentStock: 0,
            unitName: '',
            quantity: 1,
            reason: '{{ old('reason', '') }}',
            notes: '{{ old('notes', '') }}',
        }
    }
</script>

<div class="animate-in max-w-2xl mx-auto" x-data="stockOutForm()">
    <div class="page-header mb-6">
        <div>
            <h2 class="page-title text-2xl font-bold text-gray-800">Catat Barang Keluar</h2>
            <p class="page-subtitle text-gray-500">Mencatat produk rusak, expired, retur, dll. dan mengurangi stok</p>
        </div>
        <a wire:navigate href="{{ route('stock-outs.index') }}" class="btn btn-secondary flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            Kembali
        </a>
    </div>

    @if($errors->any())
    <div class="mb-6 p-4 bg-red-50 border border-red-200 rounded-xl text-red-700 text-sm">
        <ul class="list-disc pl-5">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <div class="card p-6 bg-white border border-gray-100 rounded-2xl shadow-sm">
        <form action="{{ route('stock-outs.store') }}" method="POST">
            @csrf
            
            <div class="space-y-5">
                {{-- Product Search Input --}}
                <div class="relative" x-data="{ openSearch: false, query: '', results: [] }">
                    <label class="form-label">Pilih Produk <span class="text-red-500">*</span></label>
                    <input type="hidden" name="product_id" :value="productId" required>
                    
                    <input type="text" 
                           placeholder="Ketik nama / kode produk..." 
                           class="form-input" 
                           x-model="productName"
                           @input.debounce.300ms="
                                if(productName.length > 1) {
                                    fetch(`/products/search?q=${productName}`)
                                        .then(res => res.json())
                                        .then(data => { results = data; openSearch = true; });
                                } else {
                                    results = [];
                                    openSearch = false;
                                }
                           "
                           @click.away="openSearch = false"
                           required>
                    
                    {{-- Dropdown Search Results --}}
                    <div class="absolute left-0 right-0 mt-1 bg-white border border-gray-200 rounded-xl shadow-lg z-50 max-h-48 overflow-y-auto" 
                         x-show="openSearch && results.length > 0" x-cloak>
                        <template x-for="p in results" :key="p.id">
                            <div class="px-4 py-2.5 text-sm hover:bg-blue-50 cursor-pointer border-b border-gray-50 flex items-center justify-between"
                                 @click="
                                    productId = p.id;
                                    productName = p.name;
                                    currentStock = p.stock;
                                    unitName = p.unit || 'pcs';
                                    openSearch = false;
                                 ">
                                <div>
                                    <p class="font-bold text-gray-800" x-text="p.name"></p>
                                    <p class="text-xs text-gray-400 font-mono" x-text="p.code"></p>
                                </div>
                                <span class="text-xs bg-gray-100 text-gray-600 px-2 py-1 rounded-md font-semibold" x-text="'Stok: ' + p.stock"></span>
                            </div>
                        </template>
                    </div>

                    {{-- Current Stock Info --}}
                    <div x-show="productId" class="mt-2 text-xs flex items-center gap-1 text-gray-500 bg-gray-50 px-3 py-2 rounded-xl border border-gray-100 w-fit" x-cloak>
                        <span>Stok Sistem Saat Ini:</span>
                        <strong class="text-primary-600 font-bold" x-text="currentStock + ' ' + unitName"></strong>
                    </div>
                </div>

                {{-- Quantity --}}
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="form-label">Jumlah Barang Keluar <span class="text-red-500">*</span></label>
                        <input type="number" name="quantity" x-model.number="quantity" class="form-input" min="1" :max="currentStock" required>
                        <span class="text-[11px] text-red-500 mt-1 block" x-show="quantity > currentStock">Jumlah melebihi stok yang tersedia!</span>
                    </div>

                    <div>
                        <label class="form-label">Tanggal Keluar <span class="text-red-500">*</span></label>
                        <input type="datetime-local" name="out_date" value="{{ old('out_date', date('Y-m-d\TH:i')) }}" class="form-input" required>
                    </div>
                </div>

                {{-- Reason --}}
                <div>
                    <label class="form-label">Alasan Barang Keluar <span class="text-red-500">*</span></label>
                    <select name="reason" x-model="reason" class="form-input" required>
                        <option value="">Pilih Alasan</option>
                        <option value="expired">Kadaluarsa (Expired)</option>
                        <option value="damaged">Rusak / Cacat</option>
                        <option value="returned">Retur ke Supplier</option>
                        <option value="other">Lainnya</option>
                    </select>
                </div>

                {{-- Notes --}}
                <div>
                    <label class="form-label">Catatan / Detail Tambahan <span class="text-red-500" x-show="reason === 'other'" x-cloak>*</span></label>
                    <textarea name="notes" placeholder="Tulis catatan pendukung, contoh: rusak karena basah, dll." rows="4" class="form-input" :required="reason === 'other'" x-model="notes">{{ old('notes') }}</textarea>
                </div>

                {{-- Submit Actions --}}
                <div class="flex justify-end gap-3 border-t border-gray-100 pt-5">
                    <a wire:navigate href="{{ route('stock-outs.index') }}" class="btn btn-secondary">Batal</a>
                    <button type="submit" class="btn btn-primary" :disabled="!productId || quantity > currentStock || quantity < 1 || (reason === 'other' && !notes.trim())">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2"/></svg>
                        Simpan Barang Keluar
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>


@endsection
