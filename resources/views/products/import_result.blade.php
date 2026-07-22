@extends('layouts.app')
@section('title', 'Hasil Import Excel')
@section('page-title', 'Hasil Import')

@section('breadcrumb')
<svg class="w-3 h-3 text-gray-400" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"/></svg>
<a wire:navigate href="{{ route('products.index') }}" class="hover:text-primary-600 transition-colors">Master Produk</a>
<svg class="w-3 h-3 text-gray-400" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"/></svg>
<a wire:navigate href="{{ route('products.import.form') }}" class="hover:text-primary-600 transition-colors">Import Excel</a>
<svg class="w-3 h-3 text-gray-400" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"/></svg>
<span class="text-gray-600 font-medium">Hasil Import</span>
@endsection

@section('content')
<div class="animate-in max-w-5xl mx-auto">

    {{-- Header --}}
    <div class="page-header mb-6">
        <div>
            <h2 class="page-title text-2xl font-bold">Hasil Import Excel</h2>
            <p class="page-subtitle text-gray-500">
                Ringkasan proses import
                @if(!empty($filename))
                    file <strong class="text-gray-700">{{ $filename }}</strong>
                @endif
            </p>
        </div>
        <div class="flex items-center gap-3">
            <a wire:navigate href="{{ route('products.import.form') }}" class="btn btn-secondary flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 1121.21 8H18.5"/></svg>
                Import Lagi
            </a>
            <a wire:navigate href="{{ route('products.index') }}" class="btn btn-primary flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                Lihat Semua Produk
            </a>
        </div>
    </div>

    {{-- ── Summary Cards ──────────────────────────────────────────── --}}
    @php
        $total = $success_count + $failed_count;
        $createdCount = collect($logs)->where('status', 'created')->count();
        $updatedCount = collect($logs)->where('status', 'updated')->count();
    @endphp

    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
        {{-- Total Baris --}}
        <div class="card p-4 border-l-4 border-blue-400">
            <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Total Baris Diproses</p>
            <h3 class="text-3xl font-extrabold text-blue-600 mt-1">{{ $total }}</h3>
            <p class="text-xs text-gray-400 mt-1">dari file Excel</p>
        </div>

        {{-- Produk Baru --}}
        <div class="card p-4 border-l-4 border-emerald-500">
            <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Produk Baru</p>
            <h3 class="text-3xl font-extrabold text-emerald-600 mt-1">{{ $createdCount }}</h3>
            <p class="text-xs text-gray-400 mt-1">berhasil ditambahkan</p>
        </div>

        {{-- Produk Diperbarui --}}
        <div class="card p-4 border-l-4 border-sky-500">
            <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Produk Diperbarui</p>
            <h3 class="text-3xl font-extrabold text-sky-600 mt-1">{{ $updatedCount }}</h3>
            <p class="text-xs text-gray-400 mt-1">data diperbarui</p>
        </div>

        {{-- Gagal --}}
        <div class="card p-4 border-l-4 {{ $failed_count > 0 ? 'border-red-500' : 'border-gray-200' }}">
            <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Baris Gagal</p>
            <h3 class="text-3xl font-extrabold {{ $failed_count > 0 ? 'text-red-600' : 'text-gray-300' }} mt-1">{{ $failed_count }}</h3>
            <p class="text-xs text-gray-400 mt-1">{{ $failed_count > 0 ? 'lihat detail di bawah' : 'tidak ada error' }}</p>
        </div>
    </div>

    {{-- ── Progress Bar ────────────────────────────────────────────── --}}
    @if($total > 0)
    <div class="card p-4 mb-6">
        <div class="flex justify-between items-center mb-2">
            <span class="text-sm font-semibold text-gray-700">Tingkat Keberhasilan</span>
            <span class="text-sm font-bold {{ $success_count == $total ? 'text-emerald-600' : 'text-amber-600' }}">
                {{ round(($success_count / $total) * 100) }}%
            </span>
        </div>
        <div class="h-3 bg-gray-100 rounded-full overflow-hidden">
            <div class="h-full rounded-full transition-all duration-700 {{ $success_count == $total ? 'bg-emerald-500' : 'bg-amber-500' }}"
                 style="width: {{ round(($success_count / $total) * 100) }}%"></div>
        </div>
        @if($failed_count > 0)
        <p class="text-xs text-amber-600 mt-2">⚠️ {{ $failed_count }} baris gagal diimport. Periksa log detail di bawah untuk memperbaiki data.</p>
        @else
        <p class="text-xs text-emerald-600 mt-2">✅ Semua baris berhasil diimport tanpa error!</p>
        @endif
    </div>
    @endif

    {{-- ── Log Detail ──────────────────────────────────────────────── --}}
    <div class="card p-6">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-bold text-gray-800 flex items-center gap-2">
                <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                Log Detail Import
            </h3>
            <div class="flex items-center gap-2 text-xs">
                <span class="inline-flex items-center gap-1 px-2 py-1 bg-emerald-50 text-emerald-700 rounded-full font-medium">
                    <span class="w-1.5 h-1.5 bg-emerald-500 rounded-full"></span>Baru
                </span>
                <span class="inline-flex items-center gap-1 px-2 py-1 bg-sky-50 text-sky-700 rounded-full font-medium">
                    <span class="w-1.5 h-1.5 bg-sky-500 rounded-full"></span>Update
                </span>
                <span class="inline-flex items-center gap-1 px-2 py-1 bg-red-50 text-red-700 rounded-full font-medium">
                    <span class="w-1.5 h-1.5 bg-red-500 rounded-full"></span>Gagal
                </span>
            </div>
        </div>

        <div class="overflow-x-auto max-h-[480px] overflow-y-auto border border-gray-100 rounded-xl">
            <table class="data-table w-full text-sm">
                <thead class="sticky top-0 z-10">
                    <tr>
                        <th class="w-10 text-center">#</th>
                        <th class="w-20">Status</th>
                        <th class="w-32">Kode</th>
                        <th>Nama Produk</th>
                        <th>Keterangan</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($logs as $i => $log)
                    <tr class="{{ $log['status'] === 'failed' ? 'bg-red-50/50' : ($log['status'] === 'updated' ? 'bg-sky-50/30' : '') }}">
                        <td class="text-center text-gray-400 text-xs">{{ $i + 1 }}</td>
                        <td>
                            @if($log['status'] === 'created')
                                <span class="inline-flex items-center gap-1 badge bg-emerald-50 text-emerald-700 border border-emerald-200 text-xs">
                                    <span class="w-1.5 h-1.5 bg-emerald-500 rounded-full"></span>Baru
                                </span>
                            @elseif($log['status'] === 'updated')
                                <span class="inline-flex items-center gap-1 badge bg-sky-50 text-sky-700 border border-sky-200 text-xs">
                                    <span class="w-1.5 h-1.5 bg-sky-500 rounded-full"></span>Update
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1 badge bg-red-50 text-red-700 border border-red-200 text-xs">
                                    <span class="w-1.5 h-1.5 bg-red-500 rounded-full"></span>Gagal
                                </span>
                            @endif
                        </td>
                        <td class="font-mono text-xs text-gray-600 font-semibold">{{ $log['code'] ?? '-' }}</td>
                        <td class="font-medium text-gray-800">{{ $log['name'] ?? '-' }}</td>
                        <td class="text-xs text-gray-500">{{ $log['message'] }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="text-center text-gray-400 py-10">
                            <svg class="w-10 h-10 mx-auto mb-2 text-gray-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                            Tidak ada log proses import.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($failed_count > 0)
        <div class="mt-4 p-3 bg-amber-50 border border-amber-200 rounded-xl text-xs text-amber-700">
            <strong>💡 Cara memperbaiki baris gagal:</strong> Periksa pesan error di kolom Keterangan. Umumnya disebabkan oleh nilai kosong pada kolom wajib (Kode/Nama) atau format data yang tidak sesuai.
        </div>
        @endif
    </div>

</div>
@endsection
