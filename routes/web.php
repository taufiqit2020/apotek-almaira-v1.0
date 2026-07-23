<?php
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\PurchaseController;
use App\Http\Controllers\StockOutController;
use App\Http\Controllers\StockOpnameController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\PrescriptionController;
use App\Http\Controllers\AnalyticsController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\SaleController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\CatalogController;
use App\Http\Controllers\PartnerController;
use App\Http\Controllers\PartnerPortalController;
use App\Http\Controllers\PartnerCartController;
use App\Http\Controllers\PartnerPortalOrderController;
use App\Http\Controllers\CreditController;
use App\Http\Controllers\PartnerOrderAdminController;
use App\Http\Controllers\LandingController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\JobPositionController;
use Illuminate\Support\Facades\Route;

// Landing page publik — halaman utama perusahaan
Route::get('/', [LandingController::class, 'index'])->name('home');

// E-Catalog — Halaman publik tanpa login, bisa dibagikan ke pelanggan
Route::get('/catalog', [CatalogController::class, 'index'])->name('catalog.index');
Route::get('/catalog/{product}', [CatalogController::class, 'show'])->name('catalog.show');

// Portal Mitra B2B (daftar mandiri + login)
Route::prefix('mitra')->name('mitra.')->group(function () {
    Route::get('/daftar', [PartnerPortalController::class, 'registerForm'])->name('register');
    Route::post('/daftar', [PartnerPortalController::class, 'register'])->name('register.post')->middleware('throttle:8,1');
    Route::get('/daftar/berhasil/{code?}', [PartnerPortalController::class, 'registerSuccess'])->name('register.success');
    Route::get('/login', [PartnerPortalController::class, 'loginForm'])->name('login');
    Route::post('/login', [PartnerPortalController::class, 'login'])->name('login.post')->middleware('throttle:8,1');
});

// Auth routes
Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login')->middleware('guest');
Route::get('/login/pending-partners', [LoginController::class, 'pendingPartners'])->name('login.pending-partners');
Route::post('/login', [LoginController::class, 'login'])->name('login.post')->middleware('throttle:5,1');
Route::post('/logout', [LoginController::class, 'logout'])->name('logout')->middleware('auth');
Route::get('/logout', [LoginController::class, 'logout'])->middleware('auth');

