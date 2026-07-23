@extends('layouts.app')

@section('title', 'Kasir')
@section('page-title', 'Kasir / POS')
@section('dense-main', true)

@section('content')
<script>
window.posManager = () => ({
        searchQuery: '',
        activeCategory: null,
        categories: @json($categories),
        productsList: [],
        currentPage: 1,
        pageSize: 10,
        posRevision: null,
        settingsRevision: null,
        isSyncing: false,
        syncTimer: null,
        lastSyncLabel: '',
        syncNotice: '',

        get totalPages() {
            return Math.max(1, Math.ceil(this.productsList.length / this.pageSize));
        },

        get paginatedProducts() {
            const start = (this.currentPage - 1) * this.pageSize;
            const end = start + this.pageSize;
            return this.productsList.slice(start, end);
        },

        get pageNumbers() {
            const last = this.totalPages;
            const cur = this.currentPage;
            let start = Math.max(1, cur - 2);
            let end = Math.min(last, cur + 2);
            if (end - start < 4) {
                start = Math.max(1, end - 4);
                end = Math.min(last, start + 4);
            }
            const pages = [];
            if (start > 1) {
                pages.push(1);
                if (start > 2) pages.push('…');
            }
            for (let i = start; i <= end; i++) pages.push(i);
            if (end < last) {
                if (end < last - 1) pages.push('…');
                pages.push(last);
            }
            return pages;
        },

        get displayRange() {
            if (!this.productsList.length) return '0–0';
            const from = (this.currentPage - 1) * this.pageSize + 1;
            const to = Math.min(this.productsList.length, this.currentPage * this.pageSize);
            return from + '–' + to;
        },
        isLoadingProducts: false,
        cart: [],
        customerName: 'Pelanggan Umum',
        paymentMethod: 'Tunai',
        discountPercent: 0,
        ppnActive: @json($ppnActive),
        ppnPercent: @json($ppnDefault),
        ppnBearer: @json($ppnBearer === 'buyer' ? 'Ditanggung Pembeli' : 'Ditanggung Penjual'),
        cashReceived: null,
        notes: '',
        showQrisModal: false,
        showSuccessModal: false,
        createdSale: null,
        isCartModalOpen: false,
        isSaving: false,
        checkoutStep: 1,
        qrisNmid: @json($qrisNmid),
        qrisPaymentState: 'idle',  // 'idle' | 'waiting' | 'paid'
        qrisPollingTimer: null,
        qrisCountdown: 300,
        qrisMaxCountdown: 300,
        customerId: null,
        partnerId: null,
        buyerType: 'umum', // umum | crm | mitra
        customerPoints: 0,
        selectedCustomerOverdue: false,
        pointsRedeemed: 0,
        usePoints: false,
        crmPointMultiplier: @json($crmPointMultiplier),
        crmPointValue: @json($crmPointValue),
        customerSearchQuery: '',
        customerSearchResults: [],
        showCustomerSearchDropdown: false,
        isLoadingBuyers: false,
        partnerCode: null,
        partnerTypeLabel: null,
        partnerPriceMode: null,
        partnerPriceModeLabel: null,
        partnerInvoiceEnabled: false,
        partnerCreditDays: 30,
        partnerAllowTransfer: true,
        partnerAllowCod: true,
        partnerAddress: '',
        partnerPicName: '',
        partnerPhone: '',
        mitraCheckoutMode: 'sale', // sale = ambil sekarang | po = buat PO Mitra
        showQuickCreate: false,
        quickCreateSaving: false,
        quickName: '',
        quickPhone: '',
        quickType: 'apotek',
        partnerTypes: @json($partnerTypes),
        poPicName: '',
        poPicPhone: '',
        poShippingAddress: '',
        poPaymentMethod: 'transfer',
        poNotes: '',
        prescriptionId: null,

        get canUseInvoice() {
            if (this.selectedCustomerOverdue) return false;
            if (this.buyerType === 'crm') return !!this.customerId;
            if (this.buyerType === 'mitra') return !!this.partnerId && this.partnerInvoiceEnabled;
            return false;
        },

        get invoiceDisabledReason() {
            if (this.buyerType === 'umum') return 'Pilih pelanggan CRM atau mitra untuk Invoice';
            if (this.buyerType === 'crm' && !this.customerId) return 'Pilih pelanggan CRM terlebih dahulu';
            if (this.buyerType === 'mitra' && !this.partnerId) return 'Pilih mitra terlebih dahulu';
            if (this.buyerType === 'mitra' && !this.partnerInvoiceEnabled) return 'Mitra ini tidak diizinkan Invoice (tempo)';
            if (this.selectedCustomerOverdue) return 'Ada tagihan jatuh tempo belum lunas';
            return '';
        },

        searchAborter: null,

        init() {
            this.fetchProducts();
            this.startPosSync();
            const urlParams = new URLSearchParams(window.location.search);
            if (urlParams.has('prescription_id')) {
                this.prescriptionId = urlParams.get('prescription_id');
                this.loadPrescription(this.prescriptionId);
            }

            // Bersihkan timer/fetch saat SPA navigate keluar dari POS (hindari reload aneh)
            this._onNavigating = () => this.teardown();
            document.addEventListener('livewire:navigating', this._onNavigating);
        },

        destroy() {
            this.teardown();
        },

        teardown() {
            if (this.searchAborter) {
                try { this.searchAborter.abort(); } catch (e) {}
                this.searchAborter = null;
            }
            if (this.qrisPollingTimer) {
                clearInterval(this.qrisPollingTimer);
                this.qrisPollingTimer = null;
            }
            if (this.syncTimer) {
                clearInterval(this.syncTimer);
                this.syncTimer = null;
            }
            if (this._onVisibility) {
                document.removeEventListener('visibilitychange', this._onVisibility);
                this._onVisibility = null;
            }
            if (this._onFocus) {
                window.removeEventListener('focus', this._onFocus);
                this._onFocus = null;
            }
            if (this._onNavigating) {
                document.removeEventListener('livewire:navigating', this._onNavigating);
                this._onNavigating = null;
            }
        },

        startPosSync() {
            this.softSync({ silent: true, force: true });
            this.syncTimer = setInterval(() => {
                if (document.visibilityState === 'visible' && !this.isSaving) {
                    this.softSync({ silent: true });
                }
            }, 12000);
            this._onVisibility = () => {
                if (document.visibilityState === 'visible' && !this.isSaving) {
                    this.softSync({ silent: true });
                }
            };
            this._onFocus = () => {
                if (!this.isSaving) this.softSync({ silent: true });
            };
            document.addEventListener('visibilitychange', this._onVisibility);
            window.addEventListener('focus', this._onFocus);
        },

        async softSync({ silent = true, force = false } = {}) {
            if (this.isSyncing || this.isSaving) return;
            this.isSyncing = true;
            try {
                const params = new URLSearchParams();
                if (this.posRevision) params.set('since', this.posRevision);
                if (force) params.set('force', '1');
                params.set('q', this.searchQuery || '');
                if (this.activeCategory) params.set('category_id', this.activeCategory);
                if (this.cart.length) {
                    params.set('cart_ids', this.cart.map(i => i.product_id).join(','));
                }
                if (this.partnerId) params.set('partner_id', this.partnerId);
                if (this.customerId) params.set('customer_id', this.customerId);

                const res = await fetch('/pos/sync?' + params.toString(), {
                    headers: { 'Accept': 'application/json' },
                });
                if (!res.ok) return;
                const data = await res.json();
                if (!data.changed) {
                    this.lastSyncLabel = 'Terbaru';
                    return;
                }
                this.applyPosSync(data, { silent });
                this.posRevision = data.revision;
                if (data.settings_revision) this.settingsRevision = data.settings_revision;
                this.lastSyncLabel = new Date().toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit', second: '2-digit' });
            } catch (e) {
                console.error('POS sync error', e);
            } finally {
                this.isSyncing = false;
            }
        },

        applyPosSync(data, { silent = true } = {}) {
            const notices = [];

            // Settings hanya diterapkan saat admin mengubah setting (bukan tiap update stok)
            const settingsChanged = data.settings_revision && data.settings_revision !== this.settingsRevision;
            if (data.settings && (settingsChanged || !this.settingsRevision)) {
                const s = data.settings;
                this.ppnPercent = parseFloat(s.ppn_percent) || this.ppnPercent;
                this.ppnActive = !!s.ppn_active;
                if (s.ppn_bearer) this.ppnBearer = s.ppn_bearer;
                if (s.qris_nmid) this.qrisNmid = s.qris_nmid;
                if (Array.isArray(s.discount_rules)) this.discountRules = s.discount_rules;
                if (s.crm_point_multiplier != null) this.crmPointMultiplier = parseInt(s.crm_point_multiplier, 10) || this.crmPointMultiplier;
                if (s.crm_point_value != null) this.crmPointValue = parseInt(s.crm_point_value, 10) || this.crmPointValue;
                if (settingsChanged && this.settingsRevision) {
                    notices.push('Pengaturan kasir diperbarui dari admin');
                }
            }

            if (Array.isArray(data.categories)) {
                this.categories = data.categories;
                if (this.activeCategory && !this.categories.some(c => Number(c.id) === Number(this.activeCategory))) {
                    this.activeCategory = null;
                    notices.push('Kategori aktif diganti — menampilkan semua produk');
                }
            }

            if (data.partner_types && typeof data.partner_types === 'object') {
                this.partnerTypes = data.partner_types;
            }

            // Katalog: jangan ganggu jika user sedang memuat hasil pencarian
            if (Array.isArray(data.catalog) && !this.isLoadingProducts) {
                const keepPage = this.currentPage;
                this.productsList = data.catalog;
                this.currentPage = Math.min(keepPage, this.totalPages);
            }

            // Patch keranjang dari data produk terbaru
            if (Array.isArray(data.cart_products) && this.cart.length) {
                const map = {};
                data.cart_products.forEach(p => { map[p.id] = p; });

                const nextCart = [];
                this.cart.forEach(item => {
                    const p = map[item.product_id];
                    if (!p || !p.is_active) {
                        notices.push(`"${item.name}" tidak tersedia lagi dan dihapus dari keranjang`);
                        return;
                    }

                    const oldSell = parseFloat(item.sell_price);
                    const oldWs = parseFloat(item.wholesale_price);
                    const newSell = parseFloat(p.sell_price);
                    const newWs = parseFloat(p.wholesale_price) || newSell;

                    item.name = p.name;
                    item.code = p.code;
                    item.barcode = p.barcode;
                    item.unit_name = p.unit || item.unit_name || 'Pcs';
                    item.sell_price = newSell;
                    item.wholesale_price = newWs;
                    item.stock = parseInt(p.stock, 10) || 0;
                    item.stock_min = parseInt(p.stock_min, 10) || 0;

                    if (Math.abs(oldSell - newSell) > 0.01 || Math.abs(oldWs - newWs) > 0.01) {
                        notices.push(`Harga "${item.name}" diperbarui`);
                    }

                    if (item.stock <= 0) {
                        notices.push(`"${item.name}" habis stok — dihapus dari keranjang`);
                        return;
                    }
                    if (item.quantity > item.stock) {
                        item.quantity = item.stock;
                        notices.push(`Qty "${item.name}" disesuaikan ke stok ${item.stock}`);
                    }

                    this.updateDiscountTiers(item);
                    this.calculateRowSubtotal(item);
                    nextCart.push(item);
                });
                this.cart = nextCart;
            }

            if (this.partnerId && Object.prototype.hasOwnProperty.call(data, 'selected_partner')) {
                const p = data.selected_partner;
                if (!p || !p.is_approved) {
                    notices.push(p ? `Mitra "${p.name}" tidak aktif — pilihan dibersihkan` : 'Mitra terpilih tidak ditemukan — pilihan dibersihkan');
                    this.clearBuyerSelection(false);
                    this.buyerType = 'mitra';
                    this.loadBuyers();
                } else {
                    this.customerName = p.name;
                    this.partnerCode = p.code;
                    this.partnerTypeLabel = p.type_label || p.type;
                    this.partnerPriceMode = p.price_mode || 'eceran';
                    this.partnerPriceModeLabel = p.price_mode_label || p.price_mode;
                    this.partnerInvoiceEnabled = !!p.invoice_enabled;
                    this.partnerCreditDays = parseInt(p.credit_days, 10) || 30;
                    this.partnerAllowTransfer = p.allow_transfer !== false;
                    this.partnerAllowCod = p.allow_cod !== false;
                    this.partnerAddress = p.address || '';
                    this.partnerPicName = p.pic_name || p.name || '';
                    this.partnerPhone = p.phone || '';
                    this.selectedCustomerOverdue = !!p.has_overdue_invoice;
                    if ((!this.partnerInvoiceEnabled || this.selectedCustomerOverdue) && this.paymentMethod === 'Invoice') {
                        this.paymentMethod = 'Tunai';
                    }
                    if (this.isMitraPoCheckout) {
                        if (this.poPaymentMethod === 'invoice' && (!this.partnerInvoiceEnabled || this.selectedCustomerOverdue)) {
                            this.syncPoDefaults(false);
                        } else if (this.poPaymentMethod === 'transfer' && !this.partnerAllowTransfer) {
                            this.syncPoDefaults(false);
                        } else if (this.poPaymentMethod === 'cod' && !this.partnerAllowCod) {
                            this.syncPoDefaults(false);
                        }
                    }
                    this.applyPartnerPricingToCart();
                }
            }

            if (this.customerId && Object.prototype.hasOwnProperty.call(data, 'selected_customer')) {
                const c = data.selected_customer;
                if (!c || !c.is_active) {
                    notices.push(c ? `Pelanggan "${c.name}" nonaktif — pilihan dibersihkan` : 'Pelanggan terpilih tidak ditemukan — pilihan dibersihkan');
                    this.clearBuyerSelection(false);
                    this.buyerType = 'crm';
                    this.loadBuyers();
                } else {
                    this.customerName = c.name;
                    this.customerPoints = c.points || 0;
                    this.selectedCustomerOverdue = !!c.has_overdue_invoice;
                    if (this.pointsRedeemed > this.customerPoints) this.pointsRedeemed = this.customerPoints;
                    if (this.selectedCustomerOverdue && this.paymentMethod === 'Invoice') {
                        this.paymentMethod = 'Tunai';
                    }
                }
            }

            // Segarkan daftar pembeli jika picker sedang terbuka
            if (this.buyerType !== 'umum' && !this.customerId && !this.partnerId) {
                this.loadBuyers();
            }

            if (notices.length) {
                const msg = notices.slice(0, 2).join(' · ') + (notices.length > 2 ? ` (+${notices.length - 2} lainnya)` : '');
                this.syncNotice = msg;
                this.warning(msg);
                setTimeout(() => { if (this.syncNotice === msg) this.syncNotice = ''; }, 6000);
            } else if (!silent) {
                this.info('Data kasir diperbarui dari admin');
            }
        },

        goToPage(page) {
            const target = parseInt(page, 10);
            if (!Number.isFinite(target)) return;
            if (target < 1 || target > this.totalPages) return;
            if (target === this.currentPage) return;
            this.currentPage = target;
        },

        prevPage() {
            if (this.currentPage > 1) this.currentPage--;
        },

        nextPage() {
            if (this.currentPage < this.totalPages) this.currentPage++;
        },

        setBuyerType(type) {
            if (this.buyerType === type) return;
            this.buyerType = type;
            this.customerSearchQuery = '';
            this.customerSearchResults = [];
            this.showCustomerSearchDropdown = false;
            this.showQuickCreate = false;
            this.resetQuickCreateForm();
            this.clearBuyerSelection(false);
            if (type === 'umum') {
                this.info('Mode Pelanggan Umum');
                return;
            }
            this.loadBuyers();
        },

        resetQuickCreateForm() {
            this.quickName = '';
            this.quickPhone = '';
            this.quickType = 'apotek';
            this.quickCreateSaving = false;
        },

        clearBuyerSelection(resetType = true) {
            const wasMitra = !!this.partnerId;
            this.customerId = null;
            this.partnerId = null;
            this.customerName = 'Pelanggan Umum';
            this.customerPoints = 0;
            this.selectedCustomerOverdue = false;
            this.pointsRedeemed = 0;
            this.usePoints = false;
            this.partnerCode = null;
            this.partnerTypeLabel = null;
            this.partnerPriceMode = null;
            this.partnerPriceModeLabel = null;
            this.partnerInvoiceEnabled = false;
            this.partnerCreditDays = 30;
            this.partnerAllowTransfer = true;
            this.partnerAllowCod = true;
            this.partnerAddress = '';
            this.partnerPicName = '';
            this.partnerPhone = '';
            this.mitraCheckoutMode = 'sale';
            this.poPicName = '';
            this.poPicPhone = '';
            this.poShippingAddress = '';
            this.poPaymentMethod = 'transfer';
            this.poNotes = '';
            if (resetType) this.buyerType = 'umum';
            if (this.paymentMethod === 'Invoice') {
                this.paymentMethod = 'Tunai';
            }
            if (wasMitra) {
                this.cart.forEach(item => {
                    item.price_type = 'eceran';
                    this.calculateRowSubtotal(item);
                });
            }
        },

        setMitraCheckoutMode(mode, { silent = false } = {}) {
            this.mitraCheckoutMode = mode;
            if (mode === 'po') {
                this.syncPoDefaults(false);
                if (!silent) this.info('Mode PO Mitra — Transfer/COD/Invoice masuk antrian admin');
            } else {
                if (['Transfer', 'Invoice'].includes(this.paymentMethod)) {
                    this.paymentMethod = 'Tunai';
                }
                if (!silent) this.info('Mode ambil sekarang — hanya Tunai/QRIS, stok dipotong langsung');
            }
        },

        /** Pilih metode bayar Mitra: Tunai/QRIS = Sale; Transfer/COD/Invoice = PO. */
        selectMitraPay(method) {
            if (method === 'Tunai' || method === 'QRIS') {
                this.mitraCheckoutMode = 'sale';
                this.paymentMethod = method;
                return;
            }
            // transfer | cod | invoice
            if (method === 'invoice' && (!this.partnerInvoiceEnabled || this.selectedCustomerOverdue)) {
                this.error(this.selectedCustomerOverdue
                    ? 'Ada tagihan jatuh tempo belum lunas!'
                    : 'Mitra ini tidak diizinkan Invoice (tempo)');
                return;
            }
            if (method === 'transfer' && !this.partnerAllowTransfer) {
                this.error('Mitra ini tidak diizinkan Transfer.');
                return;
            }
            if (method === 'cod' && !this.partnerAllowCod) {
                this.error('Mitra ini tidak diizinkan COD.');
                return;
            }
            this.mitraCheckoutMode = 'po';
            this.syncPoDefaults(true);
            this.poPaymentMethod = method;
        },

        syncPoDefaults(keepPayment = false) {
            const prevPay = this.poPaymentMethod;
            this.poPicName = this.poPicName || this.partnerPicName || this.customerName || '';
            this.poPicPhone = this.poPicPhone || this.partnerPhone || '';
            this.poShippingAddress = this.poShippingAddress || this.partnerAddress || '';
            if (keepPayment && prevPay) {
                this.poPaymentMethod = prevPay;
                return;
            }
            if (this.partnerAllowTransfer) {
                this.poPaymentMethod = 'transfer';
            } else if (this.partnerAllowCod) {
                this.poPaymentMethod = 'cod';
            } else if (this.partnerInvoiceEnabled && !this.selectedCustomerOverdue) {
                this.poPaymentMethod = 'invoice';
            } else {
                this.poPaymentMethod = 'transfer';
            }
        },

        get isMitraPoCheckout() {
            return this.buyerType === 'mitra' && !!this.partnerId && this.mitraCheckoutMode === 'po';
        },

        get canSubmitPartnerOrder() {
            return this.isMitraPoCheckout
                && this.cart.length > 0
                && this.poPicName.trim()
                && this.poPicPhone.trim()
                && this.poShippingAddress.trim()
                && this.poPaymentMethod;
        },

        async submitCheckout() {
            if (this.isMitraPoCheckout) {
                return this.submitPartnerOrder();
            }
            return this.submitTransaction();
        },

        async quickCreateBuyer() {
            if (this.buyerType === 'umum' || this.quickCreateSaving) return;
            const name = this.quickName.trim();
            const phone = this.quickPhone.trim();
            if (!name || !phone) {
                this.error('Nama dan nomor HP wajib diisi.');
                return;
            }
            if (this.buyerType === 'mitra' && !this.quickType) {
                this.error('Pilih tipe mitra.');
                return;
            }

            this.quickCreateSaving = true;
            try {
                const endpoint = this.buyerType === 'mitra' ? '/partners/quick-store' : '/customers/quick-store';
                const body = this.buyerType === 'mitra'
                    ? { name, phone, type: this.quickType }
                    : { name, phone };
                const res = await fetch(endpoint, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    },
                    body: JSON.stringify(body),
                });
                const data = await res.json();
                if (!res.ok || !data.success) {
                    const msg = data.message
                        || (data.errors ? Object.values(data.errors).flat().join(' ') : null)
                        || 'Gagal menambah data.';
                    this.error(msg);
                    return;
                }
                this.showQuickCreate = false;
                this.resetQuickCreateForm();
                if (this.buyerType === 'mitra') {
                    this.selectPartner(data.partner);
                } else {
                    this.selectCustomer(data.customer);
                }
                this.success(data.message || 'Berhasil ditambahkan.');
            } catch (e) {
                console.error(e);
                this.error('Koneksi error, gagal menambah data.');
            } finally {
                this.quickCreateSaving = false;
            }
        },

        async loadBuyers() {
            if (this.buyerType === 'umum') {
                this.customerSearchResults = [];
                this.isLoadingBuyers = false;
                return;
            }
            this.isLoadingBuyers = true;
            try {
                const q = encodeURIComponent(this.customerSearchQuery.trim());
                const endpoint = this.buyerType === 'mitra'
                    ? `/partners/search?limit=40&q=${q}`
                    : `/customers/search?limit=40&q=${q}`;
                const res = await fetch(endpoint);
                this.customerSearchResults = await res.json();
                this.showCustomerSearchDropdown = true;
            } catch (e) {
                console.error(e);
                this.customerSearchResults = [];
            } finally {
                this.isLoadingBuyers = false;
            }
        },

        async searchBuyers() {
            return this.loadBuyers();
        },

        async searchCustomers() {
            return this.loadBuyers();
        },

        reopenBuyerPicker() {
            const type = this.buyerType;
            this.clearBuyerSelection(false);
            this.buyerType = type === 'umum' ? 'crm' : type;
            this.customerSearchQuery = '';
            this.loadBuyers();
        },

        selectCustomer(c) {
            this.buyerType = 'crm';
            this.partnerId = null;
            this.partnerCode = null;
            this.partnerTypeLabel = null;
            this.partnerPriceMode = null;
            this.partnerPriceModeLabel = null;
            this.partnerInvoiceEnabled = false;
            this.customerId = c.id;
            this.customerName = c.name;
            this.customerPoints = c.points || 0;
            this.selectedCustomerOverdue = !!c.has_overdue_invoice;
            this.pointsRedeemed = 0;
            this.customerSearchQuery = '';
            this.showCustomerSearchDropdown = false;
            this.customerSearchResults = [];
            this.showQuickCreate = false;
            if (this.selectedCustomerOverdue && this.paymentMethod === 'Invoice') {
                this.paymentMethod = 'Tunai';
            }
            this.success(`Pelanggan CRM: ${c.name}`);
        },

        selectPartner(p) {
            this.buyerType = 'mitra';
            this.customerId = null;
            this.customerPoints = 0;
            this.pointsRedeemed = 0;
            this.usePoints = false;
            this.partnerId = p.id;
            this.customerName = p.name;
            this.partnerCode = p.code;
            this.partnerTypeLabel = p.type_label || p.type;
            this.partnerPriceMode = p.price_mode || 'eceran';
            this.partnerPriceModeLabel = p.price_mode_label || p.price_mode;
            this.partnerInvoiceEnabled = !!p.invoice_enabled;
            this.partnerCreditDays = parseInt(p.credit_days, 10) || 30;
            this.partnerAllowTransfer = p.allow_transfer !== false;
            this.partnerAllowCod = p.allow_cod !== false;
            this.partnerAddress = p.address || '';
            this.partnerPicName = p.pic_name || p.name || '';
            this.partnerPhone = p.phone || '';
            this.selectedCustomerOverdue = !!p.has_overdue_invoice;
            this.customerSearchQuery = '';
            this.showCustomerSearchDropdown = false;
            this.customerSearchResults = [];
            this.showQuickCreate = false;
            this.poPicName = '';
            this.poPicPhone = '';
            this.poShippingAddress = '';
            this.syncPoDefaults(false);

            // Transfer/Invoice yang sudah terpilih → otomatis mode PO
            if (['Transfer', 'Invoice'].includes(this.paymentMethod)) {
                this.selectMitraPay(this.paymentMethod === 'Invoice' ? 'invoice' : 'transfer');
            } else {
                this.mitraCheckoutMode = 'sale';
                if (!['Tunai', 'QRIS'].includes(this.paymentMethod)) {
                    this.paymentMethod = 'Tunai';
                }
            }

            if (p.ppn_enabled) {
                this.ppnActive = true;
                if (p.ppn_percent) this.ppnPercent = parseFloat(p.ppn_percent) || this.ppnPercent;
                this.ppnBearer = p.ppn_bearer === 'seller'
                    ? 'Ditanggung Penjual'
                    : 'Ditanggung Pembeli';
            }

            this.applyPartnerPricingToCart();

            if ((!this.partnerInvoiceEnabled || this.selectedCustomerOverdue) && this.paymentMethod === 'Invoice') {
                this.paymentMethod = 'Tunai';
            }
            this.success(`Mitra terpilih: ${p.name}`);
        },

        applyPartnerPricingToCart() {
            if (!this.partnerId || !this.partnerPriceMode) return;
            this.cart.forEach(item => {
                let type = 'eceran';
                if (this.partnerPriceMode === 'grosir') {
                    type = 'grosir';
                } else if (this.partnerPriceMode === 'auto') {
                    type = (parseInt(item.quantity, 10) || 1) >= 10 ? 'grosir' : 'eceran';
                }
                item.price_type = type;
                this.calculateRowSubtotal(item);
            });
        },

        clearCustomer() {
            this.clearBuyerSelection(true);
        },

        removeCustomer() {
            this.clearBuyerSelection(true);
            this.info('Menggunakan Pelanggan Umum');
        },

        validatePointsRedeemed() {
            const maxPointsByTotal = Math.ceil((this.subtotalAfterGlobalDiscount + (this.ppnActive && this.ppnBearer === 'Ditanggung Pembeli' ? this.ppnAmount : 0)) / this.crmPointValue);
            const maxAllowed = Math.min(this.customerPoints, maxPointsByTotal);
            if (this.pointsRedeemed > maxAllowed) {
                this.pointsRedeemed = maxAllowed;
                this.warning(`Maksimal poin yang dapat ditukarkan adalah ${maxAllowed}.`);
            }
        },

        async loadPrescription(id) {
            try {
                const res = await fetch(`/prescriptions/${id}/json`);
                const data = await res.json();
                if (data.success) {
                    this.customerName = data.prescription.patient_name;
                    this.cart = [];
                    for (const item of data.prescription.items) {
                        const prodRes = await fetch(`/products/search?q=${encodeURIComponent(item.product_name)}`);
                        const prodData = await prodRes.json();
                        if (prodData.length > 0) {
                            const p = prodData[0];
                            this.cart.push({
                                product_id: p.id,
                                name: p.name,
                                code: p.code,
                                barcode: p.barcode,
                                unit_name: p.unit || 'Pcs',
                                sell_price: parseFloat(p.sell_price),
                                wholesale_price: parseFloat(p.wholesale_price),
                                price_type: 'eceran',
                                quantity: item.quantity,
                                discount_percent: 0,
                                discount_amount: 0,
                                subtotal: parseFloat(p.sell_price) * item.quantity,
                                stock: p.stock,
                                discountTiers: this.getDiscountTiers(item.quantity)
                            });
                        }
                    }
                    this.success('Resep dokter berhasil dimuat ke kasir!');
                } else {
                    this.error(data.message);
                }
            } catch (e) {
                console.error(e);
                this.error('Gagal memuat detail resep!');
            }
        },

        async fetchProducts() {
            this.currentPage = 1;
            if (this.searchAborter) {
                this.searchAborter.abort();
            }
            this.searchAborter = new AbortController();
            this.isLoadingProducts = true;

            try {
                let url = `/products/search?q=${encodeURIComponent(this.searchQuery)}`;
                if (this.activeCategory) {
                    url += `&category_id=${this.activeCategory}`;
                }

                const res = await fetch(url, { signal: this.searchAborter.signal });
                const data = await res.json();
                this.productsList = data;
            } catch (e) {
                if (e.name !== 'AbortError') console.error(e);
            } finally {
                this.isLoadingProducts = false;
            }
        },

        async handleEnter() {
            if (this.searchQuery.trim().length < 2) return;
            await this.fetchProducts();

            if (this.productsList.length === 1 && this.productsList[0].stock > 0) {
                this.addToCart(this.productsList[0]);
                this.searchQuery = '';
                this.fetchProducts();
            }
        },

        addToCart(product) {
            let item = this.cart.find(i => i.product_id === product.id);
            if (item) {
                if (item.quantity < product.stock) {
                    item.quantity++;
                    this.updateDiscountTiers(item);
                } else {
                    this.error('Stok produk tidak mencukupi!');
                    return;
                }
            } else {
                if (product.stock > 0) {
                    const newItem = {
                        product_id: product.id,
                        name: product.name,
                        code: product.code,
                        barcode: product.barcode,
                        unit_name: product.unit || 'Pcs',
                        sell_price: parseFloat(product.sell_price),
                        wholesale_price: parseFloat(product.wholesale_price) || parseFloat(product.sell_price),
                        stock: product.stock,
                        stock_min: product.stock_min,
                        price_type: 'eceran',
                        quantity: 1,
                        discount_percent: 0,
                        discount_amount: 0,
                        subtotal: parseFloat(product.sell_price),
                        discountTiers: [0]
                    };
                    this.updateDiscountTiers(newItem);
                    this.cart.push(newItem);
                    if (this.partnerId) this.applyPartnerPricingToCart();
                    this.success('Ditambahkan ke keranjang');
                } else {
                    this.error('Produk kehabisan stok!');
                    return;
                }
            }
            if (this.partnerId && this.partnerPriceMode === 'auto') {
                this.applyPartnerPricingToCart();
            }
            this.searchQuery = '';
            if (window.innerWidth < 1024) {
                this.isCartModalOpen = true;
            }
        },

        discountRules: @json($discountRules),

        getDiscountTiers(qty) {
            const q = parseInt(qty, 10) || 1;
            const tiers = [0];
            (this.discountRules || []).forEach(rule => {
                const min = parseInt(rule.min_qty, 10) || 1;
                const max = parseInt(rule.max_qty, 10) || 999;
                if (q < min || q > max || !rule.percents) {
                    return;
                }
                String(rule.percents).split(',').forEach(raw => {
                    const p = parseFloat(String(raw).trim());
                    if (Number.isNaN(p)) return;
                    if (!tiers.some(t => Math.abs(t - p) < 0.001)) {
                        tiers.push(p);
                    }
                });
            });
            tiers.sort((a, b) => a - b);
            return tiers;
        },


        updateDiscountTiers(item) {
            const qty = parseInt(item.quantity, 10) || 1;
            item.discountTiers = this.getDiscountTiers(qty);
            const current = parseFloat(item.discount_percent) || 0;
            if (!item.discountTiers.some(t => Math.abs(t - current) < 0.001)) {
                item.discount_percent = 0;
            }
            if (this.partnerId && this.partnerPriceMode === 'auto') {
                item.price_type = qty >= 10 ? 'grosir' : 'eceran';
            } else if (this.partnerId && this.partnerPriceMode === 'grosir') {
                item.price_type = 'grosir';
            }
            this.calculateRowSubtotal(item);
        },

        changePriceType(item, type) {
            item.price_type = type;
            this.calculateRowSubtotal(item);
        },

        calculateRowSubtotal(item) {
            const price = item.price_type === 'grosir' ? item.wholesale_price : item.sell_price;
            const qty = parseInt(item.quantity) || 1;
            const discPercent = parseFloat(item.discount_percent) || 0;

            const gross = price * qty;
            item.discount_amount = Math.round((gross * discPercent) / 100);
            item.subtotal = gross - item.discount_amount;
        },

        removeFromCart(index) {
            this.cart.splice(index, 1);
            this.info('Produk dihapus dari keranjang');
        },

        get subtotal() {
            return this.cart.reduce((sum, item) => sum + item.subtotal, 0);
        },

        get globalDiscountAmount() {
            return (this.subtotal * parseFloat(this.discountPercent || 0)) / 100;
        },

        get subtotalAfterGlobalDiscount() {
            return this.subtotal - this.globalDiscountAmount;
        },

        get ppnAmount() {
            if (!this.ppnActive) return 0;
            const sub = this.subtotalAfterGlobalDiscount;
            if (this.ppnBearer === 'Ditanggung Pembeli') {
                return (sub * parseFloat(this.ppnPercent)) / 100;
            } else {
                return sub - (sub / (1 + (parseFloat(this.ppnPercent) / 100)));
            }
        },

        get pointDiscountAmount() {
            return (parseInt(this.pointsRedeemed) || 0) * parseFloat(this.crmPointValue);
        },

        get grandTotal() {
            const sub = this.subtotalAfterGlobalDiscount;
            let total = sub;
            if (this.ppnActive && this.ppnBearer === 'Ditanggung Pembeli') {
                total = sub + this.ppnAmount;
            }
            total -= this.pointDiscountAmount;
            return Math.max(0, Math.round(total));
        },

        get changeAmount() {
            if (this.paymentMethod === 'QRIS') return 0;
            const cash = parseFloat(this.cashReceived || 0);
            const total = this.grandTotal;
            return cash >= total ? cash - total : 0;
        },

        setExactCash() {
            this.cashReceived = Math.ceil(this.grandTotal);
        },

        addCash(amount) {
            this.cashReceived = (parseFloat(this.cashReceived) || 0) + amount;
        },

        // ─── QRIS Payment Flow ───
        startQrisWaiting() {
            this.qrisPaymentState = 'waiting';
            this.qrisCountdown = this.qrisMaxCountdown;

            // Countdown timer (1 second tick)
            this.qrisPollingTimer = setInterval(() => {
                this.qrisCountdown--;
                if (this.qrisCountdown <= 0) {
                    // Timeout — return to QR screen
                    this.cancelQrisWaiting();
                    this.warning('Waktu habis. Silakan minta pelanggan scan ulang.');
                }
            }, 1000);
        },

        cancelQrisWaiting() {
            if (this.qrisPollingTimer) {
                clearInterval(this.qrisPollingTimer);
                this.qrisPollingTimer = null;
            }
            this.qrisPaymentState = 'idle';
            this.qrisCountdown = this.qrisMaxCountdown;
        },

        async confirmQrisPayment() {
            // Stop the countdown timer
            if (this.qrisPollingTimer) {
                clearInterval(this.qrisPollingTimer);
                this.qrisPollingTimer = null;
            }
            // Show paid state briefly before saving
            this.qrisPaymentState = 'paid';
            // Delay 8 detik agar kasir sempat melihat konfirmasi hijau
            await new Promise(resolve => setTimeout(resolve, 8000));
            // Now submit transaction
            await this.submitTransaction();
        },

        generateDynamicQris(amount) {
            // Base QRIS from BNI Apotek Almaira
            const baseQris = "00020101021126590013ID.CO.BNI.WWW011893600009150464484202096095449950303UMI51440014ID.CO.QRIS.WWW0215ID10265223592760303UMI5204591253033605802ID5914APOTEK ALMAIRA6010BANJARBARU61057071462070703A01630490BA";
            
            // 1. Change Point of Initiation Method from 11 (static) to 12 (dynamic)
            let qris = baseQris.replace("010211", "010212");
            
            // 2. Remove the last 4 characters (the old CRC16 checksum)
            qris = qris.slice(0, -4);
            
            // 3. Construct tag 54 (amount)
            const amountStr = Math.round(amount).toString();
            const amountLen = amountStr.length.toString().padStart(2, '0');
            const tag54 = "54" + amountLen + amountStr;
            
            // 4. Insert tag54 right before tag 58 (5802ID)
            const insertIdx = qris.indexOf("5802ID");
            if (insertIdx !== -1) {
                qris = qris.slice(0, insertIdx) + tag54 + qris.slice(insertIdx);
            }
            
            // 5. Recalculate CRC16-CCITT
            const newCrc = this.calculateCrc16(qris);
            
            // 6. Append the new CRC
            return qris + newCrc;
        },

        calculateCrc16(str) {
            let crc = 0xFFFF;
            for (let c = 0; c < str.length; c++) {
                crc ^= str.charCodeAt(c) << 8;
                for (let i = 0; i < 8; i++) {
                    if (crc & 0x8000) {
                        crc = ((crc << 1) ^ 0x1021) & 0xFFFF;
                    } else {
                        crc = (crc << 1) & 0xFFFF;
                    }
                }
            }
            let hex = (crc & 0xFFFF).toString(16).toUpperCase();
            return hex.padStart(4, '0');
        },

        formatRupiah(n) {
            if (!n && n !== 0) return 'Rp 0';
            return 'Rp ' + Math.round(n).toLocaleString('id-ID');
        },

        async submitTransaction() {
            if (this.cart.length === 0) {
                this.error('Keranjang belanja kosong!');
                return;
            }
            // Guard: Mitra + Transfer/Invoice harus lewat PO
            if (this.buyerType === 'mitra' && this.partnerId && ['Transfer', 'Invoice'].includes(this.paymentMethod)) {
                this.selectMitraPay(this.paymentMethod === 'Invoice' ? 'invoice' : 'transfer');
                this.error('Transfer/Invoice untuk Mitra dibuat sebagai PO. Lengkapi data PIC lalu tekan Buat PO Mitra.');
                return;
            }
            if (this.isMitraPoCheckout) {
                return this.submitPartnerOrder();
            }
            if (this.paymentMethod === 'Tunai' && parseFloat(this.cashReceived || 0) < this.grandTotal) {
                this.error('Uang tunai yang diterima kurang!');
                return;
            }
            if (this.paymentMethod === 'Invoice' && !this.canUseInvoice) {
                this.error(this.invoiceDisabledReason || 'Metode Invoice membutuhkan pelanggan CRM atau mitra!');
                return;
            }
            if (this.paymentMethod === 'Invoice' && this.selectedCustomerOverdue) {
                this.error('Ada tagihan jatuh tempo belum lunas!');
                return;
            }

            this.isSaving = true;

            const payload = {
                customer_name: this.customerName,
                payment_method: this.paymentMethod,
                discount_percent: this.discountPercent,
                discount_amount: this.globalDiscountAmount,
                ppn_active: this.ppnActive ? 1 : 0,
                ppn_percent: this.ppnPercent,
                ppn_amount: this.ppnAmount,
                ppn_bearer: this.ppnBearer,
                cash_received: this.paymentMethod === 'Tunai' ? this.cashReceived : (this.paymentMethod === 'Invoice' ? 0 : this.grandTotal),
                notes: this.notes,
                customer_id: this.buyerType === 'crm' ? this.customerId : null,
                partner_id: this.buyerType === 'mitra' ? this.partnerId : null,
                points_redeemed: this.buyerType === 'crm' ? this.pointsRedeemed : 0,
                prescription_id: this.prescriptionId,
                items: this.cart.map(item => ({
                    product_id: item.product_id,
                    price_type: item.price_type,
                    quantity: item.quantity,
                    discount_percent: item.discount_percent
                }))
            };

            try {
                const res = await fetch('/pos', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify(payload)
                });
                const data = await res.json();
                if (data.success) {
                    this.createdSale = data;
                    this.isCartModalOpen = false;
                    this.showQrisModal = false;
                    this.checkoutStep = 1;
                    this.qrisPaymentState = 'idle';
                    if (this.qrisPollingTimer) { clearInterval(this.qrisPollingTimer); this.qrisPollingTimer = null; }
                    // Show success modal WITHOUT auto-opening print
                    setTimeout(() => { this.showSuccessModal = true; }, 200);
                    setTimeout(() => this.softSync({ silent: true, force: true }), 400);
                } else {
                    this.error(data.message || 'Terjadi kesalahan saat memproses transaksi.');
                }
            } catch (e) {
                this.error('Koneksi error, transaksi gagal disimpan!');
                console.error(e);
            } finally {
                this.isSaving = false;
            }
        },

        async submitPartnerOrder() {
            if (this.cart.length === 0) {
                this.error('Keranjang belanja kosong!');
                return;
            }
            if (!this.partnerId) {
                this.error('Pilih mitra terlebih dahulu.');
                return;
            }
            if (!this.poPicName.trim() || !this.poPicPhone.trim() || !this.poShippingAddress.trim()) {
                this.error('Lengkapi PIC, telepon, dan alamat pengiriman untuk PO.');
                return;
            }
            if (this.poPaymentMethod === 'invoice' && this.selectedCustomerOverdue) {
                this.error('Ada tagihan jatuh tempo belum lunas!');
                return;
            }

            this.isSaving = true;
            try {
                const res = await fetch('/pos/partner-order', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    },
                    body: JSON.stringify({
                        partner_id: this.partnerId,
                        payment_method: this.poPaymentMethod,
                        pic_name: this.poPicName.trim(),
                        pic_phone: this.poPicPhone.trim(),
                        shipping_address: this.poShippingAddress.trim(),
                        notes: this.poNotes.trim() || this.notes || null,
                        discount_amount: this.globalDiscountAmount || 0,
                        items: this.cart.map(item => ({
                            product_id: item.product_id,
                            quantity: item.quantity,
                        })),
                    }),
                });
                const data = await res.json();
                if (!res.ok || !data.success) {
                    const msg = data.message
                        || (data.errors ? Object.values(data.errors).flat().join(' ') : null)
                        || 'Gagal membuat PO Mitra.';
                    this.error(msg);
                    return;
                }

                this.isCartModalOpen = false;
                this.checkoutStep = 1;
                this.success(data.message || `PO ${data.order_no} berhasil dibuat.`);

                if (data.redirect_url) {
                    setTimeout(() => { window.location.href = data.redirect_url; }, 600);
                    return;
                }

                this.resetPOS();
                this.info(`PO ${data.order_no} masuk antrian admin. Lanjut di menu PO Mitra.`);
                setTimeout(() => this.softSync({ silent: true, force: true }), 400);
            } catch (e) {
                console.error(e);
                this.error('Koneksi error, gagal membuat PO Mitra.');
            } finally {
                this.isSaving = false;
            }
        },

        resetPOS() {
            this.cart = [];
            this.customerName = 'Pelanggan Umum';
            this.paymentMethod = 'Tunai';
            this.discountPercent = 0;
            this.cashReceived = null;
            this.notes = '';
            this.showSuccessModal = false;
            this.showQrisModal = false;
            this.createdSale = null;
            this.isCartModalOpen = false;
            this.checkoutStep = 1;
            this.cancelQrisWaiting();
            this.qrisPaymentState = 'idle';
            this.clearBuyerSelection(true);
            this.prescriptionId = null;
            this.success('Siap untuk transaksi baru!');
        },

        // Toast helpers — proxy to global toastManager if available
        success(msg) { window.dispatchEvent(new CustomEvent('toast', { detail: { type: 'success', message: msg } })); },
        error(msg)   { window.dispatchEvent(new CustomEvent('toast', { detail: { type: 'error',   message: msg } })); },
        info(msg)    { window.dispatchEvent(new CustomEvent('toast', { detail: { type: 'info',    message: msg } })); },
        warning(msg) { window.dispatchEvent(new CustomEvent('toast', { detail: { type: 'warning', message: msg } })); },
    });
