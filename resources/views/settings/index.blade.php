@extends('layouts.app')

@section('title', 'Pengaturan')
@section('page-title', 'Pengaturan Sistem')

@section('breadcrumb')
<svg class="w-3 h-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
<span class="text-gray-600 font-medium">Pengaturan</span>
@endsection

@section('content')
<script>
    window.discountManager = () => ({
        rules: @json($discountRules ?? []),
        addRule() {
            this.rules.push({
                min_qty: 1,
                max_qty: 10,
                percents: ''
            });
        },
        removeRule(index) {
            if (confirm('Hapus aturan diskon ini?')) {
                this.rules.splice(index, 1);
            }
        }
    });
    window.wholesaleMarkupManager = () => ({
        options: @json(array_values($wholesaleMarkupOptions ?? range(1, 30))),
        defaultMarkup: {{ (int) ($wholesaleMarkupDefault ?? 0) }},
        all: Array.from({ length: 30 }, (_, i) => i + 1),
        isOn(n) {
            return this.options.map(Number).includes(Number(n));
        },
        toggle(n) {
            const val = Number(n);
            if (this.isOn(val)) {
                this.options = this.options.map(Number).filter(x => x !== val);
            } else {
                this.options = [...this.options.map(Number), val].sort((a, b) => a - b);
            }
        },
        selectAll() {
            this.options = [...this.all];
        },
        selectSteps() {
            this.options = [5, 10, 15, 20, 25, 30];
        },
        optionsCsv() {
            return this.options.map(Number).join(',');
        }
    });
    window.alpineComponents = window.alpineComponents || {};
    window.alpineComponents.discountManager = window.discountManager;
    window.alpineComponents.wholesaleMarkupManager = window.wholesaleMarkupManager;
    if (window.Alpine && typeof window.Alpine.data === 'function') {
        window.Alpine.data('discountManager', window.discountManager);
        window.Alpine.data('wholesaleMarkupManager', window.wholesaleMarkupManager);
    }
</script>

<div class="animate-in settings-page" x-data="{ activeTab: 'info' }">
    <div class="page-header mb-6">
        <div>
            <h2 class="page-title text-2xl font-bold">Pengaturan Sistem</h2>
            <p class="page-subtitle text-gray-500">Konfigurasi informasi apotek, perpajakan, diskon kasir, dan koneksi printer</p>
        </div>
    </div>

    @if($errors->any())
    <div class="mb-6 p-4 bg-red-50 border border-red-200 rounded-xl text-red-700 text-sm">
        <ul class="list-disc pl-5">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    {{-- Form --}}
    <form action="{{ route('settings.update') }}" method="POST">
        @csrf

        <div class="flex flex-col lg:flex-row gap-6">
            {{-- Tabs Sidebar --}}
            <div class="w-full lg:w-64 flex-shrink-0">
                <div class="card p-3 bg-white border border-gray-100 rounded-2xl shadow-sm flex flex-row lg:flex-col gap-1 overflow-x-auto lg:overflow-visible">
                    {{-- Tab 1: Info --}}
                    <button type="button" @click="activeTab = 'info'" 
                            class="flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-semibold transition-all w-full text-left whitespace-nowrap cursor-pointer"
                            :class="activeTab === 'info' ? 'bg-emerald-500 text-white shadow-md shadow-emerald-500/10' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900'">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2 3.5 2z"/></svg>
                        Informasi Apotek
                    </button>
                    {{-- Tab 2: PPN --}}
                    <button type="button" @click="activeTab = 'ppn'" 
                            class="flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-semibold transition-all w-full text-left whitespace-nowrap cursor-pointer"
                            :class="activeTab === 'ppn' ? 'bg-emerald-500 text-white shadow-md shadow-emerald-500/10' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900'">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2 3.5 2zM10 8.5a.5.5 0 11-1 0 .5.5 0 011 0zm5 5a.5.5 0 11-1 0 .5.5 0 011 0z"/></svg>
                        Pengaturan PPN
                    </button>
                    {{-- Tab 3: Diskon --}}
                    <button type="button" @click="activeTab = 'discount'" 
                            class="flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-semibold transition-all w-full text-left whitespace-nowrap cursor-pointer"
                            :class="activeTab === 'discount' ? 'bg-emerald-500 text-white shadow-md shadow-emerald-500/10' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900'">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/></svg>
                        Aturan Diskon
                    </button>
                    {{-- Tab 4: Printer --}}
                    <button type="button" @click="activeTab = 'printer'" 
                            class="flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-semibold transition-all w-full text-left whitespace-nowrap cursor-pointer"
                            :class="activeTab === 'printer' ? 'bg-emerald-500 text-white shadow-md shadow-emerald-500/10' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900'">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                        Pengaturan Printer
                    </button>
                    {{-- Tab 5: CRM --}}
                    <button type="button" @click="activeTab = 'crm'" 
                            class="flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-semibold transition-all w-full text-left whitespace-nowrap cursor-pointer"
                            :class="activeTab === 'crm' ? 'bg-emerald-500 text-white shadow-md shadow-emerald-500/10' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900'">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                        CRM & Loyalitas
                    </button>
                    {{-- Tab 6: Notifikasi --}}
                    <button type="button" @click="activeTab = 'notif'" 
                            class="flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-semibold transition-all w-full text-left whitespace-nowrap cursor-pointer"
                            :class="activeTab === 'notif' ? 'bg-emerald-500 text-white shadow-md shadow-emerald-500/10' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900'">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                        Notifikasi & Webhook
                    </button>
                </div>
            </div>

            {{-- Tab Contents --}}
            <div class="flex-1">
                {{-- TAB 1: Informasi Apotek --}}
                <div x-show="activeTab === 'info'" class="card p-6 bg-white border border-gray-100 rounded-2xl shadow-sm space-y-5 animate-in">
                    <h3 class="text-lg font-bold text-gray-800 border-b border-gray-50 pb-3 flex items-center gap-2">
                        <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2 3.5 2z"/></svg>
                        Informasi Apotek / Perusahaan
                    </h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="form-label font-bold text-gray-700">Nama Apotek <span class="text-red-500">*</span></label>
                            <input type="text" name="apotek_name" value="{{ old('apotek_name', $apotekName) }}" class="form-input" required>
                        </div>
                        <div>
                            <label class="form-label font-bold text-gray-700">Nama Perusahaan <span class="text-red-500">*</span></label>
                            <input type="text" name="company_name" value="{{ old('company_name', $companyName) }}" class="form-input" required>
                        </div>
                        <div class="md:col-span-2">
                            <label class="form-label font-bold text-gray-700">Alamat Apotek (Outlet) <span class="text-red-500">*</span></label>
                            <textarea name="address" rows="3" class="form-input" required>{{ old('address', $address) }}</textarea>
                        </div>
                        <div class="md:col-span-2">
                            <label class="form-label font-bold text-gray-700">Alamat Kantor PT (Pusat)</label>
                            <textarea name="office_address" rows="2" class="form-input" placeholder="Jl. Panglima Batur No. 16, Kel. Komet...">{{ old('office_address', $officeAddress) }}</textarea>
                            <p class="text-[11px] text-gray-400 mt-1">Ditampilkan di landing page & laporan resmi PT.</p>
                        </div>
                        <div>
                            <label class="form-label font-bold text-gray-700">Email Perusahaan</label>
                            <input type="email" name="company_email" value="{{ old('company_email', $companyEmail) }}" class="form-input" placeholder="ptnurmadanifarma@gmail.com">
                        </div>
                        <div>
                            <label class="form-label font-bold text-gray-700">Instagram</label>
                            <input type="text" name="company_instagram" value="{{ old('company_instagram', $companyInstagram) }}" class="form-input" placeholder="@apotekalmaira">
                        </div>
                        <div class="md:col-span-2">
                            <label class="form-label font-bold text-gray-700">Tagline Landing Page</label>
                            <input type="text" name="company_tagline" value="{{ old('company_tagline', $companyTagline) }}" class="form-input" placeholder="Solusi Kesehatan Terpercaya di Banjarbaru">
                        </div>
                        <div class="md:col-span-2">
                            <label class="form-label font-bold text-gray-700">Tentang Perusahaan (Landing Page)</label>
                            <textarea name="company_about" rows="3" class="form-input">{{ old('company_about', $companyAbout) }}</textarea>
                        </div>
                        <div class="md:col-span-2">
                            <label class="form-label font-bold text-gray-700">Visi Perusahaan</label>
                            <textarea name="company_vision" rows="2" class="form-input">{{ old('company_vision', $companyVision) }}</textarea>
                        </div>
                        <div class="md:col-span-2">
                            <label class="form-label font-bold text-gray-700">Misi Perusahaan</label>
                            <textarea name="company_mission" rows="6" class="form-input" placeholder="Satu baris per poin misi">{{ old('company_mission', $companyMission) }}</textarea>
                            <p class="text-[11px] text-gray-400 mt-1">Pisahkan setiap poin misi dengan baris baru (Enter).</p>
                        </div>
                        <div>
                            <label class="form-label font-bold text-gray-700">Telepon / WhatsApp <span class="text-red-500">*</span></label>
                            <input type="text" name="phone" value="{{ old('phone', $phone) }}" class="form-input" placeholder="Contoh: 0851-6665-7070" required>
                        </div>
                        <div>
                            <label class="form-label font-bold text-gray-700">QRIS NMID <span class="text-red-500">*</span></label>
                            <input type="text" name="qris_nmid" value="{{ old('qris_nmid', $qrisNmid) }}" class="form-input" placeholder="Contoh: ID1026522359276" required>
                        </div>
                        <div>
                            <label class="form-label font-bold text-gray-700">Bank (Rekening ATM PT Nur Madani Farma)</label>
                            <input type="text" name="bank_name" value="{{ old('bank_name', $bankName) }}" class="form-input" placeholder="Contoh: BCA / BRI / Mandiri">
                        </div>
                        <div>
                            <label class="form-label font-bold text-gray-700">No. Rekening / ATM</label>
                            <input type="text" name="bank_account" value="{{ old('bank_account', $bankAccount) }}" class="form-input" placeholder="Nomor rekening perusahaan">
                        </div>
                        <div class="md:col-span-2">
                            <label class="form-label font-bold text-gray-700">Atas Nama Rekening</label>
                            <input type="text" name="bank_holder" value="{{ old('bank_holder', $bankHolder) }}" class="form-input" placeholder="PT Nur Madani Farma">
                            <p class="text-[11px] text-gray-400 mt-1">Ditampilkan di checkout mitra & instruksi transfer PO.</p>
                        </div>

                        {{-- Divider / Section Title --}}
                        <div class="md:col-span-2 border-t border-gray-100 pt-4 mt-2">
                            <h4 class="text-sm font-bold text-emerald-700 mb-3 flex items-center gap-1.5">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                                Penanggung Jawab & Tanda Tangan Laporan
                            </h4>
                        </div>

                        {{-- Apoteker Penanggung Jawab (1) --}}
                        <div class="p-4 bg-emerald-50/30 border border-emerald-100/50 rounded-xl space-y-3">
                            <h5 class="text-xs font-bold text-emerald-800 uppercase tracking-wider">Apoteker Penanggung Jawab (APJ)</h5>
                            <div>
                                <label class="form-label text-xs font-semibold text-gray-600">Nama Lengkap & Gelar <span class="text-red-500">*</span></label>
                                <input type="text" name="apoteker_1_name" value="{{ old('apoteker_1_name', $apoteker1Name) }}" class="form-input py-1.5 text-sm" required>
                            </div>
                            <div class="grid grid-cols-2 gap-2">
                                <div>
                                    <label class="form-label text-xs font-semibold text-gray-600">Nomor SIP <span class="text-red-500">*</span></label>
                                    <input type="text" name="apoteker_1_sip" value="{{ old('apoteker_1_sip', $apoteker1Sip) }}" class="form-input py-1.5 text-sm" required>
                                </div>
                                <div>
                                    <label class="form-label text-xs font-semibold text-gray-600">Nomor STR <span class="text-red-500">*</span></label>
                                    <input type="text" name="apoteker_1_str" value="{{ old('apoteker_1_str', $apoteker1Str) }}" class="form-input py-1.5 text-sm" required>
                                </div>
                            </div>
                        </div>

                        {{-- Apoteker Pendamping (2) --}}
                        <div class="p-4 bg-teal-50/30 border border-teal-100/50 rounded-xl space-y-3">
                            <h5 class="text-xs font-bold text-teal-800 uppercase tracking-wider">Apoteker Pendamping (Aping)</h5>
                            <div>
                                <label class="form-label text-xs font-semibold text-gray-600">Nama Lengkap & Gelar <span class="text-red-500">*</span></label>
                                <input type="text" name="apoteker_2_name" value="{{ old('apoteker_2_name', $apoteker2Name) }}" class="form-input py-1.5 text-sm" required>
                            </div>
                            <div class="grid grid-cols-2 gap-2">
                                <div>
                                    <label class="form-label text-xs font-semibold text-gray-600">Nomor SIP <span class="text-red-500">*</span></label>
                                    <input type="text" name="apoteker_2_sip" value="{{ old('apoteker_2_sip', $apoteker2Sip) }}" class="form-input py-1.5 text-sm" required>
                                </div>
                                <div>
                                    <label class="form-label text-xs font-semibold text-gray-600">Nomor STR <span class="text-red-500">*</span></label>
                                    <input type="text" name="apoteker_2_str" value="{{ old('apoteker_2_str', $apoteker2Str) }}" class="form-input py-1.5 text-sm" required>
                                </div>
                            </div>
                        </div>

                        {{-- Pimpinan / Direktur --}}
                        <div class="md:col-span-2 p-4 bg-slate-50 border border-slate-200 rounded-xl space-y-3">
                            <h5 class="text-xs font-bold text-slate-700 uppercase tracking-wider">Pimpinan Apotek & Direktur PT</h5>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="form-label text-xs font-semibold text-gray-600">Nama Lengkap Pimpinan <span class="text-red-500">*</span></label>
                                    <input type="text" name="pimpinan_name" value="{{ old('pimpinan_name', $pimpinanName) }}" class="form-input py-1.5 text-sm" required>
                                </div>
                                <div class="flex items-center">
                                    <p class="text-xs text-gray-500 italic mt-0 md:mt-4">Pimpinan ini akan menandatangani laporan di kolom Pimpinan Apotek / Direktur PT Nur Madani Farma.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- TAB 2: Pengaturan PPN --}}
                <div x-show="activeTab === 'ppn'" class="card p-6 bg-white border border-gray-100 rounded-2xl shadow-sm space-y-5 animate-in" x-cloak>
                    <h3 class="text-lg font-bold text-gray-800 border-b border-gray-50 pb-3 flex items-center gap-2">
                        <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2 3.5 2zM10 8.5a.5.5 0 11-1 0 .5.5 0 011 0zm5 5a.5.5 0 11-1 0 .5.5 0 011 0z"/></svg>
                        Pengaturan PPN (Pajak Pertambahan Nilai)
                    </h3>
                    
                    <div class="space-y-4">
                        <div class="bg-gray-50 p-4 border border-gray-100 rounded-xl">
                            <label class="flex items-center gap-3 cursor-pointer">
                                <input type="checkbox" name="pos_ppn_active" value="true" class="rounded border-gray-300 text-emerald-600 focus:ring-emerald-500 w-5 h-5" {{ $ppnActive === 'true' ? 'checked' : '' }}>
                                <div>
                                    <span class="font-bold text-gray-800 text-sm">Aktifkan PPN di Kasir secara default</span>
                                    <p class="text-xs text-gray-500 mt-0.5">Jika diaktifkan, PPN akan otomatis tercentang pada setiap transaksi kasir.</p>
                                </div>
                            </label>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="form-label font-bold text-gray-700">Persentase PPN default (%)</label>
                                <div class="relative">
                                    <input type="number" step="0.1" name="pos_ppn_percent" class="form-input pr-10" value="{{ old('pos_ppn_percent', $ppnPercent) }}" required>
                                    <span class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 font-semibold">%</span>
                                </div>
                            </div>

                            <div>
                                <label class="form-label font-bold text-gray-700">Pihak Penanggung Default</label>
                                <select name="pos_ppn_bearer" class="form-input" required>
                                    <option value="buyer" {{ old('pos_ppn_bearer', $ppnBearer) === 'buyer' ? 'selected' : '' }}>Pembeli (Ditambahkan ke Total)</option>
                                    <option value="seller" {{ old('pos_ppn_bearer', $ppnBearer) === 'seller' ? 'selected' : '' }}>Penjual (Ditanggung Apotek / Absorbed)</option>
                                </select>
                            </div>
                        </div>

                        <div class="bg-amber-50/80 p-4 border border-amber-100 rounded-xl">
                            <label class="form-label font-bold text-gray-700">Markup Harga Invoice (%)</label>
                            <div class="relative max-w-xs">
                                <input type="number" min="0" max="100" step="1" name="invoice_price_markup_percent" class="form-input pr-10"
                                       value="{{ old('invoice_price_markup_percent', $invoiceMarkupPercent) }}" required>
                                <span class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 font-semibold">%</span>
                            </div>
                            <p class="text-xs text-gray-500 mt-1.5 leading-relaxed">
                                Saat metode bayar <strong>Invoice</strong> (POS CRM &amp; PO Mitra), harga unit dihitung dari <strong>harga jual + markup</strong> ini.
                                Default 5%. Tidak berlaku untuk Tunai / QRIS / Transfer / COD.
                            </p>
                        </div>
                    </div>
                </div>

                {{-- TAB 3: Pengaturan Diskon --}}
                <div x-show="activeTab === 'discount'" class="card p-6 bg-white border border-gray-100 rounded-2xl shadow-sm animate-in" x-data="discountManager()" x-cloak>
                    <div class="flex items-center justify-between border-b border-gray-50 pb-3 mb-4">
                        <h3 class="text-lg font-bold text-gray-800 flex items-center gap-2">
                            <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/></svg>
                            Master Data Diskon Bertingkat (Tiers)
                        </h3>
                        <button type="button" @click="addRule()" class="btn btn-secondary py-1.5 px-3 text-xs flex items-center gap-1.5">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                            Tambah Aturan
                        </button>
                    </div>
                    
                    <p class="text-xs text-gray-500 mb-4 leading-relaxed">Atur pilihan diskon persentase yang akan muncul secara dinamis di halaman Kasir berdasarkan kuantitas (Qty) barang yang dibeli.</p>
                    
                    <div class="flex flex-col gap-3">
                        <template x-for="(rule, index) in rules" :key="index">
                            <div class="flex flex-col md:flex-row gap-3 items-end p-4 bg-gray-50 border border-gray-100 rounded-xl relative">
                                <div class="w-full md:w-24">
                                    <label class="form-label text-[11px] font-semibold text-gray-500">Min Qty</label>
                                    <input type="number" :name="'rules['+index+'][min_qty]'" x-model.number="rule.min_qty" class="form-input text-sm py-1.5" required>
                                </div>
                                <div class="w-full md:w-24">
                                    <label class="form-label text-[11px] font-semibold text-gray-500">Max Qty</label>
                                    <input type="number" :name="'rules['+index+'][max_qty]'" x-model.number="rule.max_qty" class="form-input text-sm py-1.5" required>
                                </div>
                                <div class="flex-1 w-full">
                                    <label class="form-label text-[11px] font-semibold text-gray-500">Opsi Diskon (%) — Pisahkan dengan koma</label>
                                    <input type="text" :name="'rules['+index+'][percents]'" x-model="rule.percents" class="form-input text-sm py-1.5" placeholder="Contoh: 1.5, 2.5, 5.0" required>
                                </div>
                                <div class="pb-0.5">
                                    <button type="button" @click="removeRule(index)" class="btn btn-danger p-2 border border-red-200" title="Hapus Aturan">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    </button>
                                </div>
                            </div>
                        </template>
                        <div x-show="rules.length === 0" class="text-center p-6 text-sm text-gray-400 italic bg-gray-50 rounded-xl border border-dashed border-gray-200" x-cloak>
                            Belum ada aturan diskon yang dibuat.
                        </div>
                    </div>

                    <div class="mt-8 pt-6 border-t border-slate-200" x-data="wholesaleMarkupManager()">
                        <div class="rounded-2xl border-2 border-teal-200 bg-teal-50 p-5 sm:p-6 shadow-sm">
                            <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-3 mb-5">
                                <div>
                                    <h3 class="text-lg font-extrabold text-slate-900 flex items-center gap-2">
                                        <span class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-teal-600 text-white">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                        </span>
                                        HET Markup Grosir (%)
                                    </h3>
                                    <p class="text-sm text-slate-700 mt-2 leading-relaxed max-w-2xl">
                                        Opsi ini otomatis muncul di dropdown <strong class="text-slate-900">Master Produk → HET Markup Grosir</strong>.
                                        Grosir dihitung dari harga jual − %. Tidak ada paksaan default 5%.
                                    </p>
                                </div>
                                <div class="flex flex-wrap gap-2 shrink-0">
                                    <button type="button" @click="selectAll()"
                                            class="px-3 py-2 rounded-xl text-xs font-extrabold bg-teal-700 text-white hover:bg-teal-600 shadow-md shadow-teal-700/20">
                                        Pilih 1–30%
                                    </button>
                                    <button type="button" @click="selectSteps()"
                                            class="px-3 py-2 rounded-xl text-xs font-extrabold bg-white text-teal-800 border-2 border-teal-300 hover:bg-teal-100">
                                        5 / 10 / 15 / 20 / 25 / 30
                                    </button>
                                </div>
                            </div>

                            <input type="hidden" name="product_wholesale_markup_options" :value="optionsCsv()">

                            <div class="grid grid-cols-5 sm:grid-cols-10 gap-2 mb-5 p-3 rounded-xl bg-white border border-teal-100">
                                @foreach(range(1, 30) as $n)
                                <button type="button"
                                        @click="toggle({{ $n }})"
                                        class="py-2.5 rounded-xl text-xs font-black border-2 transition-all"
                                        :style="isOn({{ $n }})
                                            ? 'background:#0f766e;color:#ffffff;border-color:#0f766e;'
                                            : 'background:#f8fafc;color:#0f172a;border-color:#94a3b8;'">
                                    {{ $n }}%
                                </button>
                                @endforeach
                            </div>

                            <div class="flex flex-col sm:flex-row sm:items-end gap-4 p-4 rounded-xl bg-white border-2 border-teal-200">
                                <div class="flex-1">
                                    <label class="block text-xs font-extrabold text-slate-800 uppercase tracking-wide mb-1.5">Default Markup Grosir</label>
                                    <select name="product_wholesale_markup_default" x-model.number="defaultMarkup"
                                            class="form-input text-sm font-bold text-slate-900 bg-white border-2 border-slate-300">
                                        <option value="0">0% (Manual — tanpa auto markup)</option>
                                        @foreach(range(1, 30) as $n)
                                            <option value="{{ $n }}">{{ $n }}%</option>
                                        @endforeach
                                    </select>
                                    <p class="text-xs text-slate-600 mt-2 leading-relaxed">
                                        Dipakai untuk produk baru &amp; Sync Grosir hanya jika markup produk kosong.
                                        Pilih <strong>0% Manual</strong> jika tidak ingin fallback otomatis.
                                    </p>
                                </div>
                                <div class="text-sm font-extrabold text-teal-800 whitespace-nowrap pb-2 px-3 py-2 rounded-lg bg-teal-100 border border-teal-200">
                                    <span x-text="options.length"></span> opsi aktif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- TAB 4: Pengaturan Printer --}}
                <div x-show="activeTab === 'printer'" class="card p-6 bg-white border border-gray-100 rounded-2xl shadow-sm space-y-5 animate-in" x-data="{ connection: '{{ $printerConnection }}' }" x-cloak>
                    <h3 class="text-lg font-bold text-gray-800 border-b border-gray-50 pb-3 flex items-center gap-2">
                        <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                        Pengaturan Printer Termal ESC/POS
                    </h3>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label class="form-label font-bold text-gray-700">Jenis Koneksi</label>
                            <select name="printer_connection" x-model="connection" class="form-input" required>
                                <option value="USB">USB (Local Printer)</option>
                                <option value="LAN">LAN / Network (Wi-Fi/Ethernet)</option>
                                <option value="Serial">Serial / COM Port</option>
                            </select>
                        </div>

                        <div x-show="connection === 'LAN'" x-cloak>
                            <label class="form-label font-bold text-gray-700">IP Printer LAN</label>
                            <input type="text" name="printer_ip" value="{{ old('printer_ip', $printerIp) }}" class="form-input" placeholder="Contoh: 192.168.1.100" :required="connection === 'LAN'">
                        </div>

                        <div x-show="connection === 'LAN'" x-cloak>
                            <label class="form-label font-bold text-gray-700">Port Printer</label>
                            <input type="number" name="printer_port" value="{{ old('printer_port', $printerPort) }}" class="form-input" placeholder="Default: 9100" :required="connection === 'LAN'">
                        </div>
                    </div>

                    <div class="space-y-3 pt-3 border-t border-gray-50">
                        <label class="form-label font-bold text-gray-700">Teks Footer Struk (Maks 3 Baris)</label>
                        <div>
                            <span class="text-xs text-gray-400 block mb-1">Baris 1:</span>
                            <input type="text" name="printer_footer_1" value="{{ old('printer_footer_1', $printerFooter1) }}" class="form-input text-sm" placeholder="Contoh: Terima kasih telah berbelanja">
                        </div>
                        <div>
                            <span class="text-xs text-gray-400 block mb-1">Baris 2:</span>
                            <input type="text" name="printer_footer_2" value="{{ old('printer_footer_2', $printerFooter2) }}" class="form-input text-sm" placeholder="Contoh: di Apotek Almaira Banjarbaru">
                        </div>
                        <div>
                            <span class="text-xs text-gray-400 block mb-1">Baris 3:</span>
                            <input type="text" name="printer_footer_3" value="{{ old('printer_footer_3', $printerFooter3) }}" class="form-input text-sm" placeholder="Contoh: Semoga lekas sembuh dan sehat!">
                        </div>
                    </div>
                </div>

                {{-- TAB 5: CRM & Loyalitas --}}
                <div x-show="activeTab === 'crm'" class="card p-6 bg-white border border-gray-100 rounded-2xl shadow-sm space-y-5 animate-in" x-cloak>
                    <h3 class="text-lg font-bold text-gray-800 border-b border-gray-50 pb-3 flex items-center gap-2">
                        <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                        Pengaturan CRM & Poin Loyalitas
                    </h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="form-label font-bold text-gray-700">Setiap Belanja Kelipatan Rp <span class="text-red-500">*</span></label>
                            <input type="number" name="crm_point_multiplier" value="{{ old('crm_point_multiplier', $crmPointMultiplier) }}" class="form-input" min="1" required>
                            <span class="text-xs text-gray-400 mt-1 block">Contoh: 1000 berarti tiap kelipatan Rp 1.000 belanja akan mendapatkan 1 poin.</span>
                        </div>
                        <div>
                            <label class="form-label font-bold text-gray-700">Nilai Potongan Belanja per 1 Poin (Rp) <span class="text-red-500">*</span></label>
                            <input type="number" name="crm_point_value" value="{{ old('crm_point_value', $crmPointValue) }}" class="form-input" min="0" required>
                            <span class="text-xs text-gray-400 mt-1 block">Contoh: 1 berarti 1 poin bernilai potongan Rp 1.</span>
                        </div>
                    </div>
                </div>

                {{-- TAB 6: Notifikasi & Webhook --}}
                <div x-show="activeTab === 'notif'" class="card p-6 bg-white border border-gray-100 rounded-2xl shadow-sm space-y-5 animate-in" x-cloak>
                    <h3 class="text-lg font-bold text-gray-800 border-b border-gray-50 pb-3 flex items-center gap-2">
                        <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                        Konfigurasi Notifikasi & Webhook (WhatsApp / Email)
                    </h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        {{-- WhatsApp Alerts --}}
                        <div class="space-y-4 p-4 bg-slate-50 rounded-2xl border border-slate-100">
                            <div class="flex items-center justify-between">
                                <h4 class="text-sm font-bold text-gray-800">Notifikasi WhatsApp (Simulasi Webhook)</h4>
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input type="checkbox" name="notif_alert_wa" value="true" class="sr-only peer" {{ $notifAlertWa === 'true' ? 'checked' : '' }}>
                                    <div class="w-8 h-4.5 bg-slate-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-3.5 after:w-3.5 after:transition-all peer-checked:bg-emerald-500"></div>
                                </label>
                            </div>
                            <div>
                                <label class="form-label text-xs font-bold text-gray-600">Nomor WhatsApp Penerima Alert <span class="text-red-500">*</span></label>
                                <input type="text" name="notif_wa_number" value="{{ old('notif_wa_number', $notifWaNumber) }}" class="form-input text-xs py-2" placeholder="Contoh: 0851-6665-7070" required>
                            </div>
                        </div>

                        {{-- Email Alerts --}}
                        <div class="space-y-4 p-4 bg-slate-50 rounded-2xl border border-slate-100">
                            <div class="flex items-center justify-between">
                                <h4 class="text-sm font-bold text-gray-800">Notifikasi Email (Simulasi)</h4>
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input type="checkbox" name="notif_alert_email" value="true" class="sr-only peer" {{ $notifAlertEmail === 'true' ? 'checked' : '' }}>
                                    <div class="w-8 h-4.5 bg-slate-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-3.5 after:w-3.5 after:transition-all peer-checked:bg-emerald-500"></div>
                                </label>
                            </div>
                            <div>
                                <label class="form-label text-xs font-bold text-gray-600">Alamat Email Penerima Alert <span class="text-red-500">*</span></label>
                                <input type="email" name="notif_email_address" value="{{ old('notif_email_address', $notifEmailAddress) }}" class="form-input text-xs py-2" placeholder="Contoh: owner@apotekalmaira.com" required>
                            </div>
                        </div>
                    </div>

                    {{-- Event Toggles --}}
                    <div class="p-4 bg-slate-50 rounded-2xl border border-slate-100 space-y-3">
                        <h4 class="text-sm font-bold text-gray-800 mb-2">Pilih Kejadian yang Memicu Alert:</h4>
                        
                        <div class="flex items-center justify-between text-xs">
                            <div>
                                <span class="font-bold text-gray-700 block">Stok Kritis / Di Bawah Batas Minimum</span>
                                <span class="text-gray-400">Kirim alert ketika produk berada pada atau di bawah batas minimum stok</span>
                            </div>
                            <label class="relative inline-flex items-center cursor-pointer shrink-0">
                                <input type="checkbox" name="notif_alert_stock" value="true" class="sr-only peer" {{ $notifAlertStock === 'true' ? 'checked' : '' }}>
                                <div class="w-8 h-4.5 bg-slate-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-3.5 after:w-3.5 after:transition-all peer-checked:bg-emerald-500"></div>
                            </label>
                        </div>

                        <hr class="border-gray-200/60 my-2">

                        <div class="flex items-center justify-between text-xs">
                            <div>
                                <span class="font-bold text-gray-700 block">Backup Database Berhasil</span>
                                <span class="text-gray-400">Kirim alert konfirmasi setiap kali proses backup database selesai diproses</span>
                            </div>
                            <label class="relative inline-flex items-center cursor-pointer shrink-0">
                                <input type="checkbox" name="notif_alert_backup" value="true" class="sr-only peer" {{ $notifAlertBackup === 'true' ? 'checked' : '' }}>
                                <div class="w-8 h-4.5 bg-slate-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-3.5 after:w-3.5 after:transition-all peer-checked:bg-emerald-500"></div>
                            </label>
                        </div>
                    </div>
                </div>{{-- /TAB Notifikasi --}}

            </div>{{-- /Tab Contents --}}
        </div>

        {{-- Sticky save bar — di atas footer fixed, tidak terpotong --}}
        <div class="settings-save-bar">
            <div class="settings-save-bar__inner">
                <p class="settings-save-bar__hint">
                    Perubahan berlaku ke kasir & laporan setelah disimpan
                </p>
                <button type="submit" class="settings-save-bar__btn">
                    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"/></svg>
                    Simpan Pengaturan
                </button>
            </div>
        </div>
    </form>
</div>
@endsection


