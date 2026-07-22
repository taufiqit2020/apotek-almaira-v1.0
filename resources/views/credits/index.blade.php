@extends('layouts.app')
@section('title', 'Kredit')
@section('page-title', 'Kredit & Piutang')

@section('breadcrumb')
<svg class="w-3 h-3 text-gray-400 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"/></svg>
<span class="text-gray-600 font-medium">Kredit & Piutang</span>
@endsection

@section('content')
@php
    $posOverdue = $posCredits->filter(fn ($s) => $s->isOverdue())->count();
    $mitraOverdue = $mitraCredits->filter(fn ($o) => $o->isCreditOverdue())->count();
@endphp

<div class="animate-in space-y-5 pb-4">

    {{-- Hero --}}
    <div class="rounded-2xl bg-gradient-to-br from-amber-600 via-orange-600 to-amber-700 p-5 sm:p-6 text-white shadow-lg shadow-amber-900/10 relative overflow-hidden">
        <div class="absolute top-0 right-0 w-56 h-56 bg-white/5 rounded-full -translate-y-1/2 translate-x-1/3 pointer-events-none"></div>
        <div class="relative flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
            <div class="flex items-start gap-4 min-w-0">
                <div class="w-12 h-12 rounded-xl bg-white/15 border border-white/20 flex items-center justify-center shrink-0">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <div class="min-w-0">
                    <h2 class="text-xl sm:text-2xl font-extrabold leading-tight">Kredit & Piutang Invoice</h2>
                    <p class="text-amber-50/90 text-sm mt-1">Belum lunas → Kredit · Sudah lunas → Laporan Invoice</p>
                    <div class="flex flex-wrap items-center gap-2 mt-3">
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-[11px] font-bold bg-white/15 border border-white/20">
                            <span class="w-1.5 h-1.5 rounded-full bg-amber-200"></span>
                            {{ $countKredit }} kredit aktif
                        </span>
                        @if($overdueCount > 0)
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-[11px] font-bold bg-red-500/25 border border-red-300/30 text-red-50">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                            {{ $overdueCount }} overdue
                        </span>
                        @endif
                    </div>
                </div>
            </div>
            <a wire:navigate href="{{ route('reports.index') }}?type=invoice_lunas"
               class="inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl bg-white text-amber-700 text-sm font-bold shadow-md hover:bg-amber-50 transition-colors shrink-0 whitespace-nowrap">
                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                Laporan Invoice Lunas
            </a>
        </div>
    </div>

    @if($overdueCount > 0)
    <div class="flex items-start gap-3 p-4 rounded-2xl bg-red-50 border border-red-200 text-red-800 shadow-sm">
        <div class="w-9 h-9 rounded-xl bg-red-100 flex items-center justify-center shrink-0">
            <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        </div>
        <div>
            <p class="text-sm font-bold">{{ $overdueCount }} tagihan overdue</p>
            <p class="text-xs text-red-700/80 mt-0.5">Segera follow up pelunasan — {{ $posOverdue }} POS · {{ $mitraOverdue }} Mitra</p>
        </div>
    </div>
    @endif

    {{-- Statistik --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
        <div class="card bg-white border border-amber-100 rounded-2xl p-4 shadow-sm relative overflow-hidden">
            <div class="absolute top-0 right-0 w-20 h-20 bg-amber-50 rounded-full -translate-y-1/2 translate-x-1/3 pointer-events-none"></div>
            <div class="relative flex items-start gap-3">
                <div class="w-10 h-10 rounded-xl bg-amber-100 flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5 text-amber-700" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2z"/></svg>
                </div>
                <div>
                    <p class="text-[10px] font-bold uppercase tracking-wider text-amber-600">Total Kredit Aktif</p>
                    <p class="text-xl sm:text-2xl font-extrabold text-gray-800 mt-0.5">Rp {{ number_format($totalKredit, 0, ',', '.') }}</p>
                    <p class="text-[11px] text-gray-400 mt-1">{{ $countKredit }} transaksi</p>
                </div>
            </div>
        </div>
        <div class="card bg-white border border-emerald-100 rounded-2xl p-4 shadow-sm relative overflow-hidden">
            <div class="absolute top-0 right-0 w-20 h-20 bg-emerald-50 rounded-full -translate-y-1/2 translate-x-1/3 pointer-events-none"></div>
            <div class="relative flex items-start gap-3">
                <div class="w-10 h-10 rounded-xl bg-emerald-100 flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5 text-emerald-700" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <div>
                    <p class="text-[10px] font-bold uppercase tracking-wider text-emerald-600">Invoice Lunas (POS)</p>
                    <p class="text-xl sm:text-2xl font-extrabold text-gray-800 mt-0.5">{{ $posPaidCountMonth }}</p>
                    <p class="text-[11px] text-gray-400 mt-1">Bulan {{ \Carbon\Carbon::create(null, now()->month)->locale('id')->translatedFormat('F') }}</p>
                </div>
            </div>
        </div>
        <div class="card bg-white border border-sky-100 rounded-2xl p-4 shadow-sm relative overflow-hidden">
            <div class="absolute top-0 right-0 w-20 h-20 bg-sky-50 rounded-full -translate-y-1/2 translate-x-1/3 pointer-events-none"></div>
            <div class="relative flex items-start gap-3">
                <div class="w-10 h-10 rounded-xl bg-sky-100 flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5 text-sky-700" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                </div>
                <div>
                    <p class="text-[10px] font-bold uppercase tracking-wider text-sky-600">Invoice Lunas (Mitra)</p>
                    <p class="text-xl sm:text-2xl font-extrabold text-gray-800 mt-0.5">{{ $mitraPaidCountMonth }}</p>
                    <p class="text-[11px] text-gray-400 mt-1">PO mitra tempo · bulan ini</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Tab --}}
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-1.5 flex gap-1">
        <a href="{{ route('credits.index', ['tab' => 'kredit']) }}"
           class="flex-1 inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl text-sm font-bold transition-all
                  {{ $tab === 'kredit' ? 'bg-amber-500 text-white shadow-md shadow-amber-500/25' : 'text-gray-500 hover:bg-gray-50 hover:text-gray-700' }}">
            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            Kredit Belum Lunas
            @if($countKredit > 0)
            <span class="inline-flex min-w-[1.25rem] h-5 px-1.5 items-center justify-center rounded-full text-[10px] font-extrabold
                         {{ $tab === 'kredit' ? 'bg-white/25 text-white' : 'bg-amber-100 text-amber-700' }}">{{ $countKredit }}</span>
            @endif
        </a>
        <a href="{{ route('credits.index', ['tab' => 'lunas', 'month' => $filterMonth, 'year' => $filterYear]) }}"
           class="flex-1 inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl text-sm font-bold transition-all
                  {{ $tab === 'lunas' ? 'bg-emerald-600 text-white shadow-md shadow-emerald-600/25' : 'text-gray-500 hover:bg-gray-50 hover:text-gray-700' }}">
            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            Invoice Lunas
            @if(($posPaidCountMonth + $mitraPaidCountMonth) > 0)
            <span class="inline-flex min-w-[1.25rem] h-5 px-1.5 items-center justify-center rounded-full text-[10px] font-extrabold
                         {{ $tab === 'lunas' ? 'bg-white/25 text-white' : 'bg-emerald-100 text-emerald-700' }}">{{ $posPaidCountMonth + $mitraPaidCountMonth }}</span>
            @endif
        </a>
    </div>

    @if($tab === 'kredit')
    <div class="space-y-5">

        {{-- Kredit POS --}}
        <div class="card overflow-hidden bg-white border border-gray-100 rounded-2xl shadow-sm">
            <div class="px-5 py-3.5 border-b border-gray-100 bg-slate-50/80 flex flex-wrap items-center justify-between gap-2">
                <div class="flex items-center gap-2.5">
                    <div class="w-8 h-8 rounded-lg bg-blue-100 flex items-center justify-center">
                        <svg class="w-4 h-4 text-blue-700" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                    </div>
                    <div>
                        <h3 class="text-sm font-bold text-gray-800">Kredit POS (Pelanggan CRM)</h3>
                        <p class="text-[11px] text-gray-400">{{ $posCredits->count() }} invoice belum lunas</p>
                    </div>
                </div>
                @if($posOverdue > 0)
                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-lg text-[10px] font-bold bg-red-100 text-red-700 border border-red-200">
                    {{ $posOverdue }} overdue
                </span>
                @endif
            </div>
            <div class="overflow-x-auto">
                <table class="data-table w-full">
                    <thead><tr><th>Invoice</th><th>Pelanggan</th><th>Jatuh Tempo</th><th>Total</th><th class="text-center">Aksi</th></tr></thead>
                    <tbody>
                        @forelse($posCredits as $s)
                        <tr class="hover:bg-gray-50/50 {{ $s->isOverdue() ? 'bg-red-50/40' : '' }}">
                            <td>
                                <p class="font-mono text-xs font-bold text-gray-800">{{ $s->invoice_no }}</p>
                                @if($s->isOverdue())
                                <span class="inline-flex items-center gap-1 mt-1 px-1.5 py-0.5 rounded text-[9px] font-bold bg-red-100 text-red-700">Overdue</span>
                                @endif
                            </td>
                            <td>
                                <p class="font-semibold text-sm text-gray-800">{{ $s->customer_name ?? $s->customer?->name ?? '—' }}</p>
                            </td>
                            <td>
                                <span class="inline-flex items-center gap-1 text-sm {{ $s->isOverdue() ? 'text-red-600 font-bold' : 'text-gray-600' }}">
                                    <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                    {{ $s->due_date?->format('d/m/Y') ?? '—' }}
                                </span>
                            </td>
                            <td class="font-bold text-emerald-700 text-sm whitespace-nowrap">Rp {{ number_format($s->total, 0, ',', '.') }}</td>
                            <td class="text-center">
                                <a wire:navigate href="{{ route('invoices.index') }}"
                                   class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg text-xs font-bold text-blue-700 bg-blue-50 border border-blue-100 hover:bg-blue-100 transition-colors">
                                    Lunasi di Invoice
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="py-12 text-center">
                                <div class="w-14 h-14 mx-auto mb-3 rounded-2xl bg-slate-100 flex items-center justify-center">
                                    <svg class="w-7 h-7 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                </div>
                                <p class="text-sm font-bold text-gray-500">Tidak ada kredit POS</p>
                                <p class="text-xs text-gray-400 mt-1">Semua invoice pelanggan sudah lunas</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Kredit Mitra --}}
        <div class="card overflow-hidden bg-white border border-gray-100 rounded-2xl shadow-sm">
            <div class="px-5 py-3.5 border-b border-gray-100 bg-slate-50/80 flex flex-wrap items-center justify-between gap-2">
                <div class="flex items-center gap-2.5">
                    <div class="w-8 h-8 rounded-lg bg-emerald-100 flex items-center justify-center">
                        <svg class="w-4 h-4 text-emerald-700" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                    </div>
                    <div>
                        <h3 class="text-sm font-bold text-gray-800">Kredit Mitra (PO Invoice Tempo)</h3>
                        <p class="text-[11px] text-gray-400">{{ $mitraCredits->count() }} PO belum lunas · lunasi langsung di sini</p>
                    </div>
                </div>
                @if($mitraOverdue > 0)
                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-lg text-[10px] font-bold bg-red-100 text-red-700 border border-red-200">
                    {{ $mitraOverdue }} overdue
                </span>
                @endif
            </div>
            <div class="overflow-x-auto">
                <table class="data-table w-full">
                    <thead><tr><th>No PO</th><th>Mitra</th><th>Jatuh Tempo</th><th>Total</th><th>Pelunasan</th></tr></thead>
                    <tbody>
                        @forelse($mitraCredits as $o)
                        <tr class="hover:bg-gray-50/50 align-top {{ $o->isCreditOverdue() ? 'bg-red-50/40' : '' }}">
                            <td class="pt-4">
                                <p class="font-mono text-xs font-bold text-gray-800">{{ $o->order_no }}</p>
                                @if($o->isCreditOverdue())
                                <span class="inline-flex items-center gap-1 mt-1 px-1.5 py-0.5 rounded text-[9px] font-bold bg-red-100 text-red-700">Overdue</span>
                                @endif
                            </td>
                            <td class="pt-4">
                                <p class="font-semibold text-sm text-gray-800">{{ $o->partner?->name }}</p>
                                <p class="text-[11px] text-gray-400 mt-0.5">{{ $o->partner?->code ?? '—' }}</p>
                            </td>
                            <td class="pt-4">
                                <span class="inline-flex items-center gap-1 text-sm {{ $o->isCreditOverdue() ? 'text-red-600 font-bold' : 'text-gray-600' }}">
                                    <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                    {{ $o->due_date?->format('d/m/Y') ?? '—' }}
                                </span>
                            </td>
                            <td class="pt-4 font-bold text-emerald-700 text-sm whitespace-nowrap">Rp {{ number_format($o->total, 0, ',', '.') }}</td>
                            <td class="pt-3 pb-4 min-w-[280px]">
                                <form action="{{ route('credits.pay-mitra', $o) }}" method="POST" enctype="multipart/form-data"
                                      class="mitra-settle-form rounded-xl border border-emerald-200/80 bg-gradient-to-br from-white to-emerald-50/40 p-3.5 space-y-3 shadow-sm"
                                      onsubmit="return confirmSettleMitra(this)">
                                    @csrf
                                    <p class="text-[10px] font-bold uppercase tracking-wider text-emerald-800">Pelunasan PO</p>
                                    <div class="flex flex-wrap items-center gap-2">
                                        <select name="settlement_method"
                                                class="settlement-method-select form-input py-2 text-xs w-32 rounded-lg border-gray-200 bg-white"
                                                required onchange="toggleSettlementProof(this)">
                                            <option value="transfer">Transfer</option>
                                            <option value="cash">Tunai</option>
                                        </select>
                                        <button type="submit"
                                                class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold shadow-md shadow-emerald-600/20 transition-colors whitespace-nowrap">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                            Lunas
                                        </button>
                                    </div>
                                    <div class="settlement-proof-wrap rounded-lg border border-sky-200 bg-sky-50/90 p-2.5">
                                        <label class="flex items-center gap-1.5 text-[10px] font-bold uppercase tracking-wider text-sky-800 mb-1.5">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                            Bukti Transfer
                                        </label>
                                        <input type="file" name="settlement_proof"
                                               accept=".jpg,.jpeg,.png,.webp,.pdf"
                                               class="settlement-proof-input block w-full text-[11px] text-gray-600 file:mr-2 file:py-1.5 file:px-2.5 file:rounded-lg file:border-0 file:bg-sky-600 file:text-white file:font-bold file:text-[10px]">
                                        <p class="text-[10px] text-sky-700/80 mt-1">Wajib jika Transfer · JPG, PNG, WEBP, PDF · maks. 4 MB</p>
                                    </div>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="py-12 text-center">
                                <div class="w-14 h-14 mx-auto mb-3 rounded-2xl bg-slate-100 flex items-center justify-center">
                                    <svg class="w-7 h-7 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                </div>
                                <p class="text-sm font-bold text-gray-500">Tidak ada kredit mitra</p>
                                <p class="text-xs text-gray-400 mt-1">Semua PO invoice tempo sudah lunas</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    @else
    @php
        $filterLabel = \Carbon\Carbon::create($filterYear, $filterMonth, 1)->locale('id')->translatedFormat('F Y');
    @endphp

    {{-- Filter & ringkasan --}}
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-4">
        <form method="GET" class="lg:col-span-8 card p-4 bg-white border border-gray-100 rounded-2xl shadow-sm">
            <input type="hidden" name="tab" value="lunas">
            <div class="flex flex-col sm:flex-row sm:flex-wrap sm:items-end gap-3">
                <div>
                    <label class="block text-[10px] font-bold uppercase tracking-wider text-gray-400 mb-1.5">Bulan</label>
                    <select name="month" class="form-input rounded-xl min-w-[140px]">
                        @for($m=1;$m<=12;$m++)
                        <option value="{{ $m }}" @selected($filterMonth == $m)>{{ \Carbon\Carbon::create(null, $m)->locale('id')->translatedFormat('F') }}</option>
                        @endfor
                    </select>
                </div>
                <div>
                    <label class="block text-[10px] font-bold uppercase tracking-wider text-gray-400 mb-1.5">Tahun</label>
                    <input type="number" name="year" value="{{ $filterYear }}" class="form-input w-28 rounded-xl">
                </div>
                <div class="flex flex-wrap gap-2 sm:pb-0.5">
                    <button type="submit" class="btn btn-primary btn-sm inline-flex items-center gap-1.5">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/></svg>
                        Terapkan Filter
                    </button>
                    <a href="{{ route('reports.index') }}?type=invoice_lunas"
                       class="btn btn-secondary btn-sm inline-flex items-center gap-1.5">
                        Laporan Lengkap
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    </a>
                </div>
            </div>
        </form>

        <div class="lg:col-span-4 card p-4 bg-gradient-to-br from-emerald-600 to-teal-600 rounded-2xl text-white shadow-lg shadow-emerald-900/10">
            <p class="text-[10px] font-bold uppercase tracking-wider text-emerald-100">Total Lunas — {{ $filterLabel }}</p>
            <p class="text-2xl font-extrabold mt-1">Rp {{ number_format($paidTotalAmount, 0, ',', '.') }}</p>
            <p class="text-xs text-emerald-50/90 mt-1">{{ $paidTimeline->count() }} transaksi · POS {{ $posPaid->count() }} · Mitra {{ $mitraPaid->count() }}</p>
        </div>
    </div>

    {{-- Daftar gabungan Invoice Lunas --}}
    <div class="card overflow-hidden bg-white border border-gray-100 rounded-2xl shadow-sm">
        <div class="px-5 py-3.5 border-b border-gray-100 bg-slate-50/80 flex flex-wrap items-center justify-between gap-2">
            <div class="flex items-center gap-2.5">
                <div class="w-8 h-8 rounded-lg bg-emerald-100 flex items-center justify-center">
                    <svg class="w-4 h-4 text-emerald-700" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <div>
                    <h3 class="text-sm font-bold text-gray-800">Daftar Invoice Lunas</h3>
                    <p class="text-[11px] text-gray-400">POS + Mitra PO · diurutkan tanggal pelunasan terbaru</p>
                </div>
            </div>
            <span class="text-[11px] font-bold text-gray-500">{{ $filterLabel }}</span>
        </div>

        @if($paidTimeline->isEmpty())
        <div class="py-16 px-6 text-center">
            <div class="w-16 h-16 mx-auto mb-4 rounded-2xl bg-slate-100 flex items-center justify-center">
                <svg class="w-8 h-8 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
            </div>
            <p class="text-sm font-bold text-gray-500">Belum ada invoice lunas</p>
            <p class="text-xs text-gray-400 mt-1 max-w-sm mx-auto">Pelunasan PO mitra atau invoice POS akan otomatis muncul di sini setelah ditandai lunas.</p>
        </div>
        @else
        <div class="divide-y divide-gray-100">
            @foreach($paidTimeline as $row)
            @php
                $isHighlight = $highlightRef && $highlightRef === $row['ref'];
                $isPos = $row['type'] === 'pos';
                $rowDomId = 'paid-' . md5($row['ref']);
            @endphp
            <div id="{{ $rowDomId }}"
                 class="p-4 sm:p-5 flex flex-col sm:flex-row sm:items-center gap-4 transition-all
                        {{ $isHighlight ? 'bg-emerald-50 ring-2 ring-inset ring-emerald-300' : 'hover:bg-gray-50/60' }}">
                <div class="flex items-start gap-3.5 min-w-0 flex-1">
                    <div class="w-11 h-11 rounded-xl flex items-center justify-center shrink-0 border
                        {{ $isPos ? 'bg-blue-50 border-blue-100 text-blue-700' : 'bg-sky-50 border-sky-100 text-sky-700' }}">
                        @if($isPos)
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                        @else
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5"/></svg>
                        @endif
                    </div>
                    <div class="min-w-0">
                        <div class="flex flex-wrap items-center gap-2">
                            <p class="font-mono text-xs font-bold text-gray-800">{{ $row['title'] }}</p>
                            <span class="inline-flex px-2 py-0.5 rounded-md text-[10px] font-bold border
                                {{ $isPos ? 'bg-blue-50 text-blue-700 border-blue-200' : 'bg-sky-50 text-sky-700 border-sky-200' }}">
                                {{ $isPos ? 'POS' : 'Mitra PO' }}
                            </span>
                            @if($isHighlight)
                            <span class="inline-flex px-2 py-0.5 rounded-md text-[10px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-200">Baru dilunasi</span>
                            @endif
                        </div>
                        <p class="text-sm font-semibold text-gray-700 mt-0.5 truncate">{{ $row['subtitle'] }}</p>
                        <div class="flex flex-wrap items-center gap-x-3 gap-y-1 mt-1.5 text-[11px] text-gray-400">
                            <span class="inline-flex items-center gap-1">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                {{ $row['settled_at']?->timezone('Asia/Makassar')->format('d/m/Y H:i') }}
                            </span>
                            @if($row['settler'])
                            <span>Oleh {{ $row['settler'] }}</span>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="flex flex-col sm:items-end gap-2 shrink-0 sm:text-right">
                    <p class="text-lg font-extrabold text-emerald-700 whitespace-nowrap">Rp {{ number_format($row['total'], 0, ',', '.') }}</p>
                    <div class="flex flex-wrap items-center gap-1.5 sm:justify-end">
                        <span class="inline-flex px-2 py-0.5 rounded-lg text-[10px] font-bold border
                            {{ $row['method'] === 'cash' ? 'bg-amber-50 text-amber-700 border-amber-200' : 'bg-sky-50 text-sky-700 border-sky-200' }}">
                            {{ $row['method'] === 'cash' ? 'Tunai' : 'Transfer' }}
                        </span>
                        @if($row['proof'])
                        <a href="{{ asset('storage/' . $row['proof']) }}" target="_blank" rel="noopener"
                           class="inline-flex items-center gap-1 px-2 py-0.5 rounded-lg text-[10px] font-bold text-sky-700 bg-sky-50 border border-sky-200 hover:bg-sky-100 transition-colors">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                            Bukti Transfer
                        </a>
                        @endif
                    </div>
                </div>
            </div>
            @endforeach
        </div>
        @endif
    </div>
    @endif
