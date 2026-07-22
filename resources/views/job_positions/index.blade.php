@extends('layouts.app')
@section('title', 'Master Jabatan')
@section('page-title', 'Master Jabatan')

@section('breadcrumb')
<svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"/></svg>
<span class="text-gray-600 font-medium">Jabatan</span>
@endsection

@section('content')
<div class="animate-in">
    <div class="page-header">
        <div>
            <h2 class="page-title">Master Jabatan</h2>
            <p class="page-subtitle">Kelola jabatan karyawan — otomatis tersedia di form Master Karyawan</p>
        </div>
        <button onclick="document.getElementById('modalAdd').classList.remove('hidden')" class="btn btn-primary">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Tambah Jabatan
        </button>
    </div>

    <form method="GET" class="card p-4 mb-5 flex flex-wrap items-center gap-3">
        <div class="flex-1 min-w-[220px] relative">
            <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama jabatan..." class="form-input pl-9">
        </div>
        <button type="submit" class="btn btn-primary btn-sm">Filter</button>
        @if(request('search'))
        <a wire:navigate href="{{ route('job-positions.index') }}" class="btn btn-secondary btn-sm">Reset</a>
        @endif
    </form>

    <div class="card overflow-hidden">
        <div class="overflow-x-auto">
            <table class="data-table">
                <thead>
                    <tr>
                        <th class="w-12">#</th>
                        <th>Nama Jabatan</th>
                        <th class="text-center">Jumlah Karyawan</th>
                        <th class="text-center">Status</th>
                        <th class="text-center w-36">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($jobPositions as $i => $job)
                    <tr>
                        <td class="text-gray-400">{{ $jobPositions->firstItem() + $i }}</td>
                        <td>
                            <div>
                                <p class="font-semibold text-gray-800">{{ $job->name }}</p>
                                @if($job->description)
                                <p class="text-xs text-gray-400 mt-0.5">{{ $job->description }}</p>
                                @else
                                <p class="text-xs text-gray-400 font-mono">{{ $job->slug }}</p>
                                @endif
                            </div>
                        </td>
                        <td class="text-center">
                            <span class="badge badge-info">{{ $job->employees_count }} karyawan</span>
                        </td>
                        <td class="text-center">
                            @if($job->is_active)
                            <span class="badge badge-success">Aktif</span>
                            @else
                            <span class="badge badge-gray">Nonaktif</span>
                            @endif
                        </td>
                        <td>
                            <div class="flex items-center justify-center gap-2">
                                <button
                                    onclick="openEditModal({{ $job->id }}, @js($job->name), @js($job->description ?? ''))"
                                    class="btn btn-icon btn-sm btn-secondary" title="Edit">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                </button>

                                <form id="toggle-job-{{ $job->id }}" method="POST" action="{{ route('job-positions.toggle-status', $job) }}">
                                    @csrf @method('PATCH')
                                </form>
                                <button type="button"
                                    @click="confirm('toggle-job-{{ $job->id }}', '{{ $job->is_active ? 'Nonaktifkan' : 'Aktifkan' }} Jabatan', 'Yakin ingin {{ $job->is_active ? 'menonaktifkan' : 'mengaktifkan' }} jabatan ini?')"
                                    class="btn btn-icon btn-sm {{ $job->is_active ? 'bg-amber-50 text-amber-600 hover:bg-amber-100' : 'bg-green-50 text-green-600 hover:bg-green-100' }}"
                                    title="{{ $job->is_active ? 'Nonaktifkan' : 'Aktifkan' }}">
                                    @if($job->is_active)
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/></svg>
                                    @else
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    @endif
                                </button>

                                <form id="del-job-{{ $job->id }}" method="POST" action="{{ route('job-positions.destroy', $job) }}">
                                    @csrf @method('DELETE')
                                </form>
                                <button type="button"
                                    @click="confirm('del-job-{{ $job->id }}', 'Hapus Jabatan', 'Hapus jabatan {{ addslashes($job->name) }}? Pastikan tidak ada karyawan terkait.')"
                                    class="btn btn-icon btn-sm bg-red-50 text-red-500 hover:bg-red-100"
                                    title="Hapus">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="text-center py-12 text-gray-400">
                            <svg class="w-12 h-12 mx-auto mb-3 text-gray-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                            </svg>
                            <p>Belum ada jabatan</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($jobPositions->hasPages())
        <div class="px-5 py-4 border-t border-gray-100">
            {{ $jobPositions->links() }}
        </div>
        @endif
    </div>
</div>

{{-- Modal Tambah --}}
<div id="modalAdd" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4">
    <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" onclick="document.getElementById('modalAdd').classList.add('hidden')"></div>
    <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-md p-6">
        <div class="flex items-center justify-between mb-5">
            <h3 class="font-bold text-gray-800 text-lg">Tambah Jabatan</h3>
            <button onclick="document.getElementById('modalAdd').classList.add('hidden')" class="text-gray-400 hover:text-gray-600">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
        <form method="POST" action="{{ route('job-positions.store') }}">
            @csrf
            <div class="mb-4">
                <label class="form-label">Nama Jabatan <span class="text-red-500">*</span></label>
                <input type="text" name="name" value="{{ old('name') }}" class="form-input {{ $errors->has('name') ? 'error' : '' }}" placeholder="Contoh: Apoteker / Kasir / Admin" autofocus required>
                @error('name')<p class="form-error">{{ $message }}</p>@enderror
            </div>
            <div class="mb-4">
                <label class="form-label">Keterangan</label>
                <input type="text" name="description" value="{{ old('description') }}" class="form-input" placeholder="Opsional">
            </div>
            <div class="flex gap-3">
                <button type="submit" class="btn btn-primary flex-1">Simpan Jabatan</button>
                <button type="button" onclick="document.getElementById('modalAdd').classList.add('hidden')" class="btn btn-secondary">Batal</button>
            </div>
        </form>
    </div>
</div>

{{-- Modal Edit --}}
<div id="modalEdit" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4">
    <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" onclick="document.getElementById('modalEdit').classList.add('hidden')"></div>
    <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-md p-6">
        <div class="flex items-center justify-between mb-5">
            <h3 class="font-bold text-gray-800 text-lg">Edit Jabatan</h3>
            <button onclick="document.getElementById('modalEdit').classList.add('hidden')" class="text-gray-400 hover:text-gray-600">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
        <form id="editForm" method="POST">
            @csrf @method('PUT')
            <div class="mb-4">
                <label class="form-label">Nama Jabatan <span class="text-red-500">*</span></label>
                <input type="text" id="editName" name="name" class="form-input" required>
            </div>
            <div class="mb-4">
                <label class="form-label">Keterangan</label>
                <input type="text" id="editDescription" name="description" class="form-input" placeholder="Opsional">
            </div>
            <div class="flex gap-3">
                <button type="submit" class="btn btn-primary flex-1">Simpan Perubahan</button>
                <button type="button" onclick="document.getElementById('modalEdit').classList.add('hidden')" class="btn btn-secondary">Batal</button>
            </div>
        </form>
    </div>
</div>

@if($errors->any())
<script>document.addEventListener('DOMContentLoaded', () => document.getElementById('modalAdd').classList.remove('hidden'));</script>
@endif

<script>
function openEditModal(id, name, description) {
    document.getElementById('editName').value = name || '';
    document.getElementById('editDescription').value = description || '';
    document.getElementById('editForm').action = `/job-positions/${id}`;
    document.getElementById('modalEdit').classList.remove('hidden');
}
</script>
@endsection
