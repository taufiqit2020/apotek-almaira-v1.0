@extends('layouts.app')
@section('title', 'Edit Resep Dokter')
@section('page-title', 'Resep Dokter')

@section('breadcrumb')
<svg class="w-3 h-3 text-gray-400" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"/></svg>
<a wire:navigate href="{{ route('prescriptions.index') }}" class="hover:text-primary-600 transition-colors">Resep Dokter</a>
<svg class="w-3 h-3 text-gray-400" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"/></svg>
<a wire:navigate href="{{ route('prescriptions.show', $prescription->id) }}" class="hover:text-primary-600 transition-colors">Detail</a>
<svg class="w-3 h-3 text-gray-400" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"/></svg>
<span class="text-gray-600 font-medium">Edit</span>
@endsection

@section('content')
<script>
    window.prescriptionForm = function() {
        return {
            items: @json($itemsJson),
            addItem() {
                this.items.push({
                    key: Date.now() + Math.random(),
                    product_id: '',
                    product_name: '',
                    dosage: '',
                    signa: '',
                    quantity: 1
                });
            },
            removeItem(index) {
                if (this.items.length > 1) {
                    this.items.splice(index, 1);
                }
            }
        }
    }
</script>

<div class="animate-in max-w-6xl mx-auto" x-data="prescriptionForm()">
    <div class="page-header mb-6">
        <div>
            <h2 class="page-title text-2xl font-bold text-gray-800">Edit Resep Dokter</h2>
            <p class="page-subtitle text-gray-500">Ubah berkas resep masuk, aturan pakai obat, atau jumlah obat resep</p>
        </div>
        <a wire:navigate href="{{ route('prescriptions.show', $prescription->id) }}" class="btn btn-secondary flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            Batal
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

    <form action="{{ route('prescriptions.update', $prescription->id) }}" method="POST">
        @csrf
        @method('PUT')
        
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
            {{-- Header Info Card --}}
            <div class="lg:col-span-1 card p-6 bg-white border border-gray-100 rounded-2xl shadow-sm h-fit space-y-4">
                <h3 class="text-lg font-bold text-gray-800 border-b border-gray-100 pb-2">Informasi Resep</h3>
                
                <div>
                    <label class="form-label font-bold text-gray-700">Nama Dokter <span class="text-red-500">*</span></label>
                    <input type="text" name="doctor_name" value="{{ old('doctor_name', $prescription->doctor_name) }}" class="form-input" required>
                </div>

                <div>
                    <label class="form-label font-bold text-gray-700">SIP Dokter <span class="text-xs text-gray-400">(Opsional)</span></label>
                    <input type="text" name="doctor_sip" value="{{ old('doctor_sip', $prescription->doctor_sip) }}" class="form-input">
                </div>

                <div>
                    <label class="form-label font-bold text-gray-700">Nama Pasien <span class="text-red-500">*</span></label>
                    <input type="text" name="patient_name" value="{{ old('patient_name', $prescription->patient_name) }}" class="form-input" required>
                </div>

                <div>
                    <label class="form-label font-bold text-gray-700">Tanggal Resep <span class="text-red-500">*</span></label>
                    <input type="date" name="prescription_date" value="{{ old('prescription_date', $prescription->prescription_date->format('Y-m-d')) }}" class="form-input" required>
                </div>
            </div>

            {{-- Detail Items Card --}}
            <div class="lg:col-span-2 card p-6 bg-white border border-gray-100 rounded-2xl shadow-sm flex flex-col">
                <h3 class="text-lg font-bold text-gray-800 mb-4 border-b border-gray-100 pb-2">Detail Item Obat</h3>

                <div class="overflow-x-auto flex-1 min-h-[300px]">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="border-b border-gray-100 text-xs font-bold text-gray-400 uppercase tracking-wider">
                                <th class="py-3 px-2">Cari Produk</th>
                                <th class="py-3 px-2 w-28">Dosis / Kekuatan</th>
                                <th class="py-3 px-2 w-40">Aturan Pakai (Signa)</th>
                                <th class="py-3 px-2 w-20 text-center">Jumlah</th>
                                <th class="py-3 px-2 text-center w-12"></th>
                            </tr>
                        </thead>
                        <tbody>
                            <template x-for="(item, index) in items" :key="item.key">
                                <tr class="border-b border-gray-50 align-top">
                                    {{-- Search Product Autocomplete --}}
                                    <td class="py-3 px-2 relative" x-data="{ openSearch: false, query: '', results: [] }">
                                        <input type="hidden" :name="'items['+index+'][product_id]'" :value="item.product_id">
                                        
                                        <div class="relative">
                                            <input type="text" 
                                                   placeholder="Ketik nama obat..." 
                                                   class="form-input text-xs py-2"
                                                   x-model="item.product_name"
                                                   @input.debounce.300ms="
                                                        if(item.product_name.length > 1) {
                                                            fetch(`/products/search?q=${item.product_name}`)
                                                                .then(res => res.json())
                                                                .then(data => { results = data; openSearch = true; });
                                                        } else {
                                                            results = [];
                                                            openSearch = false;
                                                        }
                                                   "
                                                   @click.away="openSearch = false"
                                                   required>
                                            
                                            <div class="absolute left-0 right-0 mt-1 bg-white border border-gray-200 rounded-xl shadow-lg z-50 max-h-48 overflow-y-auto" 
                                                 x-show="openSearch && results.length > 0" x-cloak>
                                                <template x-for="p in results" :key="p.id">
                                                    <div class="px-3 py-2 text-xs hover:bg-blue-50 cursor-pointer border-b border-gray-50 flex items-center justify-between"
                                                         @click="
                                                            item.product_id = p.id;
                                                            item.product_name = p.name;
                                                            openSearch = false;
                                                         ">
                                                        <div>
                                                            <p class="font-bold text-gray-800" x-text="p.name"></p>
                                                            <p class="text-gray-400 font-mono" x-text="p.code"></p>
                                                        </div>
                                                        <span class="text-primary-600 font-semibold" x-text="'Stok: ' + p.stock"></span>
                                                    </div>
                                                </template>
                                            </div>
                                        </div>
                                    </td>

                                    {{-- Dosage --}}
                                    <td class="py-3 px-2">
                                        <input type="text" :name="'items['+index+'][dosage]'" x-model="item.dosage" placeholder="Contoh: 500mg" class="form-input text-xs py-2">
                                    </td>

                                    {{-- Signa --}}
                                    <td class="py-3 px-2">
                                        <input type="text" :name="'items['+index+'][signa]'" x-model="item.signa" placeholder="Contoh: 3 x sehari 1 tab pc" class="form-input text-xs py-2">
                                    </td>

                                    {{-- Quantity --}}
                                    <td class="py-3 px-2">
                                        <input type="number" :name="'items['+index+'][quantity]'" x-model.number="item.quantity" class="form-input text-xs py-2 text-center" min="1" required>
                                    </td>

                                    {{-- Delete Button --}}
                                    <td class="py-3 px-2 text-center align-middle">
                                        <button type="button" @click="removeItem(index)" class="text-red-500 hover:text-red-700 transition-colors p-1" :disabled="items.length === 1">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                        </button>
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>

                {{-- Action Row --}}
                <div class="flex items-center justify-between border-t border-gray-100 pt-4 mt-4">
                    <button type="button" @click="addItem()" class="btn btn-secondary btn-sm flex items-center gap-1">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                        Tambah Baris Baru
                    </button>
                </div>

                <div class="flex justify-end gap-3 mt-6 border-t border-gray-100 pt-4">
                    <a wire:navigate href="{{ route('prescriptions.show', $prescription->id) }}" class="btn btn-secondary">Batal</a>
                    <button type="submit" class="btn btn-primary flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"/></svg>
                        Simpan Perubahan
                    </button>
                </div>
            </div>
        </div>
    </form>
</div>

@php
    $itemsJson = $prescription->items->map(function($item) {
        return [
            'key' => $item->id,
            'product_id' => $item->product_id,
            'product_name' => $item->product_name,
            'dosage' => $item->dosage,
            'signa' => $item->signa,
            'quantity' => (int)$item->quantity
        ];
    });
@endphp


@endsection
