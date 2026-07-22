@extends('layouts.app')
@section('title', isset($supplier) ? 'Edit Supplier' : 'Tambah Supplier')
@section('page-title', isset($supplier) ? 'Edit Supplier' : 'Tambah Supplier')

@section('breadcrumb')
<svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"/></svg>
<a wire:navigate href="{{ route('suppliers.index') }}" class="hover:text-gray-600">Supplier</a>
<svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"/></svg>
<span class="text-gray-600 font-medium">{{ isset($supplier) ? 'Edit' : 'Tambah' }}</span>
@endsection

@section('content')
@php $isEdit = isset($supplier); @endphp
<div class="animate-in max-w-2xl">
    <div class="card p-6">
        <h3 class="font-bold text-gray-800 mb-5">{{ $isEdit ? 'Edit Data Supplier' : 'Tambah Supplier Baru' }}</h3>
        <form method="POST" action="{{ $isEdit ? route('suppliers.update', $supplier) : route('suppliers.store') }}">
            @csrf
            @if($isEdit) @method('PUT') @endif

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="md:col-span-2">
                    <label class="form-label">Nama Supplier <span class="text-red-500">*</span></label>
                    <input type="text" name="name" value="{{ old('name', $supplier->name ?? '') }}" class="form-input {{ $errors->has('name') ? 'error' : '' }}" required placeholder="Nama distributor/supplier">
                    @error('name')<p class="form-error">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="form-label">Contact Person</label>
                    <input type="text" name="contact_person" value="{{ old('contact_person', $supplier->contact_person ?? '') }}" class="form-input" placeholder="Nama PIC supplier">
                </div>
                <div>
                    <label class="form-label">Telepon / WA</label>
                    <input type="text" name="phone" value="{{ old('phone', $supplier->phone ?? '') }}" class="form-input" placeholder="08xx-xxxx-xxxx">
                </div>
                <div>
                    <label class="form-label">Email</label>
                    <input type="email" name="email" value="{{ old('email', $supplier->email ?? '') }}" class="form-input" placeholder="email@supplier.com">
                </div>
                @if($isEdit)
                <div>
                    <label class="form-label">Status</label>
                    <select name="is_active" class="form-input">
                        <option value="1" {{ old('is_active', $supplier->is_active) ? 'selected' : '' }}>Aktif</option>
                        <option value="0" {{ !old('is_active', $supplier->is_active) ? 'selected' : '' }}>Nonaktif</option>
                    </select>
                </div>
                @endif
                <div class="md:col-span-2">
                    <label class="form-label">Alamat</label>
                    <textarea name="address" rows="3" class="form-input" placeholder="Alamat lengkap supplier">{{ old('address', $supplier->address ?? '') }}</textarea>
                </div>
            </div>

            <div class="flex gap-3 mt-5">
                <button type="submit" class="btn btn-primary">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    {{ $isEdit ? 'Simpan Perubahan' : 'Tambah Supplier' }}
                </button>
                <a wire:navigate href="{{ route('suppliers.index') }}" class="btn btn-secondary">Batal</a>
            </div>
        </form>
    </div>
</div>
@endsection
