@extends('layouts.app')
@section('title', 'Edit Gaji Karyawan')
@section('page-title', 'Gaji Karyawan')

@section('breadcrumb')
<svg class="w-3 h-3 text-gray-400" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"/></svg>
<a wire:navigate href="{{ route('salaries.index') }}" class="hover:text-primary-600 transition-colors">Gaji Karyawan</a>
<svg class="w-3 h-3 text-gray-400" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"/></svg>
<span class="text-gray-600 font-medium">Edit Data</span>
@endsection

@section('content')
@php
    $moneyInit = [
        'basic' => (int) old('basic_salary', $salary->basic_salary),
        'overtime' => (int) old('overtime', $salary->overtime),
        'allowance' => (int) old('allowance', $salary->allowance),
        'bpjs_kes' => (int) old('bpjs_kesehatan', $salary->bpjs_kesehatan),
        'bpjs_ket' => (int) old('bpjs_ketenagakerjaan', $salary->bpjs_ketenagakerjaan),
        'deduction' => (int) old('deduction', $salary->deduction),
    ];
@endphp
<div class="animate-in max-w-4xl mx-auto" x-data="window.salaryMoneyForm(@js($moneyInit))">
    <div class="page-header mb-6">
        <div>
            <h2 class="page-title text-2xl font-bold text-gray-800">Edit Data Gaji Karyawan</h2>
            <p class="page-subtitle text-gray-500">Memperbarui rincian pembayaran gaji karyawan apotek & distributor</p>
        </div>
        <a wire:navigate href="{{ route('salaries.index') }}" class="btn btn-secondary flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            Kembali
        </a>
    </div>

    <form action="{{ route('salaries.update', $salary) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
            {{-- Left Side: Info Karyawan & Periode --}}
            <div class="card p-6 bg-white border border-gray-100 rounded-2xl shadow-sm h-fit">
                <h3 class="text-lg font-bold text-gray-800 mb-4 border-b border-gray-100 pb-2">Karyawan & Periode</h3>
                
                <div class="space-y-4">
                    <div>
                        <label class="form-label text-xs font-semibold mb-1 block">Karyawan <span class="text-rose-500">*</span></label>
                        <select name="employee_id" class="form-input" required>
                            @foreach($employees as $emp)
                            <option value="{{ $emp->id }}" {{ (string) old('employee_id', $salary->employee_id) === (string) $emp->id ? 'selected' : '' }}>
                                {{ $emp->code }} — {{ $emp->name }}{{ $emp->position ? ' ('.$emp->position.')' : '' }}
                            </option>
                            @endforeach
                        </select>
                        <p class="text-[11px] text-slate-400 mt-1">Karyawan yang sama bisa punya slip terpisah untuk PT dan Apotek.</p>
                    </div>

                    <div>
                        <label class="form-label text-xs font-semibold mb-1 block">Entitas Perusahaan <span class="text-rose-500">*</span></label>
                        <select name="entity" class="form-input" required>
                            @foreach($entities as $key => $label)
                            <option value="{{ $key }}" {{ old('entity', $salary->entity ?? 'pt') === $key ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                        <p class="text-[11px] text-slate-400 mt-1">Edit satu slip per entitas. Untuk membuat slip PT + Apotek sekaligus, gunakan form Catat Gaji Baru.</p>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="form-label text-xs font-semibold mb-1 block">Bulan <span class="text-rose-500">*</span></label>
                            <select name="period_month" class="form-input" required>
                                @for($m = 1; $m <= 12; $m++)
                                <option value="{{ $m }}" {{ old('period_month', $salary->period_month) == $m ? 'selected' : '' }}>
                                    {{ Carbon\Carbon::create()->month($m)->locale('id')->isoFormat('MMMM') }}
                                </option>
                                @endfor
                            </select>
                        </div>
                        <div>
                            <label class="form-label text-xs font-semibold mb-1 block">Tahun <span class="text-rose-500">*</span></label>
                            <select name="period_year" class="form-input" required>
                                @for($y = date('Y') + 1; $y >= 2020; $y--)
                                <option value="{{ $y }}" {{ old('period_year', $salary->period_year) == $y ? 'selected' : '' }}>{{ $y }}</option>
                                @endfor
                            </select>
                        </div>
                    </div>

                    <div>
                        <label class="form-label text-xs font-semibold mb-1 block">Tanggal Pembayaran <span class="text-rose-500">*</span></label>
                        <input type="date" name="payment_date" value="{{ old('payment_date', $salary->payment_date->format('Y-m-d')) }}" class="form-input" required>
                    </div>

                    <div>
                        <label class="form-label text-xs font-semibold mb-1 block">Catatan / Keterangan</label>
                        <textarea name="notes" placeholder="Catatan opsional..." rows="3" class="form-input">{{ old('notes', $salary->notes) }}</textarea>
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
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                            Simpan Perubahan
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection
