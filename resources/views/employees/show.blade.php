@extends('layouts.app')
@section('title', $employee->name)
@section('page-title', 'Master Karyawan')

@section('breadcrumb')
<svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"/></svg>
<a wire:navigate href="{{ route('employees.index') }}" class="hover:text-gray-600">Master Karyawan</a>
<svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"/></svg>
<span class="text-gray-600 font-medium">Detail</span>
@endsection

@section('content')
<div class="animate-in max-w-5xl mx-auto space-y-5"
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
    <div class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-emerald-700 via-emerald-600 to-teal-600 text-white shadow-lg">
        <div class="absolute -right-8 -top-8 w-40 h-40 rounded-full bg-white/10 blur-2xl"></div>
        <div class="relative p-6 sm:p-7 flex flex-col sm:flex-row sm:items-center gap-5">
            @if($employee->photo_url)
            <button type="button"
                    @click="openPhoto(@js($employee->photo_url), @js($employee->name))"
                    class="group/photo relative shrink-0 w-24 h-24 rounded-2xl border-2 border-white/35 bg-white/15 shadow overflow-hidden flex items-center justify-center p-1.5 cursor-zoom-in focus:outline-none focus:ring-2 focus:ring-white/70"
                    title="Lihat foto profil">
                <img src="{{ $employee->photo_url }}" alt="{{ $employee->name }}"
                     class="max-w-full max-h-full w-auto h-auto object-contain object-center rounded-xl transition-transform duration-200 group-hover/photo:scale-105">
                <span class="absolute inset-0 rounded-2xl bg-slate-950/0 group-hover/photo:bg-slate-950/30 transition-colors flex items-center justify-center">
                    <svg class="w-6 h-6 text-white opacity-0 group-hover/photo:opacity-100 transition-opacity drop-shadow" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0zM10 7v3m0 0v3m0-3h3m-3 0H7"/></svg>
                </span>
            </button>
            @else
            <div class="shrink-0 w-24 h-24 rounded-2xl border-2 border-white/35 bg-white/15 shadow overflow-hidden flex items-center justify-center p-1.5">
                <div class="w-full h-full rounded-xl bg-white/20 flex items-center justify-center text-2xl font-black">{{ $employee->initials }}</div>
            </div>
            @endif
            <div class="flex-1 min-w-0">
                <p class="text-[11px] font-bold uppercase tracking-[0.16em] text-emerald-100/80">{{ $employee->code }}</p>
                <h2 class="text-2xl font-extrabold tracking-tight truncate">{{ $employee->name }}</h2>
                <p class="mt-1 text-sm text-emerald-50/90">{{ $employee->position ?: 'Tanpa jabatan' }} · {{ $employee->entity_label }}</p>
            </div>
            <div class="flex gap-2">
                <a wire:navigate href="{{ route('employees.edit', $employee) }}" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-white text-emerald-700 text-sm font-bold">Edit</a>
                <a wire:navigate href="{{ route('salaries.create', ['employee_id' => $employee->id]) }}" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-emerald-900/30 border border-white/20 text-white text-sm font-bold hover:bg-emerald-900/40">Catat Gaji</a>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div class="card p-5 rounded-2xl border border-gray-100 bg-white shadow-sm space-y-3">
            <h3 class="text-sm font-bold text-slate-800">Kontak & Identitas</h3>
            <div class="text-sm space-y-2 text-slate-600">
                <p><span class="text-slate-400 w-28 inline-block">Telepon</span> {{ $employee->phone ?: '—' }}</p>
                <p><span class="text-slate-400 w-28 inline-block">Email</span> {{ $employee->email ?: '—' }}</p>
                <p><span class="text-slate-400 w-28 inline-block">NIK</span> {{ $employee->nik ?: '—' }}</p>
                <p><span class="text-slate-400 w-28 inline-block">Gender</span> {{ $employee->gender ? ucfirst($employee->gender) : '—' }}</p>
                <p><span class="text-slate-400 w-28 inline-block">Tgl Masuk</span> {{ $employee->join_date?->format('d/m/Y') ?: '—' }}</p>
                <p><span class="text-slate-400 w-28 inline-block">Alamat</span> {{ $employee->address ?: '—' }}</p>
            </div>
        </div>
        <div class="card p-5 rounded-2xl border border-gray-100 bg-white shadow-sm space-y-3">
            <h3 class="text-sm font-bold text-slate-800">Rekening & Akun</h3>
            <div class="text-sm space-y-2 text-slate-600">
                <p><span class="text-slate-400 w-28 inline-block">Bank</span> {{ $employee->bank_name ?: '—' }}</p>
                <p><span class="text-slate-400 w-28 inline-block">No. Rek</span> {{ $employee->bank_account ?: '—' }}</p>
                <p><span class="text-slate-400 w-28 inline-block">a/n</span> {{ $employee->bank_holder ?: '—' }}</p>
                <p><span class="text-slate-400 w-28 inline-block">Akun Login</span> {{ $employee->user?->username ?: '—' }}</p>
                <p><span class="text-slate-400 w-28 inline-block">Status</span>
                    @if($employee->is_active)
                    <span class="text-emerald-600 font-bold">Aktif</span>
                    @else
                    <span class="text-slate-500 font-bold">Nonaktif</span>
                    @endif
                </p>
            </div>
        </div>
    </div>

    <div class="card overflow-hidden rounded-2xl border border-gray-100 bg-white shadow-sm">
        <div class="px-5 py-3.5 border-b border-gray-100 bg-slate-50/80 flex items-center justify-between">
            <h3 class="text-sm font-bold text-slate-800">Riwayat Slip Gaji Terbaru</h3>
            <a wire:navigate href="{{ route('salaries.index', ['employee_id' => $employee->id]) }}" class="text-xs font-bold text-emerald-700">Lihat semua</a>
        </div>
        <div class="overflow-x-auto">
            <table class="data-table w-full">
                <thead>
                    <tr class="text-xs uppercase text-slate-400 bg-slate-50/80">
                        <th class="px-4 py-3 text-left">Periode</th>
                        <th class="px-4 py-3 text-left">Entitas</th>
                        <th class="px-4 py-3 text-right">Gaji Bersih</th>
                        <th class="px-4 py-3 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($employee->salaries as $s)
                    <tr class="border-t border-slate-50">
                        <td class="px-4 py-3 text-sm font-semibold text-slate-700">
                            {{ \Carbon\Carbon::create(null, $s->period_month)->locale('id')->isoFormat('MMMM') }} {{ $s->period_year }}
                        </td>
                        <td class="px-4 py-3 text-xs font-bold">{{ $s->entity_label }}</td>
                        <td class="px-4 py-3 text-sm text-right font-bold text-emerald-700">Rp {{ number_format($s->net_salary, 0, ',', '.') }}</td>
                        <td class="px-4 py-3 text-center">
                            <a href="{{ route('salaries.print', $s) }}" target="_blank" class="text-xs font-bold text-blue-600">Cetak</a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="px-4 py-10 text-center text-slate-400 text-sm">Belum ada slip gaji</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

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
