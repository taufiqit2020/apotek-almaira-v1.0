@extends('layouts.app')
@section('title', 'Mitra')
@section('page-title', 'Mitra Katalog')

@section('breadcrumb')
<svg class="w-3 h-3 text-gray-400" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"/></svg>
<span class="text-gray-600 font-medium">Mitra Katalog</span>
@endsection

@section('content')
@php
    $types = \App\Models\Partner::typeOptions();
    $statuses = \App\Models\Partner::statusOptions();
@endphp
<div class="animate-in"
     x-data="{
        sinceId: {{ $maxPartnerId }},
        pendingCount: {{ $pendingCount }},
        newRegistrations: [],
        polling: null,
        init() {
            this.poll();
            this.polling = setInterval(() => this.poll(), 10000);
        },
        destroy() {
            if (this.polling) clearInterval(this.polling);
        },
        async poll() {
            try {
                const res = await fetch(`{{ route('partners.pending-updates') }}?since_id=${this.sinceId}`, {
                    headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
                });
                if (!res.ok) return;
                const data = await res.json();
                if (!data.success) return;

                this.pendingCount = data.pending_count;

                (data.new_registrations || []).forEach(reg => {
                    if (this.newRegistrations.some(r => r.id === reg.id)) return;
                    this.newRegistrations.unshift(reg);
                    window.dispatchEvent(new CustomEvent('toast', {
                        detail: {
                            type: 'warning',
                            message: `Pendaftar mitra baru: ${reg.name} (${reg.code}) — menunggu approval Anda.`
                        }
                    }));
                    if (window.Livewire) {
                        window.Livewire.dispatch('partner-pending-refresh');
                    }
                });

                if (data.max_id > this.sinceId) {
                    this.sinceId = data.max_id;
                }
            } catch (e) {}
        },
        dismissNew(id) {
            this.newRegistrations = this.newRegistrations.filter(r => r.id !== id);
        }
     }"
     x-init="init()"
