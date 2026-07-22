@extends('layouts.app')
@section('title', 'Profil Pelanggan')
@section('page-title', 'Pelanggan')

@section('breadcrumb')
<svg class="w-3 h-3 text-gray-400" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"/></svg>
<a wire:navigate href="{{ route('customers.index') }}" class="hover:text-primary-600 transition-colors">Pelanggan</a>
<svg class="w-3 h-3 text-gray-400" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"/></svg>
<span class="text-gray-600 font-medium">Profil</span>
@endsection

@section('content')
<div class="animate-in max-w-5xl mx-auto">
    {{-- Header --}}
    <div class="page-header mb-6">
        <div>
            <h2 class="page-title text-2xl font-bold text-gray-800">Profil Pelanggan</h2>
            <p class="page-subtitle text-gray-500">Melihat statistik belanja, poin loyalitas, dan riwayat transaksi pelanggan</p>
        </div>
        <div class="flex gap-3">
            <a wire:navigate href="{{ route('customers.edit', $customer) }}" class="btn btn-warning flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                Edit Profil
            </a>
            <a wire:navigate href="{{ route('customers.index') }}" class="btn btn-secondary flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                Kembali
            </a>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Profile Card --}}
        <div class="lg:col-span-1 space-y-6">
            <div class="card p-6 bg-white border border-gray-100 rounded-2xl shadow-sm">
                <div class="flex flex-col items-center text-center pb-6 border-b border-gray-100">
                    <div class="w-20 h-20 bg-emerald-50 text-emerald-600 rounded-full flex items-center justify-center font-bold text-2xl mb-4 ring-4 ring-emerald-50">
                        {{ strtoupper(substr($customer->name, 0, 2)) }}
                    </div>
                    <h3 class="text-lg font-bold text-gray-800">{{ $customer->name }}</h3>
                    <p class="text-sm text-gray-500 font-mono mt-1">{{ $customer->phone }}</p>
                    <div class="mt-3">
                        @if($customer->is_active)
                        <span class="px-3 py-1 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800">Aktif</span>
                        @else
                        <span class="px-3 py-1 rounded-full text-xs font-bold bg-red-100 text-red-800">Nonaktif</span>
                        @endif
                    </div>
                </div>

                <div class="pt-6 space-y-4 text-sm text-gray-600">
                    <div>
                        <span class="text-xs font-bold text-gray-400 uppercase tracking-wider block">NIK</span>
                        <span class="font-medium text-gray-800">{{ $customer->nik ?: '-' }}</span>
                    </div>
                    <div>
                        <span class="text-xs font-bold text-gray-400 uppercase tracking-wider block">Tanggal Lahir</span>
                        <span class="font-medium text-gray-800">{{ $customer->dob ? $customer->dob->format('d F Y') : '-' }}</span>
                    </div>
                    <div>
                        <span class="text-xs font-bold text-gray-400 uppercase tracking-wider block">Alamat</span>
                        <span class="font-medium text-gray-800 block mt-1 leading-relaxed">{{ $customer->address ?: '-' }}</span>
                    </div>
                    <div>
                        <span class="text-xs font-bold text-gray-400 uppercase tracking-wider block">Terdaftar Sejak</span>
                        <span class="font-medium text-gray-850">{{ $customer->created_at->format('d M Y H:i') }}</span>
                    </div>
                </div>
            </div>

            {{-- Loyalty Point Widget --}}
            <div class="card p-6 bg-gradient-to-br from-emerald-600 to-teal-700 text-white rounded-2xl shadow-md relative overflow-hidden">
                <div class="absolute right-0 bottom-0 translate-x-6 translate-y-6 opacity-10">
                    <svg class="w-40 h-40" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 17h-2v-2h2v2zm2.07-7.75l-.9.92C13.45 12.9 13 13.5 13 15h-2v-.5c0-1.1.45-2.1 1.17-2.83l1.24-1.26c.37-.36.59-.86.59-1.41 0-1.1-.9-2-2-2s-2 .9-2 2H7c0-2.76 2.24-5 5-5s5 2.24 5 5c0 1.04-.42 1.99-1.07 2.75z"/></svg>
                </div>
                <div class="relative">
                    <span class="text-xs font-bold uppercase tracking-widest text-emerald-200">Loyalty Poin</span>
                    <h4 class="text-3xl font-black mt-2">{{ number_format($customer->points) }} Pts</h4>
                    <p class="text-xs text-emerald-100 mt-2 leading-relaxed">
                        Poin dapat ditukarkan saat transaksi di kasir sebagai potongan belanja.
                    </p>
                </div>
            </div>
        </div>

        {{-- Statistics & Transaction History --}}
        <div class="lg:col-span-2 space-y-6">
            {{-- Stats Cards --}}
            <div class="grid grid-cols-2 gap-4">
                <div class="card p-6 bg-white border border-gray-100 rounded-2xl shadow-sm flex items-center gap-4">
                    <div class="p-3 bg-blue-50 text-blue-600 rounded-xl">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
                    </div>
                    <div>
                        <span class="text-xs font-semibold text-gray-400 uppercase tracking-wider block">Total Transaksi</span>
                        <span class="text-xl font-extrabold text-gray-800">{{ number_format($lifetimeTransactions) }} x</span>
                    </div>
                </div>

                <div class="card p-6 bg-white border border-gray-100 rounded-2xl shadow-sm flex items-center gap-4">
                    <div class="p-3 bg-purple-50 text-purple-600 rounded-xl">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                    <div>
                        <span class="text-xs font-semibold text-gray-400 uppercase tracking-wider block">Lifetime Belanja</span>
                        <span class="text-xl font-extrabold text-primary-600">Rp {{ number_format($lifetimeSpend, 0, ',', '.') }}</span>
                    </div>
                </div>
            </div>

            {{-- Transaction History Table --}}
            <div class="card p-6 bg-white border border-gray-100 rounded-2xl shadow-sm">
                <h3 class="text-lg font-bold text-gray-800 mb-4 border-b border-gray-100 pb-2">Riwayat Transaksi Belanja</h3>
                
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse text-sm">
                        <thead>
                            <tr class="border-b border-gray-100 text-xs font-bold text-gray-400 uppercase">
                                <th class="py-3 px-2">No. Dokumen</th>
                                <th class="py-3 px-2">Tanggal</th>
                                <th class="py-3 px-2 text-center">Metode</th>
                                <th class="py-3 px-2 text-right">Poin Diterima</th>
                                <th class="py-3 px-2 text-right">Total Belanja</th>
                                <th class="py-3 px-2 text-center w-20">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50">
                            @forelse($sales as $sale)
                            <tr>
                                <td class="py-3 px-2">
                                    <span class="block text-[9px] font-bold text-gray-400 uppercase">{{ $sale->document_label }}</span>
                                    <span class="font-mono font-semibold text-gray-700">{{ $sale->invoice_no }}</span>
                                </td>
                                <td class="py-3 px-2 text-gray-550">{{ $sale->sold_at->format('d M Y H:i') }}</td>
                                <td class="py-3 px-2 text-center">
                                    <span class="uppercase font-semibold text-[10px] text-gray-650 bg-gray-100 px-2 py-0.5 rounded">{{ $sale->payment_method }}</span>
                                </td>
                                <td class="py-3 px-2 text-right text-emerald-600 font-bold">+{{ $sale->points_earned }} pts</td>
                                <td class="py-3 px-2 text-right font-bold text-primary-600">Rp {{ number_format($sale->total, 0, ',', '.') }}</td>
                                <td class="py-3 px-2 text-center">
                                    <a wire:navigate href="{{ route('sales.show', $sale->id) }}" class="btn btn-secondary btn-sm py-1 px-2">Detail</a>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="text-center py-8 text-gray-400">
                                    Belum ada transaksi belanja yang dilakukan pelanggan ini.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if($sales->hasPages())
                <div class="pt-4 border-t border-gray-50">
                    {{ $sales->links() }}
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
