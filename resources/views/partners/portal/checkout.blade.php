@extends('layouts.catalog')
@section('title', 'Checkout PO')

@section('content')
@php
    $hasStockIssues = !empty($stockIssues);
    $stockMetaFor = function ($product) {
        $stock = (int) $product->stock;
        $min = max(1, (int) $product->stock_min);
        if ($stock <= 0) {
            return ['state' => 'habis', 'label' => 'Habis', 'badge' => 'bg-red-100 text-red-700'];
        }
        if ($stock <= $min) {
            return ['state' => 'terbatas', 'label' => 'Terbatas', 'badge' => 'bg-amber-100 text-amber-800'];
        }
        return ['state' => 'tersedia', 'label' => 'Tersedia', 'badge' => 'bg-emerald-100 text-emerald-800'];
    };
    $defaultPayment = old('payment_method', array_key_first($methods));
    $creditDays = (int) ($partner->credit_days ?: 30);
    $invoiceDate = now()->locale('id');
    $dueDate = now()->addDays($creditDays)->locale('id');
@endphp
<div class="max-w-6xl mx-auto px-4 sm:px-6 py-8 lg:py-10">
    <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h2 class="font-banner text-2xl sm:text-3xl font-extrabold text-slate-800 uppercase tracking-wide">Checkout Purchase Order</h2>
            <p class="text-sm text-slate-500 mt-1">{{ $partner->name }} · {{ $partner->code }} · Harga {{ $priceLabel }}</p>
        </div>
        <a href="{{ route('mitra.cart') }}"
           class="inline-flex items-center justify-center gap-2 px-5 py-2.5 rounded-xl border border-slate-200 bg-white text-slate-700 text-sm font-bold shadow-sm hover:bg-slate-50 hover:border-emerald-300 hover:text-emerald-700 transition-colors shrink-0">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            Kembali ke Keranjang
        </a>
    </div>

    @if($errors->any())
    <div class="mb-4 p-3.5 rounded-xl bg-red-50 border border-red-200 text-red-700 text-sm">
        <ul class="list-disc pl-5">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
    </div>
    @endif

    @if($hasStockIssues)
    <div class="mb-5 p-4 rounded-2xl bg-red-50 border border-red-200">
        <div class="flex items-start gap-3">
            <div class="w-9 h-9 rounded-xl bg-red-100 flex items-center justify-center shrink-0">
                <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/></svg>
            </div>
            <div>
                <p class="text-sm font-bold text-red-800">Checkout diblokir — qty melebihi stok tersedia</p>
                <ul class="mt-2 space-y-1 text-xs text-red-700">
                    @foreach($stockIssues as $issue)
                    <li><span class="font-semibold">{{ $issue['name'] }}</span>: diminta {{ $issue['qty'] }}, stok {{ $issue['stock'] }}</li>
                    @endforeach
                </ul>
                <a href="{{ route('mitra.cart') }}" class="inline-flex items-center gap-1.5 mt-3 text-xs font-bold text-red-700 hover:text-red-900 underline underline-offset-2">
                    Sesuaikan qty di keranjang
                </a>
            </div>
        </div>
    </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 lg:gap-8">
        <div class="lg:col-span-7">
            <form action="{{ route('mitra.checkout.post') }}" method="POST" class="bg-white rounded-2xl border border-slate-200 shadow-sm p-5 sm:p-6 space-y-4">
                @csrf
                <div class="space-y-3">
                    <label class="block text-sm font-semibold text-slate-700">Metode Pembayaran <span class="text-red-500">*</span></label>
                    <div class="space-y-2" id="paymentMethodGroup">
                        @foreach($methods as $key => $label)
                        <label class="flex items-start gap-3 p-3 rounded-xl border border-slate-200 hover:border-emerald-300 cursor-pointer has-[:checked]:border-emerald-500 has-[:checked]:bg-emerald-50/50 {{ $hasStockIssues ? 'opacity-60 pointer-events-none' : '' }}">
                            <input type="radio" name="payment_method" value="{{ $key }}"
                                   class="checkout-payment-radio mt-1 text-emerald-600 focus:ring-emerald-500"
                                   @checked($defaultPayment === $key) required {{ $hasStockIssues ? 'disabled' : '' }}>
                            <span>
                                <span class="block text-sm font-bold text-slate-800">{{ $label }}</span>
                                @if($key === 'transfer')
                                <span class="block text-[11px] text-slate-500 mt-0.5">Transfer ke rekening resmi perusahaan.</span>
                                @elseif($key === 'invoice')
                                <span class="block text-[11px] text-slate-500 mt-0.5">Pembayaran tempo {{ $creditDays }} hari.</span>
                                @else
                                <span class="block text-[11px] text-slate-500 mt-0.5">Bayar saat barang diterima.</span>
                                @endif
                            </span>
                        </label>
                        @endforeach
                    </div>

                    {{-- Panel Transfer Bank --}}
                    <div id="paymentPanelTransfer"
                         class="mt-1 rounded-2xl border border-emerald-200 bg-gradient-to-br from-emerald-50 to-teal-50/80 p-4 sm:p-5 {{ $defaultPayment === 'transfer' ? '' : 'hidden' }}">
                        <div class="flex items-center gap-2 mb-3">
                            <div class="w-8 h-8 rounded-lg bg-emerald-600 text-white flex items-center justify-center shrink-0">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
                            </div>
                            <div>
                                <p class="text-xs font-bold uppercase tracking-wider text-emerald-800">Rekening Resmi Perusahaan</p>
                                <p class="text-[11px] text-emerald-700/80">Gunakan rekening berikut untuk transfer pembayaran PO</p>
                            </div>
                        </div>
                        <div class="rounded-xl bg-white border border-emerald-100 overflow-hidden shadow-sm">
                            <div class="grid grid-cols-1 divide-y divide-slate-100">
                                <div class="flex items-center justify-between gap-3 px-4 py-3">
                                    <div>
                                        <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Bank</p>
                                        <p class="text-sm font-extrabold text-slate-800 mt-0.5">{{ $bankName ?: 'BNI' }}</p>
                                    </div>
                                </div>
                                <div class="flex items-center justify-between gap-3 px-4 py-3">
                                    <div class="min-w-0">
                                        <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400">No. Rekening</p>
                                        <p id="checkoutBankAccount" class="text-lg font-extrabold text-emerald-700 mt-0.5 tracking-wide">{{ $bankAccount ?: '2050169349' }}</p>
                                    </div>
                                    <button type="button" onclick="copyCheckoutText('checkoutBankAccount', 'Nomor rekening')"
                                            class="shrink-0 inline-flex items-center gap-1.5 px-3 py-2 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold transition-colors">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                                        Salin
                                    </button>
                                </div>
                                <div class="flex items-center justify-between gap-3 px-4 py-3">
                                    <div class="min-w-0">
                                        <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400">A/N (Atas Nama)</p>
                                        <p id="checkoutBankHolder" class="text-sm font-bold text-slate-800 mt-0.5">{{ $bankHolder ?: 'PT NUR MADANI FARMA' }}</p>
                                    </div>
                                    <button type="button" onclick="copyCheckoutText('checkoutBankHolder', 'Nama pemilik rekening')"
                                            class="shrink-0 inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg border border-emerald-200 bg-emerald-50 hover:bg-emerald-100 text-emerald-800 text-xs font-bold transition-colors">
                                        Salin
                                    </button>
                                </div>
                            </div>
                        </div>
                        <button type="button" onclick="copyCheckoutBankFull()"
                                class="mt-3 w-full inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl border border-emerald-300 bg-white hover:bg-emerald-50 text-emerald-800 text-xs font-bold transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                            Salin Semua Data Rekening
                        </button>
                    </div>

                    {{-- Panel COD --}}
                    <div id="paymentPanelCod"
                         class="rounded-2xl border border-slate-200 bg-slate-50 p-4 {{ $defaultPayment === 'cod' ? '' : 'hidden' }}">
                        <div class="flex items-start gap-3">
                            <div class="w-8 h-8 rounded-lg bg-slate-200 text-slate-600 flex items-center justify-center shrink-0">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                            </div>
                            <div>
                                <p class="text-sm font-bold text-slate-800">Pembayaran COD (Cash on Delivery)</p>
                                <p class="text-xs text-slate-500 mt-1 leading-relaxed">Tidak perlu transfer bank. Pembayaran dilakukan langsung saat barang diterima oleh tim pengiriman apotek.</p>
                            </div>
                        </div>
                    </div>

                    {{-- Panel Invoice Tempo --}}
                    <div id="paymentPanelInvoice"
                         class="rounded-2xl border border-amber-200 bg-gradient-to-br from-amber-50 to-orange-50/60 p-4 sm:p-5 {{ $defaultPayment === 'invoice' ? '' : 'hidden' }}">
                        <div class="flex items-center gap-2 mb-3">
                            <div class="w-8 h-8 rounded-lg bg-amber-500 text-white flex items-center justify-center shrink-0">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                            </div>
                            <div>
                                <p class="text-xs font-bold uppercase tracking-wider text-amber-800">Informasi Invoice Tempo</p>
                                <p class="text-[11px] text-amber-700/80">Estimasi tanggal invoice setelah PO diajukan</p>
                            </div>
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <div class="rounded-xl bg-white border border-amber-100 px-4 py-3 shadow-sm">
                                <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Tanggal Invoice</p>
                                <p class="text-base font-extrabold text-slate-800 mt-1">{{ $invoiceDate->translatedFormat('d F Y') }}</p>
                                <p class="text-[10px] text-slate-400 mt-0.5">Diterbitkan saat PO disetujui</p>
                            </div>
                            <div class="rounded-xl bg-white border border-amber-100 px-4 py-3 shadow-sm">
                                <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Jatuh Tempo</p>
                                <p class="text-base font-extrabold text-amber-700 mt-1">{{ $dueDate->translatedFormat('d F Y') }}</p>
                                <p class="text-[10px] text-slate-400 mt-0.5">{{ $creditDays }} hari setelah invoice</p>
                            </div>
                        </div>
                        <div class="mt-3 rounded-xl bg-amber-100/60 border border-amber-200 px-3 py-2.5 text-xs text-amber-900 leading-relaxed">
                            <span class="font-bold">Catatan:</span> Pembayaran dapat dilakukan sebelum atau pada tanggal jatuh tempo. Keterlambatan dapat mempengaruhi limit kredit mitra.
                            <span class="block mt-1.5 font-semibold">Harga Invoice = harga jual + {{ (int) ($invoiceMarkupPercent ?? 5) }}% (bukan harga grosir mitra).</span>
                        </div>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-1">Alamat Pengiriman <span class="text-red-500">*</span></label>
                    <textarea name="shipping_address" rows="3" required {{ $hasStockIssues ? 'disabled' : '' }}
                              class="w-full rounded-xl border border-slate-200 px-3.5 py-2.5 text-sm focus:ring-2 focus:ring-emerald-500/30 focus:border-emerald-500 outline-none disabled:bg-slate-50 disabled:text-slate-400">{{ old('shipping_address', $partner->address) }}</textarea>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1">Nama PIC <span class="text-red-500">*</span></label>
                        <input type="text" name="pic_name" value="{{ old('pic_name', $partner->pic_name) }}" required {{ $hasStockIssues ? 'disabled' : '' }}
                               class="w-full rounded-xl border border-slate-200 px-3.5 py-2.5 text-sm focus:ring-2 focus:ring-emerald-500/30 focus:border-emerald-500 outline-none disabled:bg-slate-50 disabled:text-slate-400">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1">Telepon PIC <span class="text-red-500">*</span></label>
                        <input type="text" name="pic_phone" value="{{ old('pic_phone', $partner->phone) }}" required {{ $hasStockIssues ? 'disabled' : '' }}
                               class="w-full rounded-xl border border-slate-200 px-3.5 py-2.5 text-sm focus:ring-2 focus:ring-emerald-500/30 focus:border-emerald-500 outline-none disabled:bg-slate-50 disabled:text-slate-400">
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-1">Catatan (opsional)</label>
                    <textarea name="notes" rows="2" {{ $hasStockIssues ? 'disabled' : '' }}
                              class="w-full rounded-xl border border-slate-200 px-3.5 py-2.5 text-sm focus:ring-2 focus:ring-emerald-500/30 focus:border-emerald-500 outline-none disabled:bg-slate-50 disabled:text-slate-400">{{ old('notes') }}</textarea>
                </div>

                <div class="flex flex-col sm:flex-row gap-3 pt-1">
                    <button type="submit"
                            @disabled($hasStockIssues)
                            class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-6 py-3 rounded-xl text-white text-sm font-bold shadow-md transition-colors
                                   {{ $hasStockIssues ? 'bg-slate-300 cursor-not-allowed shadow-none' : 'bg-emerald-600 hover:bg-emerald-700 shadow-emerald-600/20' }}">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        Ajukan Purchase Order
                    </button>
                    <a href="{{ route('mitra.cart') }}"
                       class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-6 py-3 rounded-xl border border-slate-200 bg-white text-slate-700 text-sm font-bold hover:bg-slate-50 transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                        Kembali
                    </a>
                </div>
            </form>
        </div>

        <div class="lg:col-span-5">
            <div class="lg:sticky lg:top-6 space-y-4">
                <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
                    <div class="px-5 py-4 bg-gradient-to-r from-emerald-700 to-teal-600 text-white">
                        <h3 class="font-banner text-sm font-extrabold uppercase tracking-wide">Ringkasan PO</h3>
                        <p class="text-emerald-50/90 text-xs mt-0.5">{{ count($cart['items']) }} jenis produk · {{ $cart['count'] }} item</p>
                    </div>

                    <div class="p-5 space-y-4">
                        <div class="grid grid-cols-2 gap-3 text-sm">
                            <div class="rounded-xl bg-slate-50 border border-slate-100 px-3 py-2.5">
                                <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Mitra</p>
                                <p class="font-semibold text-slate-800 mt-0.5 truncate" title="{{ $partner->name }}">{{ $partner->name }}</p>
                            </div>
                            <div class="rounded-xl bg-slate-50 border border-slate-100 px-3 py-2.5">
                                <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Tipe</p>
                                <p class="font-semibold text-slate-700 mt-0.5">{{ $partner->type_label }}</p>
                            </div>
                        </div>

                        <div>
                            <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400 mb-2">Detail Produk</p>
                            <div class="rounded-xl border border-slate-100 divide-y divide-slate-100 max-h-72 overflow-y-auto">
                                @foreach($cart['items'] as $idx => $line)
                                @php
                                    $p = $line['product'];
                                    $stockMeta = $stockMetaFor($p);
                                    $overStock = $line['qty'] > $p->stock;
                                    $unitName = $p->unit?->name ?? 'pcs';
                                    $invLine = $cartInvoice['items'][$idx] ?? null;
                                @endphp
                                <div class="px-3.5 py-3 checkout-line-row {{ $overStock ? 'bg-red-50/60' : '' }}"
                                     data-unit-normal="{{ (float) $line['unit_price'] }}"
                                     data-sub-normal="{{ (float) $line['subtotal'] }}"
                                     data-unit-invoice="{{ (float) ($invLine['unit_price'] ?? $line['unit_price']) }}"
                                     data-sub-invoice="{{ (float) ($invLine['subtotal'] ?? $line['subtotal']) }}"
                                     data-price-type-normal="{{ $line['price_type'] }}"
                                     data-qty="{{ $line['qty'] }}"
                                     data-unit-name="{{ $unitName }}">
                                    <div class="flex items-start justify-between gap-2">
                                        <div class="min-w-0 flex-1">
                                            <p class="text-sm font-bold text-slate-800 leading-snug">{{ $p->name }}</p>
                                            <div class="flex flex-wrap items-center gap-1.5 mt-1">
                                                <span class="checkout-price-type inline-flex px-1.5 py-0.5 rounded text-[10px] font-bold {{ $line['price_type'] === 'grosir' ? 'bg-amber-100 text-amber-800' : 'bg-blue-50 text-blue-700' }}">
                                                    {{ $line['price_type'] === 'grosir' ? 'Grosir' : 'Eceran' }}
                                                </span>
                                                <span class="inline-flex px-1.5 py-0.5 rounded text-[10px] font-bold {{ $stockMeta['badge'] }}">
                                                    Stok {{ $stockMeta['label'] }}
                                                </span>
                                            </div>
                                        </div>
                                        <p class="checkout-line-subtotal text-sm font-extrabold text-emerald-700 shrink-0">Rp {{ number_format($line['subtotal'], 0, ',', '.') }}</p>
                                    </div>
                                    <div class="mt-2 flex items-center justify-between text-[11px] text-slate-500">
                                        <span class="checkout-line-unit">{{ $line['qty'] }} {{ $unitName }} × Rp {{ number_format($line['unit_price'], 0, ',', '.') }}</span>
                                        <span class="font-semibold {{ $overStock ? 'text-red-600' : 'text-slate-600' }}">
                                            Stok: {{ number_format($p->stock, 0, ',', '.') }} {{ $unitName }}
                                        </span>
                                    </div>
                                    @if($overStock)
                                    <p class="mt-1.5 text-[10px] font-bold text-red-600 flex items-center gap-1">
                                        <svg class="w-3 h-3 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01"/></svg>
                                        Qty melebihi stok ({{ $line['qty'] }} / {{ $p->stock }})
                                    </p>
                                    @endif
                                </div>
                                @endforeach
                            </div>
                        </div>

                        <div class="pt-4 border-t border-slate-100">
                            @include('partners.portal._order-totals-summary', [
                                'totals' => $ppn,
                                'totalLabel' => 'Estimasi Total',
                                'subtotalId' => 'checkoutSubtotal',
                                'discId' => 'checkoutDisc',
                                'ppnId' => 'checkoutPpn',
                                'grandId' => 'checkoutGrand',
                            ])
                            <p id="checkoutInvoiceHint" class="text-[11px] text-amber-700 font-semibold mt-2 leading-relaxed {{ $defaultPayment === 'invoice' ? '' : 'hidden' }}">
                                Estimasi memakai harga jual + {{ (int) ($invoiceMarkupPercent ?? 5) }}% (Invoice).
                            </p>
                            <p class="text-[11px] text-slate-400 mt-2 leading-relaxed">Harga final dikonfirmasi apotek setelah PO diajukan.</p>
                        </div>
                    </div>
                </div>

                <a href="{{ route('catalog.index') }}"
                   class="inline-flex items-center gap-1.5 text-sm text-slate-500 hover:text-emerald-600 font-semibold transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                    Lanjut belanja di E-Catalog
                </a>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
