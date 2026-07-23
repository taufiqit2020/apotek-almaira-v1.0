@extends('layouts.app')

@section('title', 'Penjualan')

@section('breadcrumb')
    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
    </svg>
    <span class="text-gray-600 font-medium">Riwayat Penjualan</span>
@endsection

@section('content')
<div class="flex flex-col gap-6" x-data="{ isCancelModalOpen: false, cancelAction: '', invoiceNo: '', cancelReason: '' }">
    
    {{-- Header & Stats Summary --}}
    <div class="page-header flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
        <div>
            <h1 class="page-title text-gradient text-2xl font-bold">Riwayat Penjualan</h1>
            <p class="page-subtitle text-gray-500">Daftar seluruh laporan transaksi penjualan obat dan resep.</p>
        </div>
        <div class="flex items-center gap-3">
            @if(auth()->user()->canAccessPos())
            <a wire:navigate href="{{ route('pos.index') }}" class="btn btn-primary flex items-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Kasir Baru (POS)
            </a>
            @endif
        </div>
    </div>


    {{-- ✅ Livewire Component: stats + filter + tabel + pagination live tanpa reload --}}
    <livewire:sales.sale-table />

    {{-- Cancel Confirmation Modal --}}
    <div class="fixed inset-0 bg-black/50 backdrop-blur-sm z-50 flex items-center justify-center p-4 animate-fade-in" x-show="isCancelModalOpen" x-cloak>
        <div class="bg-white rounded-2xl max-w-md w-full p-6 shadow-xl border border-gray-100 animate-in" @click.away="isCancelModalOpen = false">
            <h3 class="text-lg font-bold text-gray-800 mb-2">Batalkan Transaksi</h3>
            <p class="text-sm text-gray-500 mb-4">Apakah Anda yakin ingin membatalkan invoice <strong class="text-red-700" x-text="invoiceNo"></strong>? Tindakan ini akan mengembalikan stok obat ke sistem.</p>

            <form :action="cancelAction" method="POST">
                @csrf
                <div class="mb-4">
                    <label class="form-label text-xs font-semibold text-gray-600 block mb-1">Alasan Pembatalan <span class="text-red-500">*</span></label>
                    <textarea 
                        name="cancel_reason" 
                        x-model="cancelReason" 
                        placeholder="Contoh: Salah input barang, pelanggan membatalkan pembelian, dll." 
                        rows="3" 
                        class="form-input text-sm"
                        required
                    ></textarea>
                </div>

                <div class="flex gap-3 justify-end border-t border-gray-100 pt-4">
                    <button type="button" @click="isCancelModalOpen = false" class="btn btn-secondary">Kembali</button>
                    <button type="submit" class="btn btn-danger bg-red-600 hover:bg-red-700 text-white" :disabled="!cancelReason.trim()">
                        Ya, Batalkan Transaksi
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
