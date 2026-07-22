@extends('layouts.app')
@section('title', 'Backup')
@section('page-title', 'Database Backup')

@section('breadcrumb')
<svg class="w-3 h-3 text-gray-400" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"/></svg>
<span class="text-gray-600 font-medium">Database Backup</span>
@endsection

@section('content')
<div class="animate-in max-w-4xl mx-auto">
    <div class="page-header mb-6 flex justify-between items-center">
        <div>
            <h2 class="page-title text-2xl font-bold text-gray-800">Database Backup</h2>
            <p class="page-subtitle text-gray-500">Cadangkan database sistem secara berkala untuk pengamanan data</p>
        </div>
        <a wire:navigate href="{{ route('backup.create') }}" class="btn btn-primary flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
            Buat Backup Sekarang
        </a>
    </div>

    <div class="card p-6 bg-white border border-gray-100 rounded-2xl shadow-sm">
        <h3 class="text-lg font-bold text-gray-800 mb-4">Daftar File Backup</h3>
        
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-gray-50 text-gray-600 text-xs font-semibold uppercase border-b border-gray-100">
                        <th class="px-5 py-3 w-12">#</th>
                        <th class="px-5 py-3">Nama File Backup</th>
                        <th class="px-5 py-3 text-center">Ukuran File</th>
                        <th class="px-5 py-3 text-center">Tanggal Pembuatan</th>
                        <th class="px-5 py-3 text-center w-40">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 text-sm">
                    @forelse($backups as $index => $backup)
                    <tr class="hover:bg-gray-50/50 transition-colors">
                        <td class="px-5 py-3.5 text-gray-400 font-mono">{{ $index + 1 }}</td>
                        <td class="px-5 py-3.5 font-medium text-gray-800 font-mono text-xs">
                            {{ $backup['filename'] }}
                        </td>
                        <td class="px-5 py-3.5 text-center text-gray-600 font-semibold">
                            {{ $backup['size'] }}
                        </td>
                        <td class="px-5 py-3.5 text-center text-gray-500">
                            {{ $backup['created_at'] }}
                        </td>
                        <td class="px-5 py-3.5 text-center">
                            <div class="flex items-center justify-center gap-2">
                                {{-- Download --}}
                                <a href="{{ route('backup.download', $backup['filename']) }}" download class="btn btn-secondary px-3 py-1.5 text-xs flex items-center gap-1 hover:bg-emerald-50 hover:text-emerald-700 hover:border-emerald-200 transition-all no-loading" data-no-loading>
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                                    Unduh
                                </a>

                                {{-- Delete --}}
                                <form action="{{ route('backup.destroy', $backup['filename']) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus file backup ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-secondary px-3 py-1.5 text-xs text-red-600 border-red-100 hover:bg-red-50 hover:text-red-700 hover:border-red-200 transition-all">
                                        Hapus
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-5 py-12 text-center text-gray-400">
                            <div class="flex flex-col items-center justify-center gap-2">
                                <svg class="w-10 h-10 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4m0 5c0 2.21-3.582 4-8 4s-8-1.79-8-4"/></svg>
                                <p class="text-sm font-medium">Belum ada file backup database</p>
                                <p class="text-xs text-gray-400">Klik tombol "Buat Backup Sekarang" di atas untuk membuat cadangan pertama Anda</p>
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
