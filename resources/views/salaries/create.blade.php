@extends('layouts.app')
@section('title', 'Catat Gaji Karyawan')
@section('page-title', 'Gaji Karyawan')

@section('breadcrumb')
<svg class="w-3 h-3 text-gray-400" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"/></svg>
<a wire:navigate href="{{ route('salaries.index') }}" class="hover:text-primary-600 transition-colors">Gaji Karyawan</a>
<svg class="w-3 h-3 text-gray-400" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"/></svg>
<span class="text-gray-600 font-medium">Catat Baru</span>
@endsection

@section('content')
@php
    $selectedEntities = array_values((array) old('entities', ['pt']));
    $moneyInit = [
        'basic' => (int) old('basic_salary', 0),
        'overtime' => (int) old('overtime', 0),
        'allowance' => (int) old('allowance', 0),
        'bpjs_kes' => (int) old('bpjs_kesehatan', 0),
        'bpjs_ket' => (int) old('bpjs_ketenagakerjaan', 0),
        'deduction' => (int) old('deduction', 0),
        'entities' => $selectedEntities,
    ];
@endphp
<div class="animate-in max-w-4xl mx-auto" x-data="window.salaryMoneyForm(@js($moneyInit))">
    <div class="page-header mb-6">
        <div>
            <h2 class="page-title text-2xl font-bold text-gray-800">Catat Gaji Karyawan Baru</h2>
            <p class="page-subtitle text-gray-500">Merekam slip gaji bulanan — bisa pilih PT, Apotek, atau keduanya sekaligus</p>
        </div>
        <a wire:navigate href="{{ route('salaries.index') }}" class="btn btn-secondary flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            Kembali
        </a>
    </div>

    <form action="{{ route('salaries.store') }}" method="POST">
        @csrf

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
            {{-- Left Side: Info Karyawan & Periode --}}
            <div class="card p-6 bg-white border border-gray-100 rounded-2xl shadow-sm h-fit">
                <h3 class="text-lg font-bold text-gray-800 mb-4 border-b border-gray-100 pb-2">Karyawan & Periode</h3>
                
                <div class="space-y-4">
                    <div>
                        <label class="form-label text-xs font-semibold mb-1 block">Karyawan <span class="text-rose-500">*</span></label>
                        <select name="employee_id" class="form-input" required>
                            <option value="">Pilih Karyawan</option>
                            @foreach($employees as $emp)
                            <option value="{{ $emp->id }}" {{ (string) old('employee_id', request('employee_id')) === (string) $emp->id ? 'selected' : '' }}>
                                {{ $emp->code }} — {{ $emp->name }}{{ $emp->position ? ' ('.$emp->position.')' : '' }}
                            </option>
                            @endforeach
                        </select>
                        <p class="text-[11px] text-slate-400 mt-1">
                            Data dari <a wire:navigate href="{{ route('employees.index') }}" class="text-emerald-600 font-semibold hover:underline">Master Karyawan</a>.
                            Karyawan yang sama bisa digaji di PT dan Apotek.
                        </p>
                    </div>

                    <div>
                        <label class="form-label text-xs font-semibold mb-1 block">Entitas Perusahaan <span class="text-rose-500">*</span></label>
                        <div class="grid grid-cols-1 gap-2">
                            @foreach($entities as $key => $label)
                            <label class="flex items-start gap-3 p-3 rounded-xl border cursor-pointer transition-colors"
                                   :class="entities.includes('{{ $key }}') ? 'border-emerald-300 bg-emerald-50/50' : 'border-slate-200 bg-white hover:border-emerald-200'">
                                <input type="checkbox" name="entities[]" value="{{ $key }}"
                                       class="mt-0.5 w-4 h-4 accent-emerald-600"
                                       x-model="entities">
                                <span>
                                    <span class="block text-sm font-bold text-slate-800">{{ $label }}</span>
                                    <span class="block text-[11px] text-slate-400 mt-0.5">
                                        {{ $key === 'pt' ? 'Slip gaji berlogo PT Nur Madani Farma' : 'Slip gaji berlogo Apotek Almaira' }}
                                    </span>
                                </span>
                            </label>
                            @endforeach
                        </div>
                        <p class="text-[11px] text-slate-400 mt-2">Centang satu atau keduanya — sistem membuat slip terpisah per entitas dengan komponen gaji yang sama.</p>
                        @error('entities')<p class="form-error mt-1">{{ $message }}</p>@enderror
                        @error('entities.*')<p class="form-error mt-1">{{ $message }}</p>@enderror
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="form-label text-xs font-semibold mb-1 block">Bulan <span class="text-rose-500">*</span></label>
                            <select name="period_month" class="form-input" required>
                                @for($m = 1; $m <= 12; $m++)
                                <option value="{{ $m }}" {{ old('period_month', date('n')) == $m ? 'selected' : '' }}>
                                    {{ Carbon\Carbon::create()->month($m)->locale('id')->isoFormat('MMMM') }}
                                </option>
                                @endfor
                            </select>
                        </div>
                        <div>
                            <label class="form-label text-xs font-semibold mb-1 block">Tahun <span class="text-rose-500">*</span></label>
                            <select name="period_year" class="form-input" required>
                                @for($y = date('Y') + 1; $y >= 2020; $y--)
                                <option value="{{ $y }}" {{ old('period_year', date('Y')) == $y ? 'selected' : '' }}>{{ $y }}</option>
                                @endfor
                            </select>
                        </div>
                    </div>

                    <div>
                        <label class="form-label text-xs font-semibold mb-1 block">Tanggal Pembayaran <span class="text-rose-500">*</span></label>
                        <input type="date" name="payment_date" value="{{ old('payment_date', date('Y-m-d')) }}" class="form-input" required>
                    </div>

                    <div>
                        <label class="form-label text-xs font-semibold mb-1 block">Catatan / Keterangan</label>
                        <textarea name="notes" placeholder="Catatan opsional..." rows="3" class="form-input">{{ old('notes') }}</textarea>
                    </div>
                </div>
            </div>

            {{-- Right Side: Rincian Gaji & Perhitungan --}}
            <div class="card p-6 bg-white border border-gray-100 rounded-2xl shadow-sm flex flex-col justify-between">
                <div>
                    <h3 class="text-lg font-bold text-gray-800 mb-4 border-b border-gray-100 pb-2">Komponen Keuangan</h3>
                    
                    @include('salaries._money-fields')
                </div>

                {{-- Grand Total display --}}
                <div class="mt-6 border-t border-gray-100 pt-4">
                    <div class="bg-emerald-50 border border-emerald-100 p-4 rounded-xl flex items-center justify-between gap-3">
                        <div class="min-w-0">
                            <span class="text-xs font-bold text-emerald-700/70 uppercase tracking-wider block">Gaji Bersih (Net)</span>
                            <span class="text-2xl sm:text-3xl font-extrabold text-emerald-700 tracking-tight" x-text="formatRupiah(net)"></span>
                        </div>
                        <div class="w-10 h-10 rounded-full bg-emerald-100 text-emerald-700 flex items-center justify-center shrink-0">
                            💰
                        </div>
                    </div>

                    <div class="flex justify-end gap-3 mt-6">
                        <a wire:navigate href="{{ route('salaries.index') }}" class="btn btn-secondary">Batal</a>
                        <button type="submit" class="btn btn-primary flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"/></svg>
                            Simpan Gaji
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection
