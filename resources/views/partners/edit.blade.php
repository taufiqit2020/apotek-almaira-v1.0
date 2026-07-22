@extends('layouts.app')
@section('title', 'Edit Mitra')
@section('page-title', 'Mitra Katalog')

@section('breadcrumb')
<svg class="w-3 h-3 text-gray-400 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"/></svg>
<a href="{{ route('partners.index') }}" class="hover:text-primary-600 transition-colors whitespace-nowrap">Mitra Katalog</a>
<svg class="w-3 h-3 text-gray-400 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"/></svg>
<a href="{{ route('partners.show', $partner) }}" class="hover:text-primary-600 transition-colors truncate max-w-[10rem] sm:max-w-none">{{ $partner->code }}</a>
<svg class="w-3 h-3 text-gray-400 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"/></svg>
<span class="text-gray-600 font-medium">Edit</span>
@endsection

@section('content')
@php
    $statusBadge = match($partner->status) {
        'approved' => 'bg-emerald-100 text-emerald-800 border-emerald-200',
        'pending'  => 'bg-amber-100 text-amber-800 border-amber-200',
        'rejected' => 'bg-red-100 text-red-800 border-red-200',
        default    => 'bg-slate-100 text-slate-600 border-slate-200',
    };
@endphp

<div class="animate-in max-w-4xl mx-auto pb-36" x-data="{ createLogin: {{ old('create_login') ? 'true' : 'false' }}, ppnEnabled: {{ old('ppn_enabled', $partner->ppn_enabled) ? 'true' : 'false' }} }">

    {{-- Hero --}}
    <div class="mb-5 rounded-2xl bg-gradient-to-br from-emerald-700 via-emerald-600 to-teal-600 p-5 sm:p-6 text-white shadow-lg shadow-emerald-900/10 relative overflow-hidden">
        <div class="absolute top-0 right-0 w-48 h-48 bg-white/5 rounded-full -translate-y-1/2 translate-x-1/3 pointer-events-none"></div>
        <div class="relative flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div class="flex items-start gap-4 min-w-0">
                <div class="w-12 h-12 rounded-xl bg-white/15 border border-white/20 flex items-center justify-center shrink-0">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                </div>
                <div class="min-w-0">
                    <p class="font-mono text-xs font-bold text-emerald-100/90">{{ $partner->code }}</p>
                    <h2 class="text-xl sm:text-2xl font-extrabold leading-tight mt-0.5 break-words">{{ $partner->name }}</h2>
                    <div class="flex flex-wrap items-center gap-2 mt-2">
                        <span class="inline-flex px-2.5 py-1 rounded-lg text-[11px] font-bold border {{ $statusBadge }}">{{ $partner->status_label }}</span>
                        <span class="inline-flex px-2.5 py-1 rounded-lg text-[11px] font-bold bg-white/15 border border-white/20">{{ $partner->type_label }}</span>
                    </div>
                </div>
            </div>
            <a href="{{ route('partners.show', $partner) }}"
               class="inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl bg-white text-emerald-700 text-sm font-bold shadow-md hover:bg-emerald-50 transition-colors shrink-0 whitespace-nowrap">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                Kembali ke Detail
            </a>
        </div>
    </div>

    @if($errors->any())
    <div class="mb-5 flex items-start gap-3 p-4 rounded-2xl bg-red-50 border border-red-200 text-red-800 shadow-sm">
        <div class="w-9 h-9 rounded-xl bg-red-100 flex items-center justify-center shrink-0">
            <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        </div>
        <ul class="text-sm list-disc pl-4 space-y-0.5">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
    </div>
    @endif

    <form action="{{ route('partners.update', $partner) }}" method="POST" class="space-y-5" id="partner-edit-form">
        @csrf
        @method('PUT')

        {{-- Identitas --}}
        <div class="card overflow-hidden bg-white border border-gray-100 rounded-2xl shadow-sm">
            <div class="px-5 py-3.5 border-b border-gray-100 bg-slate-50/80 flex items-center gap-2.5">
                <div class="w-8 h-8 rounded-lg bg-indigo-100 flex items-center justify-center">
                    <svg class="w-4 h-4 text-indigo-700" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                </div>
                <div>
                    <h3 class="text-sm font-bold text-gray-800">Identitas & Status</h3>
                    <p class="text-[11px] text-gray-400">Data usaha dan status persetujuan mitra</p>
                </div>
            </div>
            <div class="p-5 sm:p-6 grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="sm:col-span-2">
                    <label class="form-label font-bold">Nama Usaha <span class="text-red-500">*</span></label>
                    <input type="text" name="name" value="{{ old('name', $partner->name) }}" class="form-input rounded-xl" required>
                </div>
                <div>
                    <label class="form-label font-bold">Tipe <span class="text-red-500">*</span></label>
                    <select name="type" class="form-input rounded-xl" required>
                        @foreach($types as $key => $label)
                        <option value="{{ $key }}" @selected(old('type', $partner->type) === $key)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="form-label font-bold">Status</label>
                    <select name="status" class="form-input rounded-xl">
                        @foreach($statuses as $key => $label)
                        <option value="{{ $key }}" @selected(old('status', $partner->status) === $key)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="form-label font-bold">Kota</label>
                    <input type="text" name="city" value="{{ old('city', $partner->city) }}" class="form-input rounded-xl">
                </div>
                <div>
                    <label class="form-label font-bold">NPWP</label>
                    <input type="text" name="npwp" value="{{ old('npwp', $partner->npwp) }}" class="form-input rounded-xl">
                </div>
                <div>
                    <label class="form-label font-bold">NIB</label>
                    <input type="text" name="nib" value="{{ old('nib', $partner->nib) }}" class="form-input rounded-xl">
                </div>
                <div class="sm:col-span-2">
                    <label class="form-label font-bold">Alamat</label>
                    <textarea name="address" rows="2" class="form-input rounded-xl">{{ old('address', $partner->address) }}</textarea>
                </div>
                <div class="sm:col-span-2">
                    <label class="form-label font-bold">Alasan penolakan (jika status ditolak)</label>
                    <input type="text" name="rejection_reason" value="{{ old('rejection_reason', $partner->rejection_reason) }}" class="form-input rounded-xl" placeholder="Wajib diisi jika status = Ditolak">
                </div>
            </div>
        </div>

        {{-- Kontak --}}
        <div class="card overflow-hidden bg-white border border-gray-100 rounded-2xl shadow-sm">
            <div class="px-5 py-3.5 border-b border-gray-100 bg-slate-50/80 flex items-center gap-2.5">
                <div class="w-8 h-8 rounded-lg bg-blue-100 flex items-center justify-center">
                    <svg class="w-4 h-4 text-blue-700" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                </div>
                <div>
                    <h3 class="text-sm font-bold text-gray-800">Kontak PIC</h3>
                    <p class="text-[11px] text-gray-400">Person in charge untuk komunikasi order</p>
                </div>
            </div>
            <div class="p-5 sm:p-6 grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="form-label font-bold">Nama PIC</label>
                    <input type="text" name="pic_name" value="{{ old('pic_name', $partner->pic_name) }}" class="form-input rounded-xl">
                </div>
                <div>
                    <label class="form-label font-bold">Telepon / WA <span class="text-red-500">*</span></label>
                    <input type="text" name="phone" value="{{ old('phone', $partner->phone) }}" class="form-input rounded-xl" required>
                </div>
                <div class="sm:col-span-2">
                    <label class="form-label font-bold">Email</label>
                    <input type="email" name="email" value="{{ old('email', $partner->email) }}" class="form-input rounded-xl">
                </div>
            </div>
        </div>

        {{-- Komersial --}}
        <div class="card overflow-hidden bg-white border border-gray-100 rounded-2xl shadow-sm">
            <div class="px-5 py-3.5 border-b border-gray-100 bg-slate-50/80 flex items-center gap-2.5">
                <div class="w-8 h-8 rounded-lg bg-amber-100 flex items-center justify-center">
                    <svg class="w-4 h-4 text-amber-700" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <div>
                    <h3 class="text-sm font-bold text-gray-800">Komersial</h3>
                    <p class="text-[11px] text-gray-400">Skema harga, tempo kredit, dan metode bayar PO</p>
                </div>
            </div>
            <div class="p-5 sm:p-6 space-y-5">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="form-label font-bold">Mode Harga</label>
                        <select name="price_mode" class="form-input rounded-xl">
                            @foreach($priceModes as $key => $label)
                            <option value="{{ $key }}" @selected(old('price_mode', $partner->price_mode) === $key)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="form-label font-bold">Tempo Invoice (hari)</label>
                        <input type="number" name="credit_days" value="{{ old('credit_days', $partner->credit_days) }}" min="1" max="90" class="form-input rounded-xl">
                        <p class="text-[11px] text-gray-400 mt-1">Jatuh tempo PO metode Invoice tempo</p>
                    </div>
                </div>

                <div>
                    <p class="text-[10px] font-bold uppercase tracking-wider text-gray-400 mb-2.5">Metode Pembayaran PO</p>
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                        <label class="relative flex items-start gap-3 p-3.5 rounded-xl border-2 cursor-pointer transition-all
                            {{ old('allow_transfer', $partner->allow_transfer) ? 'border-emerald-300 bg-emerald-50/60' : 'border-gray-200 bg-white hover:border-gray-300' }}">
                            <input type="hidden" name="allow_transfer" value="0">
                            <input type="checkbox" name="allow_transfer" value="1"
                                   class="mt-0.5 rounded border-gray-300 text-emerald-600 focus:ring-emerald-500"
                                   @checked(old('allow_transfer', $partner->allow_transfer))
                                   onchange="this.closest('label').classList.toggle('border-emerald-300', this.checked); this.closest('label').classList.toggle('bg-emerald-50/60', this.checked); this.closest('label').classList.toggle('border-gray-200', !this.checked); this.closest('label').classList.toggle('bg-white', !this.checked);">
                            <div>
                                <p class="text-sm font-bold text-gray-800">Transfer</p>
                                <p class="text-[11px] text-gray-500 mt-0.5">Bayar via rekening bank</p>
                            </div>
                        </label>
                        <label class="relative flex items-start gap-3 p-3.5 rounded-xl border-2 cursor-pointer transition-all
                            {{ old('allow_cod', $partner->allow_cod) ? 'border-emerald-300 bg-emerald-50/60' : 'border-gray-200 bg-white hover:border-gray-300' }}">
                            <input type="hidden" name="allow_cod" value="0">
                            <input type="checkbox" name="allow_cod" value="1"
                                   class="mt-0.5 rounded border-gray-300 text-emerald-600 focus:ring-emerald-500"
                                   @checked(old('allow_cod', $partner->allow_cod))
                                   onchange="this.closest('label').classList.toggle('border-emerald-300', this.checked); this.closest('label').classList.toggle('bg-emerald-50/60', this.checked); this.closest('label').classList.toggle('border-gray-200', !this.checked); this.closest('label').classList.toggle('bg-white', !this.checked);">
                            <div>
                                <p class="text-sm font-bold text-gray-800">COD</p>
                                <p class="text-[11px] text-gray-500 mt-0.5">Bayar di tempat saat kirim</p>
                            </div>
                        </label>
                        <label class="relative flex items-start gap-3 p-3.5 rounded-xl border-2 cursor-pointer transition-all
                            {{ old('invoice_enabled', $partner->invoice_enabled) ? 'border-emerald-300 bg-emerald-50/60' : 'border-gray-200 bg-white hover:border-gray-300' }}">
                            <input type="hidden" name="invoice_enabled" value="0">
                            <input type="checkbox" name="invoice_enabled" value="1"
                                   class="mt-0.5 rounded border-gray-300 text-emerald-600 focus:ring-emerald-500"
                                   @checked(old('invoice_enabled', $partner->invoice_enabled))
                                   onchange="this.closest('label').classList.toggle('border-emerald-300', this.checked); this.closest('label').classList.toggle('bg-emerald-50/60', this.checked); this.closest('label').classList.toggle('border-gray-200', !this.checked); this.closest('label').classList.toggle('bg-white', !this.checked);">
                            <div>
                                <p class="text-sm font-bold text-gray-800">Invoice Tempo</p>
                                <p class="text-[11px] text-gray-500 mt-0.5">Kredit & piutang mitra</p>
                            </div>
                        </label>
                    </div>
                </div>

                <div class="rounded-xl border border-sky-100 bg-sky-50/40 p-4 space-y-4">
                    <div class="flex items-start gap-3">
                        <input type="hidden" name="ppn_enabled" value="0">
                        <input type="checkbox" name="ppn_enabled" value="1" id="ppn_enabled"
                               class="mt-1 rounded border-gray-300 text-sky-600 focus:ring-sky-500"
                               x-model="ppnEnabled"
                               @checked(old('ppn_enabled', $partner->ppn_enabled))>
                        <div class="flex-1">
                            <label for="ppn_enabled" class="text-sm font-bold text-sky-900 cursor-pointer">Aktifkan PPN untuk mitra ini</label>
                            <p class="text-[11px] text-sky-800/80 mt-0.5">Jika aktif, PPN otomatis dihitung di keranjang & checkout PO mitra.</p>
                        </div>
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pl-0 sm:pl-8" x-show="ppnEnabled" x-cloak x-transition>
                        <div>
                            <label class="form-label font-bold">Persentase PPN (%)</label>
                            <input type="number" step="0.1" min="0" max="100" name="ppn_percent"
                                   value="{{ old('ppn_percent', $partner->ppn_percent ?? 11) }}"
                                   class="form-input rounded-xl">
                        </div>
                        <div>
                            <label class="form-label font-bold">Penanggung PPN</label>
                            <select name="ppn_bearer" class="form-input rounded-xl">
                                @foreach(\App\Models\Partner::ppnBearerOptions() as $key => $label)
                                <option value="{{ $key }}" @selected(old('ppn_bearer', $partner->ppn_bearer ?? 'buyer') === $key)>{{ $label }}</option>
                                @endforeach
                            </select>
                            <p class="text-[11px] text-gray-400 mt-1">Pembeli = ditambah ke total · PT NMF = harga netto (PPN ditanggung apotek)</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Akun Login --}}
        <div class="card overflow-hidden bg-white border border-gray-100 rounded-2xl shadow-sm">
            <div class="px-5 py-3.5 border-b border-gray-100 bg-slate-50/80 flex items-center gap-2.5">
                <div class="w-8 h-8 rounded-lg bg-violet-100 flex items-center justify-center">
                    <svg class="w-4 h-4 text-violet-700" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/></svg>
                </div>
                <div>
                    <h3 class="text-sm font-bold text-gray-800">Akun Login Portal</h3>
                    <p class="text-[11px] text-gray-400">Akses mitra ke e-catalog & PO</p>
                </div>
            </div>
            <div class="p-5 sm:p-6 space-y-4">
                @if($partner->user)
                <div class="rounded-xl border border-violet-100 bg-violet-50/50 p-4 flex flex-wrap items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-violet-100 flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5 text-violet-700" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                    </div>
                    <div class="min-w-0">
                        <p class="text-sm font-bold text-gray-800">{{ $partner->user->username }}</p>
                        <p class="text-xs text-gray-500">{{ $partner->user->email }}</p>
                    </div>
                </div>
                <div>
                    <label class="form-label font-bold">Ganti Password</label>
                    <input type="password" name="password" class="form-input rounded-xl" minlength="6" placeholder="Kosongkan jika tidak diubah">
                    <p class="text-[11px] text-gray-400 mt-1">Minimal 6 karakter</p>
                </div>
                @else
                <label class="inline-flex items-center gap-2.5 p-3.5 rounded-xl border-2 border-dashed border-gray-200 cursor-pointer hover:border-emerald-300 hover:bg-emerald-50/30 transition-all w-full sm:w-auto">
                    <input type="checkbox" name="create_login" value="1" class="rounded border-gray-300 text-emerald-600" x-model="createLogin">
                    <span class="text-sm font-bold text-gray-700">Buat akun login sekarang</span>
                </label>
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4" x-show="createLogin" x-cloak x-transition>
                    <div>
                        <label class="form-label font-bold">Username</label>
                        <input type="text" name="username" value="{{ old('username') }}" class="form-input rounded-xl">
                    </div>
                    <div>
                        <label class="form-label font-bold">Email Login</label>
                        <input type="email" name="login_email" value="{{ old('login_email', $partner->email) }}" class="form-input rounded-xl">
                    </div>
                    <div>
                        <label class="form-label font-bold">Password</label>
                        <input type="password" name="password" class="form-input rounded-xl" minlength="6">
                    </div>
                </div>
                @endif
            </div>
        </div>

        {{-- Catatan --}}
        <div class="card overflow-hidden bg-white border border-gray-100 rounded-2xl shadow-sm">
            <div class="px-5 py-3.5 border-b border-gray-100 bg-slate-50/80">
                <h3 class="text-sm font-bold text-gray-800">Catatan Internal</h3>
            </div>
            <div class="p-5 sm:p-6">
                <textarea name="notes" rows="3" class="form-input rounded-xl" placeholder="Catatan admin (opsional)">{{ old('notes', $partner->notes) }}</textarea>
            </div>
        </div>

        {{-- Spacer untuk sticky bar --}}
        <div class="h-4" aria-hidden="true"></div>
    </form>

    {{-- Sticky action bar (di atas footer fixed, mengikuti lebar sidebar) --}}
    <div class="app-sticky-bar fixed bottom-[4.75rem] right-0 z-30 px-4 sm:px-6 pointer-events-none"
         :class="{ 'is-sidebar-collapsed': collapsed }">
        <div class="max-w-4xl mx-auto pointer-events-auto">
            <div class="flex flex-col-reverse sm:flex-row sm:items-center sm:justify-between gap-3 p-3 sm:p-4 rounded-2xl bg-white/95 backdrop-blur-md border border-gray-200/80 shadow-lg shadow-gray-900/10">
                <p class="text-[11px] text-gray-400 hidden sm:block pl-1">Perubahan disimpan ke profil mitra & portal e-catalog</p>
                <div class="flex items-center justify-end gap-2.5 w-full sm:w-auto">
                    <a href="{{ route('partners.show', $partner) }}"
                       class="inline-flex items-center justify-center gap-1.5 px-4 py-2.5 rounded-xl border border-gray-200 bg-white text-gray-700 text-sm font-bold hover:bg-gray-50 transition-colors flex-1 sm:flex-none">
                        Batal
                    </a>
                    <button type="submit" form="partner-edit-form"
                            class="inline-flex items-center justify-center gap-1.5 px-5 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-bold shadow-md shadow-emerald-600/25 transition-colors flex-1 sm:flex-none">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        Simpan Perubahan
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
