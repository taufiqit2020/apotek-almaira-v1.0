<?php
namespace App\Http\Controllers;

use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Product;
use App\Models\Partner;
use App\Models\PartnerOrder;
use App\Models\PartnerOrderItem;
use App\Models\Category;
use App\Models\Customer;
use App\Models\Setting;
use App\Services\ActivityLogService;
use App\Services\NotificationService;
use App\Services\PartnerPricingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class SaleController extends Controller {
    
    public function pos() {
        $ppnDefault = floatval(\App\Models\Setting::get('pos_ppn_percent', 11));
        $ppnActive = filter_var(\App\Models\Setting::get('pos_ppn_active', true), FILTER_VALIDATE_BOOLEAN);
        $ppnBearer = \App\Models\Setting::get('pos_ppn_bearer', 'buyer');
        $qrisNmid = \App\Models\Setting::get('qris_nmid', 'ID1026522359276');
        
        $defaultRules = [
            ['min_qty' => 1, 'max_qty' => 10, 'percents' => '1.5, 2.5, 3.5, 4.5, 5.5, 6.5, 7.5, 8.5, 9.5, 10.5'],
            ['min_qty' => 11, 'max_qty' => 20, 'percents' => '11.5, 12.5, 13.5, 14.5, 15.5, 16.5, 17.5, 18.5, 19.5, 20.5'],
            ['min_qty' => 21, 'max_qty' => 999, 'percents' => '21.5, 22.5, 23.5, 24.5, 25.5, 26.5, 27.5, 28.5, 29.5, 30.5']
        ];
        $discountRules = json_decode(\App\Models\Setting::get('pos_discount_rules', json_encode($defaultRules)), true);
        if (! is_array($discountRules) || $discountRules === []) {
            $discountRules = $defaultRules;
        }

        // Ambil data kategori untuk Visual Catalog POS
        $categories = \App\Models\Category::active()->orderBy('name', 'asc')->get(['id', 'name']);

        $crmPointMultiplier = (int)\App\Models\Setting::get('crm_point_multiplier', 1000);
        $crmPointValue = (int)\App\Models\Setting::get('crm_point_value', 1);
        $partnerTypes = Partner::typeOptions();

        return view('sales.pos', compact(
            'ppnDefault', 'ppnActive', 'ppnBearer', 'qrisNmid', 'discountRules',
            'categories', 'crmPointMultiplier', 'crmPointValue', 'partnerTypes'
        ));
    }

    /**
     * Soft-sync data admin → kasir (tanpa reload halaman / tanpa reset keranjang).
     * Client kirim ?since=revision; jika sama → {changed:false}.
     */
    public function sync(Request $request)
    {
        $revisions = $this->posDataRevisions();
        $revision = $revisions['revision'];
        $since = trim((string) $request->get('since', ''));
        $force = $request->boolean('force');

        if (!$force && $since !== '' && hash_equals($revision, $since)) {
            return response()->json([
                'changed'  => false,
                'revision' => $revision,
            ]);
        }

        $defaultRules = [
            ['min_qty' => 1, 'max_qty' => 10, 'percents' => '1.5, 2.5, 3.5, 4.5, 5.5, 6.5, 7.5, 8.5, 9.5, 10.5'],
            ['min_qty' => 11, 'max_qty' => 20, 'percents' => '11.5, 12.5, 13.5, 14.5, 15.5, 16.5, 17.5, 18.5, 19.5, 20.5'],
            ['min_qty' => 21, 'max_qty' => 999, 'percents' => '21.5, 22.5, 23.5, 24.5, 25.5, 26.5, 27.5, 28.5, 29.5, 30.5'],
        ];
        $discountRules = json_decode(Setting::get('pos_discount_rules', json_encode($defaultRules)), true);
        if (!is_array($discountRules) || $discountRules === []) {
            $discountRules = $defaultRules;
        }

        $ppnBearer = Setting::get('pos_ppn_bearer', 'buyer');

        $catalogQuery = Product::active()->with('unit');
        $categoryId = $request->get('category_id');
        $q = trim((string) $request->get('q', ''));
        if ($categoryId) {
            $catalogQuery->where('category_id', $categoryId);
        }
        $catalogQuery->searchKeyword($q, 'ops');
        $catalog = $catalogQuery
            ->select(['id', 'name', 'code', 'barcode', 'purchase_price', 'sell_price', 'wholesale_price', 'stock', 'stock_min', 'unit_id', 'images', 'is_active', 'description', 'composition', 'dosage_form', 'manufacturer', 'drug_class', 'route'])
            ->limit(200)
            ->get()
            ->map(fn (Product $p) => $this->mapPosProduct($p));

        $cartIds = collect(explode(',', (string) $request->get('cart_ids', '')))
            ->map(fn ($id) => (int) trim($id))
            ->filter(fn ($id) => $id > 0)
            ->unique()
            ->values()
            ->all();

        $cartProducts = [];
        if ($cartIds !== []) {
            $cartProducts = Product::with('unit')
                ->whereIn('id', $cartIds)
                ->get(['id', 'name', 'code', 'barcode', 'purchase_price', 'sell_price', 'wholesale_price', 'stock', 'stock_min', 'unit_id', 'images', 'is_active', 'description', 'composition', 'dosage_form', 'manufacturer', 'drug_class', 'route'])
                ->map(fn (Product $p) => $this->mapPosProduct($p))
                ->values();
        }

        $selectedPartner = null;
        if ($request->filled('partner_id')) {
            $partner = Partner::find($request->partner_id);
            if ($partner) {
                $selectedPartner = [
                    'id'                  => $partner->id,
                    'code'                => $partner->code,
                    'name'                => $partner->name,
                    'phone'               => $partner->phone,
                    'pic_name'            => $partner->pic_name,
                    'address'             => $partner->address,
                    'type'                => $partner->type,
                    'type_label'          => $partner->type_label,
                    'price_mode'          => $partner->price_mode,
                    'price_mode_label'    => $partner->price_mode_label,
                    'invoice_enabled'     => (bool) $partner->invoice_enabled,
                    'credit_days'         => (int) ($partner->credit_days ?: 30),
                    'allow_transfer'      => (bool) $partner->allow_transfer,
                    'allow_cod'           => (bool) $partner->allow_cod,
                    'ppn_enabled'         => (bool) $partner->ppn_enabled,
                    'ppn_percent'         => (float) ($partner->ppn_percent ?: 0),
                    'ppn_bearer'          => $partner->ppn_bearer,
                    'is_approved'         => $partner->isApproved(),
                    'has_overdue_invoice' => $partner->hasOverdueInvoice(),
                ];
            }
        }

        $selectedCustomer = null;
        if ($request->filled('customer_id')) {
            $customer = Customer::find($request->customer_id);
            if ($customer) {
                $selectedCustomer = [
                    'id'                  => $customer->id,
                    'name'                => $customer->name,
                    'phone'               => $customer->phone,
                    'points'              => (int) $customer->points,
                    'is_active'           => (bool) $customer->is_active,
                    'has_overdue_invoice' => $customer->hasOverdueInvoice(),
                ];
            }
        }

        $payload = [
            'changed'            => true,
            'revision'           => $revision,
            'settings_revision'  => $revisions['settings'],
            'settings' => [
                'ppn_percent'          => floatval(Setting::get('pos_ppn_percent', 11)),
                'ppn_active'           => filter_var(Setting::get('pos_ppn_active', true), FILTER_VALIDATE_BOOLEAN),
                'ppn_bearer'           => $ppnBearer === 'seller' ? 'Ditanggung Penjual' : 'Ditanggung Pembeli',
                'qris_nmid'            => Setting::get('qris_nmid', 'ID1026522359276'),
                'discount_rules'       => $discountRules,
                'crm_point_multiplier' => (int) Setting::get('crm_point_multiplier', 1000),
                'crm_point_value'      => (int) Setting::get('crm_point_value', 1),
            ],
            'categories'    => Category::active()->orderBy('name')->get(['id', 'name']),
            'catalog'       => $catalog,
            'cart_products' => $cartProducts,
            'partner_types' => Partner::typeOptions(),
        ];

        if ($request->filled('partner_id')) {
            $payload['selected_partner'] = $selectedPartner;
        }
        if ($request->filled('customer_id')) {
            $payload['selected_customer'] = $selectedCustomer;
        }

        return response()->json($payload);
    }

    /** @return array<string, mixed> */
    private function mapPosProduct(Product $p): array
    {
        $meta = $p->catalogMeta();

        return [
            'id'               => $p->id,
            'name'             => $p->name,
            'code'             => $p->code,
            'barcode'          => $p->barcode,
            'purchase_price'   => $p->purchase_price,
            'sell_price'       => $p->sell_price,
            'wholesale_price'  => $p->wholesale_price,
            'stock'            => (int) $p->stock,
            'stock_min'        => (int) $p->stock_min,
            'unit'             => $p->unit?->name,
            'images'           => $p->images,
            'image_url'        => $p->image_url,
            'has_image'        => $p->has_image,
            'is_active'        => (bool) $p->is_active,
            'indikasi'         => $meta['indikasi'],
            'kandungan'        => $meta['kandungan'],
            'bentuk_sediaan'   => $meta['bentuk_sediaan'],
        ];
    }

    /** @return array{revision: string, settings: string} */
    private function posDataRevisions(): array
    {
        $settingsRev = md5((string) Setting::query()->max('updated_at'));
        $dataRev = md5(implode('|', [
            (string) Product::query()->max('updated_at'),
            (string) Product::query()->sum('stock'),
            (string) Category::query()->max('updated_at'),
            (string) Partner::withTrashed()->max('updated_at'),
            (string) Customer::query()->max('updated_at'),
            (string) PartnerOrder::query()->max('updated_at'),
            (string) Sale::query()->max('updated_at'),
            $settingsRev,
        ]));

        return [
            'revision' => $dataRev,
            'settings' => $settingsRev,
        ];
    }

    public function index() {
        // Query logic dan kalkulasi total dipindahkan ke Livewire\Sales\SaleTable component
        return view('sales.index');
    }

    public function show(Sale $sale) {
        $sale->load(['user', 'items.product']);
        return view('sales.show', compact('sale'));
    }

    public function store(Request $request) {
        $request->validate([
            'customer_name' => 'nullable|string|max:100',
            'payment_method' => 'required|in:Tunai,QRIS,Transfer,Invoice',
            'discount_percent' => 'required|numeric|min:0|max:100',
            'discount_amount' => 'required|numeric|min:0',
            'ppn_active' => 'required|boolean',
            'ppn_percent' => 'required|numeric|min:0',
            'ppn_amount' => 'required|numeric|min:0',
            'ppn_bearer' => 'required|in:Ditanggung Pembeli,Ditanggung Penjual',
            'cash_received' => 'required_if:payment_method,Tunai|nullable|numeric|min:0',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.price_type' => 'required|in:eceran,grosir',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.discount_percent' => 'required|numeric|min:0|max:100',
            'customer_id' => 'nullable|exists:customers,id',
            'partner_id' => 'nullable|exists:partners,id',
            'points_redeemed' => 'nullable|integer|min:0',
            'prescription_id' => 'nullable|exists:prescriptions,id',
        ]);

        if ($request->filled('customer_id') && $request->filled('partner_id')) {
            return response()->json([
                'success' => false,
                'message' => 'Pilih salah satu: pelanggan CRM atau mitra B2B, tidak keduanya.',
            ], 422);
        }

        // Mitra + Transfer/Invoice wajib lewat alur PO (bukan penjualan kasir langsung).
        if ($request->filled('partner_id') && in_array($request->payment_method, ['Transfer', 'Invoice'], true)) {
            return response()->json([
                'success' => false,
                'message' => 'Untuk Mitra dengan Transfer/Invoice, gunakan tombol Buat PO Mitra. Transaksi kasir langsung hanya untuk Tunai atau QRIS (ambil sekarang).',
            ], 422);
        }

        try {
            $sale = DB::transaction(function () use ($request) {
                $subtotal = 0;
                $itemsData = [];

                $defaultRules = [
                    ['min_qty' => 1, 'max_qty' => 10, 'percents' => '1.5, 2.5, 3.5, 4.5, 5.5, 6.5, 7.5, 8.5, 9.5, 10.5'],
                    ['min_qty' => 11, 'max_qty' => 20, 'percents' => '11.5, 12.5, 13.5, 14.5, 15.5, 16.5, 17.5, 18.5, 19.5, 20.5'],
                    ['min_qty' => 21, 'max_qty' => 999, 'percents' => '21.5, 22.5, 23.5, 24.5, 25.5, 26.5, 27.5, 28.5, 29.5, 30.5']
                ];
                $discountRules = json_decode(\App\Models\Setting::get('pos_discount_rules', json_encode($defaultRules)), true);
                if (! is_array($discountRules) || $discountRules === []) {
                    $discountRules = $defaultRules;
                }

                foreach ($request->items as $item) {
                    $product = Product::lockForUpdate()->findOrFail($item['product_id']);

                    // Check stock
                    if ($product->stock < $item['quantity']) {
                        throw new \Exception("Stok tidak mencukupi untuk produk: {$product->name}. Tersedia: {$product->stock}");
                    }

                    // Determine unit price based on price type
                    $unitPrice = $item['price_type'] === 'grosir' ? $product->wholesale_price : $product->sell_price;
                    
                    // Fallback to sell price if wholesale price is 0
                    if ($item['price_type'] === 'grosir' && floatval($unitPrice) <= 0) {
                        $unitPrice = $product->sell_price;
                    }

                    $itemQty = intval($item['quantity']);
                    $itemDiscPercent = floatval($item['discount_percent']);

                    // Diskon % hanya valid jika ada di aturan yang cocok dengan Qty item
                    $validDisc = abs($itemDiscPercent) < 0.01;
                    if (! $validDisc) {
                        foreach ($discountRules ?: [] as $rule) {
                            $min = (int) ($rule['min_qty'] ?? 1);
                            $max = (int) ($rule['max_qty'] ?? 999);
                            if ($itemQty < $min || $itemQty > $max || empty($rule['percents'])) {
                                continue;
                            }
                            foreach (explode(',', (string) $rule['percents']) as $allowed) {
                                if (abs($itemDiscPercent - (float) trim($allowed)) < 0.01) {
                                    $validDisc = true;
                                    break 2;
                                }
                            }
                        }
                    }

                    if (! $validDisc) {
                        throw new \Exception("Persentase diskon {$itemDiscPercent}% tidak valid untuk kuantitas {$itemQty}.");
                    }

                    $itemSubtotalBeforeDisc = $unitPrice * $itemQty;
                    $itemDiscountAmount = ($itemSubtotalBeforeDisc * $itemDiscPercent) / 100;
                    $itemSubtotal = $itemSubtotalBeforeDisc - $itemDiscountAmount;

                    $subtotal += $itemSubtotal;

                    // Deduct stock
                    $product->decrement('stock', $itemQty);
                    $product->refresh();
                    if ($product->stock <= $product->stock_min) {
                        \App\Services\NotificationService::triggerStockAlert($product);
                    }

                    $itemsData[] = [
                        'product_id' => $product->id,
                        'product_name' => $product->name,
                        'product_code' => $product->code,
                        'unit_name' => $product->unit?->name ?? 'Pcs',
                        'price_type' => $item['price_type'],
                        'unit_price' => $unitPrice,
                        'quantity' => $itemQty,
                        'discount_percent' => $itemDiscPercent,
                        'discount_amount' => $itemDiscountAmount,
                        'subtotal' => $itemSubtotal,
                    ];
                }

                // Calculate PPN and Total
                $ppnActive = filter_var($request->ppn_active, FILTER_VALIDATE_BOOLEAN);
                $ppnPercent = floatval($request->ppn_percent);
                $ppnBearer = $request->ppn_bearer;
                $totalDiscountAmount = floatval($request->discount_amount);
                
                $finalSubtotal = $subtotal;
                $ppnAmount = 0;
                $total = 0;

                if ($ppnActive) {
                    if ($ppnBearer === 'Ditanggung Pembeli') {
                        // Adds to subtotal
                        $ppnAmount = ($finalSubtotal * $ppnPercent) / 100;
                        $total = $finalSubtotal + $ppnAmount;
                    } else {
                        // Absorb in price (subtotal remains total, PPN is calculated internally)
                        $total = $finalSubtotal;
                        $ppnAmount = $total - ($total / (1 + ($ppnPercent / 100)));
                    }
                } else {
                    $total = $finalSubtotal;
                }

                // Handle CRM / Mitra & Prescription
                $customerId = $request->customer_id;
                $partnerId = $request->partner_id;
                $pointsRedeemed = intval($request->points_redeemed ?? 0);
                $pointsEarned = 0;
                $customerName = $request->customer_name ?: 'Pelanggan Umum';
                $partner = null;
                $invoiceCreditDays = 30;

                if ($partnerId) {
                    $partner = \App\Models\Partner::lockForUpdate()->findOrFail($partnerId);
                    if (!$partner->isApproved()) {
                        throw new \Exception('Mitra belum aktif / belum di-approve.');
                    }
                    $customerName = $partner->name;
                    $customerId = null;
                    $pointsRedeemed = 0;
                    $invoiceCreditDays = max(1, (int) ($partner->credit_days ?: 30));
                } elseif ($customerId) {
                    $customer = \App\Models\Customer::lockForUpdate()->findOrFail($customerId);
                    $customerName = $customer->name;
                    $pointValue = intval(\App\Models\Setting::get('crm_point_value', 1));
                    
                    if ($pointsRedeemed > 0) {
                        if ($customer->points < $pointsRedeemed) {
                            throw new \Exception("Poin pelanggan ({$customer->points}) tidak mencukupi untuk ditukar (diminta: {$pointsRedeemed}).");
                        }
                        
                        $pointDiscount = $pointsRedeemed * $pointValue;
                        $pointDiscount = min($total, $pointDiscount);
                        
                        $pointsRedeemed = ceil($pointDiscount / $pointValue);
                        $total -= $pointDiscount;
                        
                        $customer->decrement('points', $pointsRedeemed);
                    }
                    
                    $pointMultiplier = intval(\App\Models\Setting::get('crm_point_multiplier', 1000));
                    $pointsEarned = floor($total / $pointMultiplier);
                    $customer->increment('points', $pointsEarned);
                }

                $prescriptionId = $request->prescription_id;
                if ($prescriptionId) {
                    $prescription = \App\Models\Prescription::lockForUpdate()->findOrFail($prescriptionId);
                    $prescription->update(['status' => 'processed']);
                }

                // Check payment details
                $paymentMethod = $request->payment_method;
                $dueDate = null;
                $paymentStatus = 'paid';

                if ($paymentMethod === 'Invoice') {
                    if (!$customerId && !$partnerId) {
                        throw new \Exception('Pembayaran Invoice hanya untuk pelanggan CRM atau mitra B2B terdaftar.');
                    }
                    if ($partnerId) {
                        if (!$partner->canUseInvoice()) {
                            throw new \Exception('Mitra ini tidak diizinkan memakai Invoice (tempo).');
                        }
                        if ($partner->hasOverdueInvoice()) {
                            throw new \Exception('Mitra ini memiliki tagihan jatuh tempo yang belum lunas. Gunakan metode lain atau lunasi terlebih dahulu.');
                        }
                    } else {
                        $customer = \App\Models\Customer::findOrFail($customerId);
                        if ($customer->hasOverdueInvoice()) {
                            throw new \Exception('Pelanggan ini memiliki tagihan jatuh tempo yang belum lunas. Silakan gunakan metode pembayaran lain atau lunasi tagihan terlebih dahulu.');
                        }
                    }
                    $cashReceived = 0;
                    $changeAmount = 0;
                    $dueDate = now()->addDays($invoiceCreditDays);
                    $paymentStatus = 'unpaid';
                } elseif ($paymentMethod === 'Tunai') {
                    $cashReceived = floatval($request->cash_received);
                    if ($cashReceived < $total) {
                        throw new \Exception("Uang tunai yang diterima kurang dari total belanja.");
                    }
                    $changeAmount = $cashReceived - $total;
                } else { // QRIS or Transfer
                    $cashReceived = $total;
                    $changeAmount = 0;
                }

                // Mitra Katalog + Tunai/QRIS pakai nomor faktur khusus (FTR-.../NMF/mm/yyyy),
                // satu urutan bersama dengan PO Mitra Transfer (lihat PartnerOrder::generateOrderNo()).
                $invoiceNo = ($partnerId && in_array($paymentMethod, ['Tunai', 'QRIS'], true))
                    ? Sale::generateMitraInvoiceNo()
                    : Sale::generateDocumentNo($paymentMethod);

                $sale = Sale::create([
                    'invoice_no' => $invoiceNo,
                    'user_id' => Auth::id(),
                    'customer_name' => $customerName,
                    'payment_method' => $paymentMethod,
                    'subtotal' => $subtotal,
                    'discount_percent' => $request->discount_percent,
                    'discount_amount' => $totalDiscountAmount,
                    'ppn_active' => $ppnActive,
                    'ppn_percent' => $ppnPercent,
                    'ppn_amount' => $ppnAmount,
                    'ppn_bearer' => $ppnBearer,
                    'total' => $total,
                    'cash_received' => $cashReceived,
                    'change_amount' => $changeAmount,
                    'notes' => $request->notes,
                    'status' => 'completed',
                    'sold_at' => now(),
                    'customer_id' => $customerId,
                    'partner_id' => $partnerId,
                    'points_earned' => $pointsEarned,
                    'points_redeemed' => $pointsRedeemed,
                    'prescription_id' => $prescriptionId,
                    'due_date' => $dueDate,
                    'payment_status' => $paymentStatus,
                ]);

                foreach ($itemsData as $itemRow) {
                    $itemRow['sale_id'] = $sale->id;
                    SaleItem::create($itemRow);
                }

                // Log the activity
                ActivityLogService::transaction($sale->invoice_no, $sale->total);

                return $sale;
            });

            return response()->json([
                'success'          => true,
                'message'          => 'Transaksi berhasil disimpan!',
                'redirect_url'     => route('sales.show', $sale->id),
                'print_url'        => route('sales.print', $sale->id),
                'sale_id'          => $sale->id,
                'invoice_no'       => $sale->invoice_no,
                'document_label'   => $sale->document_label,
                'document_display' => $sale->document_display,
                'total'            => floatval($sale->getRawOriginal('total') ?? $sale->total),
                'change_amount'    => floatval($sale->getRawOriginal('change_amount') ?? $sale->change_amount),
            ]);

        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 422);
        }
    }

    public function printReceipt(Sale $sale) {
        $sale->load(['user', 'items.product']);
        return view('sales.receipt', compact('sale'));
    }

    public function printThermal(Sale $sale) {
        $sale->load(['user', 'items.product']);
        $printerService = new \App\Services\ThermalPrinterService();
        $result = $printerService->printReceipt($sale);
        return response()->json($result);
    }

    public function cancel(Request $request, Sale $sale) {
        if ($sale->status === 'cancelled') {
            return back()->with('toast_error', 'Transaksi ini sudah dibatalkan sebelumnya!');
        }

        $request->validate([
            'cancel_reason' => 'required|string|max:255',
        ], [
            'cancel_reason.required' => 'Alasan pembatalan wajib diisi.',
        ]);

        try {
            DB::transaction(function () use ($request, $sale) {
                // Revert stock for each item
                foreach ($sale->items as $item) {
                    $product = Product::findOrFail($item->product_id);
                    $product->increment('stock', $item->quantity);
                }

                // Update sale status and reason
                $sale->update([
                    'status' => 'cancelled',
                    'cancel_reason' => $request->cancel_reason,
                ]);

                // Revert CRM points if applicable
                if ($sale->customer_id) {
                    $customer = \App\Models\Customer::lockForUpdate()->find($sale->customer_id);
                    if ($customer) {
                        $customer->increment('points', $sale->points_redeemed);
                        $customer->decrement('points', $sale->points_earned);
                        if ($customer->points < 0) {
                            $customer->points = 0;
                        }
                        $customer->save();
                    }
                }
                
                // Revert prescription status if applicable
                if ($sale->prescription_id) {
                    $prescription = \App\Models\Prescription::lockForUpdate()->find($sale->prescription_id);
                    if ($prescription) {
                        $prescription->update(['status' => 'pending']);
                    }
                }

                // Log the activity
                ActivityLogService::log(
                    'CANCEL_TRANSACTION',
                    'POS',
                    "Membatalkan transaksi {$sale->invoice_no} — Alasan: {$request->cancel_reason}"
                );
            });

            return back()->with('toast_success', 'Transaksi berhasil dibatalkan dan stok produk telah dikembalikan!');
        } catch (\Exception $e) {
            return back()->with('toast_error', 'Gagal membatalkan transaksi: ' . $e->getMessage());
        }
    }

    public function payInvoice(Request $request, Sale $sale) {
        if ($sale->attributes['payment_method'] !== 'invoice') {
            return back()->with('toast_error', 'Transaksi ini bukan metode pembayaran Invoice.');
        }
        
        if ($sale->payment_status === 'paid') {
            return back()->with('toast_error', 'Tagihan invoice ini sudah dilunasi sebelumnya.');
        }

        $request->validate([
            'settlement_method' => 'required|in:cash,transfer',
        ], [
            'settlement_method.required' => 'Metode pelunasan wajib dipilih.',
        ]);

        $sale->update([
            'payment_status'    => 'paid',
            'settlement_method' => $request->settlement_method,
            'settled_at'        => now(),
            'settled_by'        => Auth::id(),
        ]);

        ActivityLogService::log(
            'UPDATE',
            'Transaksi Penjualan',
            "Pelunasan tagihan invoice {$sale->invoice_no} sebesar Rp " . number_format($sale->total, 0, ',', '.') .
            " — Metode: " . ($request->settlement_method === 'cash' ? 'Tunai' : 'Transfer')
        );

        return back()->with('toast_success', 'Invoice berhasil dilunasi!');
    }

    /**
     * Buat PO Mitra dari keranjang kasir (stok belum dipotong — menunggu fulfill admin).
     */
    public function createPartnerOrder(Request $request)
    {
        $request->validate([
            'partner_id'       => 'required|exists:partners,id',
            'payment_method'   => 'required|string',
            'shipping_address' => 'required|string|max:2000',
            'pic_name'         => 'required|string|max:150',
            'pic_phone'        => 'required|string|max:30',
            'notes'            => 'nullable|string|max:2000',
            'discount_amount'  => 'nullable|numeric|min:0',
            'items'            => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity'   => 'required|integer|min:1',
        ], [
            'partner_id.required'       => 'Pilih mitra terlebih dahulu.',
            'payment_method.required'   => 'Pilih metode pembayaran PO.',
            'shipping_address.required' => 'Alamat pengiriman wajib diisi.',
            'pic_name.required'         => 'Nama PIC wajib diisi.',
            'pic_phone.required'        => 'Telepon PIC wajib diisi.',
            'items.required'            => 'Keranjang masih kosong.',
        ]);

        $partner = Partner::findOrFail($request->partner_id);
        if (!$partner->isApproved()) {
            return response()->json([
                'success' => false,
                'message' => 'Mitra belum aktif / belum di-approve.',
            ], 422);
        }

        if ($partner->hasOverdueInvoice()) {
            return response()->json([
                'success' => false,
                'message' => 'Mitra memiliki tagihan jatuh tempo. Selesaikan pelunasan sebelum membuat PO baru.',
            ], 422);
        }

        $allowed = [];
        if ($partner->allow_transfer) {
            $allowed[] = PartnerOrder::PAY_TRANSFER;
        }
        if ($partner->allow_cod) {
            $allowed[] = PartnerOrder::PAY_COD;
        }
        if ($partner->canUseInvoice()) {
            $allowed[] = PartnerOrder::PAY_INVOICE;
        }

        if (empty($allowed)) {
            return response()->json([
                'success' => false,
                'message' => 'Mitra ini belum memiliki metode pembayaran PO yang diizinkan.',
            ], 422);
        }

        $request->validate([
            'payment_method' => ['required', Rule::in($allowed)],
        ], [
            'payment_method.in' => 'Metode pembayaran tidak diizinkan untuk mitra ini.',
        ]);

        try {
            $order = DB::transaction(function () use ($request, $partner) {
                $lines = [];
                $subtotal = 0;

                foreach ($request->items as $item) {
                    $qty = (int) $item['quantity'];
                    $product = Product::lockForUpdate()->findOrFail($item['product_id']);

                    if (!$product->is_active) {
                        throw new \RuntimeException("Produk \"{$product->name}\" tidak aktif.");
                    }
                    if ($product->stock < $qty) {
                        throw new \RuntimeException(
                            "Stok \"{$product->name}\" tidak mencukupi. Tersedia: {$product->stock}, diminta: {$qty}."
                        );
                    }

                    $priced = PartnerPricingService::resolve($product, $partner, $qty);
                    $lineSubtotal = $priced['unit_price'] * $qty;
                    $subtotal += $lineSubtotal;

                    $lines[] = [
                        'product'     => $product,
                        'price_type'  => $priced['price_type'],
                        'unit_price'  => $priced['unit_price'],
                        'qty'         => $qty,
                        'subtotal'    => $lineSubtotal,
                    ];
                }

                $discountAmount = min((float) ($request->discount_amount ?? 0), $subtotal);
                $totals = $partner->calculateOrderTotals($subtotal, $discountAmount);

                $dueDate = null;
                if ($request->payment_method === PartnerOrder::PAY_INVOICE) {
                    $dueDate = now()->addDays((int) ($partner->credit_days ?: 30))->toDateString();
                }

                $order = PartnerOrder::create([
                    'order_no'            => PartnerOrder::generateOrderNo($partner, $request->payment_method),
                    'partner_id'          => $partner->id,
                    'user_id'             => Auth::id(),
                    'status'              => PartnerOrder::STATUS_SUBMITTED,
                    'payment_method'      => $request->payment_method,
                    'payment_status'      => PartnerOrder::PAYMENT_UNPAID,
                    'subtotal'            => $subtotal,
                    'discount_amount'     => $totals['discount_amount'],
                    'ppn_enabled'         => $totals['ppn_enabled'],
                    'ppn_percent'         => $totals['ppn_enabled'] ? $totals['ppn_percent'] : null,
                    'ppn_amount'          => $totals['ppn_amount'],
                    'ppn_bearer'          => $totals['ppn_bearer'],
                    'total'               => $totals['grand_total'],
                    'price_mode_snapshot' => $partner->price_mode,
                    'shipping_address'    => $request->shipping_address,
                    'pic_name'            => $request->pic_name,
                    'pic_phone'           => $request->pic_phone,
                    'notes'               => $request->filled('notes') ? trim((string) $request->notes) : null,
                    'due_date'            => $dueDate,
                ]);

                foreach ($lines as $line) {
                    /** @var Product $product */
                    $product = $line['product'];
                    PartnerOrderItem::create([
                        'partner_order_id' => $order->id,
                        'product_id'       => $product->id,
                        'product_name'     => $product->name,
                        'product_code'     => $product->code,
                        'unit_name'        => $product->unit?->name,
                        'price_type'       => $line['price_type'],
                        'unit_price'       => $line['unit_price'],
                        'quantity'         => $line['qty'],
                        'subtotal'         => $line['subtotal'],
                    ]);
                }

                return $order->load('partner');
            });
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }

        ActivityLogService::log(
            'CREATE',
            'PO Mitra',
            "PO {$order->order_no} dibuat dari kasir untuk {$partner->name} — " . number_format((float) $order->total, 0, ',', '.')
        );

        NotificationService::triggerPartnerOrderSubmitted($order);

        $redirectUrl = null;
        $user = Auth::user();
        if ($user && $user->canAccessAdmin()) {
            $redirectUrl = route('partner-orders.show', $order);
        }

        return response()->json([
            'success'      => true,
            'message'      => "PO {$order->order_no} berhasil dibuat. Stok dipotong saat admin memenuhi pesanan.",
            'order_no'     => $order->order_no,
            'order_id'     => $order->id,
            'total'        => (float) $order->total,
            'redirect_url' => $redirectUrl,
        ]);
    }
}
