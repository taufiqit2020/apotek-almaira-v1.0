{{-- Modal pemilih cetak PO Mitra — $order wajib PartnerOrder --}}
@php
    $printUrl = fn (string $type, string $entity, string $printer = 'a4') => route("partner-orders.print.{$type}", [
        'partnerOrder' => $order,
        'entity'       => $entity,
        'printer'      => $printer,
    ]);
    $printers = [
        'a4'        => ['label' => 'A4 / PDF',       'icon' => 'pdf',   'desc' => 'Preview & simpan PDF'],
        'thermal'   => ['label' => 'Thermal 80mm',   'icon' => 'thermal','desc' => 'Printer struk thermal'],
        'dotmatrix' => ['label' => 'Epson LX-310',   'icon' => 'dot',   'desc' => 'Continuous 25 × 28,5 cm'],
    ];
@endphp
<div x-data="{ open: false, tab: 'surat-jalan' }"
     x-effect="document.body.style.overflow = open ? 'hidden' : ''"
     @keydown.escape.window="open = false">

    {{-- Trigger --}}
    <button type="button" @click="open = true"
            class="group inline-flex items-center justify-center gap-2.5 w-full px-4 py-3 rounded-xl bg-gradient-to-r from-slate-800 to-slate-900 hover:from-slate-900 hover:to-black text-white text-sm font-bold shadow-md shadow-slate-900/20 transition-all duration-200">
        <span class="flex items-center justify-center w-8 h-8 rounded-lg bg-white/10 group-hover:bg-white/15 transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
            </svg>
        </span>
        <span class="flex-1 text-left">Cetak Dokumen</span>
        <svg class="w-4 h-4 opacity-60" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
        </svg>
    </button>

    {{-- Modal overlay --}}
    <template x-teleport="body">
        <div x-show="open" x-cloak
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 z-[200] flex items-end sm:items-center justify-center p-0 sm:p-4"
             role="dialog" aria-modal="true" aria-labelledby="print-modal-title">

            <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm" @click="open = false"></div>

            <div x-show="open"
                 x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                 x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave="transition ease-in duration-150"
                 x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                 class="relative w-full sm:max-w-lg bg-white rounded-t-2xl sm:rounded-2xl shadow-2xl flex flex-col max-h-[92vh] sm:max-h-[85vh] overflow-hidden"
                 @click.stop>

                {{-- Header --}}
                <div class="shrink-0 px-5 pt-5 pb-4 border-b border-gray-100 bg-gradient-to-r from-slate-50 to-white">
                    <div class="flex items-start justify-between gap-3">
                        <div class="min-w-0">
                            <p class="text-[10px] font-bold uppercase tracking-widest text-emerald-600 mb-1">Cetak PO Mitra</p>
                            <h3 id="print-modal-title" class="text-base font-extrabold text-gray-900 truncate">{{ $order->order_no }}</h3>
                            <p class="text-xs text-gray-500 mt-0.5 truncate">{{ $order->partner?->name }} · {{ $order->payment_method_label }}</p>
                        </div>
                        <button type="button" @click="open = false"
                                class="shrink-0 p-2 rounded-xl text-gray-400 hover:text-gray-600 hover:bg-gray-100 transition-colors">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>

                    {{-- Tabs --}}
                    <div class="flex gap-1.5 mt-4 p-1 bg-gray-100 rounded-xl">
                        <button type="button" @click="tab = 'surat-jalan'"
                                :class="tab === 'surat-jalan' ? 'bg-white text-gray-900 shadow-sm' : 'text-gray-500 hover:text-gray-700'"
                                class="flex-1 flex items-center justify-center gap-1.5 py-2 px-3 rounded-lg text-xs font-bold transition-all">
                            <span>📦</span> Surat Jalan
                        </button>
                        <button type="button" @click="tab = 'penjualan'"
                                :class="tab === 'penjualan' ? 'bg-white text-gray-900 shadow-sm' : 'text-gray-500 hover:text-gray-700'"
                                class="flex-1 flex items-center justify-center gap-1.5 py-2 px-3 rounded-lg text-xs font-bold transition-all">
                            <span>🧾</span> Faktur
                        </button>
                    </div>
                </div>

                {{-- Body scroll --}}
                <div class="flex-1 overflow-y-auto overscroll-contain px-5 py-4 space-y-3">

                    {{-- Surat Jalan --}}
                    <div x-show="tab === 'surat-jalan'" class="space-y-3">
                        @foreach($printers as $key => $p)
                        <div class="rounded-xl border border-gray-200 bg-white overflow-hidden shadow-sm">
                            <div class="flex items-center gap-3 px-4 py-3 bg-gray-50/80 border-b border-gray-100">
                                <div class="w-9 h-9 rounded-lg flex items-center justify-center shrink-0
                                    {{ $key === 'a4' ? 'bg-blue-100 text-blue-600' : ($key === 'thermal' ? 'bg-orange-100 text-orange-600' : 'bg-slate-200 text-slate-700') }}">
                                    @if($key === 'a4')
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                    @elseif($key === 'thermal')
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                                    @else
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                                    @endif
                                </div>
                                <div class="min-w-0 flex-1">
                                    <p class="text-sm font-bold text-gray-800">{{ $p['label'] }}</p>
                                    <p class="text-[11px] text-gray-400">{{ $p['desc'] }}</p>
                                </div>
                            </div>
                            <div class="grid grid-cols-2 gap-2 p-3">
                                <a href="{{ $printUrl('surat-jalan', 'pt', $key) }}" target="_blank" rel="noopener"
                                   @click="open = false"
                                   class="flex items-center gap-2 px-3 py-2.5 rounded-lg border border-emerald-200 bg-emerald-50 hover:bg-emerald-100 text-emerald-800 text-xs font-bold transition-colors">
                                    <span class="w-2 h-2 rounded-full bg-emerald-600 shrink-0"></span>
                                    <span class="truncate">PT NMF</span>
                                </a>
                                <a href="{{ $printUrl('surat-jalan', 'apotek', $key) }}" target="_blank" rel="noopener"
                                   @click="open = false"
                                   class="flex items-center gap-2 px-3 py-2.5 rounded-lg border border-blue-200 bg-blue-50 hover:bg-blue-100 text-blue-800 text-xs font-bold transition-colors">
                                    <span class="w-2 h-2 rounded-full bg-blue-600 shrink-0"></span>
                                    <span class="truncate">Apotek Almaira</span>
                                </a>
                            </div>
                        </div>
                        @endforeach
                    </div>

                    {{-- Faktur Penjualan --}}
                    <div x-show="tab === 'penjualan'" x-cloak class="space-y-3">
                        @foreach($printers as $key => $p)
                        <div class="rounded-xl border border-gray-200 bg-white overflow-hidden shadow-sm">
                            <div class="flex items-center gap-3 px-4 py-3 bg-gray-50/80 border-b border-gray-100">
                                <div class="w-9 h-9 rounded-lg flex items-center justify-center shrink-0
                                    {{ $key === 'a4' ? 'bg-blue-100 text-blue-600' : ($key === 'thermal' ? 'bg-orange-100 text-orange-600' : 'bg-slate-200 text-slate-700') }}">
                                    @if($key === 'a4')
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                    @elseif($key === 'thermal')
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                                    @else
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                                    @endif
                                </div>
                                <div class="min-w-0 flex-1">
                                    <p class="text-sm font-bold text-gray-800">{{ $p['label'] }}</p>
                                    <p class="text-[11px] text-gray-400">{{ $p['desc'] }}</p>
                                </div>
                            </div>
                            <div class="grid grid-cols-2 gap-2 p-3">
                                <a href="{{ $printUrl('penjualan', 'pt', $key) }}" target="_blank" rel="noopener"
                                   @click="open = false"
                                   class="flex items-center gap-2 px-3 py-2.5 rounded-lg border border-emerald-200 bg-emerald-50 hover:bg-emerald-100 text-emerald-800 text-xs font-bold transition-colors">
                                    <span class="w-2 h-2 rounded-full bg-emerald-600 shrink-0"></span>
                                    <span class="truncate">PT NMF</span>
                                </a>
                                <a href="{{ $printUrl('penjualan', 'apotek', $key) }}" target="_blank" rel="noopener"
                                   @click="open = false"
                                   class="flex items-center gap-2 px-3 py-2.5 rounded-lg border border-blue-200 bg-blue-50 hover:bg-blue-100 text-blue-800 text-xs font-bold transition-colors">
                                    <span class="w-2 h-2 rounded-full bg-blue-600 shrink-0"></span>
                                    <span class="truncate">Apotek Almaira</span>
                                </a>
                            </div>
                        </div>
                        @endforeach

                        @if($order->payment_method === 'invoice')
                        <div class="flex items-start gap-2.5 p-3 rounded-xl bg-amber-50 border border-amber-200 text-amber-800">
                            <svg class="w-4 h-4 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            <p class="text-[11px] leading-relaxed">Tanda tangan resmi hanya pada cetak <strong>Invoice Tempo</strong> (A4 &amp; Epson LX-310).</p>
                        </div>
                        @endif
                    </div>
                </div>

                {{-- Footer --}}
                <div class="shrink-0 px-5 py-3 border-t border-gray-100 bg-gray-50/50">
                    <p class="text-[10px] text-center text-gray-400">Dokumen dibuka di tab baru · Pilih format printer sesuai perangkat Anda</p>
                </div>
            </div>
        </div>
    </template>
</div>