</div>
@endsection

@push('scripts')
<script>
function toggleSettlementProof(selectEl) {
    const form = selectEl.closest('.mitra-settle-form');
    if (!form) return;
    const wrap = form.querySelector('.settlement-proof-wrap');
    const input = form.querySelector('.settlement-proof-input');
    const isTransfer = selectEl.value === 'transfer';
    if (wrap) wrap.classList.toggle('hidden', !isTransfer);
    if (input) {
        input.required = isTransfer;
        if (!isTransfer) input.value = '';
    }
}

function confirmSettleMitra(form) {
    const method = form.querySelector('[name="settlement_method"]')?.value;
    const proof = form.querySelector('.settlement-proof-input');
    if (method === 'transfer' && proof && !proof.files.length) {
        alert('Unggah bukti transfer terlebih dahulu.');
        proof.focus();
        return false;
    }
    return confirm('Lunasi kredit PO ini? Setelah lunas, PO otomatis masuk daftar Invoice Lunas.');
}

document.querySelectorAll('.settlement-method-select').forEach(function (sel) {
    toggleSettlementProof(sel);
});

@if($tab === 'lunas' && !empty($highlightRef))
document.addEventListener('DOMContentLoaded', function () {
    const el = document.getElementById('paid-{{ md5($highlightRef) }}');
    if (el) el.scrollIntoView({ behavior: 'smooth', block: 'center' });
});
@endif
</script>
@endpush