</script>


<div x-data="posManager()"
     class="pos-workspace"
     @pos-new-transaction.window="resetPOS()">

    {{-- LEFT: Catalog & Search --}}
    <div class="pos-catalog">
        {{-- Search + Category Bar --}}
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm p-3.5 flex flex-col gap-2.5 shrink-0">
            {{-- Search --}}
            <div class="flex items-center gap-2">
                <div class="relative flex-1">
                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
                        <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                    </div>
                    <input
                        type="text"
                        class="w-full pl-10 pr-4 py-2.5 text-sm bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:border-emerald-400 focus:ring-2 focus:ring-emerald-100 transition-all font-medium text-slate-800 placeholder:text-slate-400"
                        placeholder="Cari nama, barcode, kode, atau indikasi/fungsi..."
                        x-model="searchQuery"
                        @input.debounce.400ms="fetchProducts()"
                        @keydown.escape.prevent="searchQuery = ''; fetchProducts()"
                        @keydown.enter.prevent="handleEnter"
                        id="searchInput"
                    >
                </div>
                <button
                    x-show="searchQuery.length > 0"
                    @click="searchQuery = ''; fetchProducts(); $nextTick(() => document.getElementById('searchInput').focus())"
                    class="h-10 w-10 bg-red-50 hover:bg-red-100 text-red-500 rounded-xl flex items-center justify-center cursor-pointer transition-colors border border-red-100 shrink-0"
                    title="Hapus Pencarian"
                    x-cloak
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            {{-- Category Pills (dinamis dari sync admin) --}}
            <div class="flex overflow-x-auto gap-1.5 pb-0.5 items-center" style="scrollbar-width: none; -ms-overflow-style: none;">
                <button
                    type="button"
                    @click="activeCategory = null; fetchProducts()"
                    class="px-3.5 py-1.5 rounded-full text-[11px] font-bold whitespace-nowrap transition-all border cursor-pointer shrink-0"
                    :class="activeCategory === null
                        ? 'bg-emerald-600 text-white border-emerald-700 shadow-sm'
                        : 'bg-white text-slate-500 border-slate-200 hover:border-emerald-300 hover:text-emerald-700 hover:bg-emerald-50'"
                >
                    Semua Lini Obat
                </button>
                <template x-for="cat in categories" :key="'cat-' + cat.id">
                    <button
                        type="button"
                        @click="activeCategory = cat.id; fetchProducts()"
                        class="px-3.5 py-1.5 rounded-full text-[11px] font-bold whitespace-nowrap transition-all border cursor-pointer shrink-0"
                        :class="Number(activeCategory) === Number(cat.id)
                            ? 'bg-emerald-600 text-white border-emerald-700 shadow-sm'
                            : 'bg-white text-slate-500 border-slate-200 hover:border-emerald-300 hover:text-emerald-700 hover:bg-emerald-50'"
                        x-text="cat.name"
                    ></button>
                </template>
                <button type="button"
                        @click="softSync({ silent: false, force: true })"
                        class="ml-auto shrink-0 inline-flex items-center gap-1.5 px-2.5 py-1.5 rounded-full text-[10px] font-bold border border-slate-200 bg-white text-slate-500 hover:text-emerald-700 hover:border-emerald-300 cursor-pointer"
                        :title="isSyncing ? 'Menyinkronkan...' : ('Sinkron data admin' + (lastSyncLabel ? ' · ' + lastSyncLabel : ''))">
                    <svg class="w-3 h-3" :class="isSyncing && 'animate-spin'" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                    <span x-text="isSyncing ? 'Sync...' : 'Live'"></span>
                </button>
            </div>
            <div x-show="syncNotice" class="mt-1 text-[10px] font-semibold text-amber-700 bg-amber-50 border border-amber-100 rounded-lg px-2.5 py-1.5" x-cloak x-text="syncNotice"></div>
        </div>

        {{-- Product Grid --}}
        <div class="pos-catalog-scroll">

            {{-- Loading --}}
            <div x-show="isLoadingProducts" class="flex flex-col items-center justify-center h-48 gap-3" x-cloak>
                <svg class="animate-spin h-7 w-7 text-emerald-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <span class="text-sm font-semibold text-slate-500 animate-pulse">Memuat katalog...</span>
            </div>

            {{-- Empty --}}
            <div x-show="!isLoadingProducts && productsList.length === 0" class="flex flex-col items-center justify-center h-48 text-center" x-cloak>
                <div class="w-14 h-14 bg-slate-100 rounded-2xl flex items-center justify-center text-slate-300 mb-3">
                    <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                </div>
                <h4 class="text-sm font-bold text-slate-600">Produk tidak ditemukan</h4>
                <p class="text-xs text-slate-400 mt-1">Coba kata kunci atau kategori lain.</p>
            </div>

            {{-- Grid --}}
            <div class="grid grid-cols-1 xl:grid-cols-2 gap-3" x-show="!isLoadingProducts && productsList.length > 0">
                <template x-for="product in paginatedProducts" :key="product.id">
                    <div
                        @click="if(product.stock > 0) addToCart(product)"
                        class="rounded-2xl border transition-all flex items-start p-3 group relative overflow-hidden min-h-[96px]"
                        :class="product.stock <= 0
                            ? 'opacity-50 grayscale-[30%] cursor-not-allowed bg-slate-50 border-slate-200/80'
                            : (product.stock <= product.stock_min
                                ? 'bg-amber-50/20 border-amber-200 hover:shadow-[0_4px_20px_rgba(217,119,6,0.08)] hover:border-amber-400 cursor-pointer'
                                : 'bg-white border-slate-200/80 shadow-[0_1px_8px_rgba(0,0,0,0.04)] hover:shadow-[0_4px_20px_rgba(5,150,105,0.1)] hover:border-emerald-300 cursor-pointer')"
                    >
                        {{-- Hover indicator --}}
                        <div
                            class="absolute inset-y-0 left-0 w-1 rounded-l-full opacity-0 group-hover:opacity-100 transition-opacity"
                            :class="product.stock <= product.stock_min ? 'bg-amber-500' : 'bg-emerald-500'"
                            x-show="product.stock > 0"
                        ></div>

                        {{-- Icon / Photo --}}
                        <div class="w-12 h-12 rounded-xl overflow-hidden mr-3.5 mt-0.5 shrink-0 border flex items-center justify-center bg-gradient-to-br"
                             :class="product.stock <= 0
                                ? 'bg-slate-100 border-slate-200 text-slate-400'
                                : 'from-emerald-50 to-emerald-100 text-emerald-600 border-emerald-100/50 group-hover:from-emerald-100 group-hover:to-emerald-200 transition-all'">
                            <img :src="product.image_url || '{{ asset(\App\Models\Product::DEFAULT_IMAGE) }}'"
                                 class="w-full h-full"
                                 :class="product.has_image ? 'object-cover' : 'object-contain p-1 bg-white'"
                                 alt="">
                        </div>

                        {{-- Info --}}
                        <div class="flex flex-col flex-1 min-w-0 pr-3">
                            <h4 class="font-bold text-slate-800 text-[13.5px] leading-snug line-clamp-2 group-hover:text-emerald-700 transition-colors" x-text="product.name"></h4>

                            {{-- Indikasi: 1 baris, tinggi tetap agar grid seragam --}}
                            <div class="mt-1.5 h-[22px]" x-show="product.indikasi" x-cloak>
                                <div class="flex items-center gap-1.5 max-w-full h-full rounded-md bg-teal-50/70 px-1.5 ring-1 ring-inset ring-teal-100/60"
                                     :title="'Indikasi / Fungsi: ' + product.indikasi">
                                    <span class="shrink-0 inline-flex items-center justify-center w-3.5 h-3.5 rounded-full bg-teal-500/15 text-teal-600">
                                        <svg class="w-2.5 h-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 16h-1v-4h-1m1-4h.01M12 2a10 10 0 100 20 10 10 0 000-20z"/>
                                        </svg>
                                    </span>
                                    <p class="min-w-0 flex-1 text-[10.5px] leading-none text-teal-800/85 truncate">
                                        <span class="font-semibold text-teal-700/70">Fungsi</span>
                                        <span class="text-teal-600/40 mx-0.5">·</span>
                                        <span x-text="product.indikasi"></span>
                                    </p>
                                </div>
                            </div>
                            <div class="mt-1.5 h-[22px]" x-show="!product.indikasi" aria-hidden="true"></div>

                            <div class="flex flex-wrap items-center gap-1.5 mt-1.5">
                                <span
                                    class="text-[10px] font-semibold px-2 py-0.5 rounded-full border"
                                    :class="product.stock <= 0
                                        ? 'bg-red-50 text-red-600 border-red-100'
                                        : (product.stock <= product.stock_min
                                            ? 'bg-amber-50 text-amber-700 border-amber-200'
                                            : 'bg-emerald-50 text-emerald-700 border-emerald-100')"
                                    x-text="product.stock <= 0
                                        ? 'Habis'
                                        : (product.stock <= product.stock_min
                                            ? 'Stok: ' + product.stock + ' ' + (product.unit || '') + ' (Menipis)'
                                            : 'Stok: ' + product.stock + ' ' + (product.unit || ''))"
                                ></span>
                                <span class="text-[10px] text-slate-400 font-medium" x-text="'SKU: ' + (product.code || '-')"></span>
                            </div>
                        </div>

                        {{-- Price --}}
                        <div class="flex flex-col items-end shrink-0 pt-0.5">
                            <span class="font-black text-emerald-600 text-[15px] tracking-tight" x-text="formatRupiah(product.sell_price)"></span>
                            <span class="text-[10px] text-slate-400 font-medium mt-0.5" x-show="product.wholesale_price && product.wholesale_price < product.sell_price">
                                Grosir: <span x-text="formatRupiah(product.wholesale_price)"></span>
                            </span>
                        </div>
                    </div>
                </template>
            </div>

            {{-- Pagination Controls — Alpine only, tanpa full reload --}}
            <div x-show="!isLoadingProducts && productsList.length > 0" class="mt-5 pt-4 border-t border-slate-100" x-cloak>
                <div class="flex flex-col items-center gap-3.5">
                    <p class="text-xs text-slate-500">
                        Menampilkan
                        <span class="inline-flex items-center px-2 py-0.5 mx-0.5 rounded-md bg-emerald-50 text-emerald-700 font-bold" x-text="displayRange"></span>
                        dari
                        <span class="font-bold text-slate-700" x-text="productsList.length"></span>
                        produk
                    </p>

                    <nav class="inline-flex items-center gap-1 p-1.5 rounded-2xl bg-slate-50 border border-slate-200/80 shadow-sm" aria-label="Pagination">
                        <button type="button"
                            @click.prevent="prevPage()"
                            :disabled="currentPage === 1"
                            class="inline-flex items-center justify-center gap-1 h-9 px-3 rounded-xl text-xs font-bold transition-colors
                                   disabled:opacity-40 disabled:cursor-not-allowed text-slate-500 hover:bg-white hover:text-emerald-700 hover:shadow-sm">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7"/></svg>
                            <span class="hidden sm:inline">Kembali</span>
                        </button>

                        <template x-for="(page, idx) in pageNumbers" :key="'pnav-' + idx + '-' + page">
                            <span class="inline-flex">
                                <button type="button"
                                    x-show="page !== '…'"
                                    @click.prevent="goToPage(page)"
                                    class="inline-flex items-center justify-center min-w-[36px] h-9 px-2 rounded-xl text-xs font-bold transition-all"
                                    :class="Number(page) === currentPage
                                        ? 'bg-emerald-600 text-white shadow-md shadow-emerald-600/25'
                                        : 'text-slate-600 hover:bg-white hover:text-emerald-700 hover:shadow-sm'"
                                    x-text="page">
                                </button>
                                <span x-show="page === '…'" class="inline-flex items-center justify-center min-w-[28px] h-9 text-slate-300 text-xs font-bold select-none">…</span>
                            </span>
                        </template>

                        <button type="button"
                            @click.prevent="nextPage()"
                            :disabled="currentPage === totalPages"
                            class="inline-flex items-center justify-center gap-1 h-9 px-3 rounded-xl text-xs font-bold transition-colors
                                   disabled:opacity-40 disabled:cursor-not-allowed text-slate-500 hover:bg-white hover:text-emerald-700 hover:shadow-sm">
                            <span class="hidden sm:inline">Berikutnya</span>
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/></svg>
                        </button>
                    </nav>
                </div>
            </div>
        </div>
    </div>

    {{-- RIGHT: Cart Panel (desktop) --}}
    <div class="pos-cart">
        {{-- Sidebar Header with Stepper --}}
        <div class="px-5 py-4 bg-white border-b border-slate-100 flex flex-col gap-3 shrink-0 shadow-[0_1px_0_rgba(0,0,0,0.04)]">
            <div class="flex justify-between items-center">
                {{-- Header Title: Step 1 --}}
                <div x-show="checkoutStep === 1" class="flex items-center gap-3" x-cloak>
                    <div class="w-9 h-9 rounded-xl bg-gradient-to-br from-emerald-500 to-emerald-600 text-white flex items-center justify-center shadow-md shadow-emerald-500/25">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
                        </svg>
                    </div>
                    <div>
                        <h3 class="font-bold text-slate-900 text-sm leading-tight">Keranjang Belanja</h3>
                        <p class="text-[10px] text-slate-400 mt-0.5" x-text="cart.length + ' item'"></p>
                    </div>
                </div>
                {{-- Header Title: Step 2 --}}
                <div x-show="checkoutStep === 2" class="flex items-center gap-2.5" x-cloak>
                    <button @click="checkoutStep = 1" class="w-8 h-8 rounded-lg bg-slate-50 hover:bg-slate-100 text-slate-600 flex items-center justify-center cursor-pointer border border-slate-200/50 transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                    </button>
                    <div>
                        <h3 class="font-bold text-slate-900 text-sm leading-tight">Metode Pembayaran</h3>
                        <p class="text-[10px] text-slate-400 mt-0.5" x-text="'Bayar ' + cart.length + ' item'"></p>
                    </div>
                </div>
                
                {{-- Clear Cart button (Only in Step 1) --}}
                <button
                    x-show="checkoutStep === 1"
                    @click="cart = []; success('Keranjang dikosongkan')"
                    class="text-[11px] text-red-500 font-bold hover:bg-red-50 px-2.5 py-1.5 rounded-lg cursor-pointer transition-colors disabled:opacity-30"
                    :disabled="cart.length === 0"
                    x-cloak
                >
                    Kosongkan
                </button>
            </div>

            {{-- Stepper Progress --}}
            <div class="flex flex-col gap-1.5 mt-0.5">
                <div class="flex items-center justify-between text-[10px] font-extrabold tracking-wider uppercase">
                    <span class="cursor-pointer transition-colors" @click="if(cart.length > 0) checkoutStep = 1" :class="checkoutStep === 1 ? 'text-emerald-600' : 'text-slate-400 hover:text-slate-600'">1. Keranjang</span>
                    <svg class="w-2.5 h-2.5 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/></svg>
                    <span class="cursor-pointer transition-colors" @click="if(cart.length > 0) checkoutStep = 2" :class="checkoutStep === 2 ? 'text-emerald-600' : 'text-slate-400 hover:text-slate-600'">2. Pembayaran</span>
                </div>
                <div class="w-full bg-slate-100 h-1.5 rounded-full overflow-hidden relative">
                    <div class="bg-gradient-to-r from-emerald-500 to-emerald-600 h-full transition-all duration-300 rounded-full" :style="checkoutStep === 1 ? 'width: 50%' : 'width: 100%'"></div>
                </div>
            </div>
        </div>

        {{-- STEP 1: Empty state --}}
        <div x-show="cart.length === 0 && checkoutStep === 1" class="flex-1 flex flex-col items-center justify-center text-center p-8 bg-slate-50/50">
            <div class="w-16 h-16 bg-emerald-50 text-emerald-300 rounded-2xl flex items-center justify-center mb-4 border border-emerald-100">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.25" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
            </div>
            <h4 class="text-xs font-bold text-slate-700">Keranjang masih kosong</h4>
            <p class="text-[10px] text-slate-400 mt-1 max-w-[180px]">Pilih produk dari katalog untuk memulai transaksi.</p>
        </div>

        {{-- STEP 1: Cart Content --}}
        <div x-show="cart.length > 0 && checkoutStep === 1" class="flex-1 flex flex-col min-h-0 overflow-hidden" x-cloak>
            <div class="flex-1 overflow-y-auto p-4 flex flex-col gap-3" style="scrollbar-width: thin;">
                <template x-for="(item, index) in cart" :key="item.product_id">
                    <div class="bg-white rounded-xl border border-slate-200/80 p-3 shadow-[0_1px_3px_rgba(0,0,0,0.02)] flex flex-col gap-2">
                        <div class="flex justify-between items-start gap-2">
                            <div class="min-w-0 flex-1">
                                <p class="font-bold text-slate-800 text-xs truncate" x-text="item.name"></p>
                                <span class="text-[9px] text-slate-450 mt-0.5 block" x-text="'SKU: ' + item.code"></span>
                            </div>
                            <button @click="removeFromCart(index)" class="text-slate-300 hover:text-red-500 transition-colors cursor-pointer">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/></svg>
                            </button>
                        </div>
                        
                        <div class="pos-cart-row">
                            {{-- Price Type Toggle --}}
                            <div class="flex bg-slate-100 p-0.5 rounded-lg h-7 border border-slate-200/50 shrink-0">
                                <button type="button" @click="changePriceType(item, 'eceran')" class="px-2 text-[10px] font-bold rounded-md cursor-pointer" :class="item.price_type === 'eceran' ? 'bg-white text-emerald-700 shadow-xs' : 'text-slate-400'">Ecer</button>
                                <button type="button" @click="changePriceType(item, 'grosir')" class="px-2 text-[10px] font-bold rounded-md cursor-pointer" :class="item.price_type === 'grosir' ? 'bg-white text-emerald-700 shadow-xs' : 'text-slate-400'">Grosir</button>
                            </div>

                            {{-- Quantity control --}}
                            <div class="flex items-center bg-white border border-slate-200 rounded-md h-7 overflow-hidden shrink-0">
                                <button type="button" @click="if(item.quantity > 1) { item.quantity--; updateDiscountTiers(item); }" class="w-6 h-full flex items-center justify-center text-slate-500 hover:bg-slate-50 text-xs font-bold cursor-pointer">-</button>
                                <input type="number" class="w-8 h-full text-center font-black text-xs border-none p-0 focus:ring-0 bg-transparent text-slate-800" x-model.number="item.quantity" @input="if(item.quantity < 1) item.quantity = 1; if(item.quantity > item.stock) item.quantity = item.stock; updateDiscountTiers(item);">
                                <button type="button" @click="if(item.quantity < item.stock) { item.quantity++; updateDiscountTiers(item); } else { error('Stok terbatas!'); }" class="w-6 h-full flex items-center justify-center text-slate-500 hover:bg-slate-50 text-xs font-bold cursor-pointer">+</button>
                            </div>

                            {{-- Discount dropdown --}}
                            <div class="relative shrink-0">
                                <select
                                    x-model.number="item.discount_percent"
                                    @change="calculateRowSubtotal(item)"
                                    class="appearance-none h-7 pl-2 pr-6 rounded-md text-[10px] font-bold border focus:outline-none bg-slate-50 text-slate-650 border-slate-200 cursor-pointer"
                                    style="background-image: url(&quot;data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%236b7280' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='M6 8l4 4 4-4'/%3e%3c/svg%3e&quot;); background-position: right 0.25rem center; background-repeat: no-repeat; background-size: 1.1em 1.1em; padding-right: 1.25rem;"
                                >
                                    <option value="0">0%</option>
                                    <template x-for="tier in (item.discountTiers || []).filter(t => t > 0)" :key="tier">
                                        <option :value="tier" x-text="tier + '%'"></option>
                                    </template>
                                </select>
                            </div>

                            {{-- Subtotal --}}
                            <div class="pos-cart-total">
                                <span class="font-extrabold text-emerald-600 text-xs" x-text="formatRupiah(item.subtotal)"></span>
                            </div>
                        </div>
                    </div>
                </template>
            </div>

            {{-- Checkout Footer: Step 1 --}}
            <div class="p-4 bg-slate-50 border-t border-slate-200 shrink-0 flex flex-col gap-3">
                <div class="rounded-xl p-3 flex justify-between items-center bg-slate-900 text-white shadow">
                    <span class="text-[10px] text-slate-400 font-bold uppercase tracking-wider">Subtotal</span>
                    <span class="text-sm font-black text-emerald-400" x-text="formatRupiah(subtotal)"></span>
                </div>
                <button
                    @click="checkoutStep = 2"
                    class="w-full py-3 rounded-xl text-xs font-black text-white bg-gradient-to-r from-emerald-500 to-emerald-600 hover:from-emerald-600 hover:to-emerald-700 shadow-md shadow-emerald-500/20 active:scale-[0.98] transition-all flex items-center justify-center gap-2 cursor-pointer"
                >
                    <span>Lanjut ke Pembayaran ➜</span>
                </button>
            </div>
        </div>

        {{-- STEP 2: Payment Fields & Checkout --}}
        <div x-show="checkoutStep === 2" class="flex-1 flex flex-col min-h-0 overflow-hidden" x-cloak>
            <div class="pos-pay-scroll">
                {{-- Card: Pembeli --}}
                <section class="pos-pay-card" :class="buyerType === 'mitra' && partnerId ? 'is-mitra' : ''">
                    <span class="pos-pay-label">Pembeli</span>
                    <div class="pos-pay-seg mb-2.5">
                        <button type="button" @click="setBuyerType('umum')" :class="buyerType === 'umum' ? 'is-on' : ''">Umum</button>
                        <button type="button" @click="setBuyerType('crm')" :class="buyerType === 'crm' ? 'is-on is-crm' : ''">CRM</button>
                        <button type="button" @click="setBuyerType('mitra')" :class="buyerType === 'mitra' ? 'is-on is-mitra' : ''">Mitra</button>
                    </div>

                    <div x-show="buyerType === 'umum'" class="rounded-xl bg-slate-50 border border-slate-100 px-3 py-2.5 text-[11px] text-slate-500 font-medium" x-cloak>
                        Walk-in · tanpa akun pelanggan
                    </div>

                    <div x-show="buyerType !== 'umum' && !customerId && !partnerId" class="rounded-xl border border-slate-200 overflow-hidden bg-white" x-cloak>
                        <div class="px-2.5 py-2 bg-slate-50/90 border-b border-slate-100">
                            <div class="relative">
                                <svg class="w-3.5 h-3.5 text-slate-400 absolute left-2.5 top-1/2 -translate-y-1/2 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                                <input type="text"
                                       class="pos-pay-field !pl-8 !py-1.5"
                                       :placeholder="buyerType === 'mitra' ? 'Cari mitra...' : 'Cari pelanggan...'"
                                       x-model="customerSearchQuery"
                                       @input.debounce.250ms="loadBuyers()">
                            </div>
                        </div>
                        <div class="max-h-44 overflow-y-auto divide-y divide-slate-50" style="scrollbar-width: thin;">
                            <div x-show="isLoadingBuyers" class="px-3 py-5 text-center text-[11px] text-slate-400 font-semibold" x-cloak>Memuat...</div>
                            <div x-show="!isLoadingBuyers && customerSearchResults.length === 0" class="px-3 py-5 text-center text-[11px] text-slate-400" x-cloak>
                                <span x-text="buyerType === 'mitra' ? 'Belum ada mitra aktif.' : 'Belum ada pelanggan CRM.'"></span>
                            </div>
                            <template x-for="c in customerSearchResults" :key="(buyerType === 'mitra' ? 'p' : 'c') + c.id">
                                <button type="button"
                                        class="w-full px-2.5 py-2 flex items-center gap-2.5 text-left hover:bg-slate-50 transition-colors cursor-pointer"
                                        @click="buyerType === 'mitra' ? selectPartner(c) : selectCustomer(c)">
                                    <div class="w-8 h-8 rounded-full flex items-center justify-center text-[11px] font-black text-white shrink-0"
                                         :class="buyerType === 'mitra' ? 'bg-gradient-to-br from-cyan-500 to-teal-600' : 'bg-gradient-to-br from-emerald-500 to-emerald-600'"
                                         x-text="(c.name || '?').charAt(0).toUpperCase()"></div>
                                    <div class="min-w-0 flex-1">
                                        <p class="text-[12px] font-bold text-slate-800 truncate" x-text="c.name"></p>
                                        <p class="text-[10px] text-slate-400 truncate"
                                           x-text="buyerType === 'mitra'
                                                ? ((c.code || '-') + (c.type_label ? ' · ' + c.type_label : ''))
                                                : (c.phone || 'Tanpa no. HP')"></p>
                                    </div>
                                    <span x-show="buyerType === 'crm'" class="text-[10px] font-bold text-emerald-600" x-text="(c.points || 0) + ' Pts'"></span>
                                    <span x-show="c.has_overdue_invoice" class="text-[8px] font-extrabold text-red-600 bg-red-50 px-1 py-0.5 rounded">OD</span>
                                </button>
                            </template>
                        </div>
                        <div class="px-2.5 py-2 border-t border-slate-100 bg-slate-50/70 space-y-2">
                            <button type="button" @click="showQuickCreate = !showQuickCreate"
                                    class="w-full text-[10px] font-bold text-emerald-700 hover:text-emerald-800 py-0.5 cursor-pointer">
                                <span x-text="showQuickCreate ? 'Tutup form' : (buyerType === 'mitra' ? '+ Mitra baru' : '+ Pelanggan baru')"></span>
                            </button>
                            <div x-show="showQuickCreate" class="space-y-1.5" x-cloak>
                                <input type="text" x-model="quickName" placeholder="Nama" class="pos-pay-field !py-1.5">
                                <input type="text" x-model="quickPhone" placeholder="No. HP" class="pos-pay-field !py-1.5">
                                <select x-show="buyerType === 'mitra'" x-model="quickType" class="pos-pay-field !py-1.5" x-cloak>
                                    <template x-for="(label, key) in partnerTypes" :key="key">
                                        <option :value="key" x-text="label"></option>
                                    </template>
                                </select>
                                <button type="button" @click="await quickCreateBuyer()" :disabled="quickCreateSaving"
                                        class="w-full py-2 rounded-lg text-[10px] font-black text-white bg-emerald-600 hover:bg-emerald-700 disabled:opacity-50 cursor-pointer">
                                    <span x-text="quickCreateSaving ? 'Menyimpan...' : 'Simpan & pilih'"></span>
                                </button>
                            </div>
                        </div>
                    </div>

                    <div x-show="buyerType === 'crm' && customerId" class="rounded-xl bg-emerald-50/80 border border-emerald-100 p-2.5 space-y-2" x-cloak>
                        <div class="flex justify-between items-center gap-2">
                            <div class="flex items-center gap-2 min-w-0">
                                <div class="w-9 h-9 rounded-full bg-gradient-to-br from-emerald-500 to-emerald-600 text-white text-[12px] font-black flex items-center justify-center shrink-0" x-text="(customerName || '?').charAt(0).toUpperCase()"></div>
                                <div class="min-w-0">
                                    <p class="text-xs font-bold text-slate-800 truncate" x-text="customerName"></p>
                                    <p class="text-[10px] text-emerald-700 font-semibold" x-text="customerPoints + ' poin'"></p>
                                </div>
                            </div>
                            <button type="button" @click="reopenBuyerPicker()" class="text-[10px] font-bold text-emerald-700 bg-white/80 hover:bg-white px-2.5 py-1 rounded-lg border border-emerald-100 cursor-pointer shrink-0">Ganti</button>
                        </div>
                        <div class="flex items-center gap-2 pt-1 border-t border-emerald-100/80">
                            <label class="relative inline-flex items-center cursor-pointer shrink-0">
                                <input type="checkbox" x-model="usePoints" class="sr-only peer" @change="if(!usePoints) pointsRedeemed = 0">
                                <div class="w-7 h-4 bg-slate-300 rounded-full peer peer-checked:after:translate-x-full after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-3 after:w-3 after:transition-all peer-checked:bg-emerald-500"></div>
                            </label>
                            <span class="text-[10px] text-slate-500 font-bold uppercase tracking-wider">Tukar poin</span>
                            <template x-if="usePoints">
                                <input type="number" class="pos-pay-field !py-1 !w-20 ml-auto" placeholder="0" x-model.number="pointsRedeemed" @input="validatePointsRedeemed()" min="0" :max="customerPoints">
                            </template>
                        </div>
                    </div>

                    <div x-show="buyerType === 'mitra' && partnerId" class="rounded-xl bg-white/70 border border-cyan-100/80 p-2.5" x-cloak>
                        <div class="flex justify-between items-center gap-2">
                            <div class="flex items-center gap-2.5 min-w-0">
                                <div class="w-9 h-9 rounded-full bg-gradient-to-br from-cyan-500 to-teal-600 text-white text-[12px] font-black flex items-center justify-center shrink-0 ring-2 ring-white" x-text="(customerName || '?').charAt(0).toUpperCase()"></div>
                                <div class="min-w-0">
                                    <p class="text-xs font-bold text-slate-800 truncate" x-text="customerName"></p>
                                    <p class="text-[10px] text-slate-500 truncate" x-text="(partnerCode || '-') + ' · ' + (partnerTypeLabel || 'Mitra')"></p>
                                </div>
                            </div>
                            <button type="button" @click="reopenBuyerPicker()" class="text-[10px] font-bold text-cyan-700 bg-white hover:bg-cyan-50 px-2.5 py-1 rounded-lg border border-cyan-100 cursor-pointer shrink-0">Ganti</button>
                        </div>
                        <div class="flex flex-wrap gap-1 mt-2">
                            <span class="text-[9px] font-bold px-2 py-0.5 rounded-full bg-cyan-100/90 text-cyan-800" x-text="partnerPriceModeLabel || partnerPriceMode"></span>
                            <span class="text-[9px] font-bold px-2 py-0.5 rounded-full"
                                  :class="partnerInvoiceEnabled ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-500'"
                                  x-text="partnerInvoiceEnabled ? ('Tempo ' + partnerCreditDays + 'h') : 'Tanpa tempo'"></span>
                        </div>
                    </div>
                </section>

                {{-- Card: Metode bayar Mitra --}}
                <section x-show="buyerType === 'mitra' && partnerId" class="pos-pay-card" :class="isMitraPoCheckout ? 'is-po' : ''" x-cloak>
                    <span class="pos-pay-label">Jenis transaksi</span>
                    <div class="pos-pay-seg mb-3">
                        <button type="button" @click="setMitraCheckoutMode('sale')" :class="!isMitraPoCheckout ? 'is-on' : ''">Ambil sekarang</button>
                        <button type="button" @click="setMitraCheckoutMode('po')" :class="isMitraPoCheckout ? 'is-on is-mitra' : ''">Buat PO</button>
                    </div>

                    <div x-show="!isMitraPoCheckout" class="pos-pay-methods" x-cloak>
                        <button type="button" class="pos-pay-method is-tunai" :class="paymentMethod === 'Tunai' ? 'is-on' : ''" @click="selectMitraPay('Tunai')">
                            Tunai<span class="hint">Stok langsung</span>
                        </button>
                        <button type="button" class="pos-pay-method is-qris" :class="paymentMethod === 'QRIS' ? 'is-on' : ''" @click="selectMitraPay('QRIS')">
                            QRIS<span class="hint">Stok langsung</span>
                        </button>
                    </div>

                    <div x-show="isMitraPoCheckout" class="space-y-3" x-cloak>
                        <div class="pos-pay-methods">
                            <button type="button" x-show="partnerAllowTransfer" class="pos-pay-method is-transfer" :class="poPaymentMethod === 'transfer' ? 'is-on' : ''" @click="selectMitraPay('transfer')">
                                Transfer<span class="hint">Masuk antrian</span>
                            </button>
                            <button type="button" x-show="partnerAllowCod" class="pos-pay-method is-cod" :class="poPaymentMethod === 'cod' ? 'is-on' : ''" @click="selectMitraPay('cod')">
                                COD<span class="hint">Bayar di tempat</span>
                            </button>
                            <button type="button" x-show="partnerInvoiceEnabled && !selectedCustomerOverdue" class="pos-pay-method is-invoice" :class="poPaymentMethod === 'invoice' ? 'is-on' : ''" @click="selectMitraPay('invoice')">
                                Invoice<span class="hint" x-text="partnerCreditDays + ' hari'"></span>
                            </button>
                        </div>

                        <div class="rounded-xl bg-slate-50/80 border border-slate-100 p-2.5 space-y-2">
                            <p class="text-[10px] font-extrabold text-cyan-700 uppercase tracking-wider">Pengiriman</p>
                            <div>
                                <label class="pos-pay-field-label">Nama PIC</label>
                                <input type="text" x-model="poPicName" class="pos-pay-field" placeholder="Nama penerima / PIC">
                            </div>
                            <div>
                                <label class="pos-pay-field-label">Telepon PIC</label>
                                <input type="text" x-model="poPicPhone" class="pos-pay-field" placeholder="08…">
                            </div>
                            <div>
                                <label class="pos-pay-field-label">Alamat pengiriman</label>
                                <textarea x-model="poShippingAddress" rows="2" class="pos-pay-field resize-none" placeholder="Alamat lengkap"></textarea>
                            </div>
                            <div>
                                <label class="pos-pay-field-label">Catatan <span class="font-medium text-slate-400">(opsional)</span></label>
                                <input type="text" x-model="poNotes" class="pos-pay-field" placeholder="Catatan pesanan">
                            </div>
                        </div>
                    </div>
                </section>

                {{-- Card: Metode bayar Umum / CRM --}}
                <section x-show="buyerType !== 'mitra' || !partnerId" class="pos-pay-card" x-cloak>
                    <span class="pos-pay-label">Metode pembayaran</span>
                    <div class="grid grid-cols-2 gap-2">
                        <button type="button" class="pos-pay-method is-tunai" :class="paymentMethod === 'Tunai' ? 'is-on' : ''" @click="paymentMethod = 'Tunai'">Tunai</button>
                        <button type="button" class="pos-pay-method is-transfer" :class="paymentMethod === 'Transfer' ? 'is-on' : ''" @click="paymentMethod = 'Transfer'">Transfer</button>
                        <button type="button" class="pos-pay-method is-qris" :class="paymentMethod === 'QRIS' ? 'is-on' : ''" @click="paymentMethod = 'QRIS'">QRIS</button>
                        <button type="button" class="pos-pay-method is-invoice" :class="paymentMethod === 'Invoice' ? 'is-on' : ''"
                                @click="if(canUseInvoice) paymentMethod = 'Invoice'"
                                :disabled="!canUseInvoice"
                                :title="invoiceDisabledReason"
                                :style="!canUseInvoice ? 'opacity:0.4;cursor:not-allowed' : ''">Invoice</button>
                    </div>
                    <div x-show="selectedCustomerOverdue" class="mt-2 px-2.5 py-2 rounded-lg bg-red-50 border border-red-100 text-[10px] text-red-600 font-bold" x-cloak>
                        Tagihan jatuh tempo belum lunas — Invoice diblokir.
                    </div>
                </section>

                {{-- Card: Tunai / QRIS detail --}}
                <section x-show="paymentMethod === 'Tunai' && !isMitraPoCheckout" class="pos-pay-card" x-cloak>
                    <span class="pos-pay-label">Uang diterima</span>
                    <div class="flex items-stretch rounded-xl overflow-hidden border border-slate-200 focus-within:border-emerald-400 bg-white">
                        <span class="flex items-center px-3 bg-emerald-600 text-white text-[10px] font-black shrink-0">Rp</span>
                        <input type="number" class="flex-1 py-2.5 px-3 font-bold text-slate-800 text-sm border-none focus:outline-none bg-transparent" placeholder="0" x-model.number="cashReceived">
                    </div>
                    <div class="grid grid-cols-3 gap-1.5 mt-2">
                        <button type="button" @click="setExactCash()" class="py-1.5 text-[10px] font-bold bg-emerald-50 border border-emerald-100 text-emerald-700 rounded-lg cursor-pointer">Pas</button>
                        <button type="button" @click="addCash(50000)" class="py-1.5 text-[10px] font-bold bg-white border border-slate-200 text-slate-600 rounded-lg cursor-pointer">+50k</button>
                        <button type="button" @click="addCash(100000)" class="py-1.5 text-[10px] font-bold bg-white border border-slate-200 text-slate-600 rounded-lg cursor-pointer">+100k</button>
                    </div>
                </section>

                <section x-show="paymentMethod === 'QRIS' && !isMitraPoCheckout" class="pos-pay-card text-center" x-cloak>
                    <p class="text-[11px] font-bold text-violet-700">QRIS · NMID <span x-text="qrisNmid"></span></p>
                    <button type="button" @click="showQrisModal = true" class="mt-2 w-full py-2 bg-violet-50 border border-violet-100 text-violet-700 text-[11px] font-bold rounded-xl hover:bg-violet-100 cursor-pointer">Tampilkan QR Code</button>
                </section>

                {{-- Card: Diskon + PPN kompakt --}}
                <section class="pos-pay-card">
                    <span class="pos-pay-label">Penyesuaian</span>
                    <div class="grid grid-cols-2 gap-2.5">
                        <div>
                            <label class="pos-pay-field-label">Diskon %</label>
                            <div class="relative">
                                <input type="number" min="0" max="100" placeholder="0" x-model.number="discountPercent"
                                       class="pos-pay-field !pr-7"
                                       :class="discountPercent > 0 ? '!text-orange-600 !border-orange-200 !bg-orange-50' : ''">
                                <span class="absolute right-2.5 top-1/2 -translate-y-1/2 text-[11px] font-bold text-slate-400">%</span>
                            </div>
                        </div>
                        <div>
                            <label class="pos-pay-field-label">PPN <span x-text="ppnPercent + '%'"></span></label>
                            <div class="flex items-center justify-between h-[38px] px-2.5 rounded-[10px] border border-slate-200 bg-slate-50">
                                <span class="text-[11px] font-bold" :class="ppnActive ? 'text-emerald-700' : 'text-slate-400'" x-text="ppnActive ? 'Aktif' : 'Off'"></span>
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input type="checkbox" x-model="ppnActive" class="sr-only peer">
                                    <div class="w-8 h-4.5 bg-slate-300 rounded-full peer peer-checked:after:translate-x-full after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-3.5 after:w-3.5 after:transition-all peer-checked:bg-emerald-500"></div>
                                </label>
                            </div>
                        </div>
                    </div>
                    <div x-show="ppnActive" class="pos-pay-seg mt-2.5" x-cloak>
                        <button type="button" @click="ppnBearer = 'Ditanggung Pembeli'" :class="ppnBearer === 'Ditanggung Pembeli' ? 'is-on is-crm' : ''">Pembeli</button>
                        <button type="button" @click="ppnBearer = 'Ditanggung Penjual'" :class="ppnBearer === 'Ditanggung Penjual' ? 'is-on is-crm' : ''">Penjual</button>
                    </div>
                </section>
            </div>

            {{-- Footer ringkas --}}
            <div class="pos-pay-footer">
                <div class="pos-pay-total text-xs space-y-1.5">
                    <div class="flex justify-between text-slate-400">
                        <span>Subtotal</span>
                        <span class="text-slate-200 font-semibold" x-text="formatRupiah(subtotal)"></span>
                    </div>
                    <div x-show="discountPercent > 0" class="flex justify-between text-orange-300">
                        <span>Diskon</span>
                        <span x-text="'-' + formatRupiah(globalDiscountAmount)"></span>
                    </div>
                    <div x-show="ppnActive" class="flex justify-between text-slate-400">
                        <span x-text="'PPN ' + ppnPercent + '%'"></span>
                        <span x-text="formatRupiah(ppnAmount)"></span>
                    </div>
                    <div x-show="pointsRedeemed > 0" class="flex justify-between text-emerald-300">
                        <span>Poin</span>
                        <span x-text="'-' + formatRupiah(pointDiscountAmount)"></span>
                    </div>
                    <div class="flex justify-between items-end pt-2 mt-0.5 border-t border-white/10">
                        <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400" x-text="isMitraPoCheckout ? 'Total PO' : 'Total bayar'"></span>
                        <span class="text-lg font-black text-emerald-400 leading-none" x-text="formatRupiah(grandTotal)"></span>
                    </div>
                    <div x-show="paymentMethod === 'Tunai' && cashReceived > 0 && !isMitraPoCheckout" class="flex justify-between text-[10px] text-slate-400 pt-1" x-cloak>
                        <span>Kembalian</span>
                        <span class="font-bold text-emerald-400" x-text="formatRupiah(changeAmount)"></span>
                    </div>
                </div>

                <button type="button" x-show="!isMitraPoCheckout" @click="await submitCheckout()"
                        class="pos-pay-cta is-sale"
                        :disabled="cart.length === 0 || (paymentMethod === 'Tunai' && parseFloat(cashReceived || 0) < grandTotal) || isSaving">
                    <span x-text="isSaving ? 'Menyimpan...' : 'Simpan Transaksi'"></span>
                </button>
                <button type="button" x-show="isMitraPoCheckout" @click="await submitCheckout()"
                        class="pos-pay-cta is-po"
                        :disabled="!canSubmitPartnerOrder || isSaving" x-cloak>
                    <span x-text="isSaving ? 'Membuat PO...' : 'Buat PO Mitra'"></span>
                </button>
            </div>
        </div>
    </div>

    {{-- ═══════ FLOATING CART BUTTON (Mobile only) ═══════ --}}
    <div class="fixed bottom-20 right-4 z-45 lg:hidden">
        <button
            @click="isCartModalOpen = true"
            class="group relative overflow-hidden bg-gradient-to-r from-emerald-500 to-emerald-600 hover:from-emerald-600 hover:to-emerald-700 text-white rounded-2xl shadow-lg hover:shadow-emerald-500/30 hover:shadow-xl transition-all active:scale-95 cursor-pointer"
            :class="cart.length > 0 ? 'px-5 py-3.5' : 'px-4 py-3'"
        >
            <div class="flex items-center gap-2.5 relative z-10">
                {{-- Cart icon --}}
                <div class="relative">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                    {{-- Badge --}}
                    <span
                        x-show="cart.length > 0"
                        class="absolute -top-2 -right-2 w-4 h-4 bg-white text-emerald-700 text-[9px] font-black rounded-full flex items-center justify-center shadow"
                        x-text="cart.length"
                        x-cloak
                    ></span>
                </div>
                <div class="flex flex-col leading-tight" x-show="cart.length > 0" x-cloak>
                    <span class="text-[10px] font-bold opacity-80">Keranjang</span>
                    <span class="text-sm font-black" x-text="formatRupiah(grandTotal)"></span>
                </div>
                <span class="text-sm font-bold" x-show="cart.length === 0">Keranjang Kosong</span>
            </div>
        </button>
    </div>

    {{-- ═══════ MODAL KERANJANG ═══════ --}}
    <div
        x-show="isCartModalOpen"
        class="fixed inset-0 z-50 flex items-end sm:items-center justify-center bg-slate-900/70 backdrop-blur-sm"
        x-transition:enter="transition ease-out duration-250"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        x-cloak
    >
        <div
            class="w-full sm:w-[96vw] sm:max-w-6xl h-[96vh] sm:h-[92vh] bg-slate-50 sm:rounded-2xl rounded-t-3xl shadow-2xl overflow-hidden flex flex-col border border-white/10"
            @click.away="isCartModalOpen = false"
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0 translate-y-8 scale-[0.97]"
            x-transition:enter-end="opacity-100 translate-y-0 scale-100"
            x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100 translate-y-0 scale-100"
            x-transition:leave-end="opacity-0 translate-y-8 scale-[0.97]"
        >
            {{-- ─── MODAL HEADER ─── --}}
            <div class="px-5 py-3.5 bg-white border-b border-slate-100 flex flex-col gap-3 shrink-0 shadow-[0_1px_0_rgba(0,0,0,0.04)]">
                <div class="flex justify-between items-center">
                    <div class="flex items-center gap-3">
                        <div x-show="checkoutStep === 1" class="flex items-center gap-3" x-cloak>
                            <div class="w-9 h-9 rounded-xl bg-gradient-to-br from-emerald-500 to-emerald-600 text-white flex items-center justify-center shadow-md shadow-emerald-500/25">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
                                </svg>
                            </div>
                            <div>
                                <h3 class="font-bold text-slate-900 text-base leading-tight">Keranjang Transaksi</h3>
                                <p class="text-[11px] text-slate-400 mt-0.5" x-text="cart.length + ' item'"></p>
                            </div>
                        </div>
                        <div x-show="checkoutStep === 2" class="flex items-center gap-2.5" x-cloak>
                            <button @click="checkoutStep = 1" class="w-8 h-8 rounded-lg bg-slate-50 hover:bg-slate-100 text-slate-600 flex items-center justify-center cursor-pointer border border-slate-200/50 transition-colors">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                            </button>
                            <div>
                                <h3 class="font-bold text-slate-900 text-base leading-tight">Metode Pembayaran</h3>
                                <p class="text-[11px] text-slate-400 mt-0.5" x-text="'Bayar ' + cart.length + ' item'"></p>
                            </div>
                        </div>
                    </div>

                    <div class="flex items-center gap-2">
                        <button
                            x-show="checkoutStep === 1"
                            @click="cart = []; success('Keranjang dikosongkan')"
                            class="text-[11px] text-red-500 font-bold hover:bg-red-50 px-3 py-1.5 rounded-lg cursor-pointer transition-colors flex items-center gap-1.5 border border-transparent hover:border-red-100 disabled:opacity-30"
                            :disabled="cart.length === 0"
                            x-cloak
                        >
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                            Kosongkan
                        </button>
                        <button @click="isCartModalOpen = false" class="w-8 h-8 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-500 hover:text-slate-800 flex items-center justify-center cursor-pointer transition-all">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>
                </div>

                {{-- Stepper Progress --}}
                <div class="flex flex-col gap-1.5">
                    <div class="flex items-center justify-between text-[10px] font-extrabold tracking-wider uppercase">
                        <span class="cursor-pointer transition-colors" @click="if(cart.length > 0) checkoutStep = 1" :class="checkoutStep === 1 ? 'text-emerald-600' : 'text-slate-400 hover:text-slate-600'">1. Keranjang</span>
                        <svg class="w-2.5 h-2.5 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/></svg>
                        <span class="cursor-pointer transition-colors" @click="if(cart.length > 0) checkoutStep = 2" :class="checkoutStep === 2 ? 'text-emerald-600' : 'text-slate-400 hover:text-slate-600'">2. Pembayaran</span>
                    </div>
                    <div class="w-full bg-slate-100 h-1.5 rounded-full overflow-hidden relative">
                        <div class="bg-gradient-to-r from-emerald-500 to-emerald-600 h-full transition-all duration-300 rounded-full" :style="checkoutStep === 1 ? 'width: 50%' : 'width: 100%'"></div>
                    </div>
                </div>
            </div>

            {{-- ─── MODAL BODY ─── --}}
            <div class="flex-1 min-h-0 overflow-hidden flex flex-col">

                {{-- Empty cart state --}}
                <div x-show="cart.length === 0" class="flex-1 flex flex-col items-center justify-center text-center p-8 bg-slate-50/50" x-cloak>
                    <div class="w-20 h-20 bg-emerald-50 text-emerald-300 rounded-3xl flex items-center justify-center mb-5 border border-emerald-100">
                        <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.25" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
                    </div>
                    <h4 class="text-base font-bold text-slate-700">Keranjang masih kosong</h4>
                    <p class="text-xs text-slate-400 mt-1.5 max-w-xs">Pilih produk dari katalog untuk memulai transaksi.</p>
                    <button @click="isCartModalOpen = false" class="mt-6 px-6 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-sm rounded-xl shadow-md shadow-emerald-500/20 transition-all cursor-pointer">
                        Pilih Produk
                    </button>
                </div>

                {{-- Split Content --}}
                <div x-show="cart.length > 0" class="flex-1 flex flex-col overflow-hidden" x-cloak>

                    {{-- ───── STEP 1: Cart Items ───── --}}
                    <div x-show="checkoutStep === 1" class="flex-1 min-w-0 flex flex-col overflow-hidden p-4 lg:p-5" x-cloak>
                        <div class="flex items-center justify-between mb-3 shrink-0">
                            <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Item Belanjaan</span>
                            <span class="text-[11px] font-bold text-emerald-700 bg-emerald-50 border border-emerald-100 px-2.5 py-0.5 rounded-full" x-text="cart.length + ' produk'"></span>
                        </div>

                        {{-- Scrollable item list --}}
                        <div class="flex-1 overflow-y-auto flex flex-col gap-3 pr-1" style="scrollbar-width: thin;">
                            <template x-for="(item, index) in cart" :key="item.product_id">
                                <div class="bg-white rounded-xl border border-slate-200/80 p-4 shadow-[0_1px_4px_rgba(0,0,0,0.04)] flex flex-col gap-3 shrink-0">

                                    {{-- Row 1: Name & Remove --}}
                                    <div class="flex items-start justify-between gap-3">
                                        <div class="flex items-start gap-3 min-w-0 flex-1">
                                            <div class="w-9 h-9 bg-emerald-50 border border-emerald-100 rounded-lg flex items-center justify-center shrink-0">
                                                <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                                            </div>
                                            <div class="min-w-0">
                                                <p class="font-bold text-slate-900 text-sm leading-tight truncate" x-text="item.name"></p>
                                                <div class="flex items-center gap-2 mt-1">
                                                    <span
                                                        class="text-[10px] font-semibold px-2 py-0.5 rounded-full border"
                                                        :class="item.stock <= 0
                                                            ? 'bg-red-50 text-red-650 border-red-100'
                                                            : (item.stock <= item.stock_min
                                                                ? 'bg-amber-50 text-amber-650 border-amber-200'
                                                                : 'bg-slate-50 text-slate-500 border-slate-200')"
                                                        x-text="item.stock <= 0
                                                            ? 'Stok Habis'
                                                            : (item.stock <= item.stock_min
                                                                ? 'Stok ' + item.stock + ' ' + (item.unit_name || '') + ' (Menipis)'
                                                                : 'Stok ' + item.stock + ' ' + (item.unit_name || ''))"
                                                    ></span>
                                                    <span class="text-[10px] text-slate-400" x-text="item.code"></span>
                                                </div>
                                            </div>
                                        </div>
                                        <button @click="removeFromCart(index)" class="shrink-0 w-7 h-7 rounded-lg text-slate-300 hover:text-red-500 hover:bg-red-50 flex items-center justify-center cursor-pointer transition-all">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/></svg>
                                        </button>
                                    </div>

                                    {{-- Row 2: Controls --}}
                                    <div class="flex flex-wrap items-center gap-2.5 pt-2.5 border-t border-slate-100">

                                        {{-- Price Type Toggle --}}
                                        <div class="flex bg-slate-100 p-0.5 rounded-lg h-8 border border-slate-200/50 shrink-0">
                                            <button @click="changePriceType(item, 'eceran')"
                                                class="px-3 text-[11px] font-bold transition-all cursor-pointer rounded-md"
                                                :class="item.price_type === 'eceran' ? 'bg-white text-emerald-700 shadow-sm ring-1 ring-black/5' : 'text-slate-400 hover:text-slate-700'">
                                                Eceran
                                            </button>
                                            <button @click="changePriceType(item, 'grosir')"
                                                class="px-3 text-[11px] font-bold transition-all cursor-pointer rounded-md"
                                                :class="item.price_type === 'grosir' ? 'bg-white text-emerald-700 shadow-sm ring-1 ring-black/5' : 'text-slate-400 hover:text-slate-700'">
                                                Grosir
                                            </button>
                                        </div>

                                        {{-- Quantity --}}
                                        <div class="flex items-center bg-white border border-slate-200 rounded-lg h-8 overflow-hidden shadow-xs shrink-0">
                                            <button @click="if(item.quantity > 1) { item.quantity--; updateDiscountTiers(item); }"
                                                class="w-8 h-full flex items-center justify-center font-bold text-slate-500 hover:bg-slate-50 cursor-pointer text-sm">−</button>
                                            <input type="number"
                                                class="w-12 h-full text-center font-black text-sm border-none p-0 focus:ring-0 bg-transparent text-slate-800"
                                                x-model.number="item.quantity"
                                                @input="if(item.quantity < 1) item.quantity = 1; if(item.quantity > item.stock) item.quantity = item.stock; updateDiscountTiers(item);">
                                            <button @click="if(item.quantity < item.stock) { item.quantity++; updateDiscountTiers(item); } else { error('Stok terbatas!'); }"
                                                class="w-8 h-full flex items-center justify-center font-bold text-slate-500 hover:bg-slate-50 cursor-pointer text-sm">+</button>
                                        </div>

                                        {{-- Discount: Native Select Dropdown --}}
                                        <div class="relative shrink-0 flex items-center">
                                            <select
                                                x-model.number="item.discount_percent"
                                                @change="calculateRowSubtotal(item)"
                                                class="appearance-none flex items-center h-8 pl-3 pr-8 rounded-lg text-[11px] font-bold cursor-pointer transition-all border focus:outline-none focus:ring-2 focus:ring-orange-100"
                                                :class="parseFloat(item.discount_percent) > 0
                                                    ? 'bg-orange-50 text-orange-600 border-orange-200 hover:bg-orange-100 focus:border-orange-400'
                                                    : 'bg-slate-50 text-slate-500 border-slate-200 hover:bg-slate-100 hover:text-slate-700 focus:border-slate-300'"
                                                style="background-image: url(&quot;data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%236b7280' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='M6 8l4 4 4-4'/%3e%3c/svg%3e&quot;); background-position: right 0.5rem center; background-repeat: no-repeat; background-size: 1.25em 1.25em; padding-right: 2rem;"
                                            >
                                                <option value="0">Tanpa Diskon</option>
                                                <template x-for="tier in (item.discountTiers || []).filter(t => t > 0)" :key="tier">
                                                    <option :value="tier" x-text="tier + '% diskon'"></option>
                                                </template>
                                            </select>
                                        </div>

                                        {{-- Subtotal --}}
                                        <div class="ml-auto flex flex-col items-end">
                                            <span class="text-[10px] text-slate-400 line-through" x-show="item.discount_percent > 0" x-text="formatRupiah((item.price_type === 'grosir' ? item.wholesale_price : item.sell_price) * item.quantity)"></span>
                                            <span class="font-black text-emerald-600 text-base tracking-tight" x-text="formatRupiah(item.subtotal)"></span>
                                        </div>
                                    </div>
                                </div>
                            </template>
                        </div>

                        {{-- Footer for Modal Step 1 --}}
                        <div class="p-4 bg-slate-50 border-t border-slate-200 shrink-0 flex flex-col gap-3 mt-3 rounded-2xl">
                            <div class="flex justify-between items-center bg-slate-900 text-white shadow p-3.5 rounded-xl">
                                <span class="text-[10px] text-slate-400 font-bold uppercase tracking-wider">Subtotal</span>
                                <span class="text-sm font-black text-emerald-400" x-text="formatRupiah(subtotal)"></span>
                            </div>
                            <button
                                @click="checkoutStep = 2"
                                class="w-full py-3.5 rounded-xl text-xs font-black text-white bg-gradient-to-r from-emerald-500 to-emerald-600 hover:from-emerald-600 hover:to-emerald-700 shadow-md shadow-emerald-500/20 active:scale-[0.98] transition-all flex items-center justify-center gap-2 cursor-pointer"
                            >
                                <span>Lanjut ke Pembayaran ➜</span>
                            </button>
                        </div>
                    </div>

                    {{-- ───── STEP 2: Payment Panel ───── --}}
                    <div x-show="checkoutStep === 2" class="flex-1 flex flex-col bg-white overflow-hidden p-4 lg:p-5" x-cloak>
                        <div class="overflow-y-auto flex-1 p-2 flex flex-col gap-4" style="scrollbar-width: thin;">

                            {{-- Section: Pembeli Umum / CRM / Mitra --}}
                            <div class="flex flex-col gap-1.5">
                                <label class="text-[11px] font-bold text-slate-500 uppercase tracking-wider flex items-center gap-1.5">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                    Pembeli
                                </label>
                                <div class="flex bg-slate-100 p-0.5 rounded-xl h-9 border border-slate-200/60">
                                    <button type="button" @click="setBuyerType('umum')" class="flex-1 text-[11px] font-bold rounded-lg cursor-pointer" :class="buyerType === 'umum' ? 'bg-white text-slate-800 shadow-sm' : 'text-slate-500'">Umum</button>
                                    <button type="button" @click="setBuyerType('crm')" class="flex-1 text-[11px] font-bold rounded-lg cursor-pointer" :class="buyerType === 'crm' ? 'bg-white text-emerald-700 shadow-sm' : 'text-slate-500'">CRM</button>
                                    <button type="button" @click="setBuyerType('mitra')" class="flex-1 text-[11px] font-bold rounded-lg cursor-pointer" :class="buyerType === 'mitra' ? 'bg-white text-cyan-700 shadow-sm' : 'text-slate-500'">Mitra</button>
                                </div>

                                <div x-show="buyerType === 'umum'" class="px-3.5 py-2.5 text-sm text-slate-500 bg-slate-50 border border-slate-200 rounded-xl" x-cloak>
                                    Transaksi walk-in · Pelanggan Umum
                                </div>

                                <div x-show="buyerType !== 'umum' && !customerId && !partnerId" class="rounded-2xl border border-slate-200 bg-white overflow-hidden shadow-xs" x-cloak>
                                    <div class="px-3 py-2.5 border-b border-slate-100 bg-slate-50/80">
                                        <input type="text"
                                               class="w-full px-3 py-2 text-sm font-semibold bg-white border border-slate-200 rounded-xl focus:outline-none focus:border-emerald-400 focus:ring-2 focus:ring-emerald-100"
                                               :placeholder="buyerType === 'mitra' ? 'Filter mitra (opsional)...' : 'Filter pelanggan (opsional)...'"
                                               x-model="customerSearchQuery"
                                               @input.debounce.250ms="loadBuyers()">
                                    </div>
                                    <div class="max-h-64 overflow-y-auto divide-y divide-slate-50" style="scrollbar-width: thin;">
                                        <div x-show="isLoadingBuyers" class="px-4 py-8 text-center text-sm text-slate-400" x-cloak>Memuat daftar...</div>
                                        <div x-show="!isLoadingBuyers && customerSearchResults.length === 0" class="px-4 py-8 text-center text-sm text-slate-400" x-cloak>
                                            <span x-text="buyerType === 'mitra' ? 'Belum ada mitra aktif.' : 'Belum ada pelanggan CRM.'"></span>
                                        </div>
                                        <template x-for="c in customerSearchResults" :key="'m-' + buyerType + c.id">
                                            <button type="button"
                                                    class="w-full px-3.5 py-3 flex items-center gap-3 text-left hover:bg-emerald-50 transition-colors cursor-pointer"
                                                    @click="buyerType === 'mitra' ? selectPartner(c) : selectCustomer(c)">
                                                <div class="w-10 h-10 rounded-full flex items-center justify-center text-sm font-black text-white shrink-0"
                                                     :class="buyerType === 'mitra' ? 'bg-gradient-to-br from-cyan-500 to-teal-600' : 'bg-gradient-to-br from-emerald-500 to-emerald-600'"
                                                     x-text="(c.name || '?').charAt(0).toUpperCase()"></div>
                                                <div class="min-w-0 flex-1">
                                                    <p class="text-sm font-bold text-slate-800 truncate" x-text="c.name"></p>
                                                    <p class="text-[11px] text-slate-400 truncate"
                                                       x-text="buyerType === 'mitra' ? ((c.code || '-') + (c.type_label ? ' · ' + c.type_label : '')) : (c.phone || 'Tanpa no. HP')"></p>
                                                </div>
                                                <span x-show="buyerType === 'crm'" class="text-xs font-bold text-emerald-600 shrink-0" x-text="(c.points || 0) + ' Pts'"></span>
                                                <span x-show="c.has_overdue_invoice" class="text-[9px] font-extrabold text-red-600 bg-red-50 px-1.5 py-0.5 rounded shrink-0">OD</span>
                                            </button>
                                        </template>
                                    </div>
                                    <div class="px-3 py-2.5 border-t border-slate-100 bg-slate-50/80 space-y-2">
                                        <button type="button" @click="showQuickCreate = !showQuickCreate"
                                                class="w-full text-xs font-bold text-emerald-700 hover:text-emerald-800 py-1 cursor-pointer">
                                            <span x-text="showQuickCreate ? 'Tutup form tambah' : (buyerType === 'mitra' ? '+ Tambah mitra baru' : '+ Tambah pelanggan baru')"></span>
                                        </button>
                                        <div x-show="showQuickCreate" class="space-y-2" x-cloak>
                                            <input type="text" x-model="quickName" placeholder="Nama"
                                                   class="w-full px-3 py-2 text-sm font-semibold bg-white border border-slate-200 rounded-xl focus:outline-none focus:border-emerald-400">
                                            <input type="text" x-model="quickPhone" placeholder="No. HP"
                                                   class="w-full px-3 py-2 text-sm font-semibold bg-white border border-slate-200 rounded-xl focus:outline-none focus:border-emerald-400">
                                            <select x-show="buyerType === 'mitra'" x-model="quickType" class="w-full px-3 py-2 text-sm font-semibold bg-white border border-slate-200 rounded-xl" x-cloak>
                                                <template x-for="(label, key) in partnerTypes" :key="'m-type-' + key">
                                                    <option :value="key" x-text="label"></option>
                                                </template>
                                            </select>
                                            <button type="button" @click="await quickCreateBuyer()" :disabled="quickCreateSaving"
                                                    class="w-full py-2 rounded-xl text-xs font-black text-white bg-emerald-600 hover:bg-emerald-700 disabled:opacity-50 cursor-pointer">
                                                <span x-text="quickCreateSaving ? 'Menyimpan...' : 'Simpan & pilih'"></span>
                                            </button>
                                        </div>
                                    </div>
                                </div>

                                <div x-show="buyerType === 'crm' && customerId" class="flex items-center justify-between w-full px-3.5 py-2.5 text-sm font-semibold text-emerald-800 bg-emerald-50 border border-emerald-200 rounded-xl shadow-xs" x-cloak>
                                    <div class="flex items-center gap-2 min-w-0">
                                        <span class="truncate" x-text="customerName"></span>
                                        <span class="text-[10px] font-bold text-emerald-600 shrink-0" x-text="customerPoints + ' Pts'"></span>
                                    </div>
                                    <button type="button" @click="reopenBuyerPicker()" class="text-[11px] font-bold text-emerald-700 hover:text-emerald-900 px-2 py-1 cursor-pointer">Ganti</button>
                                </div>

                                <div x-show="buyerType === 'mitra' && partnerId" class="pos-pay-card is-mitra !p-3 space-y-3" x-cloak>
                                    <div class="flex items-center justify-between gap-2">
                                        <div class="flex items-center gap-2.5 min-w-0">
                                            <div class="w-10 h-10 rounded-full bg-gradient-to-br from-cyan-500 to-teal-600 text-white text-sm font-black flex items-center justify-center shrink-0" x-text="(customerName || '?').charAt(0).toUpperCase()"></div>
                                            <div class="min-w-0">
                                                <p class="text-sm font-bold text-slate-800 truncate" x-text="customerName"></p>
                                                <p class="text-[11px] text-slate-500" x-text="(partnerCode || '') + ' · ' + (partnerTypeLabel || '')"></p>
                                            </div>
                                        </div>
                                        <button type="button" @click="reopenBuyerPicker()" class="text-[11px] font-bold text-cyan-700 bg-white px-2.5 py-1 rounded-lg border border-cyan-100 cursor-pointer shrink-0">Ganti</button>
                                    </div>

                                    <div>
                                        <span class="pos-pay-label !mb-2">Jenis transaksi</span>
                                        <div class="pos-pay-seg mb-2.5">
                                            <button type="button" @click="setMitraCheckoutMode('sale')" :class="!isMitraPoCheckout ? 'is-on' : ''">Ambil sekarang</button>
                                            <button type="button" @click="setMitraCheckoutMode('po')" :class="isMitraPoCheckout ? 'is-on is-mitra' : ''">Buat PO</button>
                                        </div>
                                        <div x-show="!isMitraPoCheckout" class="pos-pay-methods" x-cloak>
                                            <button type="button" class="pos-pay-method is-tunai" :class="paymentMethod === 'Tunai' ? 'is-on' : ''" @click="selectMitraPay('Tunai')">Tunai<span class="hint">Stok langsung</span></button>
                                            <button type="button" class="pos-pay-method is-qris" :class="paymentMethod === 'QRIS' ? 'is-on' : ''" @click="selectMitraPay('QRIS')">QRIS<span class="hint">Stok langsung</span></button>
                                        </div>
                                        <div x-show="isMitraPoCheckout" class="pos-pay-methods" x-cloak>
                                            <button type="button" x-show="partnerAllowTransfer" class="pos-pay-method is-transfer" :class="poPaymentMethod === 'transfer' ? 'is-on' : ''" @click="selectMitraPay('transfer')">Transfer</button>
                                            <button type="button" x-show="partnerAllowCod" class="pos-pay-method is-cod" :class="poPaymentMethod === 'cod' ? 'is-on' : ''" @click="selectMitraPay('cod')">COD</button>
                                            <button type="button" x-show="partnerInvoiceEnabled && !selectedCustomerOverdue" class="pos-pay-method is-invoice" :class="poPaymentMethod === 'invoice' ? 'is-on' : ''" @click="selectMitraPay('invoice')">Invoice</button>
                                        </div>
                                    </div>

                                    <div x-show="isMitraPoCheckout" class="rounded-xl bg-slate-50 border border-slate-100 p-3 space-y-2" x-cloak>
                                        <p class="text-[11px] font-extrabold text-cyan-700 uppercase tracking-wider">Pengiriman</p>
                                        <input type="text" x-model="poPicName" placeholder="Nama PIC" class="pos-pay-field">
                                        <input type="text" x-model="poPicPhone" placeholder="Telepon PIC" class="pos-pay-field">
                                        <textarea x-model="poShippingAddress" rows="2" placeholder="Alamat pengiriman" class="pos-pay-field resize-none"></textarea>
                                        <input type="text" x-model="poNotes" placeholder="Catatan (opsional)" class="pos-pay-field">
                                    </div>
                                </div>
                            </div>

                            {{-- Section: Diskon Global --}}
                            <div class="flex flex-col gap-1.5">
                                <label class="text-[11px] font-bold text-slate-500 uppercase tracking-wider flex items-center gap-1.5">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/></svg>
                                    Diskon Transaksi (%)
                                </label>
                                <div class="relative">
                                    <input type="number"
                                        class="w-full pl-3.5 pr-10 py-2.5 text-sm font-bold bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:border-orange-400 focus:ring-2 focus:ring-orange-100 focus:bg-white transition-all shadow-xs"
                                        :class="discountPercent > 0 ? 'text-orange-600 border-orange-200 bg-orange-50' : 'text-slate-800'"
                                        min="0" max="100" placeholder="0"
                                        x-model.number="discountPercent">
                                    <span class="absolute right-3 top-1/2 -translate-y-1/2 text-xs font-bold text-slate-400">%</span>
                                </div>
                                <div x-show="discountPercent > 0" class="flex items-center justify-between text-[11px] bg-orange-50 border border-orange-100 rounded-lg px-3 py-1.5" x-cloak>
                                    <span class="text-orange-600 font-medium">Hemat</span>
                                    <span class="text-orange-700 font-black" x-text="formatRupiah(globalDiscountAmount)"></span>
                                </div>
                            </div>

                            {{-- Section: PPN --}}
                            <div class="flex flex-col gap-2 p-3.5 bg-slate-50 border border-slate-200 rounded-xl shadow-xs">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center gap-2">
                                        <svg class="w-3.5 h-3.5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5"/></svg>
                                        <span class="text-xs font-bold text-slate-700">PPN <span x-text="ppnPercent + '%'"></span></span>
                                    </div>
                                    <label class="relative inline-flex items-center cursor-pointer">
                                        <input type="checkbox" x-model="ppnActive" class="sr-only peer">
                                        <div class="w-9 h-5 bg-slate-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-emerald-500"></div>
                                    </label>
                                </div>
                                {{-- PPN Bearer --}}
                                <div x-show="ppnActive" x-transition class="flex bg-slate-200 p-0.5 rounded-lg h-8">
                                    <button @click="ppnBearer = 'Ditanggung Pembeli'"
                                        class="flex-1 text-[11px] font-bold transition-all cursor-pointer rounded-md"
                                        :class="ppnBearer === 'Ditanggung Pembeli' ? 'bg-white text-emerald-700 shadow-sm' : 'text-slate-500 hover:text-slate-700'">
                                        Ditanggung Pembeli
                                    </button>
                                    <button @click="ppnBearer = 'Ditanggung Penjual'"
                                        class="flex-1 text-[11px] font-bold transition-all cursor-pointer rounded-md"
                                        :class="ppnBearer === 'Ditanggung Penjual' ? 'bg-white text-emerald-700 shadow-sm' : 'text-slate-500 hover:text-slate-700'">
                                        Ditanggung Penjual
                                    </button>
                                </div>
                            </div>

                             {{-- Section: Metode Pembayaran Umum / CRM --}}
                             <div x-show="buyerType !== 'mitra' || !partnerId" class="flex flex-col gap-1.5" x-cloak>
                                 <label class="text-[11px] font-bold text-slate-500 uppercase tracking-wider flex items-center gap-1.5">
                                     <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                                     Metode Pembayaran
                                 </label>
                                 <div class="grid grid-cols-2 gap-2">
                                     <button @click="paymentMethod = 'Tunai'"
                                         class="flex items-center justify-center gap-2 py-2.5 rounded-xl text-sm font-bold border transition-all cursor-pointer"
                                         :class="paymentMethod === 'Tunai'
                                             ? 'bg-emerald-600 text-white border-emerald-700 shadow-md shadow-emerald-500/20'
                                             : 'bg-white text-slate-500 border-slate-200 hover:border-emerald-300 hover:text-emerald-700'">
                                         Tunai
                                     </button>
                                     <button @click="paymentMethod = 'Transfer'"
                                         class="flex items-center justify-center gap-2 py-2.5 rounded-xl text-sm font-bold border transition-all cursor-pointer"
                                         :class="paymentMethod === 'Transfer'
                                             ? 'bg-blue-600 text-white border-blue-700 shadow-md shadow-blue-500/20'
                                             : 'bg-white text-slate-500 border-slate-200 hover:border-blue-300 hover:text-blue-700'">
                                         Transfer
                                     </button>
                                     <button @click="paymentMethod = 'QRIS'"
                                         class="flex items-center justify-center gap-2 py-2.5 rounded-xl text-sm font-bold border transition-all cursor-pointer"
                                         :class="paymentMethod === 'QRIS'
                                             ? 'bg-violet-600 text-white border-violet-700 shadow-md shadow-violet-500/20'
                                             : 'bg-white text-slate-500 border-slate-200 hover:border-violet-300 hover:text-violet-700'">
                                         QRIS
                                     </button>
                                     <button
                                         @click="if(canUseInvoice) paymentMethod = 'Invoice'"
                                         class="flex items-center justify-center gap-2 py-2.5 rounded-xl text-sm font-bold border transition-all cursor-pointer disabled:opacity-40"
                                         :disabled="!canUseInvoice"
                                         :title="invoiceDisabledReason"
                                         :class="paymentMethod === 'Invoice'
                                             ? 'bg-orange-600 text-white border-orange-700 shadow-md shadow-orange-500/20'
                                             : 'bg-white text-slate-500 border-slate-200 hover:border-orange-300 hover:text-orange-700'">
                                         Invoice
                                     </button>
                                 </div>
                                 <div x-show="selectedCustomerOverdue" class="mt-1 p-2 bg-red-50 border border-red-150 rounded-xl text-[10px] text-red-650 font-bold" x-cloak>
                                     ⚠️ Ada tagihan jatuh tempo belum lunas! Metode Invoice diblokir.
                                 </div>
                             </div>

                            {{-- Section: Uang Tunai --}}
                            <div x-show="paymentMethod === 'Tunai' && !isMitraPoCheckout" x-transition class="flex flex-col gap-2.5">
                                <label class="text-[11px] font-bold text-slate-500 uppercase tracking-wider flex items-center gap-1.5">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    Uang Diterima
                                </label>
                                <div class="flex items-stretch rounded-xl overflow-hidden border bg-white shadow-xs transition-colors" :class="cashReceived && parseFloat(cashReceived || 0) >= grandTotal ? 'border-emerald-400' : 'border-slate-200 focus-within:border-emerald-400'">
                                    <div class="flex items-center px-3.5 bg-emerald-600 text-white text-xs font-black tracking-widest shrink-0">Rp</div>
                                    <input type="number"
                                        class="flex-1 py-2.5 px-3 font-black text-slate-900 text-lg border-none focus:outline-none focus:ring-0 bg-transparent"
                                        placeholder="0"
                                        x-model.number="cashReceived"
                                        style="border: none;">
                                </div>
                                {{-- Warning --}}
                                <div x-show="cashReceived !== null && parseFloat(cashReceived || 0) < grandTotal && cashReceived !== ''" class="flex items-center gap-1.5 text-[11px] text-red-500 font-semibold" x-cloak>
                                    <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                                    Uang kurang dari total bayar
                                </div>
                                {{-- Shortcuts --}}
                                <div class="grid grid-cols-5 gap-1.5">
                                    <button @click="setExactCash()" class="py-2 text-[11px] font-bold bg-emerald-50 border border-emerald-200 hover:bg-emerald-100 text-emerald-700 rounded-lg cursor-pointer transition-all active:scale-95">Pas</button>
                                    <button @click="addCash(10000)" class="py-2 text-[11px] font-bold bg-white border border-slate-200 hover:bg-slate-50 text-slate-600 rounded-lg cursor-pointer transition-all active:scale-95">+10k</button>
                                    <button @click="addCash(20000)" class="py-2 text-[11px] font-bold bg-white border border-slate-200 hover:bg-slate-50 text-slate-600 rounded-lg cursor-pointer transition-all active:scale-95">+20k</button>
                                    <button @click="addCash(50000)" class="py-2 text-[11px] font-bold bg-white border border-slate-200 hover:bg-slate-50 text-slate-600 rounded-lg cursor-pointer transition-all active:scale-95">+50k</button>
                                    <button @click="addCash(100000)" class="py-2 text-[11px] font-bold bg-white border border-slate-200 hover:bg-slate-50 text-slate-600 rounded-lg cursor-pointer transition-all active:scale-95">+100k</button>
                                </div>
                            </div>

                            {{-- Section: QRIS --}}
                            <div x-show="paymentMethod === 'QRIS' && !isMitraPoCheckout" x-transition class="flex flex-col gap-2 p-3.5 bg-violet-50 border border-violet-100 rounded-xl text-center shadow-xs" x-cloak>
                                <div class="text-xs font-bold text-violet-700">Pembayaran QRIS</div>
                                <div class="text-lg font-black text-violet-850 my-0.5" x-text="formatRupiah(grandTotal)"></div>
                                <div class="text-[11px] text-slate-500">NMID: <span class="font-bold text-slate-700" x-text="qrisNmid"></span></div>
                                <button @click="showQrisModal = true" class="flex items-center justify-center gap-2 mt-1 w-full py-2 bg-white border border-violet-200 hover:bg-violet-50 text-violet-700 text-xs font-bold rounded-lg cursor-pointer transition-all">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                    Tampilkan QR Code
                                </button>
                            </div>

                            {{-- Section: Catatan --}}
                            <div class="flex flex-col gap-1.5">
                                <label class="text-[11px] font-bold text-slate-500 uppercase tracking-wider">Catatan</label>
                                <textarea rows="1" class="w-full px-3.5 py-2.5 text-sm bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:border-emerald-400 focus:ring-2 focus:ring-emerald-100 focus:bg-white resize-none transition-all font-medium text-slate-700 shadow-xs" placeholder="Catatan opsional..." x-model="notes"></textarea>
                            </div>
                        </div>

                        {{-- ─── FOOTER SUMMARY & SAVE BUTTON ─── --}}
                        <div class="p-4 border-t border-slate-200 bg-slate-50 shrink-0 flex flex-col gap-3 rounded-2xl mt-3">
                            {{-- Ringkasan Harga (Sticky) --}}
                            <div class="rounded-xl p-4 flex flex-col gap-3 shadow-md" style="background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%); border: 1px solid rgba(255,255,255,0.08); color: #fff;">
                                <div class="flex justify-between items-center text-xs">
                                    <span style="color: #94a3b8; font-weight: 500;">Subtotal</span>
                                    <span class="text-sm font-bold" style="color: #fff;" x-text="formatRupiah(subtotal)"></span>
                                </div>
                                <div x-show="globalDiscountAmount > 0" class="flex justify-between items-center text-xs" style="color: #fb923c;">
                                    <span class="flex items-center gap-1.5 font-medium">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/></svg>
                                        Diskon (<span x-text="discountPercent + '%'"></span>)
                                    </span>
                                    <span class="font-bold text-sm" x-text="'− ' + formatRupiah(globalDiscountAmount)"></span>
                                </div>
                                <div x-show="ppnActive" class="flex justify-between items-center text-xs" style="color: #93c5fd;">
                                    <span class="font-medium">PPN <span x-text="ppnPercent + '%'"></span> <span style="color: #60a5fa; font-size: 10px; font-weight: bold;" x-text="ppnBearer === 'Ditanggung Penjual' ? '(Penjual)' : '(Pembeli)'"></span></span>
                                    <span class="font-bold text-sm" style="color: #fff;" x-text="formatRupiah(ppnAmount)"></span>
                                </div>
                                <div style="border-top: 1px solid rgba(255,255,255,0.1); margin: 2px 0;"></div>
                                <div class="flex justify-between items-center">
                                    <span class="text-xs font-black uppercase tracking-wider" style="color: #94a3b8;" x-text="isMitraPoCheckout ? 'Total PO' : 'Total Bayar'"></span>
                                    <span class="text-2xl font-black leading-none" style="color: #34d399;" x-text="formatRupiah(grandTotal)"></span>
                                </div>
                                <div x-show="paymentMethod === 'Tunai' && cashReceived > 0 && !isMitraPoCheckout" class="flex justify-between items-center text-xs pt-2" style="border-top: 1px solid rgba(255,255,255,0.1); margin-top: 2px;">
                                    <span style="color: #94a3b8; font-weight: 500;">Kembalian</span>
                                    <span class="font-black text-sm" :style="changeAmount > 0 ? 'color: #34d399;' : 'color: #94a3b8;'" x-text="formatRupiah(changeAmount)"></span>
                                </div>
                            </div>

                            {{-- SAVE BUTTON --}}
                            <div class="p-0 shrink-0 space-y-2">
                                <button
                                    x-show="!isMitraPoCheckout"
                                    @click="await submitCheckout()"
                                    class="w-full py-3.5 rounded-2xl text-sm font-black flex items-center justify-center gap-2 cursor-pointer transition-all shadow-lg disabled:opacity-50 disabled:cursor-not-allowed"
                                    :class="cart.length === 0 || (paymentMethod === 'Tunai' && parseFloat(cashReceived || 0) < grandTotal) || isSaving
                                        ? 'bg-slate-200 text-slate-400 shadow-none'
                                        : 'bg-gradient-to-r from-emerald-500 to-emerald-600 hover:from-emerald-600 hover:to-emerald-700 text-white shadow-emerald-500/30 hover:shadow-emerald-500/40 hover:shadow-xl active:scale-[0.98]'"
                                    :disabled="cart.length === 0 || (paymentMethod === 'Tunai' && parseFloat(cashReceived || 0) < grandTotal) || isSaving"
                                >
                                    <svg x-show="!isSaving" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                    <svg x-show="isSaving" class="animate-spin h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" x-cloak>
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                    <span x-text="isSaving ? 'Menyimpan Transaksi...' : 'Simpan Transaksi'"></span>
                                </button>
                                <button
                                    x-show="isMitraPoCheckout"
                                    @click="await submitCheckout()"
                                    class="w-full py-3.5 rounded-2xl text-sm font-black flex items-center justify-center gap-2 cursor-pointer transition-all shadow-lg disabled:opacity-50 disabled:cursor-not-allowed"
                                    :class="!canSubmitPartnerOrder || isSaving
                                        ? 'bg-slate-200 text-slate-400 shadow-none'
                                        : 'bg-gradient-to-r from-cyan-500 to-teal-600 hover:from-cyan-600 hover:to-teal-700 text-white shadow-cyan-500/30 hover:shadow-xl active:scale-[0.98]'"
                                    :disabled="!canSubmitPartnerOrder || isSaving"
                                    x-cloak
                                >
                                    <span x-text="isSaving ? 'Membuat PO...' : 'Buat PO Mitra'"></span>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
        </div>
    </div>
