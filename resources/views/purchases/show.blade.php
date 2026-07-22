@extends('layouts.app')
@section('title', 'Detail Barang Masuk')
@section('page-title', 'Barang Masuk')

@section('breadcrumb')
<svg class="w-3 h-3 text-gray-400" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"/></svg>
<a wire:navigate href="{{ route('purchases.index') }}" class="hover:text-primary-600 transition-colors">Barang Masuk</a>
<svg class="w-3 h-3 text-gray-400" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"/></svg>
<span class="text-gray-600 font-medium">Detail</span>
@endsection

@section('content')
<style>
    @media print {
        body * {
            visibility: hidden;
        }
        #printable-area, #printable-area * {
            visibility: visible;
        }
        #printable-area {
            position: absolute;
            left: 0;
            top: 0;
            width: 100%;
            border: none !important;
            box-shadow: none !important;
            padding: 0 !important;
            margin: 0 !important;
        }
        .no-print {
            display: none !important;
        }
    }
</style>

<div class="animate-in max-w-4xl mx-auto" x-data="{ entity: 'pt' }">
    {{-- Header --}}
    <div class="page-header mb-6 no-print">
        <div>
            <h2 class="page-title text-2xl font-bold text-gray-800">Detail Faktur Barang Masuk</h2>
            <p class="page-subtitle text-gray-500">Melihat detail transaksi barang masuk dan daftar item terkait</p>
        </div>
        <div class="flex items-center gap-3">
            @if($purchase->status !== 'received')
            <a wire:navigate href="{{ route('purchases.edit', $purchase) }}" class="btn btn-warning flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                Edit PO
            </a>
            <form action="{{ route('purchases.receive', $purchase) }}" method="POST" class="inline">
                @csrf
                <button type="submit" class="btn btn-success flex items-center gap-2 bg-emerald-600 hover:bg-emerald-700 text-white font-bold py-2.5 px-4 rounded-xl shadow-md cursor-pointer transition-all">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                    Terima Barang
                </button>
            </form>
            @endif
            <div class="flex items-center gap-2 no-print">
                <select x-model="entity" class="form-input py-2 px-3 text-sm rounded-xl border-gray-200" style="width: 170px;">
                    <option value="pt">PT Nur Madani Farma</option>
                    <option value="apotek">Apotek Almaira</option>
                </select>
                <a :href="'{{ route('purchases.pdf', $purchase) }}?entity=' + entity" target="_blank" class="btn btn-secondary flex items-center gap-2 bg-red-50 text-red-600 hover:bg-red-100 border-red-100">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    Unduh PDF A4
                </a>
            </div>
            <button onclick="window.print()" class="btn btn-secondary flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                Cetak Faktur
            </button>
            <a wire:navigate href="{{ route('purchases.index') }}" class="btn btn-primary flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                Daftar Barang Masuk
            </a>
        </div>
    </div>

    {{-- Invoice Card --}}
    <div class="card p-8 bg-white border border-gray-100 rounded-2xl shadow-sm relative overflow-hidden" id="printable-area">
        {{-- Watermark background --}}
        <div class="absolute inset-0 flex items-center justify-center opacity-[0.08] pointer-events-none select-none">
            <img :src="entity === 'pt' ? '{{ asset('assets/images/watermark-ptnmf.png') }}' : '{{ asset('assets/images/watermark-apotek.png') }}'" class="w-[500px]" alt="Watermark">
        </div>

        {{-- Invoice Header / Kop --}}
        <div class="flex items-start justify-between border-b-2 border-gray-100 pb-6 mb-6">
            <div class="flex items-center gap-4">
                <img :src="entity === 'pt' ? '{{ asset('assets/images/logo-ptnmf.png') }}' : '{{ asset('assets/images/logo-apotek.png') }}'" class="w-14 h-14 object-contain rounded-lg" alt="Logo">
                <div>
                    <h1 class="text-xl font-bold text-gray-800 uppercase tracking-tight" x-text="entity === 'pt' ? 'PT Nur Madani Farma' : 'Apotek Almaira'"></h1>
                    <p class="text-xs text-gray-500 font-semibold uppercase tracking-wider" x-text="entity === 'pt' ? 'Distributor & Mitra Pengadaan Alat Kesehatan & Farmasi' : ''" x-show="entity === 'pt'"></p>
                    <template x-if="entity === 'pt'">
                        <p class="text-[11px] text-gray-400 mt-1 max-w-md leading-relaxed">
                            Jl. Panglima Batur No. 16, Kel. Komet, Kec. Banjarbaru Utara, Kota Banjarbaru, Kalsel 70714
                            <br>WhatsApp: 0851-6665-7070 &nbsp;|&nbsp; Email: ptnurmadanifarma@gmail.com &nbsp;|&nbsp; Instagram: @apotekalmaira
                        </p>
                    </template>
                    <template x-if="entity !== 'pt'">
                        <p class="text-[11px] text-gray-400 mt-1 max-w-sm">
                            Jl. Nuri No.14 RT/RW 001/005, Kel. Komet, Kec. Banjarbaru Utara, Kalsel 70714
                            <br>Telp/WA: 0851-6665-7070
                        </p>
                    </template>
                </div>
            </div>
            
            <div class="text-right">
                @if($purchase->status === 'received')
                <span class="badge bg-emerald-50 text-emerald-700 border border-emerald-200 uppercase tracking-widest text-[10px] font-bold px-3 py-1">Barang Masuk</span>
                @elseif($purchase->status === 'sent')
                <span class="badge bg-amber-50 text-amber-700 border border-amber-200 uppercase tracking-widest text-[10px] font-bold px-3 py-1">PO Dikirim</span>
                @else
                <span class="badge bg-gray-50 text-gray-700 border border-gray-200 uppercase tracking-widest text-[10px] font-bold px-3 py-1">Draft PO</span>
                @endif
                <p class="font-mono text-sm font-semibold text-gray-700 mt-2">No. FAKTUR Pembelian: {{ $purchase->reference_no }}</p>
                <p class="text-xs text-gray-400 mt-1">Tanggal: {{ $purchase->purchase_date->format('d M Y') }}</p>
            </div>
        </div>

        {{-- Details Grid --}}
        <div class="grid grid-cols-2 gap-6 mb-8 text-sm">
            <div>
                <p class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">Diterima Dari (Supplier):</p>
                <p class="font-bold text-gray-800 text-base">{{ $purchase->supplier?->name ?? '-' }}</p>
                @if($purchase->supplier?->phone)
                <p class="text-xs text-gray-500 mt-1">Telp: {{ $purchase->supplier->phone }}</p>
                @endif
                @if($purchase->supplier?->address)
                <p class="text-xs text-gray-400 mt-1 max-w-xs">{{ $purchase->supplier->address }}</p>
                @endif
            </div>

            <div class="text-right">
                <p class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">Petugas Penerima:</p>
                <p class="font-bold text-gray-800">{{ $purchase->user?->name ?? '-' }}</p>
                <p class="text-xs text-gray-500 mt-1">Role: {{ $purchase->user?->role?->name ?? '-' }}</p>
                <p class="text-xs text-gray-400 mt-1">Dicatat pada: {{ $purchase->created_at->format('d/m/Y H:i') }}</p>
            </div>
        </div>

        {{-- Items Table --}}
        <div class="border border-gray-100 rounded-xl overflow-hidden mb-6">
            <table class="w-full text-left border-collapse text-sm">
                <thead>
                    <tr class="bg-gray-50 border-b border-gray-100 text-xs font-bold text-gray-500 uppercase">
                        <th class="py-3 px-4 w-12 text-center">#</th>
                        <th class="py-3 px-4">Nama Produk</th>
                        <th class="py-3 px-4 text-center w-24">Qty</th>
                        <th class="py-3 px-4 text-center w-24">Satuan</th>
                        <th class="py-3 px-4 text-right w-36">Harga Beli (HPP)</th>
                        <th class="py-3 px-4 text-right w-36">Harga Jual</th>
                        <th class="py-3 px-4 text-right w-36">Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($purchase->items as $i => $item)
                    <tr class="border-b border-gray-100 last:border-0 hover:bg-gray-50/50">
                        <td class="py-3.5 px-4 text-center text-gray-400">{{ $i + 1 }}</td>
                        <td class="py-3.5 px-4 font-semibold text-gray-800">
                            {{ $item->product_name }}
                            @if($item->expired_date)
                            <span class="block text-[10px] text-amber-600 font-semibold mt-0.5">Exp: {{ $item->expired_date->format('d/m/Y') }}</span>
                            @endif
                        </td>
                        <td class="py-3.5 px-4 text-center font-semibold text-gray-700">{{ $item->quantity }}</td>
                        <td class="py-3.5 px-4 text-center text-gray-500 text-xs">{{ $item->product?->unit?->name ?? '-' }}</td>
                        <td class="py-3.5 px-4 text-right font-medium text-gray-600">Rp {{ number_format($item->purchase_price, 0, ',', '.') }}</td>
                        <td class="py-3.5 px-4 text-right font-medium text-gray-600">Rp {{ number_format($item->sell_price, 0, ',', '.') }}</td>
                        <td class="py-3.5 px-4 text-right font-bold text-gray-800">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{-- Totals --}}
        <div class="flex flex-col items-end border-t border-gray-100 pt-4">
            <div class="w-80 space-y-2">
                <div class="flex items-center justify-between text-sm">
                    <span class="text-gray-400 font-medium">Subtotal</span>
                    <span class="font-bold text-gray-700">Rp {{ number_format($purchase->total_amount, 0, ',', '.') }}</span>
                </div>
                <div class="flex items-center justify-between border-t border-gray-100 pt-2 text-base">
                    <span class="text-gray-500 font-bold">Total Pembayaran</span>
                    <span class="font-extrabold text-primary-600 text-lg">Rp {{ number_format($purchase->total_amount, 0, ',', '.') }}</span>
                </div>
            </div>
        </div>

        @if($purchase->notes)
        <div class="mt-8 border-t border-gray-100 pt-4 text-xs text-gray-400">
            <span class="font-bold text-gray-500 block mb-1">Catatan Tambahan:</span>
            {{ $purchase->notes }}
        </div>
        @endif

        {{-- Signature Section --}}
        <div class="mt-12 grid grid-cols-2 gap-6 text-sm text-center border-t border-gray-100 pt-6">
            <div>
                <p class="font-bold text-gray-500 uppercase tracking-wider mb-14 text-xs">Diserahkan Oleh / Supplier</p>
                <p class="font-bold text-gray-800 underline">....................................</p>
                <p class="text-xs text-gray-400 mt-1">Tanda Tangan & Cap Supplier</p>
            </div>
            <div>
                <p class="font-bold text-gray-500 uppercase tracking-wider mb-14 text-xs" x-text="entity === 'pt' ? 'Diterima Oleh / Direktur' : 'Diterima Oleh / Apoteker'"></p>
                <p class="font-bold text-gray-800 underline" x-text="entity === 'pt' ? 'Hj. Nor Maulida, S.H.' : 'Apt. Wulan Ageng Sujatmiko, S.Farm., M.M.'"></p>
                <p class="text-xs text-gray-400 mt-1" x-text="entity === 'pt' ? 'Direktur PT Nur Madani Farma' : 'Apoteker Penanggung Jawab'"></p>
                <template x-if="entity === 'apotek'">
                    <p class="text-[10px] text-gray-400">SIP: NR63722606010965</p>
                </template>
            </div>
        </div>

        {{-- Invoice Footer --}}
        <div class="mt-12 border-t border-dashed border-gray-200 pt-6 flex items-center justify-between text-[10px] text-gray-400">
            <div class="flex items-center gap-2">
                <img :src="entity === 'pt' ? '{{ asset('assets/images/logo-ptnmf.png') }}' : '{{ asset('assets/images/logo-apotek.png') }}'" class="w-6 h-6 object-contain" alt="Logo">
                <span x-text="entity === 'pt' ? 'PT Nur Madani Farma' : 'Apotek Almaira'"></span>
            </div>
            <span>Halaman 1 dari 1</span>
        </div>
    </div>
</div>


@endsection
