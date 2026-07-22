@extends('layouts.app')
@section('title', 'Karyawan')
@section('page-title', 'Master Karyawan')

@section('breadcrumb')
<svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"/></svg>
<span class="text-gray-600 font-medium">Master Karyawan</span>
@endsection

@section('content')
<div class="animate-in space-y-5"
     x-data="{
        photoOpen: false,
        photoUrl: '',
        photoName: '',
        openPhoto(url, name) {
            if (!url) return;
            this.photoUrl = url;
            this.photoName = name;
            this.photoOpen = true;
        },
        closePhoto() {
            this.photoOpen = false;
        }
     }"
     @keydown.escape.window="closePhoto()">
    {{-- Hero --}}
    <div class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-emerald-700 via-emerald-600 to-teal-600 text-white shadow-lg shadow-emerald-700/20">
        <div class="absolute -right-10 -top-10 w-48 h-48 rounded-full bg-white/10 blur-2xl"></div>
        <div class="absolute -left-8 -bottom-12 w-40 h-40 rounded-full bg-teal-300/20 blur-2xl"></div>
        <div class="relative px-5 sm:px-7 py-6 sm:py-7 flex flex-col sm:flex-row sm:items-end sm:justify-between gap-4">
            <div>
                <p class="text-[11px] font-bold uppercase tracking-[0.18em] text-emerald-100/80">Master Data</p>
                <h2 class="mt-1 text-2xl sm:text-3xl font-extrabold tracking-tight">Master Karyawan</h2>
                <p class="mt-1.5 text-sm text-emerald-50/90 max-w-xl">Kelola data karyawan untuk slip gaji PT Nur Madani Farma &amp; Apotek Almaira.</p>
            </div>
            <a wire:navigate href="{{ route('employees.create') }}" class="inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl bg-white text-emerald-700 text-sm font-bold shadow-md hover:bg-emerald-50 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Tambah Karyawan
            </a>
        </div>
    </div>

    {{-- Stats --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3">
        <div class="rounded-2xl bg-white border border-gray-100 shadow-sm p-4">
            <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Total</p>
            <p class="mt-1 text-2xl font-black text-slate-800">{{ $stats['total'] }}</p>
        </div>
        <div class="rounded-2xl bg-white border border-gray-100 shadow-sm p-4">
            <p class="text-[10px] font-bold uppercase tracking-wider text-emerald-500">Aktif</p>
            <p class="mt-1 text-2xl font-black text-emerald-700">{{ $stats['active'] }}</p>
        </div>
        <div class="rounded-2xl bg-white border border-gray-100 shadow-sm p-4">
            <p class="text-[10px] font-bold uppercase tracking-wider text-emerald-600">PT NMF</p>
            <p class="mt-1 text-2xl font-black text-slate-800">{{ $stats['pt'] }}</p>
        </div>
        <div class="rounded-2xl bg-white border border-gray-100 shadow-sm p-4">
            <p class="text-[10px] font-bold uppercase tracking-wider text-sky-600">Apotek</p>
            <p class="mt-1 text-2xl font-black text-slate-800">{{ $stats['apotek'] }}</p>
        </div>
    </div>

    {{-- Filters --}}
    <div class="card p-4 sm:p-5 bg-white border border-gray-100 rounded-2xl shadow-sm">
        <form method="GET" action="{{ route('employees.index') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3 items-end">
            <div class="lg:col-span-2">
                <label class="form-label text-xs font-semibold text-gray-500 mb-1 block">Cari</label>
                <input type="text" name="q" value="{{ request('q') }}" class="form-input text-sm rounded-xl" placeholder="Nama, kode, jabatan, telepon...">
            </div>
            <div>
                <label class="form-label text-xs font-semibold text-gray-500 mb-1 block">Lingkup Entitas</label>
                <select name="entity_scope" class="form-input text-sm rounded-xl">
                    <option value="">Semua</option>
                    @foreach($entityScopes as $key => $label)
                    <option value="{{ $key }}" {{ request('entity_scope') === $key ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="form-label text-xs font-semibold text-gray-500 mb-1 block">Status</label>
                <select name="status" class="form-input text-sm rounded-xl">
                    <option value="">Semua</option>
                    <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Aktif</option>
                    <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Nonaktif</option>
                </select>
            </div>
            <div class="flex gap-2">
                <button type="submit" class="btn btn-primary btn-sm flex-1">Filter</button>
                @if(request()->hasAny(['q', 'entity_scope', 'status']))
                <a wire:navigate href="{{ route('employees.index') }}" class="btn btn-secondary btn-sm">Reset</a>
                @endif
            </div>
        </form>
    </div>

    {{-- Grid cards --}}
    @if($employees->count())
    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4">
        @foreach($employees as $emp)
        <article class="group rounded-2xl bg-white border border-gray-100 shadow-sm hover:shadow-md hover:border-emerald-200/70 transition-all overflow-hidden">
            <div class="p-5">
                <div class="flex items-start gap-3.5">
                    @if($emp->photo_url)
                    <button type="button"
                            @click="openPhoto(@js($emp->photo_url), @js($emp->name))"
                            class="group/photo relative shrink-0 w-16 h-16 sm:w-[4.5rem] sm:h-[4.5rem] rounded-2xl border border-slate-100 bg-gradient-to-br from-slate-50 to-emerald-50/60 shadow-sm overflow-hidden flex items-center justify-center p-1 cursor-zoom-in focus:outline-none focus:ring-2 focus:ring-emerald-400 focus:ring-offset-2"
                            title="Lihat foto profil">
                        <img src="{{ $emp->photo_url }}" alt="{{ $emp->name }}"
                             class="w-full h-full object-contain object-center rounded-xl transition-transform duration-200 group-hover/photo:scale-105">
                        <span class="absolute inset-0 rounded-2xl bg-slate-900/0 group-hover/photo:bg-slate-900/25 transition-colors flex items-center justify-center">
                            <svg class="w-5 h-5 text-white opacity-0 group-hover/photo:opacity-100 transition-opacity drop-shadow" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0zM10 7v3m0 0v3m0-3h3m-3 0H7"/></svg>
                        </span>
                    </button>
                    @else
                    <div class="shrink-0 w-16 h-16 sm:w-[4.5rem] sm:h-[4.5rem] rounded-2xl border border-slate-100 bg-gradient-to-br from-slate-50 to-emerald-50/60 shadow-sm overflow-hidden flex items-center justify-center p-1">
                        <div class="w-full h-full rounded-xl bg-gradient-to-br from-emerald-500 to-teal-600 text-white flex items-center justify-center text-lg font-black">
                            {{ $emp->initials }}
                        </div>
                    </div>
                    @endif
                    <div class="min-w-0 flex-1">
                        <div class="flex items-start justify-between gap-2">
                            <div class="min-w-0">
                                <h3 class="font-bold text-slate-800 truncate group-hover:text-emerald-700 transition-colors">{{ $emp->name }}</h3>
                                <p class="text-xs text-slate-400 font-mono mt-0.5">{{ $emp->code }}</p>
                            </div>
                            @if($emp->is_active)
                            <span class="shrink-0 px-2 py-0.5 rounded-md text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-100">Aktif</span>
                            @else
                            <span class="shrink-0 px-2 py-0.5 rounded-md text-[10px] font-bold bg-slate-50 text-slate-500 border border-slate-100">Nonaktif</span>
                            @endif
                        </div>
                        <p class="mt-1.5 text-sm text-slate-600 font-medium truncate">{{ $emp->position ?: '— Tanpa jabatan —' }}</p>
                    </div>
                </div>

                <div class="mt-4 flex flex-wrap gap-1.5">
                    @if($emp->entity_scope === 'pt')
                    <span class="px-2 py-0.5 rounded-md text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-100">PT NUR MADANI FARMA</span>
                    @elseif($emp->entity_scope === 'apotek')
                    <span class="px-2 py-0.5 rounded-md text-[10px] font-bold bg-sky-50 text-sky-700 border border-sky-100">APOTEK ALMAIRA</span>
                    @else
                    <span class="px-2 py-0.5 rounded-md text-[10px] font-bold bg-violet-50 text-violet-700 border border-violet-100">PT &amp; APOTEK</span>
                    @endif
                    <span class="px-2 py-0.5 rounded-md text-[10px] font-bold bg-slate-50 text-slate-600 border border-slate-100">{{ $emp->salaries_count }} slip gaji</span>
                </div>

                <div class="mt-4 space-y-1.5 text-xs text-slate-500">
                    <p class="truncate"><span class="text-slate-400">Telepon:</span> <span class="font-semibold text-slate-600">{{ $emp->phone ?: '—' }}</span></p>
                    <p class="truncate"><span class="text-slate-400">Email:</span> <span class="font-semibold text-slate-600">{{ $emp->email ?: '—' }}</span></p>
                    @if($emp->join_date)
                    <p><span class="text-slate-400">Masuk:</span> <span class="font-semibold text-slate-600">{{ $emp->join_date->format('d/m/Y') }}</span></p>
                    @endif
                </div>
            </div>

            <div class="px-4 py-3 border-t border-slate-50 bg-slate-50/60 flex items-center justify-between gap-2">
                <a wire:navigate href="{{ route('employees.show', $emp) }}" class="text-xs font-bold text-emerald-700 hover:text-emerald-800">Detail</a>
                <div class="flex items-center gap-1">
                    <a wire:navigate href="{{ route('employees.edit', $emp) }}" class="btn btn-icon btn-sm btn-secondary" title="Edit">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                    </a>
                    <form method="POST" action="{{ route('employees.toggle-status', $emp) }}">
                        @csrf @method('PATCH')
                        <button type="submit" class="btn btn-icon btn-sm {{ $emp->is_active ? 'bg-amber-50 text-amber-600 hover:bg-amber-100' : 'bg-emerald-50 text-emerald-600 hover:bg-emerald-100' }}" title="{{ $emp->is_active ? 'Nonaktifkan' : 'Aktifkan' }}">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/></svg>
                        </button>
                    </form>
                    <form id="del-emp-{{ $emp->id }}" method="POST" action="{{ route('employees.destroy', $emp) }}">
                        @csrf @method('DELETE')
                    </form>
                    <button type="button" @click="confirm('del-emp-{{ $emp->id }}')" class="btn btn-icon btn-sm bg-red-50 text-red-500 hover:bg-red-100" title="Hapus">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                    </button>
                </div>
            </div>
        </article>
        @endforeach
    </div>

    @if($employees->hasPages())
    <div class="card px-5 py-4 bg-white border border-gray-100 rounded-2xl shadow-sm">
        {{ $employees->links() }}
    </div>
    @endif
    @else
    <div class="card rounded-2xl border border-dashed border-slate-200 bg-white py-16 text-center">
        <div class="mx-auto w-14 h-14 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center mb-3">
            <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
        </div>
        <p class="font-bold text-slate-700">Belum ada data karyawan</p>
        <p class="text-sm text-slate-400 mt-1">Tambahkan karyawan untuk mulai membuat slip gaji.</p>
        <a wire:navigate href="{{ route('employees.create') }}" class="btn btn-primary mt-4 inline-flex">Tambah Karyawan</a>
    </div>
    @endif

    {{-- Lightbox foto profil --}}
    <div x-show="photoOpen"
         x-cloak
         class="fixed inset-0 z-[120] flex items-center justify-center p-4 sm:p-8"
         role="dialog"
         aria-modal="true"
         aria-label="Pratinjau foto profil">
        <div class="absolute inset-0 bg-slate-950/70 backdrop-blur-sm"
             @click="closePhoto()"
             x-show="photoOpen"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-end="opacity-0"></div>

        <div class="relative z-10 w-full max-w-lg"
             x-show="photoOpen"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 scale-95 translate-y-2"
             x-transition:enter-end="opacity-100 scale-100 translate-y-0"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-end="opacity-0 scale-95"
             @click.stop>
            <div class="overflow-hidden rounded-2xl bg-white shadow-2xl shadow-slate-950/30 border border-white/20">
                <div class="flex items-center justify-between gap-3 px-4 py-3 border-b border-slate-100 bg-gradient-to-r from-slate-50 to-emerald-50/40">
                    <div class="min-w-0">
                        <p class="text-[10px] font-bold uppercase tracking-[0.16em] text-emerald-600">Foto Profil</p>
                        <p class="text-sm font-bold text-slate-800 truncate" x-text="photoName"></p>
                    </div>
                    <button type="button"
                            @click="closePhoto()"
                            class="shrink-0 w-9 h-9 rounded-xl bg-white border border-slate-200 text-slate-500 hover:text-slate-800 hover:border-slate-300 flex items-center justify-center transition-colors"
                            aria-label="Tutup">
                        <svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
                <div class="bg-gradient-to-br from-slate-50 via-white to-emerald-50/50 p-4 sm:p-6 flex items-center justify-center min-h-[280px] sm:min-h-[360px]">
                    <img :src="photoUrl" :alt="photoName"
                         class="max-h-[70vh] max-w-full w-auto h-auto object-contain rounded-xl shadow-md shadow-slate-900/10 bg-white">
                </div>
                <div class="px-4 py-3 border-t border-slate-100 bg-slate-50/70 text-center">
                    <p class="text-[11px] text-slate-400">Klik di luar foto atau tekan Esc untuk menutup</p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
