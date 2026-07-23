@extends('layouts.app')

@section('title', 'Laporan')
@section('page-title', 'Laporan')

@section('content')
<script>
    window.reportsPortal = function() {
        return {
            selectedReport: new URLSearchParams(window.location.search).get('type') || @json(auth()->user()->isStaffIt() && !auth()->user()->isKepalaIt() ? 'log_aktivitas' : 'penjualan_harian'),
            previewActive:  false,
            iframeLoading:  false,
            pdfLoading:     false,
            showExcelConfirmModal: false,
            excelDownloadUrl: '',
            _loadingTimer: null,

            // ─────────────────────────────────────────────────────────────────
            // Daftar Laporan
            // ─────────────────────────────────────────────────────────────────
            penjualanReports: [
                @unless(auth()->user()->isStaffIt() && !auth()->user()->isKepalaIt())
                { id: 'penjualan_harian',     name: 'Penjualan Harian' },
                { id: 'penjualan_bulanan',    name: 'Penjualan Bulanan' },
                { id: 'penjualan_per_produk', name: 'Penjualan Per Produk' },
                { id: 'penjualan_per_kasir',  name: 'Penjualan Per Kasir' },
                { id: 'transaksi_qris',       name: 'Transaksi QRIS (BNI Wondr)' },
                @endunless
            ],
            inventoriReports: [
                @unless(auth()->user()->isStaffIt() && !auth()->user()->isKepalaIt())
                { id: 'pembelian',         name: 'Pembelian (Barang Masuk)' },
                { id: 'stok_saat_ini',     name: 'Stok Saat Ini' },
                { id: 'stok_menipis',      name: 'Stok Menipis (Batas Min)' },
                { id: 'produk_kadaluarsa', name: 'Produk Kadaluarsa (Expired)' },
                { id: 'stok_opname',       name: 'Riwayat Stok Opname' },
                @endunless
            ],
            keuanganReports: [
                @unless(auth()->user()->isStaffIt() && !auth()->user()->isKepalaIt())
                { id: 'kredit_piutang',  name: 'Kredit / Piutang (Belum Lunas)' },
                { id: 'invoice_lunas', name: 'Invoice Lunas' },
                { id: 'laba_rugi',     name: 'Laba Rugi (Admin Only)' },
                { id: 'ppn_pajak',     name: 'Pajak (PPN)' },
                { id: 'diskon',        name: 'Penggunaan Diskon' },
                { id: 'gaji_karyawan', name: 'Gaji Karyawan' },
                @endunless
                @if(auth()->user()->isKepalaIt() || auth()->user()->isStaffIt())
                { id: 'log_aktivitas', name: 'Log Aktivitas Sistem (IT)' },
                @endif
            ],

            // ─────────────────────────────────────────────────────────────────
            // Klik menu laporan kiri — langsung tampilkan preview
            // ─────────────────────────────────────────────────────────────────
            selectReport(id) {
                this.selectedReport = id;
                this._loadPreview();
            },

            getReportName() {
                const all = [...this.penjualanReports, ...this.inventoriReports, ...this.keuanganReports];
                return all.find(r => r.id === this.selectedReport)?.name ?? 'Laporan';
            },

            // ─────────────────────────────────────────────────────────────────
            // Filter visibility rules
            // ─────────────────────────────────────────────────────────────────
            showFilter(field) {
                const rules = {
                    start_date:     ['penjualan_harian','penjualan_per_produk','penjualan_per_kasir','pembelian','laba_rugi','ppn_pajak','diskon','stok_opname','log_aktivitas','transaksi_qris','gaji_karyawan','kredit_piutang','invoice_lunas'],
                    end_date:       ['penjualan_harian','penjualan_per_produk','penjualan_per_kasir','pembelian','laba_rugi','ppn_pajak','diskon','stok_opname','log_aktivitas','transaksi_qris','gaji_karyawan','kredit_piutang','invoice_lunas'],
                    user_id:        ['penjualan_harian','penjualan_per_kasir','log_aktivitas','transaksi_qris','gaji_karyawan'],
                    payment_method: ['penjualan_harian'],
                    category_id:    ['penjualan_per_produk','stok_saat_ini','stok_menipis','produk_kadaluarsa'],
                    supplier_id:    ['pembelian','stok_saat_ini','stok_menipis','produk_kadaluarsa'],
                    product_id:     ['penjualan_per_produk'],
                    stock_level:    ['stok_saat_ini'],
                    expiry_range:   ['produk_kadaluarsa'],
                    ppn_bearer:     ['ppn_pajak'],
                    month:          ['penjualan_bulanan'],
                    year:           ['penjualan_bulanan'],
                    module:         ['log_aktivitas'],
                };
                return rules[field]?.includes(this.selectedReport) ?? false;
            },

            // ─────────────────────────────────────────────────────────────────
            // Build URL dari filter yang aktif di form
            // ─────────────────────────────────────────────────────────────────
            buildUrl(format) {
                const params = new URLSearchParams();
                params.append('type',   this.selectedReport);
                params.append('format', format);

                // Baca semua input/select di dalam form yang nilainya tidak kosong
                const form = document.getElementById('reportForm');
                if (form) {
                    form.querySelectorAll('input, select').forEach(el => {
                        if (!el.name || el.name === 'type' || el.name === 'format') return;
                        const val = el.value?.trim();
                        if (val) params.append(el.name, val);
                    });
                }
                return '{{ route("reports.generate") }}?' + params.toString();
            },

            // ─────────────────────────────────────────────────────────────────
            // CORE: Muat laporan ke iframe langsung via src — tanpa form.submit()
            // Ini menghilangkan semua race condition Alpine x-show + form target
            // ─────────────────────────────────────────────────────────────────
            _loadPreview() {
                clearTimeout(this._loadingTimer);

                // 1. Tampilkan loading state segera (instan, <16ms)
                this.previewActive  = true;
                this.iframeLoading  = true;

                // 2. 🟢 Progress bar
                this._startBar();

                // 3. ⏳ Cursor wait
                document.body.classList.add('navigating');

                // 4. Bangun URL dan langsung set ke iframe src
                //    Tidak perlu form.submit(), tidak ada race condition
                const url = this.buildUrl('html');
                const iframe = document.getElementById('report-preview-iframe');

                if (iframe) {
                    iframe.src = url; // ← KUNCI: langsung dan pasti
                } else {
                    // Fallback: iframe belum ada di DOM, tunggu satu frame
                    requestAnimationFrame(() => {
                        const f = document.getElementById('report-preview-iframe');
                        if (f) f.src = url;
                    });
                }

                // 5. Fallback timeout 45 detik — reset jika server lambat/error
                this._loadingTimer = setTimeout(() => {
                    if (this.iframeLoading) {
                        this.iframeLoading = false;
                        this._stopBar();
                        console.warn('[Reports] Timeout 45s — reset state.');
                    }
                }, 45000);
            },

            // ─────────────────────────────────────────────────────────────────
            // Progress bar helpers
            // ─────────────────────────────────────────────────────────────────
            _startBar() {
                const bar = document.getElementById('global-loading-bar');
                if (!bar) return;
                bar.style.transition = 'none';
                bar.style.opacity    = '1';
                bar.style.width      = '0%';
                bar.getBoundingClientRect(); // force reflow
                bar.style.transition = 'width 0.2s ease-out, opacity 0.4s ease';
                bar.style.width      = '18%';
                setTimeout(() => { if (bar.style.opacity === '1') bar.style.width = '50%'; },  150);
                setTimeout(() => { if (bar.style.opacity === '1') bar.style.width = '78%'; },  400);
                setTimeout(() => { if (bar.style.opacity === '1') bar.style.width = '92%'; }, 1000);
            },

            _stopBar() {
                const bar = document.getElementById('global-loading-bar');
                if (!bar) return;
                bar.style.transition = 'width 0.15s ease-out, opacity 0.35s ease';
                bar.style.width      = '100%';
                setTimeout(() => {
                    bar.style.opacity = '0';
                    setTimeout(() => { bar.style.width = '0%'; }, 380);
                }, 200);
                document.body.classList.remove('navigating');
            },

            // ─────────────────────────────────────────────────────────────────
            // Iframe selesai load
            // ─────────────────────────────────────────────────────────────────
            onIframeLoad() {
                clearTimeout(this._loadingTimer);
                this.iframeLoading = false;
                this._stopBar();

                // Auto-adjust iframe tinggi ke konten
                const iframe = document.getElementById('report-preview-iframe');
                if (iframe) {
                    try {
                        const h = iframe.contentDocument?.body?.scrollHeight;
                        if (h && h > 400) {
                            iframe.style.minHeight = Math.min(h + 40, 900) + 'px';
                        }
                    } catch (_) { /* cross-origin – skip */ }
                }
            },

            // ─────────────────────────────────────────────────────────────────
            // Public submit dari tombol Tampilkan / Cetak PDF / Ekspor Excel
            // ─────────────────────────────────────────────────────────────────
            submitReport(format) {
                if (format === 'html') {
                    this._loadPreview();

                } else if (format === 'pdf') {
                    if (this.pdfLoading) return;
                    this.pdfLoading = true;

                    // Progress bar singkat untuk PDF
                    this._startBar();
                    document.body.classList.add('navigating');

                    // Buka URL PDF di tab baru — TIDAK mengubah halaman ini sama sekali
                    const url = this.buildUrl('pdf');
                    window.open(url, '_blank', 'noopener');

                    // Selesaikan feedback setelah 1.5 detik
                    setTimeout(() => {
                        this.pdfLoading = false;
                        this._stopBar();
                    }, 1500);

                } else if (format === 'excel') {
                    this._loadPreview();
                    this.excelDownloadUrl = this.buildUrl('excel');
                    setTimeout(() => { this.showExcelConfirmModal = true; }, 500);
                }
            },

            confirmDownloadExcel() {
                this.showExcelConfirmModal = false;
                const a = document.createElement('a');
                a.href = this.excelDownloadUrl;
                a.download = '';
                document.body.appendChild(a);
                a.click();
                document.body.removeChild(a);
            }
        };
    }