>
    <div class="page-header mb-6">
        <div>
            <h2 class="page-title text-2xl font-bold text-gray-800">Mitra E-Catalog</h2>
            <p class="page-subtitle text-gray-500">Kelola mitra B2B (RS, Klinik, Apotek, UMKM, Instansi) untuk order/PO katalog</p>
        </div>
        <a wire:navigate href="{{ route('partners.create') }}" class="btn btn-primary flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Tambah Mitra
        </a>
    </div>

    {{-- Notifikasi pendaftar baru (real-time) --}}
    <template x-for="reg in newRegistrations" :key="reg.id">
        <div class="mb-4 flex flex-col sm:flex-row sm:items-center justify-between gap-3 px-4 py-4 rounded-xl bg-gradient-to-r from-amber-50 to-orange-50 border-2 border-amber-300 text-amber-900 shadow-md shadow-amber-100 animate-in"
             x-transition>
            <div class="flex items-start gap-3 min-w-0">
                <div class="w-10 h-10 rounded-xl bg-amber-500 text-white flex items-center justify-center shrink-0 font-black text-xs">NEW</div>
                <div class="min-w-0">
                    <p class="text-sm font-extrabold text-amber-900">Pendaftar Mitra Baru!</p>
                    <p class="text-sm font-bold text-slate-800 mt-0.5" x-text="`${reg.code} — ${reg.name}`"></p>
                    <p class="text-xs text-amber-800/80 mt-1">
                        <span x-text="reg.type_label"></span> · PIC: <span x-text="reg.pic_name || '-'"></span> · <span x-text="reg.phone"></span>
                        <template x-if="reg.city"> · <span x-text="reg.city"></span></template>
                    </p>
                    <p class="text-[11px] text-amber-700/70 mt-0.5" x-text="`Terdaftar: ${reg.created_at}`"></p>
                </div>
            </div>
            <div class="flex items-center gap-2 shrink-0">
                <a :href="reg.show_url" wire:navigate class="btn btn-primary btn-sm whitespace-nowrap">Tinjau & Approve</a>
                <button type="button" @click="dismissNew(reg.id)" class="btn btn-secondary btn-sm" title="Tutup">×</button>
            </div>
        </div>
    </template>

    <div x-show="pendingCount > 0" x-cloak class="mb-4 flex items-center justify-between gap-3 px-4 py-3 rounded-xl bg-amber-50 border border-amber-200 text-amber-800 text-sm">
        <span class="font-semibold">
            <span x-text="pendingCount"></span> mitra menunggu approval
            <span class="text-[11px] font-normal text-amber-600 ml-1">· pembaruan otomatis setiap 10 detik</span>
        </span>
        <a wire:navigate href="{{ route('partners.index', ['status' => 'pending']) }}" class="text-xs font-bold underline hover:no-underline">Lihat pending →</a>
    </div>

    <form method="GET" class="card p-4 mb-6 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3 bg-white border border-gray-100 rounded-2xl shadow-sm">
        <div class="lg:col-span-2 relative">
            <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama, kode, HP, email..." class="form-input pl-9">
        </div>
        <select name="type" class="form-input">
            <option value="">Semua Tipe</option>
            @foreach($types as $key => $label)
            <option value="{{ $key }}" @selected(request('type') === $key)>{{ $label }}</option>
            @endforeach
        </select>
        <select name="status" class="form-input">
            <option value="">Semua Status</option>
            @foreach($statuses as $key => $label)
            <option value="{{ $key }}" @selected(request('status') === $key)>{{ $label }}</option>
            @endforeach
        </select>
        <div class="flex gap-2">
            <button type="submit" class="btn btn-primary btn-sm flex-1">Filter</button>
            @if(request()->hasAny(['search','type','status']))
            <a wire:navigate href="{{ route('partners.index') }}" class="btn btn-secondary btn-sm">Reset</a>
            @endif
        </div>
    </form>

    <div class="card overflow-hidden bg-white border border-gray-100 rounded-2xl shadow-sm">
        <div class="overflow-x-auto">
            <table class="data-table w-full">
                <thead>
                    <tr>
                        <th>Kode</th>
                        <th>Mitra</th>
                        <th>Tipe</th>
                        <th>Kontak</th>
                        <th>Harga</th>
                        <th>Bayar</th>
                        <th class="text-center">Status</th>
                        <th class="text-center w-52">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($partners as $p)
                    <tr class="hover:bg-gray-50/50 {{ $p->status === 'pending' && $p->registration_source === 'self' ? 'bg-amber-50/40' : '' }}">
                        <td class="font-mono text-xs font-bold text-slate-600">{{ $p->code ?? '—' }}</td>
                        <td>
                            <p class="font-semibold text-gray-800 text-sm">{{ $p->name }}</p>
                            <p class="text-[11px] text-gray-400">
                                {{ $p->city ?: '—' }} · {{ $p->registration_source === 'self' ? 'Daftar sendiri' : 'Admin' }}
                                @if($p->status === 'pending' && $p->registration_source === 'self')
                                <span class="ml-1 px-1.5 py-0.5 rounded bg-amber-100 text-amber-700 font-bold">Baru</span>
                                @endif
                            </p>
                        </td>
                        <td>
                            <span class="px-2 py-0.5 rounded-md text-[10px] font-bold bg-violet-50 text-violet-700 border border-violet-100">{{ $p->type_label }}</span>
                        </td>
                        <td class="text-xs text-gray-600">
                            <div>{{ $p->pic_name ?: '—' }}</div>
                            <div class="text-gray-400">{{ $p->phone }}</div>
                        </td>
                        <td class="text-xs font-semibold text-emerald-700">{{ $p->price_mode_label }}</td>
                        <td class="text-[10px] space-x-1">
                            @if($p->allow_transfer)<span class="px-1.5 py-0.5 rounded bg-slate-100 text-slate-600 font-bold">TF</span>@endif
                            @if($p->allow_cod)<span class="px-1.5 py-0.5 rounded bg-slate-100 text-slate-600 font-bold">COD</span>@endif
                            @if($p->invoice_enabled)<span class="px-1.5 py-0.5 rounded bg-amber-50 text-amber-700 font-bold border border-amber-100">INV</span>@endif
                        </td>
                        <td class="text-center">
                            @php
                                $badge = match($p->status) {
                                    'approved' => 'bg-emerald-100 text-emerald-800',
                                    'pending'  => 'bg-amber-100 text-amber-800',
                                    'rejected' => 'bg-red-100 text-red-800',
                                    default    => 'bg-slate-100 text-slate-600',
                                };
                            @endphp
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold {{ $badge }}">{{ $p->status_label }}</span>
                        </td>
                        <td class="text-center">
                            <div class="flex justify-center items-center gap-1.5">
                                <a wire:navigate href="{{ route('partners.show', $p) }}" class="btn btn-secondary btn-sm py-1 px-2.5">Detail</a>
                                <a wire:navigate href="{{ route('partners.edit', $p) }}" class="btn btn-secondary btn-sm py-1 px-2.5 text-amber-600 border-amber-200">Edit</a>
                                <form action="{{ route('partners.destroy', $p) }}" method="POST"
                                      onsubmit="return confirm('Hapus mitra {{ addslashes($p->name) }} ({{ $p->code }})?\n\nAkun login mitra akan dinonaktifkan. Data yang sudah dihapus tidak tampil di daftar.')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-secondary btn-sm py-1 px-2.5 text-red-600 border-red-200 hover:bg-red-50" title="Hapus mitra">
                                        Hapus
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center text-gray-400 py-12">Belum ada data mitra.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($partners->hasPages())
        <div class="px-6 py-4 border-t border-gray-100 bg-gray-50/50">
            {{ $partners->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