(function () {
    const panels = {
        transfer: document.getElementById('paymentPanelTransfer'),
        cod: document.getElementById('paymentPanelCod'),
        invoice: document.getElementById('paymentPanelInvoice'),
    };

    const totalsNormal = @json([
        'subtotal' => (float) ($ppn['subtotal'] ?? 0),
        'discount_amount' => (float) ($ppn['discount_amount'] ?? 0),
        'ppn_amount' => (float) ($ppn['ppn_amount'] ?? 0),
        'grand_total' => (float) ($ppn['grand_total'] ?? 0),
    ]);
    const totalsInvoice = @json([
        'subtotal' => (float) ($ppnInvoice['subtotal'] ?? $ppn['subtotal'] ?? 0),
        'discount_amount' => (float) ($ppnInvoice['discount_amount'] ?? $ppn['discount_amount'] ?? 0),
        'ppn_amount' => (float) ($ppnInvoice['ppn_amount'] ?? $ppn['ppn_amount'] ?? 0),
        'grand_total' => (float) ($ppnInvoice['grand_total'] ?? $ppn['grand_total'] ?? 0),
    ]);

    function formatRp(n) {
        return 'Rp ' + Math.round(Number(n) || 0).toLocaleString('id-ID');
    }

    function applyCheckoutPricing(method) {
        const isInvoice = method === 'invoice';
        const totals = isInvoice ? totalsInvoice : totalsNormal;

        document.querySelectorAll('.checkout-line-row').forEach(function (row) {
            const unit = parseFloat(row.getAttribute(isInvoice ? 'data-unit-invoice' : 'data-unit-normal') || '0');
            const sub = parseFloat(row.getAttribute(isInvoice ? 'data-sub-invoice' : 'data-sub-normal') || '0');
            const qty = row.getAttribute('data-qty') || '1';
            const unitName = row.getAttribute('data-unit-name') || 'pcs';
            const typeEl = row.querySelector('.checkout-price-type');
            const unitEl = row.querySelector('.checkout-line-unit');
            const subEl = row.querySelector('.checkout-line-subtotal');

            if (unitEl) unitEl.textContent = qty + ' ' + unitName + ' × ' + formatRp(unit);
            if (subEl) subEl.textContent = formatRp(sub);
            if (typeEl) {
                if (isInvoice) {
                    typeEl.textContent = 'Invoice';
                    typeEl.className = 'checkout-price-type inline-flex px-1.5 py-0.5 rounded text-[10px] font-bold bg-orange-100 text-orange-800';
                } else {
                    const t = row.getAttribute('data-price-type-normal') || 'eceran';
                    typeEl.textContent = t === 'grosir' ? 'Grosir' : 'Eceran';
                    typeEl.className = 'checkout-price-type inline-flex px-1.5 py-0.5 rounded text-[10px] font-bold ' +
                        (t === 'grosir' ? 'bg-amber-100 text-amber-800' : 'bg-blue-50 text-blue-700');
                }
            }
        });

        const map = {
            checkoutSubtotal: totals.subtotal,
            checkoutDisc: totals.discount_amount,
            checkoutPpn: totals.ppn_amount,
            checkoutGrand: totals.grand_total,
        };
        Object.keys(map).forEach(function (id) {
            const el = document.getElementById(id);
            if (!el) return;
            if (id === 'checkoutDisc' && map[id] > 0) {
                el.textContent = '-' + formatRp(map[id]);
            } else {
                el.textContent = formatRp(map[id]);
            }
        });

        const hint = document.getElementById('checkoutInvoiceHint');
        if (hint) hint.classList.toggle('hidden', !isInvoice);
    }

    function togglePaymentPanels(method) {
        Object.keys(panels).forEach(function (key) {
            if (panels[key]) panels[key].classList.toggle('hidden', key !== method);
        });
        applyCheckoutPricing(method);
    }

    document.querySelectorAll('.checkout-payment-radio').forEach(function (radio) {
        radio.addEventListener('change', function () {
            if (this.checked) togglePaymentPanels(this.value);
        });
    });

    const checked = document.querySelector('.checkout-payment-radio:checked');
    if (checked) togglePaymentPanels(checked.value);
})();