</div>

    {{-- ═══════ MODAL: QRIS (3-State Flow) ═══════ --}}
    <div x-show="showQrisModal" class="modal-backdrop" x-cloak
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
    >
        <div
            class="modal-box max-w-sm p-6 text-center relative overflow-hidden"
            @click.away="if(qrisPaymentState === 'idle') { showQrisModal = false; }"
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0 scale-90 translate-y-4"
            x-transition:enter-end="opacity-100 scale-100 translate-y-0"
        >

            {{-- ─── STATE: IDLE — Tampilan QR Code ─── --}}
            <div x-show="qrisPaymentState === 'idle'" x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">
                <div class="flex items-center justify-between pb-3 border-b border-slate-100 mb-4">
                    <div class="flex items-center gap-2">
                        <div class="w-7 h-7 rounded-lg bg-violet-100 flex items-center justify-center">
                            <svg class="w-4 h-4 text-violet-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h.01M16 20h.01M21 12h.01M12 17h.01M17 12h.01M16 16h.01M5 8h.01M9 8h.01M5 12h.01M9 12h.01M5 16h.01M9 16h.01"/></svg>
                        </div>
                        <h3 class="font-bold text-slate-800 text-base">QRIS Apotek Almaira</h3>
                    </div>
                    <button @click="showQrisModal = false" class="text-slate-400 hover:text-slate-600 cursor-pointer p-1 rounded-lg hover:bg-slate-100 transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                {{-- QR Image --}}
                <div class="p-2 bg-white border-2 border-violet-100 rounded-2xl flex items-center justify-center min-h-[240px] shadow-inner">
                    <img
                        :src="'https://api.qrserver.com/v1/create-qr-code/?size=280x280&data=' + encodeURIComponent(generateDynamicQris(grandTotal))"
                        alt="QRIS"
                        class="max-w-full h-auto max-h-[280px] object-contain rounded-xl"
                    >
                </div>

                {{-- Amount Info --}}
                <div class="mt-3 bg-gradient-to-br from-violet-50 to-purple-50 p-3.5 rounded-xl border border-violet-100">
                    <p class="text-[11px] font-semibold text-violet-500 uppercase tracking-wider">Scan & Bayar Sebesar</p>
                    <div class="my-1 text-2xl font-black text-violet-700" x-text="formatRupiah(grandTotal)"></div>
                    <p class="text-[10px] text-violet-400">NMID: <span class="font-bold" x-text="qrisNmid"></span></p>
                </div>

                {{-- Confirm Button --}}
                <button
                    @click="startQrisWaiting()"
                    class="w-full mt-4 py-3.5 text-sm font-black text-white bg-gradient-to-r from-violet-600 to-purple-600 hover:from-violet-700 hover:to-purple-700 rounded-xl cursor-pointer transition-all shadow-md shadow-violet-500/30 active:scale-[0.98] flex items-center justify-center gap-2"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    Konfirmasi Pembayaran
                </button>
                <button @click="showQrisModal = false" class="w-full mt-2 py-2 text-xs font-semibold text-slate-400 hover:text-slate-600 border border-transparent rounded-lg cursor-pointer transition-all hover:bg-slate-50">Tutup</button>
            </div>

            {{-- ─── STATE: WAITING — Animasi Menunggu Pembayaran ─── --}}
            <div x-show="qrisPaymentState === 'waiting'"
                x-transition:enter="transition ease-out duration-300"
                x-transition:enter-start="opacity-0 translate-y-4"
                x-transition:enter-end="opacity-100 translate-y-0"
            >
                {{-- Animated Waiting Screen --}}
                <div class="py-6 flex flex-col items-center">

                    {{-- Pulsing QRIS ring animation --}}
                    <div class="relative flex items-center justify-center mb-6">
                        {{-- Outer pulse rings --}}
                        <div class="absolute w-28 h-28 rounded-full bg-violet-400/20 animate-ping" style="animation-duration: 2s;"></div>
                        <div class="absolute w-22 h-22 rounded-full bg-violet-400/30 animate-ping" style="animation-duration: 2.5s; width: 5.5rem; height: 5.5rem;"></div>
                        {{-- Main circle --}}
                        <div class="relative w-20 h-20 rounded-full bg-gradient-to-br from-violet-500 to-purple-600 flex items-center justify-center shadow-xl shadow-violet-500/40">
                            <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h.01M16 20h.01M21 12h.01M12 17h.01M17 12h.01M16 16h.01M5 8h.01M9 8h.01M5 12h.01M9 12h.01M5 16h.01M9 16h.01"/>
                            </svg>
                        </div>
                    </div>

                    <h3 class="text-lg font-black text-slate-800 mb-1">Menunggu Pembayaran</h3>
                    <p class="text-sm text-slate-400 mb-1">Pelanggan sedang memindai QR Code...</p>

                    {{-- Amount --}}
                    <div class="my-3 px-5 py-2.5 bg-violet-50 border border-violet-100 rounded-xl">
                        <p class="text-[10px] text-violet-400 font-semibold uppercase tracking-wider">Tagihan</p>
                        <p class="text-xl font-black text-violet-700" x-text="formatRupiah(grandTotal)"></p>
                    </div>

                    {{-- Animated dots --}}
                    <div class="flex items-center gap-1.5 my-2">
                        <div class="w-2 h-2 rounded-full bg-violet-400 animate-bounce" style="animation-delay: 0ms;"></div>
                        <div class="w-2 h-2 rounded-full bg-violet-400 animate-bounce" style="animation-delay: 200ms;"></div>
                        <div class="w-2 h-2 rounded-full bg-violet-400 animate-bounce" style="animation-delay: 400ms;"></div>
                    </div>
                    <p class="text-[10px] text-slate-400 font-medium mt-1">Sistem memverifikasi pembayaran secara otomatis...</p>

                    {{-- Countdown bar --}}
                    <div class="w-full mt-4 bg-slate-100 rounded-full h-1.5 overflow-hidden">
                        <div
                            class="h-full bg-gradient-to-r from-violet-500 to-purple-500 rounded-full transition-all duration-1000"
                            :style="'width: ' + Math.round((qrisCountdown / qrisMaxCountdown) * 100) + '%'"
                        ></div>
                    </div>
                    <p class="text-[10px] text-slate-400 mt-1.5">Timeout dalam <span class="font-bold text-slate-600" x-text="qrisCountdown"></span> detik</p>

                    {{-- Divider --}}
                    <div class="flex items-center gap-3 w-full my-4">
                        <div class="flex-1 h-px bg-slate-100"></div>
                        <span class="text-[10px] text-slate-400 font-medium">atau</span>
                        <div class="flex-1 h-px bg-slate-100"></div>
                    </div>

                    {{-- Manual confirm (Cashier confirms payment received physically) --}}
                    <button
                        @click="confirmQrisPayment()"
                        :disabled="isSaving"
                        class="w-full py-3 text-sm font-black text-white bg-gradient-to-r from-emerald-500 to-emerald-600 hover:from-emerald-600 hover:to-emerald-700 rounded-xl cursor-pointer transition-all shadow-md shadow-emerald-500/25 active:scale-[0.98] flex items-center justify-center gap-2 disabled:opacity-60"
                    >
                        <template x-if="!isSaving">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                            </svg>
                        </template>
                        <template x-if="isSaving">
                            <svg class="animate-spin h-4 w-4" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                        </template>
                        <span x-text="isSaving ? 'Memproses...' : 'Pembayaran Sudah Masuk ✓'"></span>
                    </button>

                    {{-- Cancel --}}
                    <button @click="cancelQrisWaiting()" class="w-full mt-2 py-2 text-xs font-semibold text-slate-400 hover:text-slate-600 rounded-lg cursor-pointer transition-all hover:bg-slate-50">← Kembali ke QR Code</button>
                </div>
            </div>

            {{-- ─── STATE: PAID — Mini Success sebelum tutup ─── --}}
            <div x-show="qrisPaymentState === 'paid'"
                x-transition:enter="transition ease-out duration-400"
                x-transition:enter-start="opacity-0 scale-90"
                x-transition:enter-end="opacity-100 scale-100"
            >
                <div class="py-8 flex flex-col items-center">
                    {{-- Success checkmark --}}
                    <div class="relative mb-5">
                        <div class="w-24 h-24 rounded-full bg-gradient-to-br from-emerald-400 to-emerald-600 flex items-center justify-center shadow-2xl shadow-emerald-500/40">
                            <svg class="w-12 h-12 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                            </svg>
                        </div>
                        <div class="absolute -inset-2 rounded-full bg-emerald-400/20 animate-ping" style="animation-duration: 1.5s;"></div>
                    </div>

                    <h3 class="text-xl font-black text-slate-900 mb-1">Pembayaran Diterima!</h3>
                    <p class="text-sm text-slate-400 mb-4">QRIS berhasil terverifikasi</p>

                    <div class="px-6 py-3 bg-emerald-50 border border-emerald-100 rounded-xl mb-2">
                        <p class="text-[10px] text-emerald-500 font-semibold uppercase tracking-wider">Total Dibayar</p>
                        <p class="text-xl font-black text-emerald-700" x-text="formatRupiah(grandTotal)"></p>
                    </div>

                    <p class="text-[11px] text-slate-400 animate-pulse">Menyimpan transaksi...</p>
                </div>
            </div>

        </div>
    </div>

    {{-- ═══════ MODAL: SUKSES TRANSAKSI ═══════ --}}
    <div x-show="showSuccessModal" class="modal-backdrop" x-cloak>
        <div
            class="modal-box max-w-md bg-white"
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0 scale-90 translate-y-4"
            x-transition:enter-end="opacity-100 scale-100 translate-y-0"
        >
            {{-- Green top bar --}}
            <div class="h-1.5 bg-gradient-to-r from-emerald-400 to-emerald-600 rounded-t-2xl -mt-px"></div>

            <div class="p-6 text-center">
                {{-- Animated check --}}
                <div class="w-16 h-16 rounded-full bg-emerald-50 border-4 border-emerald-100 flex items-center justify-center mx-auto mb-4">
                    <svg class="w-8 h-8 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                    </svg>
                </div>

                <h3 class="text-xl font-black text-slate-900">Transaksi Berhasil!</h3>
                <p class="text-sm text-slate-400 mt-1">Data tersimpan & stok telah diperbarui</p>

                {{-- Receipt summary --}}
                <div class="mt-5 bg-slate-50 border border-slate-200 rounded-2xl overflow-hidden text-left">
                    <div class="px-4 py-3 bg-slate-100 border-b border-slate-200">
                        <span class="text-[11px] font-black text-slate-500 uppercase tracking-wider">Ringkasan Transaksi</span>
                    </div>
                    <div class="px-4 py-3 flex flex-col gap-2.5 text-xs">
                        <div class="flex justify-between items-center">
                            <span class="text-slate-500 font-medium" x-text="createdSale?.document_label || 'NO FAKTUR PENJUALAN'"></span>
                            <span class="font-black text-slate-900 font-mono text-right" x-text="createdSale?.invoice_no || '-'"></span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-slate-500 font-medium">Pelanggan</span>
                            <span class="font-semibold text-slate-800" x-text="customerName"></span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-slate-500 font-medium">Metode Bayar</span>
                            <span class="font-semibold text-slate-800 flex items-center gap-1.5">
                                <span class="w-2 h-2 rounded-full" :class="paymentMethod === 'Tunai' ? 'bg-emerald-500' : 'bg-violet-500'"></span>
                                <span x-text="paymentMethod"></span>
                            </span>
                        </div>
                        <div class="border-t border-slate-200 pt-2.5 flex justify-between items-center">
                            <span class="text-slate-700 font-bold">Total Pembayaran</span>
                            <span class="text-emerald-600 font-black text-base" x-text="formatRupiah(createdSale?.total ?? grandTotal)"></span>
                        </div>
                        <div x-show="paymentMethod === 'Tunai'" class="flex justify-between items-center text-slate-500">
                            <span class="font-medium">Kembalian</span>
                            <span class="font-bold text-slate-700" x-text="formatRupiah(createdSale?.change_amount ?? changeAmount)"></span>
                        </div>
                    </div>
                </div>

                {{-- Action buttons --}}
                <div class="mt-5 flex flex-col gap-2.5">
                    <button
                        type="button"
                        @click="
                            const btn = $event.currentTarget;
                            btn.disabled = true;
                            const oldText = btn.innerHTML;
                            btn.innerHTML = 'Printing...';
                            fetch(`/sales/${createdSale?.sale_id}/print-thermal`, {
                                method: 'POST',
                                headers: {
                                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                    'Accept': 'application/json'
                                }
                            })
                            .then(res => res.json())
                            .then(data => {
                                btn.disabled = false;
                                btn.innerHTML = oldText;
                                if (data.success) {
                                    window.dispatchEvent(new CustomEvent('toast', { detail: { type: 'success', message: data.message } }));
                                } else {
                                    window.dispatchEvent(new CustomEvent('toast', { detail: { type: 'error', message: data.message } }));
                                }
                            })
                            .catch(err => {
                                btn.disabled = false;
                                btn.innerHTML = oldText;
                                window.dispatchEvent(new CustomEvent('toast', { detail: { type: 'error', message: 'Terjadi kesalahan koneksi printer!' } }));
                            });
                        "
                        class="flex items-center justify-center gap-2.5 w-full py-3.5 bg-gradient-to-r from-teal-500 to-teal-600 hover:from-teal-600 hover:to-teal-700 text-white font-bold text-sm rounded-xl shadow-lg shadow-teal-500/25 transition-all cursor-pointer active:scale-95"
                    >
                        🖨️ Cetak Termal (Fisik)
                    </button>
                    <a
                        :href="createdSale?.print_url"
                        target="_blank"
                        class="flex items-center justify-center gap-2.5 w-full py-3.5 bg-gradient-to-r from-emerald-500 to-emerald-600 hover:from-emerald-600 hover:to-emerald-700 text-white font-bold text-sm rounded-xl shadow-lg shadow-emerald-500/25 transition-all cursor-pointer active:scale-95"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                        </svg>
                        Cetak Struk (PDF)
                    </a>
                    <button
                        @click="resetPOS()"
                        class="flex items-center justify-center gap-2 w-full py-3 bg-slate-50 hover:bg-slate-100 text-slate-700 font-bold text-sm rounded-xl border border-slate-200 transition-all cursor-pointer"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h.01M16 20h.01M21 12h.01M12 17h.01M17 12h.01M16 16h.01M5 8h.01M9 8h.01M5 12h.01M9 12h.01M5 16h.01M9 16h.01"/>
                        </svg>
                        Transaksi Baru
                    </button>
                </div>
            </div>
        </div>
    </div>

</div>
@endsection


