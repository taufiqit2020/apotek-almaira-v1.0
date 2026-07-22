@extends('layouts.app')
@section('title', 'Audit Stok Opname')
@section('page-title', 'Stok Opname')

@section('breadcrumb')
<svg class="w-3 h-3 text-gray-400" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"/></svg>
<a wire:navigate href="{{ route('stock-opnames.index') }}" class="hover:text-primary-600 transition-colors">Stok Opname</a>
<svg class="w-3 h-3 text-gray-400" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"/></svg>
<span class="text-gray-600 font-medium">Audit Massal</span>
@endsection

@section('content')
<script>
    window.stockOpnameBatchForm = function() {
        return {
            items: [],
            categoryId: '',
            isLoading: false,
            isConfirmModalOpen: false,

            init() {
                // Initialize clean
            },

            loadCategoryProducts() {
                if (!this.categoryId) return;
                this.isLoading = true;
                
                fetch(`/products/search?category_id=${this.categoryId}`)
                    .then(res => res.json())
                    .then(data => {
                        data.forEach(p => {
                            if (!this.items.some(item => item.product_id === p.id)) {
                                this.items.push({
                                    product_id: p.id,
                                    name: p.name,
                                    code: p.code,
                                    barcode: p.barcode,
                                    system_stock: p.stock,
                                    physical_stock: p.stock, // default is system stock
                                    notes: '',
                                    unit: p.unit || 'pcs'
                                });
                            }
                        });
                        this.isLoading = false;
                    })
                    .catch(err => {
                        console.error('Error fetching products:', err);
                        this.isLoading = false;
                    });
            },

            addProduct(p) {
                if (!this.items.some(item => item.product_id === p.id)) {
                    this.items.push({
                        product_id: p.id,
                        name: p.name,
                        code: p.code,
                        barcode: p.barcode,
                        system_stock: p.stock,
                        physical_stock: p.stock,
                        notes: '',
                        unit: p.unit || 'pcs'
                    });
                }
            },

            removeItem(index) {
                this.items.splice(index, 1);
            },

            clearAll() {
                if (confirm('Apakah Anda yakin ingin mengosongkan sesi audit ini?')) {
                    this.items = [];
                }
            },

            getDifference(item) {
                return (item.physical_stock || 0) - item.system_stock;
            },

            getDiffText(item) {
                const diff = this.getDifference(item);
                return (diff > 0 ? '+' : '') + diff;
            },

            getDiffColor(item) {
                const diff = this.getDifference(item);
                if (diff > 0) return 'text-green-600';
                if (diff < 0) return 'text-red-600';
                return 'text-gray-400';
            },

            countMatch() {
                return this.items.filter(item => this.getDifference(item) === 0).length;
            },

            countSurplus() {
                return this.items.filter(item => this.getDifference(item) > 0).length;
            },

            countDeficit() {
                return this.items.filter(item => this.getDifference(item) < 0).length;
            },

            openConfirmModal() {
                this.isConfirmModalOpen = true;
            },

            submitOpname() {
                this.isConfirmModalOpen = false;
                // Wait briefly for modal close animation, then submit form
                this.$nextTick(() => {
                    document.getElementById('opnameForm').submit();
                });
            }
        }
    }
</script>

