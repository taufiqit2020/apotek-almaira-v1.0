@extends('layouts.catalog')
@section('title', 'PO ' . $order->order_no)

@section('content')
@php
    $statusColors = [
        'submitted' => 'bg-blue-100 text-blue-800 border-blue-200',
        'confirmed' => 'bg-indigo-100 text-indigo-800 border-indigo-200',
        'fulfilled' => 'bg-emerald-100 text-emerald-800 border-emerald-200',
        'cancelled' => 'bg-red-100 text-red-800 border-red-200',
    ];
    $payStatusColors = [
        'unpaid'                => 'bg-amber-100 text-amber-800 border-amber-200',
        'awaiting_confirmation' => 'bg-orange-100 text-orange-800 border-orange-200',
        'paid'                  => 'bg-emerald-100 text-emerald-800 border-emerald-200',
        'cancelled'             => 'bg-slate-100 text-slate-600 border-slate-200',
    ];
    $statusClass = $statusColors[$order->status] ?? 'bg-slate-100 text-slate-700 border-slate-200';
    $payStatusClass = $payStatusColors[$order->payment_status] ?? 'bg-slate-100 text-slate-700 border-slate-200';
    $bankDisplay = $bankName ?: 'BNI';
    $accountDisplay = $bankAccount ?: '2050169349';
    $holderDisplay = $bankHolder ?: 'PT NUR MADANI FARMA';
    $hasBankInfo = $bankDisplay && $accountDisplay;
    $creditDays = (int) ($partner->credit_days ?: 30);
