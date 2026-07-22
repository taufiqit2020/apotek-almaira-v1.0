@extends('layouts.app')
@section('title', 'Gaji')
@section('page-title', 'Gaji Karyawan')

@section('breadcrumb')
<svg class="w-3 h-3 text-gray-400" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"/></svg>
<span class="text-gray-600 font-medium">Gaji Karyawan</span>
@endsection

@section('content')
<div class="animate-in">
    {{-- Header --}}
    <div class="page-header mb-6">
        <div>
            <h2 class="page-title text-2xl font-bold text-gray-800">Manajemen Gaji Karyawan</h2>
            <p class="page-subtitle text-gray-500">Kelola slip gaji karyawan untuk PT Nur Madani Farma &amp; Apotek Almaira</p>
        </div>
        <div class="flex flex-wrap gap-2">
            <a wire:navigate href="{{ route('employees.index') }}" class="btn btn-secondary flex items-center gap-2">
                Master Karyawan
            </a>
            <a wire:navigate href="{{ route('salaries.create') }}" class="btn btn-primary flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Catat Gaji Baru
            </a>
        </div>
    </div>

    {{-- Filters --}}
    <div class="card p-5 bg-white border border-gray-100 rounded-2xl shadow-sm mb-6">
        <form method="GET" action="{{ route('salaries.index') }}" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-5 gap-4 items-end">
            <div>
                <label class="form-label text-xs font-semibold text-gray-500 mb-1 block">Karyawan</label>
                <select name="employee_id" class="form-input text-xs">
                    <option value="">Semua Karyawan</option>
                    @foreach($employees as $emp)
                    <option value="{{ $emp->id }}" {{ request('employee_id') == $emp->id ? 'selected' : '' }}>{{ $emp->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="form-label text-xs font-semibold text-gray-500 mb-1 block">Entitas</label>
                <select name="entity" class="form-input text-xs">
                    <option value="">Semua Entitas</option>
                    @foreach($entities as $key => $label)
                    <option value="{{ $key }}" {{ request('entity') === $key ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="form-label text-xs font-semibold text-gray-500 mb-1 block">Bulan</label>
                <select name="month" class="form-input text-xs">
                    <option value="">Semua Bulan</option>
                    @for($m = 1; $m <= 12; $m++)
                    <option value="{{ $m }}" {{ request('month') == $m ? 'selected' : '' }}>
                        {{ Carbon\Carbon::create()->month($m)->locale('id')->isoFormat('MMMM') }}
                    </option>
                    @endfor
                </select>
            </div>
            <div>
                <label class="form-label text-xs font-semibold text-gray-500 mb-1 block">Tahun</label>
                <select name="year" class="form-input text-xs">
                    <option value="">Semua Tahun</option>
                    @for($y = date('Y') + 1; $y >= 2020; $y--)
                    <option value="{{ $y }}" {{ request('year') == $y ? 'selected' : '' }}>{{ $y }}</option>
                    @endfor
                </select>
            </div>
            <div class="flex gap-2">
                <button type="submit" class="btn btn-primary btn-sm flex-1">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2a1 1 0 01-.293.707L13 13.414V19a1 1 0 01-.553.894l-4 2A1 1 0 017 21v-7.586L3.293 6.707A1 1 0 013 6V4z"/></svg>
                    Filter
                </button>
                @if(request()->hasAny(['employee_id', 'entity', 'month', 'year']))
                <a wire:navigate href="{{ route('salaries.index') }}" class="btn btn-secondary btn-sm">Reset</a>
                @endif
            </div>
        </form>
    </div>

    {{-- Table --}}
    <div class="card overflow-hidden bg-white border border-gray-100 rounded-2xl shadow-sm">
        <div class="overflow-x-auto">
            <table class="data-table w-full text-left border-collapse min-w-[950px]">
                <thead>
                    <tr class="border-b border-gray-100 text-xs font-bold text-gray-400 uppercase tracking-wider bg-slate-50">
                        <th class="py-3 px-4 w-12">#</th>
                        <th class="py-3 px-4">Karyawan</th>
                        <th class="py-3 px-4">Entitas</th>
                        <th class="py-3 px-4">Periode</th>
                        <th class="py-3 px-4 text-right">Gaji Pokok</th>
                        <th class="py-3 px-4 text-right">Tunjangan</th>
                        <th class="py-3 px-4 text-right">Potongan</th>
                        <th class="py-3 px-4 text-right">Gaji Bersih</th>
                        <th class="py-3 px-4 text-center">Tgl Bayar</th>
                        <th class="py-3 px-4 text-center w-36">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($salaries as $i => $s)
                    <tr class="border-b border-gray-50 hover:bg-slate-50/50">
                        <td class="py-3 px-4 text-gray-400 text-sm">{{ $salaries->firstItem() + $i }}</td>
                        <td class="py-3 px-4">
                            <div>
                                <p class="font-bold text-slate-800 text-sm">{{ $s->employee_name }}</p>
                                <p class="text-xs text-slate-400 font-medium">{{ $s->employee?->code ?? '—' }} · {{ $s->employee_position }}</p>
                            </div>
                        </td>
                        <td class="py-3 px-4">
                            @if(($s->entity ?? 'pt') === 'apotek')
                            <span class="inline-flex px-2 py-0.5 rounded-md text-[10px] font-bold bg-sky-50 text-sky-700 border border-sky-100">APOTEK ALMAIRA</span>
                            @else
                            <span class="inline-flex px-2 py-0.5 rounded-md text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-100">PT NUR MADANI FARMA</span>
                            @endif
                        </td>
                        <td class="py-3 px-4 text-sm font-semibold text-slate-700">
                            {{ Carbon\Carbon::create(null, $s->period_month)->locale('id')->isoFormat('MMMM') }} {{ $s->period_year }}
                        </td>
                        <td class="py-3 px-4 text-right text-sm font-medium text-slate-600">
                            Rp {{ number_format($s->basic_salary, 0, ',', '.') }}
                        </td>
                        <td class="py-3 px-4 text-right text-sm font-medium text-emerald-600">
                            Rp {{ number_format($s->allowance, 0, ',', '.') }}
                        </td>
                        <td class="py-3 px-4 text-right text-sm font-medium text-rose-600">
                            Rp {{ number_format($s->deduction, 0, ',', '.') }}
                        </td>
                        <td class="py-3 px-4 text-right text-sm font-bold text-primary-600">
                            Rp {{ number_format($s->net_salary, 0, ',', '.') }}
                        </td>
                        <td class="py-3 px-4 text-center text-sm text-slate-500">
                            {{ $s->payment_date->format('d/m/Y') }}
                        </td>
                        <td class="py-3 px-4">
                            <div class="flex items-center justify-center gap-1.5">
                                <a href="{{ route('salaries.print', $s) }}" target="_blank" class="btn btn-icon btn-sm bg-blue-50 text-blue-600 hover:bg-blue-100" title="Cetak Slip Gaji">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                                </a>
                                <a wire:navigate href="{{ route('salaries.edit', $s) }}" class="btn btn-icon btn-sm btn-secondary" title="Edit">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                </a>
                                <form id="del-s-{{ $s->id }}" method="POST" action="{{ route('salaries.destroy', $s) }}" class="inline">
                                    @csrf @method('DELETE')
                                </form>
                                <button type="button" @click="confirm('del-s-{{ $s->id }}', 'Hapus Data Gaji', 'Hapus data gaji {{ addslashes($s->employee->name) }} periode {{ $s->period_month }}/{{ $s->period_year }}?')"
                                    class="btn btn-icon btn-sm bg-rose-50 text-rose-500 hover:bg-rose-100">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="text-center py-16 text-slate-400">
                            <svg class="w-14 h-14 mx-auto mb-3 text-slate-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <p class="font-semibold text-slate-500 text-sm">Belum ada riwayat gaji</p>
                            <a wire:navigate href="{{ route('salaries.create') }}" class="btn btn-primary btn-sm mt-3">Catat Gaji Pertama</a>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($salaries->hasPages())
        <div class="px-5 py-4 border-t border-gray-100 bg-slate-50/50">
            {{ $salaries->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