<div class="animate-in max-w-6xl mx-auto" x-data="stockOpnameBatchForm()" x-init="init()">
    <div class="page-header mb-6 flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
        <div>
            <h2 class="page-title text-2xl font-bold text-gray-800">Audit Stok Opname (Batch)</h2>
            <p class="page-subtitle text-gray-500">Sesuaikan stok produk sistem dengan stok fisik aktual secara massal</p>
        </div>
        <a wire:navigate href="{{ route('stock-opnames.index') }}" class="btn btn-secondary flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            Kembali ke Riwayat
        </a>
    </div>

    @if($errors->any())
    <div class="mb-6 p-4 bg-red-50 border border-red-200 rounded-xl text-red-700 text-sm">
        <p class="font-bold mb-1">Terjadi kesalahan validasi:</p>
        <ul class="list-disc pl-5">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    {{-- Control Panel --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
        {{-- Search & Load Box --}}
        <div class="card p-5 bg-white border border-gray-100 rounded-2xl shadow-sm lg:col-span-1 space-y-4">
            <h3 class="text-sm font-bold text-gray-700 uppercase tracking-wider">Cari & Tambah Produk</h3>
            
            {{-- Category Filter --}}
            <div>
                <label class="form-label text-xs">Filter Kategori</label>
                <select x-model="categoryId" @change="loadCategoryProducts()" class="form-input text-sm">
                    <option value="">-- Pilih Kategori --</option>
                    @foreach($categories as $category)
                        <option value="{{ $category->id }}">{{ $category->name }}</option>
                    @endforeach
                </select>
            </div>

            {{-- Product Search Autocomplete --}}
            <div class="relative" x-data="{ openSearch: false, query: '', results: [] }">
                <label class="form-label text-xs">Cari Nama / Kode Produk</label>
                <input type="text" 
                       placeholder="Ketik nama atau barcode..." 
                       class="form-input text-sm" 
                       x-model="query"
                       @input.debounce.300ms="
                            if(query.length > 1) {
                                fetch(`/products/search?q=${query}&category_id=${categoryId}`)
                                    .then(res => res.json())
                                    .then(data => { results = data; openSearch = true; });
                            } else {
                                results = [];
                                openSearch = false;
                            }
                       "
                       @click.away="openSearch = false">
                
                {{-- Dropdown --}}
                <div class="absolute left-0 right-0 mt-1 bg-white border border-gray-200 rounded-xl shadow-lg z-50 max-h-48 overflow-y-auto" 
                     x-show="openSearch && results.length > 0" x-cloak>
                    <template x-for="p in results" :key="p.id">
                        <div class="px-3 py-2 text-xs hover:bg-blue-50 cursor-pointer border-b border-gray-50 flex items-center justify-between"
                             @click="
                                addProduct(p);
                                query = '';
                                results = [];
                                openSearch = false;
                             ">
                            <div>
                                <p class="font-bold text-gray-800" x-text="p.name"></p>
                                <p class="text-[10px] text-gray-400 font-mono" x-text="p.code"></p>
                            </div>
                            <span class="text-[10px] bg-gray-100 text-gray-600 px-1.5 py-0.5 rounded font-semibold" x-text="'Stok: ' + p.stock"></span>
                        </div>
                    </template>
                </div>
            </div>

            <div class="pt-2">
                <button type="button" @click="clearAll()" class="btn btn-secondary w-full text-xs py-2 flex items-center justify-center gap-1.5" :disabled="items.length === 0">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                    Kosongkan Sesi Audit
                </button>
            </div>
        </div>

        {{-- Summary Card --}}
        <div class="card p-5 bg-gradient-to-br from-emerald-50 to-teal-50 border border-emerald-100 rounded-2xl shadow-sm lg:col-span-2 flex flex-col justify-between">
            <div>
                <h3 class="text-sm font-bold text-emerald-800 uppercase tracking-wider mb-3">Ringkasan Sesi Opname</h3>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                    <div class="bg-white/80 p-3 rounded-xl border border-emerald-100">
                        <span class="text-xs text-gray-500 block">Total Item Di-audit</span>
                        <span class="text-xl font-bold text-emerald-700" x-text="items.length"></span>
                    </div>
                    <div class="bg-white/80 p-3 rounded-xl border border-emerald-100">
                        <span class="text-xs text-gray-500 block">Selisih Cocok (0)</span>
                        <span class="text-xl font-bold text-gray-700" x-text="countMatch()"></span>
                    </div>
                    <div class="bg-white/80 p-3 rounded-xl border border-emerald-100">
                        <span class="text-xs text-gray-500 block">Kelebihan (+)</span>
                        <span class="text-xl font-bold text-green-600" x-text="countSurplus()"></span>
                    </div>
                    <div class="bg-white/80 p-3 rounded-xl border border-emerald-100">
                        <span class="text-xs text-gray-500 block">Kekurangan (-)</span>
                        <span class="text-xl font-bold text-red-600" x-text="countDeficit()"></span>
                    </div>
                </div>
            </div>
            <div class="pt-4 flex justify-end gap-3">
                <button type="button" @click="openConfirmModal()" class="btn btn-primary w-full md:w-auto" :disabled="items.length === 0">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    Finalisasi Opname (Simpan)
                </button>
            </div>
        </div>
    </div>

    {{-- Audit Grid List --}}
    <div class="card bg-white border border-gray-100 rounded-2xl shadow-sm overflow-hidden mb-6">
        <div class="px-5 py-4 border-b border-gray-100 bg-gray-50/50 flex justify-between items-center">
            <h3 class="font-bold text-gray-700">Daftar Produk yang Di-audit</h3>
            <span class="text-xs text-gray-400" x-text="items.length + ' produk terpilih'"></span>
        </div>
        
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-gray-50 text-gray-600 text-xs font-semibold uppercase border-b border-gray-100">
                        <th class="px-5 py-3 w-16">No</th>
                        <th class="px-5 py-3">Produk</th>
                        <th class="px-5 py-3 text-center w-32">Stok Sistem</th>
                        <th class="px-5 py-3 text-center w-36">Stok Fisik</th>
                        <th class="px-5 py-3 text-center w-28">Selisih</th>
                        <th class="px-5 py-3 w-64">Catatan Penyesuaian</th>
                        <th class="px-5 py-3 text-center w-20">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 text-sm">
                    <template x-for="(item, index) in items" :key="item.product_id">
                        <tr class="hover:bg-gray-50/50 transition-colors">
                            <td class="px-5 py-3.5 text-gray-400 font-mono" x-text="index + 1"></td>
                            <td class="px-5 py-3.5">
                                <p class="font-bold text-gray-800" x-text="item.name"></p>
                                <span class="text-[10px] text-gray-400 font-mono" x-text="item.code"></span>
                            </td>
                            <td class="px-5 py-3.5 text-center font-semibold text-gray-600">
                                <span x-text="item.system_stock"></span>
                                <span class="text-[10px] text-gray-400 font-normal block" x-text="item.unit"></span>
                            </td>
                            <td class="px-5 py-3.5 text-center">
                                <input type="number" 
                                       x-model.number="item.physical_stock" 
                                       class="form-input text-center py-1 px-2 text-sm font-bold w-24 mx-auto border-gray-200 focus:border-primary-400" 
                                       min="0">
                            </td>
                            <td class="px-5 py-3.5 text-center font-extrabold">
                                <span :class="getDiffColor(item)" x-text="getDiffText(item)"></span>
                            </td>
                            <td class="px-5 py-3.5">
                                <input type="text" 
                                       x-model="item.notes" 
                                       placeholder="Contoh: Selisih Expired, Hilang, dll." 
                                       class="form-input py-1 px-2 text-xs border-gray-100 focus:border-primary-300">
                            </td>
                            <td class="px-5 py-3.5 text-center">
                                <button type="button" @click="removeItem(index)" class="text-red-500 hover:text-red-700 p-1.5 rounded-lg hover:bg-red-50 transition-colors">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                </button>
                            </td>
                        </tr>
                    </template>
                    <tr x-show="items.length === 0">
                        <td colspan="7" class="px-5 py-12 text-center text-gray-400">
                            <div class="flex flex-col items-center justify-center gap-2">
                                <svg class="w-10 h-10 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>
                                <p class="text-sm font-medium">Belum ada produk yang dimasukkan untuk di-audit</p>
                                <p class="text-xs text-gray-400">Gunakan filter kategori atau search produk di panel kiri</p>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    {{-- Form Submit --}}
    <form id="opnameForm" action="{{ route('stock-opnames.store') }}" method="POST" class="hidden">
        @csrf
        <template x-for="(item, index) in items" :key="item.product_id">
            <div>
                <input type="hidden" :name="'items['+index+'][product_id]'" :value="item.product_id">
                <input type="hidden" :name="'items['+index+'][physical_stock]'" :value="item.physical_stock">
                <input type="hidden" :name="'items['+index+'][notes]'" :value="item.notes">
            </div>
        </template>
    </form>

    {{-- Confirmation Modal --}}
    <div class="fixed inset-0 bg-black/50 backdrop-blur-sm z-50 flex items-center justify-center p-4" x-show="isConfirmModalOpen" x-cloak>
        <div class="bg-white rounded-2xl max-w-lg w-full p-6 shadow-xl border border-gray-100 animate-in" @click.away="isConfirmModalOpen = false">
            <h3 class="text-lg font-bold text-gray-800 mb-2">Konfirmasi Finalisasi Stok Opname</h3>
            <p class="text-sm text-gray-500 mb-4">Harap tinjau ringkasan audit berikut sebelum menyesuaikan stok di database:</p>

            <div class="space-y-3 mb-6">
                <div class="p-3 bg-gray-50 rounded-xl flex justify-between items-center text-sm">
                    <span class="text-gray-600">Total Produk yang Di-audit</span>
                    <strong class="text-gray-800" x-text="items.length + ' item'"></strong>
                </div>
                <div class="p-3 bg-red-50 rounded-xl flex justify-between items-center text-sm">
                    <span class="text-red-700">Produk Kurang (Stok Hilang/Rusak)</span>
                    <strong class="text-red-800" x-text="countDeficit() + ' item'"></strong>
                </div>
                <div class="p-3 bg-green-50 rounded-xl flex justify-between items-center text-sm">
                    <span class="text-green-700">Produk Lebih (Stok Berlebih)</span>
                    <strong class="text-green-800" x-text="countSurplus() + ' item'"></strong>
                </div>
            </div>

            <div class="flex gap-3 justify-end">
                <button type="button" @click="isConfirmModalOpen = false" class="btn btn-secondary">Kembali</button>
                <button type="button" @click="submitOpname()" class="btn btn-primary">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    Ya, Simpan ke Database
                </button>
            </div>
        </div>
    </div>
</div>


@endsection