// Authenticated routes
Route::middleware(['auth', 'session.timeout'])->group(function () {
    Route::get('/mitra/akun', [PartnerPortalController::class, 'account'])->name('mitra.account');
    Route::post('/mitra/logout', [PartnerPortalController::class, 'logout'])->name('mitra.logout');

    // Keranjang + PO mitra
    Route::post('/mitra/cart/add/{product}', [PartnerCartController::class, 'add'])->name('mitra.cart.add');
    Route::get('/mitra/cart', [PartnerCartController::class, 'index'])->name('mitra.cart');
    Route::post('/mitra/cart/update', [PartnerCartController::class, 'update'])->name('mitra.cart.update');
    Route::post('/mitra/cart/remove/{product}', [PartnerCartController::class, 'remove'])->name('mitra.cart.remove');
    Route::get('/mitra/checkout', [PartnerCartController::class, 'checkoutForm'])->name('mitra.checkout');
    Route::post('/mitra/checkout', [PartnerCartController::class, 'checkout'])->name('mitra.checkout.post');
    Route::get('/mitra/orders', [PartnerPortalOrderController::class, 'index'])->name('mitra.orders.index');
    Route::get('/mitra/orders/{order}', [PartnerPortalOrderController::class, 'show'])->name('mitra.orders.show');
    Route::post('/mitra/orders/{order}/proof', [PartnerPortalOrderController::class, 'uploadProof'])->name('mitra.orders.proof');
    Route::post('/mitra/orders/{order}/cancel', [PartnerPortalOrderController::class, 'cancel'])->name('mitra.orders.cancel');

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/profile', [\App\Http\Controllers\ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [\App\Http\Controllers\ProfileController::class, 'update'])->name('profile.update');
    Route::get('/notifications', [\App\Http\Controllers\NotificationController::class, 'index'])->name('notifications.index');

    // ── POS / belanjaan (Kepala IT, Ops, Staff Ops, Kasir, Keuangan) ──
    Route::middleware(['role:super_admin,kepala_operasional,staff_operasional,kasir,admin_keuangan'])->group(function () {
        Route::get('/products/search', [ProductController::class, 'search'])->name('products.search');
        Route::get('/customers/search', [CustomerController::class, 'search'])->name('customers.search');
        Route::post('/customers/quick-store', [CustomerController::class, 'quickStore'])->name('customers.quick-store');
        Route::resource('customers', CustomerController::class);
        Route::get('/partners/search', [PartnerController::class, 'search'])->name('partners.search');
        Route::post('/partners/quick-store', [PartnerController::class, 'quickStore'])->name('partners.quick-store');
        Route::get('/prescriptions/{prescription}/json', [PrescriptionController::class, 'getJson'])->name('prescriptions.json');
        Route::resource('prescriptions', PrescriptionController::class);
        Route::get('/pos', [SaleController::class, 'pos'])->name('pos.index');
        Route::get('/pos/sync', [SaleController::class, 'sync'])->name('pos.sync');
        Route::post('/pos', [SaleController::class, 'store'])->name('pos.store');
        Route::post('/pos/partner-order', [SaleController::class, 'createPartnerOrder'])->name('pos.partner-order');
        Route::get('/sales/{sale}/print', [SaleController::class, 'printReceipt'])->name('sales.print');
        Route::post('/sales/{sale}/print-thermal', [SaleController::class, 'printThermal'])->name('sales.print-thermal');
        Route::post('/sales/{sale}/cancel', [SaleController::class, 'cancel'])->name('sales.cancel');
        Route::post('/sales/{sale}/pay', [SaleController::class, 'payInvoice'])->name('sales.pay');
        Route::resource('sales', SaleController::class)->only(['index', 'show']);
    });

    // ── Inventori (bukan khusus kasir) ──
    Route::middleware(['role:super_admin,kepala_operasional,staff_operasional,admin_keuangan,staff_it'])->group(function () {
        Route::get('/products/import-template', [ProductController::class, 'downloadTemplate'])->name('products.import.template');
        Route::get('/products/import', [ProductController::class, 'importForm'])->name('products.import.form');
        Route::post('/products/import', [ProductController::class, 'import'])->name('products.import');
        Route::patch('/products/{product}/toggle-catalog', [ProductController::class, 'toggleCatalog'])->name('products.toggle-catalog');
        Route::post('/products/bulk-catalog', [ProductController::class, 'bulkCatalog'])->name('products.bulk-catalog');
        Route::resource('products', ProductController::class);
        Route::resource('categories', CategoryController::class)->except(['show', 'create', 'edit']);
        Route::patch('categories/{category}/toggle-status', [CategoryController::class, 'toggleStatus'])->name('categories.toggle-status');
        Route::resource('stock-outs', StockOutController::class)->except(['edit', 'update', 'destroy', 'show']);
        Route::resource('stock-opnames', StockOpnameController::class)->except(['edit', 'update', 'destroy', 'show']);
    });

    // ── Pengadaan ──
    Route::middleware(['role:super_admin,kepala_operasional,admin_keuangan'])->group(function () {
        Route::get('/purchases/reorder', [PurchaseController::class, 'reorderList'])->name('purchases.reorder');
        Route::get('/purchases/{purchase}/pdf', [PurchaseController::class, 'downloadPdf'])->name('purchases.pdf');
        Route::post('/purchases/{purchase}/receive', [PurchaseController::class, 'receive'])->name('purchases.receive');
        Route::resource('purchases', PurchaseController::class)->except(['destroy']);
        Route::resource('suppliers', SupplierController::class)->except(['show']);
    });

    // ── Invoice (keuangan + kepala ops + kepala IT) ──
    Route::middleware(['role:super_admin,kepala_operasional,admin_keuangan'])->group(function () {
        Route::get('/invoices', [InvoiceController::class, 'index'])->name('invoices.index');
        Route::post('/invoices/{sale}/pay', [InvoiceController::class, 'pay'])->name('invoices.pay');
        Route::get('/invoices/{sale}/print', [InvoiceController::class, 'print'])->name('invoices.print');
        Route::get('/invoices/{sale}/export', [InvoiceController::class, 'export'])->name('invoices.export');
    });

    // ── Master data, mitra, keuangan, laporan ──
    Route::middleware(['role:super_admin,admin_keuangan,kepala_operasional'])->group(function () {
        Route::get('/settings', [SettingsController::class, 'index'])->name('settings.index');
        Route::post('/settings', [SettingsController::class, 'update'])->name('settings.update');

        Route::get('/partners/pending-updates', [PartnerController::class, 'pendingUpdates'])->name('partners.pending-updates');
        Route::post('/partners/{partner}/approve', [PartnerController::class, 'approve'])->name('partners.approve');
        Route::post('/partners/{partner}/reject', [PartnerController::class, 'reject'])->name('partners.reject');
        Route::post('/partners/{partner}/deactivate', [PartnerController::class, 'deactivate'])->name('partners.deactivate');
        Route::resource('partners', PartnerController::class);

        Route::get('/partner-orders', [PartnerOrderAdminController::class, 'index'])->name('partner-orders.index');
        Route::get('/partner-orders/{partnerOrder}', [PartnerOrderAdminController::class, 'show'])->name('partner-orders.show');
        Route::get('/partner-orders/{partnerOrder}/print/surat-jalan', [PartnerOrderAdminController::class, 'printSuratJalan'])->name('partner-orders.print.surat-jalan');
        Route::get('/partner-orders/{partnerOrder}/print/penjualan', [PartnerOrderAdminController::class, 'printPenjualan'])->name('partner-orders.print.penjualan');
        Route::post('/partner-orders/{partnerOrder}/confirm', [PartnerOrderAdminController::class, 'confirm'])->name('partner-orders.confirm');
        Route::post('/partner-orders/{partnerOrder}/proof', [PartnerOrderAdminController::class, 'uploadProof'])->name('partner-orders.proof');
        Route::post('/partner-orders/{partnerOrder}/mark-paid', [PartnerOrderAdminController::class, 'markPaid'])->name('partner-orders.mark-paid');
        Route::post('/partner-orders/{partnerOrder}/fulfill', [PartnerOrderAdminController::class, 'fulfill'])->name('partner-orders.fulfill');
        Route::post('/partner-orders/{partnerOrder}/cancel', [PartnerOrderAdminController::class, 'cancel'])->name('partner-orders.cancel');
        Route::post('/partner-orders/{partnerOrder}/notes', [PartnerOrderAdminController::class, 'updateNotes'])->name('partner-orders.notes');

        Route::patch('/employees/{employee}/toggle-status', [EmployeeController::class, 'toggleStatus'])->name('employees.toggle-status');
        Route::resource('employees', EmployeeController::class);
        Route::resource('job-positions', JobPositionController::class)->except(['show', 'create', 'edit']);
        Route::patch('job-positions/{job_position}/toggle-status', [JobPositionController::class, 'toggleStatus'])->name('job-positions.toggle-status');
    });

    // Keuangan murni — Kepala IT + Staff Keuangan
    Route::middleware(['role:super_admin,admin_keuangan'])->group(function () {
        Route::get('/credits', [CreditController::class, 'index'])->name('credits.index');
        Route::post('/credits/mitra/{partnerOrder}/pay', [CreditController::class, 'payMitra'])->name('credits.pay-mitra');
        Route::get('/salaries/{salary}/print', [\App\Http\Controllers\SalaryController::class, 'printSlip'])->name('salaries.print');
        Route::resource('salaries', \App\Http\Controllers\SalaryController::class)->except(['show']);
        Route::get('/analytics', [AnalyticsController::class, 'index'])->name('analytics.index');
        Route::get('/analytics/shift-report', [AnalyticsController::class, 'shiftReport'])->name('analytics.shift-report');
    });

    // Laporan — Kepala IT, Staff Keuangan, Staff IT (log saja dibatasi di controller)
    Route::middleware(['role:super_admin,admin_keuangan,staff_it'])->group(function () {
        Route::get('/reports', [\App\Http\Controllers\ReportController::class, 'index'])->name('reports.index');
        Route::get('/reports/generate', [\App\Http\Controllers\ReportController::class, 'generate'])->name('reports.generate');
    });

    // Sistem & IT — Kepala IT + Staff IT
    Route::middleware(['role:super_admin,staff_it'])->group(function () {
        Route::resource('users', UserController::class)->except(['show']);
        Route::patch('users/{user}/toggle-status', [UserController::class, 'toggleStatus'])->name('users.toggle-status');
        Route::get('/backup', [\App\Http\Controllers\BackupController::class, 'index'])->name('backup.index');
        Route::get('/backup/create', [\App\Http\Controllers\BackupController::class, 'create'])->name('backup.create');
        Route::get('/backup/download/{filename}', [\App\Http\Controllers\BackupController::class, 'download'])->name('backup.download');
        Route::delete('/backup/{filename}', [\App\Http\Controllers\BackupController::class, 'destroy'])->name('backup.destroy');
    });
});
