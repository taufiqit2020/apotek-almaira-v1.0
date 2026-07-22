@extends('layouts.app')
@section('title', 'Edit Pelanggan')
@section('page-title', 'Pelanggan')

@section('breadcrumb')
<svg class="w-3 h-3 text-gray-400" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"/></svg>
<a wire:navigate href="{{ route('customers.index') }}" class="hover:text-primary-600 transition-colors">Pelanggan</a>
<svg class="w-3 h-3 text-gray-400" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"/></svg>
<span class="text-gray-600 font-medium">Edit</span>
@endsection

@section('content')
<div class="animate-in max-w-lg mx-auto">
    <div class="page-header mb-6">
        <div>
            <h2 class="page-title text-2xl font-bold text-gray-800">Edit Data Pelanggan</h2>
            <p class="page-subtitle text-gray-500">Ubah informasi identitas atau kelola status keaktifan pelanggan</p>
        </div>
        <a wire:navigate href="{{ route('customers.index') }}" class="btn btn-secondary flex items-center gap-2">
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

    <form action="{{ route('customers.update', $customer) }}" method="POST" class="card p-6 bg-white border border-gray-100 rounded-2xl shadow-sm space-y-4">
        @csrf
        @method('PUT')
        
        <div>
            <label class="form-label font-bold text-gray-700">Nama Pelanggan <span class="text-red-500">*</span></label>
            <input type="text" name="name" value="{{ old('name', $customer->name) }}" class="form-input" required>
        </div>

        <div>
            <label class="form-label font-bold text-gray-700">Nomor HP / WhatsApp <span class="text-red-500">*</span></label>
            <input type="text" name="phone" value="{{ old('phone', $customer->phone) }}" class="form-input" required>
        </div>

        <div>
            <label class="form-label font-bold text-gray-700">Nomor Identitas (NIK) <span class="text-xs text-gray-400">(Opsional)</span></label>
            <input type="text" name="nik" value="{{ old('nik', $customer->nik) }}" class="form-input">
        </div>

        <div>
            <label class="form-label font-bold text-gray-700">Tanggal Lahir <span class="text-xs text-gray-400">(Opsional)</span></label>
            <input type="date" name="dob" value="{{ old('dob', $customer->dob ? $customer->dob->format('Y-m-d') : '') }}" class="form-input">
        </div>

        <div>
            <label class="form-label font-bold text-gray-700">Alamat Tempat Tinggal <span class="text-xs text-gray-400">(Opsional)</span></label>
            <textarea name="address" rows="3" class="form-input">{{ old('address', $customer->address) }}</textarea>
        </div>

        <div>
            <label class="form-label font-bold text-gray-700">Status Pelanggan <span class="text-red-500">*</span></label>
            <select name="is_active" class="form-input" required>
                <option value="1" {{ old('is_active', $customer->is_active) ? 'selected' : '' }}>Aktif</option>
                <option value="0" {{ !old('is_active', $customer->is_active) ? 'selected' : '' }}>Nonaktif</option>
            </select>
        </div>

        <div class="flex justify-end gap-3 pt-4 border-t border-gray-50">
            <a wire:navigate href="{{ route('customers.index') }}" class="btn btn-secondary">Batal</a>
            <button type="submit" class="btn btn-primary flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"/></svg>
                Simpan Perubahan
            </button>
        </div>
    </form>
</div>
@endsection