function copyCheckoutText(elementId, label) {
    const el = document.getElementById(elementId);
    if (!el) return;
    const text = el.textContent.trim();
    if (!text) return;

    const done = function () {
        window.MitraCart?.showToast((label || 'Teks') + ' berhasil disalin.');
    };

    if (navigator.clipboard?.writeText) {
        navigator.clipboard.writeText(text).then(done).catch(function () {
            fallbackCopy(text, done);
        });
    } else {
        fallbackCopy(text, done);
    }
}

function copyCheckoutBankFull() {
    const bank = @json($bankName ?: 'BNI');
    const account = document.getElementById('checkoutBankAccount')?.textContent.trim() || @json($bankAccount ?: '2050169349');
    const holder = document.getElementById('checkoutBankHolder')?.textContent.trim() || @json($bankHolder ?: 'PT NUR MADANI FARMA');
    const text = 'Bank: ' + bank + '\nNo. Rekening: ' + account + '\nA/N: ' + holder;

    const done = function () {
        window.MitraCart?.showToast('Data rekening lengkap berhasil disalin.');
    };

    if (navigator.clipboard?.writeText) {
        navigator.clipboard.writeText(text).then(done).catch(function () {
            fallbackCopy(text, done);
        });
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
    try {
        document.execCommand('copy');
        callback();
    } catch (e) {
        window.MitraCart?.showToast('Gagal menyalin. Salin manual: ' + text, 'error');
    }
    document.body.removeChild(ta);
}
</script>
@endpush
