@extends('layouts.app')
@section('title', 'Import Produk dari Excel')
@section('page-title', 'Import Excel')

@section('breadcrumb')
<svg class="w-3 h-3 text-gray-400" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"/></svg>
<a wire:navigate href="{{ route('products.index') }}" class="hover:text-primary-600 transition-colors">Master Produk</a>
<svg class="w-3 h-3 text-gray-400" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"/></svg>
<span class="text-gray-600 font-medium">Import Excel</span>
@endsection

@section('content')
<div class="animate-in max-w-5xl mx-auto">

    {{-- Page Header --}}
    <div class="page-header mb-6">
        <div>
            <h2 class="page-title text-2xl font-bold">Import Produk dari Excel</h2>
            <p class="page-subtitle text-gray-500">Unggah file Excel untuk menambah atau memperbarui data produk secara massal</p>
        </div>
        <a wire:navigate href="{{ route('products.index') }}" class="btn btn-secondary flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            Kembali
        </a>
    </div>

    {{-- Error Alert --}}
    @if(session('error'))
    <div class="mb-5 flex items-start gap-3 p-4 bg-red-50 border border-red-200 rounded-xl text-red-700 text-sm">
        <svg class="w-5 h-5 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        <div><strong>Terjadi kesalahan:</strong> {{ session('error') }}</div>
    </div>
    @endif

    @if($errors->any())
    <div class="mb-5 flex items-start gap-3 p-4 bg-red-50 border border-red-200 rounded-xl text-red-700 text-sm">
        <svg class="w-5 h-5 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        <ul class="list-disc pl-4">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- ── Upload Card ───────────────────────────────────────────── --}}
        <div class="lg:col-span-2 card p-6"
             x-data="{
                 file: null,
                 dragging: false,
                 uploading: false,
                 fileName: '',
                 fileSize: '',
                 setFile(f) {
                     if (!f) return;
                     const ext = f.name.split('.').pop().toLowerCase();
                     if (!['xlsx','xls'].includes(ext)) {
                         alert('❌ Format file tidak didukung!\nHanya file .xlsx atau .xls yang diterima.');
                         return;
                     }
                     if (f.size > 32 * 1024 * 1024) {
                         alert('❌ File terlalu besar! Maksimal 32MB.');
                         return;
                     }
                     this.file = f;
                     this.fileName = f.name;
                     this.fileSize = (f.size / 1024).toFixed(1) + ' KB';
                 },
                 submitForm() {
                     if (!this.file) {
                         alert('⚠️ Pilih file Excel terlebih dahulu!');
                         return;
                     }
                     this.uploading = true;
                     this.$refs.form.submit();
                 }
             }">

            <div class="flex justify-between items-center mb-5">
                <h3 class="text-lg font-bold text-gray-800 flex items-center gap-2">
                    <div class="w-8 h-8 bg-emerald-100 text-emerald-600 rounded-lg flex items-center justify-center">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/></svg>
                    </div>
                    Pilih File Excel
                </h3>
                <a href="{{ route('products.import.template') }}"
                   download="template_import_produk_apotek.xlsx"
                   class="btn btn-secondary py-1.5 px-3 text-xs flex items-center gap-1.5 border border-emerald-200 !text-emerald-700 hover:!bg-emerald-50 shadow-sm no-loading"
                   data-no-loading>
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                    Download Template
                </a>
            </div>

            <form x-ref="form" action="{{ route('products.import') }}" method="POST" enctype="multipart/form-data">
                @csrf

                {{-- Dropzone --}}
                <div class="border-2 border-dashed rounded-2xl p-8 flex flex-col items-center justify-center text-center cursor-pointer transition-all duration-200 relative"
                     :class="{
                         'border-emerald-400 bg-emerald-50': dragging || file,
                         'border-gray-300 bg-gray-50/50 hover:border-emerald-400 hover:bg-emerald-50/30': !dragging && !file
                     }"
                     @click="$refs.fileInput.click()"
                     @dragover.prevent="dragging = true"
                     @dragleave.prevent="dragging = false"
                     @drop.prevent="dragging = false; setFile($event.dataTransfer.files[0])">

                    <input type="file" name="file" x-ref="fileInput" class="hidden" accept=".xlsx,.xls"
                           @change="setFile($event.target.files[0])">

                    {{-- Idle state --}}
                    <div x-show="!file" class="flex flex-col items-center">
                        <div class="w-16 h-16 rounded-2xl flex items-center justify-center mb-4 transition-colors"
                             :class="dragging ? 'bg-emerald-200 text-emerald-700' : 'bg-blue-100 text-blue-500'">
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 13h6m-3-3v6m5 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                        </div>
                        <p class="text-sm font-semibold text-gray-700" x-text="dragging ? '⬇ Lepas file di sini!' : 'Klik untuk memilih file atau seret ke sini'"></p>
                        <p class="text-xs text-gray-400 mt-1.5">Format: <strong>.xlsx</strong> atau <strong>.xls</strong> &nbsp;•&nbsp; Maks. <strong>32MB</strong></p>
                    </div>

                    {{-- File selected state --}}
                    <div x-show="file" class="w-full">
                        <div class="flex items-center gap-4 p-3 bg-white rounded-xl border border-emerald-200 shadow-sm">
                            <div class="w-12 h-12 bg-emerald-100 rounded-xl flex items-center justify-center shrink-0">
                                <svg class="w-6 h-6 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                </svg>
                            </div>
                            <div class="flex-1 text-left min-w-0">
                                <p class="text-sm font-semibold text-emerald-700 truncate" x-text="fileName"></p>
                                <p class="text-xs text-gray-500 mt-0.5" x-text="fileSize + ' — Siap diimport'"></p>
                            </div>
                            <button type="button" class="text-gray-400 hover:text-red-500 transition-colors p-1 rounded-full hover:bg-red-50"
                                    @click.stop="file = null; fileName = ''; fileSize = ''; $refs.fileInput.value = ''"
                                    title="Hapus file">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                            </button>
                        </div>
                        <p class="text-xs text-emerald-600 mt-2 font-medium">✅ File berhasil dipilih. Klik tombol "Mulai Import" untuk memproses.</p>
                    </div>
                </div>

                {{-- Upload progress overlay --}}
                <div x-show="uploading" x-cloak class="mt-4 p-4 bg-blue-50 border border-blue-200 rounded-xl flex items-center gap-3">
                    <svg class="w-5 h-5 text-blue-600 animate-spin" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                    </svg>
                    <div>
                        <p class="text-sm font-semibold text-blue-700">Sedang memproses import...</p>
                        <p class="text-xs text-blue-500">Mohon tunggu, jangan tutup halaman ini.</p>
                    </div>
                </div>

                {{-- Action buttons --}}
                <div class="flex justify-end gap-3 mt-5">
                    <button type="button" class="btn btn-secondary"
                            @click="file = null; fileName = ''; fileSize = ''; $refs.fileInput.value = ''"
                            :disabled="uploading">
                        Batal / Reset
                    </button>
                    <button type="button"
                            class="btn btn-primary flex items-center gap-2"
                            :disabled="!file || uploading"
                            :class="{ 'opacity-50 cursor-not-allowed': !file || uploading }"
                            @click="submitForm()">
                        <svg x-show="!uploading" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                        <svg x-show="uploading" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                        </svg>
                        <span x-text="uploading ? 'Memproses...' : 'Mulai Import Data'"></span>
                    </button>
                </div>
            </form>
        </div>

        {{-- ── Panduan Card ──────────────────────────────────────────── --}}
        <div class="card p-6 bg-gradient-to-b from-slate-50 to-white flex flex-col gap-5">

            {{-- Panduan Kolom --}}
            <div>
                <h3 class="text-base font-bold text-gray-800 mb-3 flex items-center gap-2">
                    <svg class="w-5 h-5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M12 2a10 10 0 100 20A10 10 0 0012 2z"/></svg>
                    Panduan Kolom Template
                </h3>
                <p class="text-xs text-gray-500 mb-3 leading-relaxed">
                    Template mengikuti form <strong>Master Produk</strong> (18 kolom A–R). Data mulai dari <strong>Baris 4</strong>:
                </p>
                <div class="space-y-2 text-xs max-h-[420px] overflow-y-auto pr-1">
                    @foreach([
                        ['A', 'NAMA PRODUK *', 'Nama Produk / Obat', 'text-emerald-600'],
                        ['B', 'KODE PRODUK', 'Kode Produk', 'text-blue-600'],
                        ['C', 'BARCODE', 'Barcode', 'text-blue-600'],
                        ['D', 'KATEGORI', 'Kategori', 'text-blue-600'],
                        ['E', 'SATUAN', 'Satuan', 'text-blue-600'],
                        ['F', 'SUPPLIER', 'Supplier', 'text-blue-600'],
                        ['G', 'PABRIK / MERK', 'Pabrik / Merk', 'text-blue-600'],
                        ['H', 'KOMPOSISI', 'Komposisi / Kandungan', 'text-blue-600'],
                        ['I', 'DESKRIPSI', 'Deskripsi / Indikasi', 'text-blue-600'],
                        ['J', 'BUTUH RESEP', 'Obat Keras (Ya/Tidak)', 'text-blue-600'],
                        ['K', 'HARGA BELI *', 'Harga Beli (HPP)', 'text-emerald-600'],
                        ['L', 'HARGA JUAL *', 'Harga Jual (Eceran)', 'text-emerald-600'],
                        ['M', 'HARGA GROSIR', 'Harga Grosir', 'text-blue-600'],
                        ['N', 'HET MARKUP %', 'HET Markup (%)', 'text-blue-600'],
                        ['O', 'HET', 'Harga Eceran Tertinggi', 'text-blue-600'],
                        ['P', 'STOK *', 'Stok Saat Ini', 'text-emerald-600'],
                        ['Q', 'STOK MINIMUM', 'Stok Minimum (Warning)', 'text-blue-600'],
                        ['R', 'KADALUARSA', 'Tanggal Kadaluarsa', 'text-blue-600'],
                    ] as [$col, $label, $desc, $color])
                    <div class="flex items-start gap-2 p-2 rounded-lg hover:bg-white transition-colors">
                        <span class="font-bold {{ $color }} w-5 shrink-0 text-center">{{ $col }}</span>
                        <div>
                            <p class="font-semibold text-gray-700">{{ $label }}</p>
                            <p class="text-gray-400 text-[10px]">{{ $desc }}</p>
                        </div>
                    </div>
                    @endforeach
                </div>
                <p class="text-[10px] text-emerald-600 mt-2">* = Wajib diisi</p>
            </div>

            <hr class="border-gray-100">

            {{-- Tips --}}
            <div>
                <h4 class="text-xs font-bold text-gray-700 mb-2 flex items-center gap-1.5">
                    <svg class="w-4 h-4 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/></svg>
                    Tips Penting
                </h4>
                <ul class="text-xs text-gray-500 space-y-1.5 leading-relaxed">
                    <li class="flex items-start gap-1.5"><span class="text-emerald-500 font-bold mt-0.5">•</span>Kode produk yang sama akan <strong>diperbarui</strong>, bukan diduplikasi</li>
                    <li class="flex items-start gap-1.5"><span class="text-emerald-500 font-bold mt-0.5">•</span>Format <strong>databaseproduk.xlsx</strong> juga didukung otomatis (Kandungan, Bentuk, Rute, Indikasi)</li>
                    <li class="flex items-start gap-1.5"><span class="text-emerald-500 font-bold mt-0.5">•</span>Kolom mengikuti form <strong>Tambah Produk</strong> (Master Produk)</li>
                    <li class="flex items-start gap-1.5"><span class="text-emerald-500 font-bold mt-0.5">•</span>Butuh resep: isi <code class="bg-gray-100 px-1 rounded">Ya</code> atau <code class="bg-gray-100 px-1 rounded">Tidak</code></li>
                    <li class="flex items-start gap-1.5"><span class="text-emerald-500 font-bold mt-0.5">•</span>Harga bisa ditulis tanpa titik: <code class="bg-gray-100 px-1 rounded">3000</code> atau <code class="bg-gray-100 px-1 rounded">3.000</code></li>
                    <li class="flex items-start gap-1.5"><span class="text-emerald-500 font-bold mt-0.5">•</span>Tanggal format: <code class="bg-gray-100 px-1 rounded">2027-12-31</code> atau <code class="bg-gray-100 px-1 rounded">31/12/2027</code></li>
                    <li class="flex items-start gap-1.5"><span class="text-emerald-500 font-bold mt-0.5">•</span>Kategori, satuan &amp; supplier baru akan <strong>otomatis dibuat</strong></li>
                    <li class="flex items-start gap-1.5"><span class="text-amber-500 font-bold mt-0.5">!</span>Jangan mengubah urutan kolom pada template</li>
                </ul>
            </div>

        </div>
    </div>
</div>
@endsection
