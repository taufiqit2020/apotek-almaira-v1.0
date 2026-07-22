@extends('layouts.app')
@section('title', 'PO Mitra')
@section('page-title', 'PO Mitra')

@section('breadcrumb')
<svg class="w-3 h-3 text-gray-400" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"/></svg>
<span class="text-gray-600 font-medium">PO Mitra</span>
@endsection

@section('content')
<div class="animate-in">
    <div class="page-header mb-6">
        <div>
            <h2 class="page-title text-2xl font-bold text-gray-800">Purchase Order Mitra</h2>
            <p class="page-subtitle text-gray-500">Order B2B dari e-catalog mitra</p>
        </div>
    </div>

    @if($openCount > 0)
    <div class="mb-4 px-4 py-3 rounded-xl bg-amber-50 border border-amber-200 text-amber-800 text-sm font-semibold">
        {{ $openCount }} PO masih terbuka (diajukan / dikonfirmasi)
    </div>
    @endif

    <form method="GET" class="card p-4 mb-6 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3 bg-white border border-gray-100 rounded-2xl shadow-sm">
        <div class="lg:col-span-2">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari no PO / mitra..." class="form-input">
        </div>
        <select name="status" class="form-input">
            <option value="">Semua Status</option>
            @foreach(\App\Models\PartnerOrder::statusOptions() as $k => $l)
            <option value="{{ $k }}" @selected(request('status') === $k)>{{ $l }}</option>
            @endforeach
        </select>
        <select name="payment_status" class="form-input">
            <option value="">Semua Bayar</option>
            @foreach(\App\Models\PartnerOrder::paymentStatusOptions() as $k => $l)
            <option value="{{ $k }}" @selected(request('payment_status') === $k)>{{ $l }}</option>
            @endforeach
        </select>
        <div class="flex gap-2">
            <button type="submit" class="btn btn-primary btn-sm flex-1">Filter</button>
            <a wire:navigate href="{{ route('partner-orders.index') }}" class="btn btn-secondary btn-sm">Reset</a>
        </div>
    </form>

    <div class="card overflow-hidden bg-white border border-gray-100 rounded-2xl shadow-sm">
        <div class="overflow-x-auto">
            <table class="data-table w-full">
                <thead>
                    <tr>
                        <th>No PO</th>
                        <th>Mitra</th>
                        <th>Bayar</th>
                        <th>Total</th>
                        <th>Status</th>
                        <th>Tanggal</th>
                        <th class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($orders as $o)
                    <tr class="hover:bg-gray-50/50">
                        <td class="font-mono text-xs font-bold">{{ $o->order_no }}</td>
                        <td>
                            <p class="font-semibold text-sm text-gray-800">{{ $o->partner?->name }}</p>
                            <p class="text-[11px] text-gray-400">{{ $o->partner?->code }} · {{ $o->items_count }} item</p>
                        </td>
                        <td>
                            <p class="text-xs font-semibold">{{ $o->payment_method_label }}</p>
                            <p class="text-[11px] text-gray-400">{{ $o->payment_status_label }}</p>
                        </td>
                        <td class="font-bold text-emerald-700 text-sm">Rp {{ number_format($o->total, 0, ',', '.') }}</td>
                        <td>
                            <span class="inline-flex px-2 py-0.5 rounded-lg text-[10px] font-bold
                                @if($o->status === 'fulfilled') bg-emerald-100 text-emerald-800
                                @elseif($o->status === 'cancelled') bg-red-100 text-red-700
                                @elseif($o->status === 'confirmed') bg-sky-100 text-sky-800
                                @else bg-amber-100 text-amber-800 @endif">
                                {{ $o->status_label }}
                            </span>
                        </td>
                        <td class="text-xs text-gray-500">{{ $o->created_at->timezone('Asia/Makassar')->format('d/m/Y H:i') }}</td>
                        <td class="text-center">
                            <div class="flex items-center justify-center gap-1 flex-wrap">
                                <a wire:navigate href="{{ route('partner-orders.show', $o) }}" class="btn btn-secondary btn-sm">Detail</a>
                                @if($o->status !== 'cancelled')
                                <a href="{{ route('partner-orders.print.surat-jalan', ['partnerOrder' => $o, 'entity' => 'pt', 'printer' => 'dotmatrix']) }}"
                                   target="_blank" rel="noopener" class="btn btn-secondary btn-sm px-2" title="Surat Jalan LX-310">📦</a>
                                <a href="{{ route('partner-orders.print.penjualan', ['partnerOrder' => $o, 'entity' => 'pt', 'printer' => 'dotmatrix']) }}"
                                   target="_blank" rel="noopener" class="btn btn-secondary btn-sm px-2" title="Faktur LX-310">🧾</a>
                                <a href="{{ route('partner-orders.print.penjualan', ['partnerOrder' => $o, 'entity' => 'pt', 'printer' => 'thermal']) }}"
                                   target="_blank" rel="noopener" class="btn btn-secondary btn-sm px-2" title="Faktur Thermal">🖨️</a>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="7" class="text-center text-gray-400 py-10">Belum ada PO mitra.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($orders->hasPages())
        <div class="p-4 border-t border-gray-100">{{ $orders->links() }}</div>
        @endif
    </div>
</div>
@endsection