@endphp
<div class="max-w-6xl mx-auto px-4 sm:px-6 py-8 lg:py-10">
    @if(session('toast_success'))
    <div class="mb-4 p-3.5 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm">{{ session('toast_success') }}</div>
    @endif
    @if(session('toast_error'))
    <div class="mb-4 p-3.5 rounded-xl bg-red-50 border border-red-200 text-red-700 text-sm">{{ session('toast_error') }}</div>
    @endif
    @if($errors->any())
    <div class="mb-4 p-3.5 rounded-xl bg-red-50 border border-red-200 text-red-700 text-sm">
        <ul class="list-disc pl-5">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
    </div>
    @endif

    {{-- Header --}}
    <div class="mb-6 flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4">
        <div class="min-w-0">
            <a href="{{ route('mitra.orders.index') }}"
               class="inline-flex items-center gap-1.5 text-sm text-slate-500 hover:text-emerald-600 font-semibold transition-colors">
                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                Daftar PO
            </a>
            <p class="font-mono text-xs font-bold text-slate-500 mt-3">{{ $order->order_no }}</p>
            <h2 class="font-banner text-2xl sm:text-3xl font-extrabold text-slate-800 uppercase tracking-wide mt-0.5">Detail PO</h2>
            <p class="text-sm text-slate-500 mt-1">{{ $order->created_at?->locale('id')->translatedFormat('d F Y, H:i') }}</p>
        </div>
        <div class="flex flex-wrap gap-2 shrink-0">
            <span class="inline-flex px-2.5 py-1 rounded-lg text-xs font-bold border {{ $statusClass }}">{{ $order->status_label }}</span>
            <span class="inline-flex px-2.5 py-1 rounded-lg text-xs font-bold border {{ $payStatusClass }}">{{ $order->payment_status_label }}</span>
            <span class="inline-flex px-2.5 py-1 rounded-lg text-xs font-bold bg-emerald-50 text-emerald-800 border border-emerald-100">{{ $order->payment_method_label }}</span>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 lg:gap-8 items-start">

        {{-- Item PO --}}
        <div class="lg:col-span-7 min-w-0">
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
                <div class="px-5 py-4 bg-gradient-to-r from-emerald-700 to-teal-600 text-white">
                    <h3 class="font-banner text-sm font-extrabold uppercase tracking-wide">Item PO</h3>
                    <p class="text-emerald-50/90 text-xs mt-0.5">{{ $order->items->count() }} produk · {{ $order->items->sum('quantity') }} qty</p>
                </div>
                <div class="divide-y divide-slate-100 max-h-[28rem] overflow-y-auto">
                    @foreach($order->items as $item)
                    @php $meta = $item->catalogDisplay(); @endphp
                    <div class="px-5 py-3.5 flex items-start justify-between gap-3 hover:bg-slate-50/60">
                        <div class="min-w-0 flex-1">
                            <p class="font-bold text-slate-800 text-sm leading-snug">{{ $item->product_name }}</p>
                            <p class="text-[11px] text-slate-500 mt-1 font-mono">{{ $meta['code'] }} · {{ $meta['category'] }} · {{ $meta['unit'] }}</p>
                            <p class="text-[11px] text-slate-500 mt-0.5 leading-snug">
                                Kandungan: {{ $meta['kandungan'] }}
                                <span class="text-slate-300 mx-1">·</span>
                                Bentuk: {{ $meta['bentuk'] }}
                            </p>
                            <div class="flex flex-wrap items-center gap-1.5 mt-1.5">
                                <span class="inline-flex px-1.5 py-0.5 rounded text-[10px] font-bold {{ $item->price_type === 'grosir' ? 'bg-amber-100 text-amber-800' : 'bg-blue-50 text-blue-700' }}">
                                    {{ $item->price_type_label }}
                                </span>
                                <span class="text-[11px] text-slate-500">
                                    {{ $item->quantity }} {{ $meta['unit'] }} × Rp {{ number_format($item->unit_price, 0, ',', '.') }}
                                </span>
                            </div>
                        </div>
                        <p class="font-extrabold text-emerald-700 text-sm shrink-0 whitespace-nowrap">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</p>
                    </div>
                    @endforeach
                </div>
                <div class="px-5 py-4 bg-emerald-50/60 border-t border-emerald-100">
                    @php $orderTotals = $order->totalsBreakdown(); @endphp
                    @include('partners.portal._order-totals-summary', [
                        'totals' => $orderTotals,
                        'totalLabel' => 'Total PO',
                    ])
                </div>
            </div>
        </div>

        {{-- Sidebar --}}
        <div class="lg:col-span-5 space-y-4 lg:sticky lg:top-6">

            {{-- Pengiriman --}}
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
                <div class="px-5 py-3 bg-slate-50 border-b border-slate-100">
                    <h3 class="text-xs font-bold uppercase tracking-wider text-slate-500">Pengiriman &amp; PIC</h3>
                </div>
                <div class="p-5 text-sm space-y-3">
                    <div>
                        <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Metode Pembayaran</p>
                        <p class="font-bold text-slate-800 mt-0.5">{{ $order->payment_method_label }}</p>
                    </div>
                    <div>
                        <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400">PIC</p>
                        <p class="font-semibold text-slate-800 mt-0.5">{{ $order->pic_name }}</p>
                        <a href="tel:{{ $order->pic_phone }}" class="text-emerald-700 font-semibold hover:underline">{{ $order->pic_phone }}</a>
                    </div>
                    <div>
                        <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Alamat Pengiriman</p>
                        <p class="text-slate-600 mt-1 leading-relaxed whitespace-pre-line break-words">{{ $order->shipping_address }}</p>
                    </div>
                    @if($order->notes)
                    <div class="pt-2 border-t border-slate-100">
                        <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Catatan</p>
                        <p class="text-slate-500 text-xs mt-1 break-words">{{ $order->notes }}</p>
                    </div>
                    @endif
                    @if($order->cancel_reason)
                    <div class="pt-2 border-t border-red-100 bg-red-50/50 -mx-5 px-5 py-3">
                        <p class="text-[10px] font-bold uppercase text-red-500">Alasan Batal</p>
                        <p class="text-red-700 text-xs font-semibold mt-1">{{ $order->cancel_reason }}</p>
                    </div>
                    @endif
                </div>
            </div>

            {{-- Pembayaran: Transfer --}}
            @if($order->payment_method === 'transfer')
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
                <div class="px-5 py-3 bg-emerald-50 border-b border-emerald-100">
                    <h3 class="text-xs font-bold uppercase tracking-wider text-emerald-800">Transfer Bank</h3>
                </div>
                <div class="p-5 space-y-4">
                    @if($hasBankInfo)
                    <div class="rounded-xl border border-emerald-200 bg-gradient-to-br from-emerald-50 to-teal-50/80 p-4">
                        <div class="flex items-center gap-2 mb-3">
                            <div class="w-8 h-8 rounded-lg bg-emerald-600 text-white flex items-center justify-center shrink-0">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
                            </div>
                            <div>
                                <p class="text-xs font-bold text-emerald-800 uppercase tracking-wider">Rekening Resmi Perusahaan</p>
                                <p class="text-[11px] text-emerald-700/80">Transfer ke rekening berikut</p>
                            </div>
                        </div>
                        <div class="rounded-xl bg-white border border-emerald-100 overflow-hidden shadow-sm divide-y divide-slate-100">
                            <div class="px-4 py-3">
                                <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Bank</p>
                                <p class="text-sm font-extrabold text-slate-800 mt-0.5">{{ $bankDisplay }}</p>
                            </div>
                            <div class="px-4 py-3 flex items-center justify-between gap-3">
                                <div class="min-w-0">
                                    <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400">No. Rekening</p>
                                    <p id="mitraPoBankAccount" class="text-lg font-extrabold text-emerald-700 mt-0.5 tracking-wide">{{ $accountDisplay }}</p>
                                </div>
                                <button type="button" onclick="copyMitraPoText('mitraPoBankAccount','Nomor rekening')"
                                        class="shrink-0 inline-flex items-center gap-1 px-3 py-2 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold transition-colors">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                                    Salin
                                </button>
                            </div>
                            <div class="px-4 py-3 flex items-center justify-between gap-3">
                                <div class="min-w-0">
                                    <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400">A/N (Atas Nama)</p>
                                    <p id="mitraPoBankHolder" class="text-sm font-bold text-slate-800 mt-0.5 break-words">{{ $holderDisplay }}</p>
                                </div>
                                <button type="button" onclick="copyMitraPoText('mitraPoBankHolder','Nama pemilik rekening')"
                                        class="shrink-0 px-3 py-1.5 rounded-lg border border-emerald-200 bg-emerald-50 hover:bg-emerald-100 text-emerald-800 text-xs font-bold transition-colors">
                                    Salin
                                </button>
                            </div>
                        </div>
                        <button type="button" onclick="copyMitraPoBankFull()"
                                class="mt-3 w-full inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl border border-emerald-300 bg-white hover:bg-emerald-50 text-emerald-800 text-xs font-bold transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                            Salin Semua Data Rekening
                        </button>
                    </div>
                    @else
                    <div class="rounded-xl border border-amber-200 bg-amber-50 p-4 flex items-start gap-3">
                        <svg class="w-5 h-5 text-amber-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01"/></svg>
                        <div>
                            <p class="text-sm font-bold text-amber-800">Rekening belum tersedia</p>
                            <p class="text-xs text-amber-700 mt-1">Hubungi apotek di
                                @if(!empty($apotekPhone))
                                <a href="https://wa.me/{{ preg_replace('/^0/', '62', preg_replace('/\D/', '', $apotekPhone)) }}" target="_blank" class="font-bold underline">{{ $apotekPhone }}</a>
                                @else
                                nomor kontak apotek
                                @endif
                                untuk mendapatkan nomor rekening transfer.
                            </p>
                        </div>
                    </div>
                    @endif

                    {{-- Bukti transfer --}}
                    @if($order->transfer_proof)
                    <div class="rounded-xl border border-emerald-200 bg-emerald-50/60 p-4">
                        <div class="flex items-center gap-2">
                            <svg class="w-5 h-5 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            <div>
                                <p class="text-sm font-bold text-emerald-800">Bukti transfer sudah diunggah</p>
                                <p class="text-xs text-emerald-700 mt-0.5">{{ $order->transfer_proof_at?->locale('id')->translatedFormat('d F Y, H:i') }}</p>
                            </div>
                        </div>
                        <a href="{{ asset('storage/' . $order->transfer_proof) }}" target="_blank" rel="noopener"
                           class="mt-3 inline-flex items-center gap-1.5 text-xs font-bold text-emerald-700 hover:text-emerald-900 underline underline-offset-2">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                            Lihat bukti transfer
                        </a>
                    </div>
                    @endif

                    @if($order->canUploadProof())
                    <form action="{{ route('mitra.orders.proof', $order) }}" method="POST" enctype="multipart/form-data" class="rounded-xl border border-slate-200 bg-slate-50 p-4 space-y-3">
                        <p class="text-xs font-bold text-slate-700">Unggah Bukti Transfer</p>
                        <input type="file" name="transfer_proof" accept=".jpg,.jpeg,.png,.webp,.pdf" required
                               class="block w-full text-xs text-slate-600 file:mr-3 file:py-2 file:px-3 file:rounded-lg file:border-0 file:bg-emerald-600 file:text-white file:font-bold file:cursor-pointer">
                        <p class="text-[10px] text-slate-400">Format: JPG, PNG, WEBP, PDF · Maks. 4 MB</p>
                        <button type="submit" class="w-full py-3 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-bold shadow-md shadow-emerald-600/20 transition-colors">
                            Unggah Bukti Transfer
                        </button>
                    </form>
                    @endif
                </div>
            </div>
            @endif

            {{-- Pembayaran: Invoice --}}
            @if($order->payment_method === 'invoice')
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
                <div class="px-5 py-3 bg-amber-50 border-b border-amber-100">
                    <h3 class="text-xs font-bold uppercase tracking-wider text-amber-800">Invoice Tempo</h3>
                </div>
                <div class="p-5">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div class="rounded-xl bg-amber-50/60 border border-amber-100 px-4 py-3">
                            <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Tanggal Invoice</p>
                            <p class="text-base font-extrabold text-slate-800 mt-1">{{ $order->created_at?->locale('id')->translatedFormat('d F Y') }}</p>
                        </div>
                        <div class="rounded-xl bg-amber-50/60 border border-amber-100 px-4 py-3">
                            <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Jatuh Tempo</p>
                            <p class="text-base font-extrabold {{ $order->isCreditOverdue() ? 'text-red-600' : 'text-amber-700' }} mt-1">
                                {{ $order->due_date?->locale('id')->translatedFormat('d F Y') ?? '-' }}
                            </p>
                            <p class="text-[10px] text-slate-400 mt-0.5">Termin {{ $creditDays }} hari</p>
                        </div>
                    </div>
                    @if($order->isCreditOverdue())
                    <p class="mt-3 text-xs font-bold text-red-600 flex items-center gap-1">
                        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01"/></svg>
                        Invoice sudah lewat jatuh tempo
                    </p>
                    @endif
                </div>
            </div>
            @endif

            {{-- Pembayaran: COD --}}
            @if($order->payment_method === 'cod')
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
                <div class="px-5 py-3 bg-slate-50 border-b border-slate-100">
                    <h3 class="text-xs font-bold uppercase tracking-wider text-slate-500">COD — Bayar di Tempat</h3>
                </div>
                <div class="p-5">
                    <div class="rounded-xl border border-slate-200 bg-slate-50 p-4 flex items-start gap-3">
                        <div class="w-9 h-9 rounded-lg bg-slate-200 flex items-center justify-center shrink-0">
                            <svg class="w-5 h-5 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2z"/></svg>
                        </div>
                        <p class="text-sm text-slate-600 leading-relaxed">Pembayaran dilakukan langsung saat barang diterima. Tidak perlu transfer bank.</p>
                    </div>
                </div>
            </div>
            @endif

            @if($order->canBeCancelledByPartner())
            <form action="{{ route('mitra.orders.cancel', $order) }}" method="POST" onsubmit="return confirm('Batalkan PO ini?')"
                  class="bg-white rounded-2xl border border-red-100 shadow-sm p-5 space-y-3">
                @csrf
                <p class="text-xs font-bold uppercase tracking-wider text-red-500">Batalkan PO</p>
                <input type="text" name="cancel_reason" placeholder="Alasan pembatalan (opsional)"
                       class="w-full rounded-xl border border-slate-200 px-3.5 py-2.5 text-sm focus:ring-2 focus:ring-red-500/20 focus:border-red-300 outline-none">
                <button type="submit" class="w-full py-2.5 rounded-xl border border-red-200 text-red-600 text-sm font-bold hover:bg-red-50 transition-colors">
                    Batalkan PO
                </button>
            </form>
            @endif
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function copyMitraPoText(elementId, label) {
    const el = document.getElementById(elementId);
    if (!el) return;
    const text = el.textContent.trim();
    const done = () => window.MitraCart?.showToast((label || 'Teks') + ' berhasil disalin.');
    if (navigator.clipboard?.writeText) {
        navigator.clipboard.writeText(text).then(done).catch(() => fallbackCopy(text, done));
    } else {
        fallbackCopy(text, done);
    }
}

function copyMitraPoBankFull() {
    const text = 'Bank: {{ $bankDisplay }}\nNo. Rekening: {{ $accountDisplay }}\nA/N: {{ $holderDisplay }}';
    const done = () => window.MitraCart?.showToast('Data rekening lengkap berhasil disalin.');
    if (navigator.clipboard?.writeText) {
        navigator.clipboard.writeText(text).then(done).catch(() => fallbackCopy(text, done));
    } else {
        fallbackCopy(text, done);
    }
}

function fallbackCopy(text, callback) {
    const ta = document.createElement('textarea');
    ta.value = text;
    ta.style.position = 'fixed';
    ta.style.left = '-9999px';
    document.body.appendChild(ta);
    ta.select();
    try { document.execCommand('copy'); callback(); } catch (e) { window.MitraCart?.showToast('Gagal menyalin.', 'error'); }
    document.body.removeChild(ta);
}
</script>
@endpush
