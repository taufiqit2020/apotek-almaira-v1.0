@extends('layouts.app')
@section('title', 'Detail Mitra')
@section('page-title', 'Mitra Katalog')

@section('breadcrumb')
<svg class="w-3 h-3 text-gray-400" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"/></svg>
<a wire:navigate href="{{ route('partners.index') }}" class="hover:text-primary-600 transition-colors">Mitra Katalog</a>
<svg class="w-3 h-3 text-gray-400" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"/></svg>
<span class="text-gray-600 font-medium">Detail</span>
@endsection

@section('content')
@php
    $badge = match($partner->status) {
        'approved' => 'bg-emerald-100 text-emerald-800',
        'pending'  => 'bg-amber-100 text-amber-800',
        'rejected' => 'bg-red-100 text-red-800',
        default    => 'bg-slate-100 text-slate-600',
    };
@endphp
<div class="animate-in max-w-4xl mx-auto">
    <div class="page-header mb-6 flex flex-wrap items-start justify-between gap-3">
        <div>
            <div class="flex items-center gap-2 mb-1">
                <span class="font-mono text-xs font-bold text-slate-500">{{ $partner->code }}</span>
                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold {{ $badge }}">{{ $partner->status_label }}</span>
            </div>
            <h2 class="page-title text-2xl font-bold text-gray-800">{{ $partner->name }}</h2>
            <p class="page-subtitle text-gray-500">{{ $partner->type_label }} · {{ $partner->city ?: '—' }}</p>
        </div>
        <div class="flex flex-wrap gap-2">
            <a wire:navigate href="{{ route('partners.index') }}" class="btn btn-secondary btn-sm">Kembali</a>
            <a wire:navigate href="{{ route('partners.edit', $partner) }}" class="btn btn-primary btn-sm">Edit</a>
            <form action="{{ route('partners.destroy', $partner) }}" method="POST"
                  onsubmit="return confirm('Hapus mitra {{ addslashes($partner->name) }} ({{ $partner->code }})?\n\nAkun login mitra akan dinonaktifkan.')">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-secondary btn-sm text-red-600 border-red-200 hover:bg-red-50">Hapus</button>
            </form>
        </div>
    </div>

    @if($partner->isPending())
    <div class="mb-6 p-4 rounded-2xl bg-amber-50 border border-amber-200 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <p class="text-sm font-semibold text-amber-800">Mitra ini menunggu approval admin.</p>
        <div class="flex flex-wrap gap-2">
            <form action="{{ route('partners.approve', $partner) }}" method="POST">
                @csrf
                <button type="submit" class="btn btn-primary btn-sm !bg-emerald-600">Setujui</button>
            </form>
            <form action="{{ route('partners.reject', $partner) }}" method="POST" class="flex gap-2 items-center"
                  onsubmit="return confirm('Tolak mitra ini?')">
                @csrf
                <input type="text" name="rejection_reason" required placeholder="Alasan penolakan" class="form-input text-sm py-1.5">
                <button type="submit" class="btn btn-secondary btn-sm text-red-600 border-red-200">Tolak</button>
            </form>
        </div>
    </div>
    @endif

    @if($partner->status === 'approved')
    <div class="mb-6">
        <form action="{{ route('partners.deactivate', $partner) }}" method="POST" onsubmit="return confirm('Nonaktifkan mitra ini?')">
            @csrf
            <button type="submit" class="btn btn-secondary btn-sm text-red-600 border-red-200">Nonaktifkan Mitra</button>
        </form>
    </div>
    @endif

    @if($partner->status === 'rejected' && $partner->rejection_reason)
    <div class="mb-6 p-4 rounded-xl bg-red-50 border border-red-100 text-sm text-red-700">
        <strong>Alasan ditolak:</strong> {{ $partner->rejection_reason }}
    </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">
        <div class="card p-6 bg-white border border-gray-100 rounded-2xl shadow-sm space-y-3 text-sm">
            <h3 class="font-bold text-gray-800 uppercase text-xs tracking-wider">Identitas</h3>
            <div class="grid grid-cols-3 gap-y-2">
                <span class="text-gray-400">NPWP</span><span class="col-span-2 font-medium">{{ $partner->npwp ?: '—' }}</span>
                <span class="text-gray-400">NIB</span><span class="col-span-2 font-medium">{{ $partner->nib ?: '—' }}</span>
                <span class="text-gray-400">Alamat</span><span class="col-span-2 font-medium">{{ $partner->address ?: '—' }}</span>
                <span class="text-gray-400">Sumber</span><span class="col-span-2 font-medium">{{ $partner->registration_source === 'self' ? 'Daftar sendiri' : 'Dibuat admin' }}</span>
            </div>
        </div>

        <div class="card p-6 bg-white border border-gray-100 rounded-2xl shadow-sm space-y-3 text-sm">
            <h3 class="font-bold text-gray-800 uppercase text-xs tracking-wider">Kontak & Akun</h3>
            <div class="grid grid-cols-3 gap-y-2">
                <span class="text-gray-400">PIC</span><span class="col-span-2 font-medium">{{ $partner->pic_name ?: '—' }}</span>
                <span class="text-gray-400">Telepon</span><span class="col-span-2 font-medium">{{ $partner->phone }}</span>
                <span class="text-gray-400">Email</span><span class="col-span-2 font-medium">{{ $partner->email ?: '—' }}</span>
                <span class="text-gray-400">Login</span>
                <span class="col-span-2 font-medium">
                    @if($partner->user)
                        {{ $partner->user->username }}
                        <span class="text-[10px] ml-1 px-1.5 py-0.5 rounded {{ $partner->user->is_active ? 'bg-emerald-50 text-emerald-700' : 'bg-slate-100 text-slate-500' }}">
                            {{ $partner->user->is_active ? 'aktif' : 'nonaktif' }}
                        </span>
                    @else
                        <span class="text-gray-400">Belum ada akun</span>
                    @endif
                </span>
            </div>
        </div>

        <div class="card p-6 bg-white border border-gray-100 rounded-2xl shadow-sm space-y-3 text-sm lg:col-span-2">
            <h3 class="font-bold text-gray-800 uppercase text-xs tracking-wider">Komersial</h3>
            <div class="flex flex-wrap gap-3">
                <span class="px-3 py-1.5 rounded-lg bg-emerald-50 text-emerald-700 text-xs font-bold border border-emerald-100">Harga: {{ $partner->price_mode_label }}</span>
                @if($partner->allow_transfer)<span class="px-3 py-1.5 rounded-lg bg-slate-50 text-slate-600 text-xs font-bold border">Transfer</span>@endif
                @if($partner->allow_cod)<span class="px-3 py-1.5 rounded-lg bg-slate-50 text-slate-600 text-xs font-bold border">COD</span>@endif
                @if($partner->invoice_enabled)
                <span class="px-3 py-1.5 rounded-lg bg-amber-50 text-amber-700 text-xs font-bold border border-amber-100">Invoice {{ $partner->credit_days }} hari</span>
                @else
                <span class="px-3 py-1.5 rounded-lg bg-slate-50 text-slate-400 text-xs font-bold border">Invoice off</span>
                @endif
                @if($partner->ppn_enabled)
                <span class="px-3 py-1.5 rounded-lg bg-sky-50 text-sky-700 text-xs font-bold border border-sky-100">
                    PPN {{ rtrim(rtrim(number_format($partner->ppn_percent ?? 11, 2, ',', '.'), '0'), ',') }}%
                    · {{ $partner->ppn_bearer === 'seller' ? 'PT NMF' : 'Pembeli' }}
                </span>
                @else
                <span class="px-3 py-1.5 rounded-lg bg-slate-50 text-slate-400 text-xs font-bold border">PPN off</span>
                @endif
            </div>
            @if($partner->approved_at)
            <p class="text-xs text-gray-400 mt-2">
                Disetujui {{ $partner->approved_at->format('d M Y H:i') }}
                @if($partner->approver) oleh {{ $partner->approver->name }} @endif
            </p>
            @endif
            @if($partner->notes)
            <p class="text-sm text-gray-600 mt-2 border-t border-gray-50 pt-3"><strong>Catatan:</strong> {{ $partner->notes }}</p>
            @endif
        </div>
    </div>
</div>
@endsection
