@extends('layouts.app')
@section('title', 'Tambah Karyawan')
@section('page-title', 'Master Karyawan')

@section('breadcrumb')
<svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"/></svg>
<a wire:navigate href="{{ route('employees.index') }}" class="hover:text-gray-600">Master Karyawan</a>
<svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"/></svg>
<span class="text-gray-600 font-medium">Tambah</span>
@endsection

@section('content')
<div class="animate-in max-w-6xl mx-auto">
    <div class="page-header mb-5">
        <div>
            <h2 class="page-title text-2xl font-bold text-gray-800">Tambah Karyawan</h2>
            <p class="page-subtitle text-gray-500">Isi data karyawan untuk slip gaji PT / Apotek</p>
        </div>
    </div>
    @include('employees._form')
</div>
@endsection
