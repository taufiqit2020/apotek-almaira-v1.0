@extends('layouts.app')
@section('title', 'Detail Resep Dokter')
@section('page-title', 'Resep Dokter')

@section('breadcrumb')
<svg class="w-3 h-3 text-gray-400" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"/></svg>
<a wire:navigate href="{{ route('prescriptions.index') }}" class="hover:text-primary-600 transition-colors">Resep Dokter</a>
<svg class="w-3 h-3 text-gray-400" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"/></svg>
<span class="text-gray-600 font-medium">Detail</span>
@endsection

@section('content')
<div class="animate-in max-w-4xl mx-auto">
    {{-- Header --}}
    <div class="page-header mb-6">
        <div>
            <h2 class="page-title text-2xl font-bold text-gray-800">Detail Resep Dokter</h2>
            <p class="page-subtitle text-gray-500">Melihat isi resep, aturan pakai obat, dan status penyelesaian transaksi</p>
        </div>
        <div class="flex gap-3">
            @if($prescription->status === 'pending')
            <a wire:navigate href="{{ route('pos.index', ['prescription_id' => $prescription->id]) }}" 
               class="btn btn-primary bg-emerald-600 hover:bg-emerald-700 text-white font-bold py-2.5 px-4 rounded-xl shadow-md flex items-center gap-2 transition-all cursor-pointer">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                Proses ke Kasir (POS)
            </a>
            <a wire:navigate href="{{ route('prescriptions.edit', $prescription->id) }}" 
               class="btn btn-secondary text-blue-600 hover:text-blue-800 flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                Edit Resep
            </a>
            <form action="{{ route('prescriptions.destroy', $prescription->id) }}" method="POST" class="inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus resep dokter ini?')">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-secondary text-red-655 hover:text-red-800 flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                    Hapus
                </button>
            </form>
            @endif
            <a wire:navigate href="{{ route('prescriptions.index') }}" class="btn btn-secondary flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                Kembali
            </a>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Left: Prescription Meta Info --}}
        <div class="lg:col-span-1 card p-6 bg-white border border-gray-100 rounded-2xl shadow-sm space-y-5 h-fit">
            <h3 class="text-lg font-bold text-gray-800 border-b border-gray-100 pb-2">Informasi Resep</h3>
            
            <div class="space-y-4 text-sm text-gray-600">
                <div>
                    <span class="text-xs font-bold text-gray-400 uppercase tracking-wider block">Status</span>
                    <div class="mt-1">
                        @if($prescription->status === 'processed')
                        <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800 ring-1 ring-emerald-500/20">Selesai (Kasir)</span>
                        @else
                        <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-amber-100 text-amber-800 ring-1 ring-amber-500/20">Pending (Belum Diproses)</span>
                        @endif
                    </div>
                </div>

                <div>
                    <span class="text-xs font-bold text-gray-400 uppercase tracking-wider block">Nama Dokter</span>
                    <span class="font-bold text-gray-800 text-base">Dr. {{ $prescription->doctor_name }}</span>
                </div>

                @if($prescription->doctor_sip)
                <div>
                    <span class="text-xs font-bold text-gray-400 uppercase tracking-wider block">SIP Dokter</span>
                    <span class="font-medium text-gray-700 font-mono">{{ $prescription->doctor_sip }}</span>
                </div>
                @endif

                <div>
                    <span class="text-xs font-bold text-gray-400 uppercase tracking-wider block">Nama Pasien</span>
                    <span class="font-bold text-gray-850 text-base">{{ $prescription->patient_name }}</span>
                </div>

                <div>
                    <span class="text-xs font-bold text-gray-400 uppercase tracking-wider block">Tanggal Resep</span>
                    <span class="font-semibold text-gray-800">{{ $prescription->prescription_date->format('d M Y') }}</span>
                </div>
            </div>
            
            {{-- Linked Transactions --}}
            @if($prescription->sales->count() > 0)
            <div class="border-t border-gray-100 pt-4 mt-2">
                <span class="text-xs font-bold text-gray-400 uppercase tracking-wider block mb-2">Transaksi Terkait:</span>
                @foreach($prescription->sales as $sale)
                <div class="flex justify-between items-center bg-gray-50 p-2.5 rounded-lg border border-gray-100 mb-2 last:mb-0">
                    <div>
                        <span class="font-mono text-xs font-bold text-gray-700 block">{{ $sale->invoice_no }}</span>
                        <span class="text-[10px] text-gray-400 block">{{ $sale->sold_at->format('d/m/Y H:i') }}</span>
                    </div>
                    <a wire:navigate href="{{ route('sales.show', $sale->id) }}" class="btn btn-secondary py-1 px-2.5 text-[10px]">Detail</a>
                </div>
                @endforeach
            </div>
            @endif
        </div>

        {{-- Right: Items List --}}
        <div class="lg:col-span-2 card p-6 bg-white border border-gray-100 rounded-2xl shadow-sm flex flex-col">
            <h3 class="text-lg font-bold text-gray-800 mb-4 border-b border-gray-100 pb-2">Rincian Obat Resep</h3>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-sm">
                    <thead>
                        <tr class="border-b border-gray-100 text-xs font-bold text-gray-400 uppercase">
                            <th class="py-3 px-2">Nama Obat</th>
                            <th class="py-3 px-2">Dosis / Kekuatan</th>
                            <th class="py-3 px-2">Aturan Pakai (Signa)</th>
                            <th class="py-3 px-2 text-center w-24">Jumlah</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @foreach($prescription->items as $item)
                        <tr>
                            <td class="py-4 px-2">
                                <span class="font-semibold text-gray-800 block">{{ $item->product_name }}</span>
                                @if($item->product)
                                <span class="text-[10px] text-gray-400 font-mono">Kode: {{ $item->product->code }} — Stok: {{ $item->product->stock }} {{ $item->product->unit?->name }}</span>
                                @endif
                            </td>
                            <td class="py-4 px-2 text-gray-600 font-medium">{{ $item->dosage ?: '-' }}</td>
                            <td class="py-4 px-2 text-gray-600 italic font-semibold">{{ $item->signa ?: '-' }}</td>
                            <td class="py-4 px-2 text-center font-bold text-gray-800">
                                {{ $item->quantity }} {{ $item->product?->unit?->name ?: 'pcs' }}
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