</script>

<div class="animate-in" x-data="reportsPortal()">

    <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
        
        {{-- Left Column: Sidebar List of Reports --}}
        <div class="lg:col-span-1 flex flex-col gap-4">
            <div class="card p-4 bg-white border border-gray-100 rounded-2xl shadow-sm">
                <h3 class="font-bold text-gray-800 text-sm uppercase tracking-wider mb-3">Pilih Jenis Laporan</h3>
                
                <div class="flex flex-col gap-5">
                    {{-- Group: Penjualan --}}
                    <div>
                        <p class="text-[10px] font-bold text-emerald-600 uppercase tracking-widest mb-1.5">Penjualan</p>
                        <div class="flex flex-col gap-1">
                            <template x-for="rep in penjualanReports" :key="rep.id">
                                <button type="button" @click.prevent="selectReport(rep.id)"
                                        :class="selectedReport === rep.id ? 'bg-emerald-50 text-emerald-800 border-l-4 border-l-emerald-600 font-bold' : 'text-gray-600 hover:bg-gray-50 border-l-4 border-l-transparent'"
                                        class="w-full text-left px-3 py-2 text-xs rounded-r-lg transition-all cursor-pointer">
                                    <span x-text="rep.name"></span>
                                </button>
                            </template>
                        </div>
                    </div>

                    {{-- Group: Inventori --}}
                    <div>
                        <p class="text-[10px] font-bold text-blue-600 uppercase tracking-widest mb-1.5">Inventori & Stok</p>
                        <div class="flex flex-col gap-1">
                            <template x-for="rep in inventoriReports" :key="rep.id">
                                <button type="button" @click.prevent="selectReport(rep.id)"
                                        :class="selectedReport === rep.id ? 'bg-blue-50 text-blue-800 border-l-4 border-l-blue-600 font-bold' : 'text-gray-600 hover:bg-gray-50 border-l-4 border-l-transparent'"
                                        class="w-full text-left px-3 py-2 text-xs rounded-r-lg transition-all cursor-pointer">
                                    <span x-text="rep.name"></span>
                                </button>
                            </template>
                        </div>
                    </div>

                    {{-- Group: Keuangan --}}
                    <div>
                        <p class="text-[10px] font-bold text-purple-600 uppercase tracking-widest mb-1.5">Keuangan & Audit</p>
                        <div class="flex flex-col gap-1">
                            <template x-for="rep in keuanganReports" :key="rep.id">
                                <button type="button" @click.prevent="selectReport(rep.id)"
                                        :class="selectedReport === rep.id ? 'bg-purple-50 text-purple-800 border-l-4 border-l-purple-600 font-bold' : 'text-gray-600 hover:bg-gray-50 border-l-4 border-l-transparent'"
                                        class="w-full text-left px-3 py-2 text-xs rounded-r-lg transition-all cursor-pointer">
                                    <span x-text="rep.name"></span>
                                </button>
                            </template>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Right Column: Filter & Preview --}}
        <div class="lg:col-span-3 flex flex-col gap-6">
            
            {{-- Filter & Actions Card --}}
            <div class="card p-5 bg-white border border-gray-100 rounded-2xl shadow-sm">
                <div class="flex items-center justify-between border-b border-gray-100 pb-3 mb-4">
                    <div>
                        <h3 class="font-bold text-gray-800 text-lg" x-text="getReportName()"></h3>
                        <p class="text-xs text-gray-400 mt-0.5">Sesuaikan kriteria filter untuk menghasilkan data</p>
                    </div>
                </div>

                {{-- Unified Form --}}
                <form action="{{ route('reports.generate') }}" method="GET"
                      target="report-preview-iframe"
                      id="reportForm"
                      data-no-loading>

                    <input type="hidden" name="type" :value="selectedReport">
                    <input type="hidden" name="format" id="reportFormat" value="html">

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-5">
                        
                        {{-- Filter: Entitas Cetak (Always Visible) --}}
                        <div>
                            <label class="form-label text-xs font-semibold text-gray-500 mb-1 block">Entitas Cetak Laporan</label>
                            <select name="entity" class="form-input text-sm w-full rounded-xl border-gray-200">
                                <option value="apotek">Apotek Almaira</option>
                                <option value="pt">PT Nur Madani Farma</option>
                            </select>
                        </div>
                        
                        {{-- Filter: Start Date --}}
                        <div x-show="showFilter('start_date')">
                            <label class="form-label text-xs font-semibold text-gray-500 mb-1 block">Tanggal Mulai</label>
                            <input type="date" name="start_date" class="form-input text-sm w-full rounded-xl border-gray-200" value="{{ today()->startOfMonth()->format('Y-m-d') }}">
                        </div>

                        {{-- Filter: End Date --}}
                        <div x-show="showFilter('end_date')">
                            <label class="form-label text-xs font-semibold text-gray-500 mb-1 block">Tanggal Akhir</label>
                            <input type="date" name="end_date" class="form-input text-sm w-full rounded-xl border-gray-200" value="{{ today()->format('Y-m-d') }}">
                        </div>

                        {{-- Filter: Cashier / Employee --}}
                        <div x-show="showFilter('user_id')">
                            <label class="form-label text-xs font-semibold text-gray-500 mb-1 block" x-text="selectedReport === 'gaji_karyawan' ? 'Pilih Karyawan' : 'Pilih Kasir'"></label>
                            <select name="user_id" class="form-input text-sm w-full rounded-xl border-gray-200">
                                <option value="">-- Semua --</option>
                                @foreach($cashiers as $c)
                                <option value="{{ $c->id }}">{{ $c->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Filter: Payment Method --}}
                        <div x-show="showFilter('payment_method')">
                            <label class="form-label text-xs font-semibold text-gray-500 mb-1 block">Metode Pembayaran</label>
                            <select name="payment_method" class="form-input text-sm w-full rounded-xl border-gray-200">
                                <option value="">-- Semua Metode --</option>
                                <option value="cash">Tunai</option>
                                <option value="qris">QRIS (BNI Wondr)</option>
                            </select>
                        </div>

                        {{-- Filter: Category --}}
                        <div x-show="showFilter('category_id')">
                            <label class="form-label text-xs font-semibold text-gray-500 mb-1 block">Kategori Obat</label>
                            <select name="category_id" class="form-input text-sm w-full rounded-xl border-gray-200">
                                <option value="">-- Semua Kategori --</option>
                                @foreach($categories as $cat)
                                <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Filter: Supplier --}}
                        <div x-show="showFilter('supplier_id')">
                            <label class="form-label text-xs font-semibold text-gray-500 mb-1 block">Supplier</label>
                            <select name="supplier_id" class="form-input text-sm w-full rounded-xl border-gray-200">
                                <option value="">-- Semua Supplier --</option>
                                @foreach($suppliers as $sup)
                                <option value="{{ $sup->id }}">{{ $sup->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Filter: Product --}}
                        <div x-show="showFilter('product_id')">
                            <label class="form-label text-xs font-semibold text-gray-500 mb-1 block">Cari Produk</label>
                            <select name="product_id" class="form-input text-sm w-full rounded-xl border-gray-200">
                                <option value="">-- Semua Produk --</option>
                                @foreach($products as $p)
                                <option value="{{ $p->id }}">{{ $p->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Filter: Stock Level --}}
                        <div x-show="showFilter('stock_level')">
                            <label class="form-label text-xs font-semibold text-gray-500 mb-1 block">Tingkat Stok</label>
                            <select name="stock_level" class="form-input text-sm w-full rounded-xl border-gray-200">
                                <option value="">-- Semua Tingkat --</option>
                                <option value="low">Stok Kritis (≤ Min)</option>
                                <option value="normal">Stok Aman (> Min)</option>
                            </select>
                        </div>

                        {{-- Filter: Expiry Range --}}
                        <div x-show="showFilter('expiry_range')">
                            <label class="form-label text-xs font-semibold text-gray-500 mb-1 block">Rentang Kadaluarsa</label>
                            <select name="expiry_range" class="form-input text-sm w-full rounded-xl border-gray-200">
                                <option value="all">Semua Produk Expired/Dekat Expired</option>
                                <option value="expired">Sudah Kadaluarsa</option>
                                <option value="30_days">Kadaluarsa ≤ 30 Hari</option>
                                <option value="60_days">Kadaluarsa 31 - 60 Hari</option>
                            </select>
                        </div>

                        {{-- Filter: PPN Bearer --}}
                        <div x-show="showFilter('ppn_bearer')">
                            <label class="form-label text-xs font-semibold text-gray-500 mb-1 block">Penanggung PPN</label>
                            <select name="ppn_bearer" class="form-input text-sm w-full rounded-xl border-gray-200">
                                <option value="all">-- Semua Penanggung --</option>
                                <option value="buyer">Ditanggung Pembeli</option>
                                <option value="seller">Ditanggung Penjual</option>
                            </select>
                        </div>

                        {{-- Filter: Month (Bulanan) --}}
                        <div x-show="showFilter('month')">
                            <label class="form-label text-xs font-semibold text-gray-500 mb-1 block">Pilih Bulan</label>
                            <select name="month" class="form-input text-sm w-full rounded-xl border-gray-200">
                                @for($m = 1; $m <= 12; $m++)
                                <option value="{{ $m }}" {{ $m == date('m') ? 'selected' : '' }}>
                                    {{ Carbon\Carbon::create(null, $m, 1)->locale('id')->isoFormat('MMMM') }}
                                </option>
                                @endfor
                            </select>
                        </div>

                        {{-- Filter: Year (Bulanan) --}}
                        <div x-show="showFilter('year')">
                            <label class="form-label text-xs font-semibold text-gray-500 mb-1 block">Pilih Tahun</label>
                            <select name="year" class="form-input text-sm w-full rounded-xl border-gray-200">
                                @for($y = date('Y'); $y >= date('Y') - 5; $y--)
                                <option value="{{ $y }}">{{ $y }}</option>
                                @endfor
                            </select>
                        </div>

                        {{-- Filter: Log Module --}}
                        <div x-show="showFilter('module')">
                            <label class="form-label text-xs font-semibold text-gray-500 mb-1 block">Modul Aktivitas</label>
                            <select name="module" class="form-input text-sm w-full rounded-xl border-gray-200">
                                <option value="">-- Semua Modul --</option>
                                @foreach($modules as $mod)
                                <option value="{{ $mod }}">{{ $mod }}</option>
                                @endforeach
                            </select>
                        </div>

                    </div>

                    {{-- Actions Button --}}
                    <div class="flex flex-wrap gap-2.5 border-t border-gray-100 pt-4">
                        <button type="button" @click="submitReport('html')" class="inline-flex items-center gap-1.5 px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-xl text-xs shadow-sm transition-all cursor-pointer">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                            </svg>
                            Tampilkan Preview
                        </button>
                        
                        <button type="button" @click="submitReport('pdf')" :disabled="pdfLoading"
                            :class="pdfLoading ? 'opacity-75 cursor-wait' : 'hover:bg-rose-700 cursor-pointer'"
                            class="inline-flex items-center gap-1.5 px-4 py-2 bg-rose-600 text-white font-bold rounded-xl text-xs shadow-sm transition-all">
                            <svg x-show="!pdfLoading" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                            <svg x-show="pdfLoading" class="w-4 h-4 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 12a8 8 0 018-8v4l3-3-3-3v4a8 8 0 100 16 8 8 0 01-8-8z"/>
                            </svg>
                            <span x-text="pdfLoading ? 'Membuka PDF...' : 'Cetak PDF (A4)'"></span>
                        </button>

                        <button type="button" @click="submitReport('excel')" class="inline-flex items-center gap-1.5 px-4 py-2 bg-green-600 hover:bg-green-700 text-white font-bold rounded-xl text-xs shadow-sm transition-all cursor-pointer">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                            Ekspor Excel
                        </button>
                    </div>
                </form>
            </div>

            {{-- Live Preview Frame --}}
            <div class="card p-0 overflow-hidden bg-white border border-gray-100 rounded-2xl shadow-sm" x-show="previewActive">
                <div class="flex items-center justify-between p-4 border-b border-gray-100 bg-gray-50/50">
                    <div class="flex items-center gap-2">
                        <span class="w-2.5 h-2.5 rounded-full" :class="iframeLoading ? 'bg-amber-500 animate-pulse' : 'bg-emerald-500'"></span>
                        <span class="text-xs font-bold text-gray-600" x-text="iframeLoading ? 'SEDANG MEMUAT...' : 'LIVE PREVIEW LAPORAN'">LIVE PREVIEW LAPORAN</span>
                    </div>
                    <button @click="previewActive = false" class="text-xs text-gray-400 hover:text-gray-600 font-semibold transition-colors">Tutup Preview</button>
                </div>
                <div class="p-4 bg-gray-100 relative">
                    {{-- Loading Overlay --}}
                    <div x-show="iframeLoading" class="absolute inset-0 bg-white/75 backdrop-blur-[1.5px] flex flex-col items-center justify-center z-10 transition-all duration-300">
                        <div class="w-10 h-10 rounded-full border-4 border-emerald-100 border-t-emerald-600 animate-spin mb-3"></div>
                        <p class="text-xs font-bold text-emerald-800 tracking-wider">SEDANG MEMPROSES DATA LAPORAN...</p>
                        <p class="text-[10px] text-gray-400 mt-1">Mengambil data real-time dari database</p>
                    </div>

                    <iframe name="report-preview-iframe" id="report-preview-iframe"
                        @load="onIframeLoad()"
                        class="w-full min-h-[500px] border border-gray-200 rounded-xl bg-white shadow-sm"
                        style="min-height: 500px; transition: min-height 0.3s ease;">
                    </iframe>
                </div>
            </div>

            {{-- Empty State (When no preview is active) --}}
            <div class="card p-12 bg-white border border-gray-100 rounded-2xl shadow-sm text-center flex flex-col items-center justify-center" x-show="!previewActive">
                <div class="w-16 h-16 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center mb-4">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                </div>
                <h4 class="font-bold text-gray-800 text-base">Belum Ada Preview Laporan</h4>
                <p class="text-gray-400 text-xs mt-1 max-w-sm">Pilih jenis laporan di kolom kiri, sesuaikan filter, lalu klik "Tampilkan Preview" untuk melihat laporan sebelum dicetak.</p>
            </div>

        </div>

    </div>

    {{-- Excel Confirm Modal --}}
    <div x-show="showExcelConfirmModal" class="fixed inset-0 z-[9999] flex items-center justify-center p-4 bg-gray-900/60 backdrop-blur-sm" x-cloak>
        <div class="bg-white rounded-2xl max-w-md w-full p-6 shadow-xl border border-gray-100 animate-in fade-in zoom-in-95 duration-150">
            <div class="w-12 h-12 rounded-full bg-emerald-50 text-emerald-600 flex items-center justify-center mb-4">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
            </div>
            <h3 class="text-base font-bold text-gray-900">Review Laporan Sebelum Ekspor</h3>
            <p class="text-xs text-gray-500 mt-2">
                Silakan periksa data laporan pada panel preview di layar. Apakah Anda ingin mengunduh laporan ini dalam format Excel?
            </p>
            <div class="flex items-center justify-end gap-3 mt-6">
                <button type="button" @click="showExcelConfirmModal = false" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 font-bold rounded-xl text-xs transition-colors cursor-pointer">
                    Batal
                </button>
                <button type="button" @click="confirmDownloadExcel()" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-xl text-xs shadow-sm transition-colors cursor-pointer">
                    Unduh Excel
                </button>
            </div>
        </div>
    </div>

</div>
@endsection





