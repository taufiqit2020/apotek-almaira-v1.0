<?php

namespace App\Http\Controllers;

use App\Models\Partner;
use App\Models\PartnerOrder;
use App\Models\PartnerOrderItem;
use App\Models\Product;
use App\Models\Setting;
use App\Services\ActivityLogService;
use App\Services\NotificationService;
use App\Services\PartnerCartService;
use App\Services\PartnerPricingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class PartnerCartController extends Controller
{
    private function apotekData(): array
    {
        return [
            'apotekName'    => Setting::get('apotek_name', 'Apotek Almaira'),
            'apotekAddress' => Setting::get('apotek_address', 'Jl. Panglima Batur No. 16, Kel. Komet, Kec. Banjarbaru Utara, Kota Banjarbaru, Kalsel 70714'),
            'apotekPhone'   => Setting::get('apotek_phone', '0851-6665-7070'),
            'bankName'      => Setting::get('bank_name', 'BNI'),
            'bankAccount'   => Setting::get('bank_account', '2050169349'),
            'bankHolder'    => Setting::get('bank_holder', 'PT NUR MADANI FARMA'),
        ];
    }

    private function requireMitraPartner(): Partner
    {
        $user = Auth::user();
        abort_unless($user && $user->isMitra(), 403);
        $partner = $user->partner;
        abort_unless($partner && $partner->isApproved(), 403, 'Akun mitra belum aktif.');

        return $partner;
    }

    /** @return array{subtotal: float, discount_amount: float, net_subtotal: float, ppn_enabled: bool, ppn_percent: float, ppn_amount: float, ppn_bearer: ?string, ppn_bearer_label: ?string, grand_total: float} */
    private function orderTotalsSummary(Partner $partner, float $subtotal, float $discountAmount = 0): array
    {
        return $partner->calculateOrderTotals($subtotal, $discountAmount);
    }

    /** @return array{state: string, label: string, bar: int, badge: string, text: string, ring: string} */
    private function stockMeta(Product $product): array
    {
        $stock = (int) $product->stock;
        $min = max(1, (int) $product->stock_min);

        if ($stock <= 0) {
            return [
                'state' => 'habis',
                'label' => 'Stok Habis',
                'badge' => 'bg-red-100 text-red-700 border border-red-200',
                'text'  => 'text-red-600',
                'ring'  => 'ring-red-100',
                'bar'   => 6,
                'bar_color' => 'bg-red-500',
            ];
        }

        if ($stock <= $min) {
            $bar = min(72, max(18, (int) round(($stock / ($min * 2)) * 100)));

            return [
                'state' => 'terbatas',
                'label' => 'Stok Terbatas',
                'badge' => 'bg-amber-100 text-amber-800 border border-amber-200',
                'text'  => 'text-amber-700',
                'ring'  => 'ring-amber-100',
                'bar'   => $bar,
                'bar_color' => 'bg-amber-500',
            ];
        }

        $bar = min(100, max(55, (int) round(($stock / ($min * 3)) * 100)));

        return [
            'state' => 'tersedia',
            'label' => 'Stok Tersedia',
            'badge' => 'bg-emerald-100 text-emerald-800 border border-emerald-200',
            'text'  => 'text-emerald-700',
            'ring'  => 'ring-emerald-100',
            'bar'   => $bar,
            'bar_color' => 'bg-emerald-500',
        ];
    }

    /** @return list<array{product_id: int, name: string, qty: int, stock: int}> */
    private function cartStockIssues(array $cart): array
    {
        $issues = [];

        foreach ($cart['items'] as $line) {
            $product = $line['product'];
            if ($line['qty'] > $product->stock) {
                $issues[] = [
                    'product_id' => (int) $product->id,
                    'name'       => $product->name,
                    'qty'        => (int) $line['qty'],
                    'stock'      => (int) $product->stock,
                ];
            }
        }

        return $issues;
    }

    /** @return array{count: int, subtotal: float, product_count: int, has_stock_issues: bool, stock_issues: list<array<string, mixed>>, items: array<int, array<string, mixed>>} */
    private function cartJson(Partner $partner): array
    {
        $cart = PartnerCartService::lines($partner);
        $stockIssues = $this->cartStockIssues($cart);
        $totals = $this->orderTotalsSummary($partner, (float) $cart['subtotal']);

        return [
            'count'             => (int) $cart['count'],
            'subtotal'          => (float) $cart['subtotal'],
            'product_count'     => count($cart['items']),
            'has_stock_issues'  => count($stockIssues) > 0,
            'stock_issues'      => $stockIssues,
            'discount_amount'   => $totals['discount_amount'],
            'ppn_enabled'       => $totals['ppn_enabled'],
            'ppn_percent'       => $totals['ppn_percent'],
            'ppn_amount'        => $totals['ppn_amount'],
            'ppn_bearer'        => $totals['ppn_bearer'],
            'grand_total'       => $totals['grand_total'],
            'items'             => collect($cart['items'])->map(function (array $line) {
                $product = $line['product'];
                $stock = $this->stockMeta($product);

                return [
                    'product_id' => $line['product_id'],
                    'qty'        => $line['qty'],
                    'price_type' => $line['price_type'],
                    'unit_price' => (float) $line['unit_price'],
                    'subtotal'   => (float) $line['subtotal'],
                    'stock'      => (int) $product->stock,
                    'stock_min'  => (int) $product->stock_min,
                    'stock_state'=> $stock['state'],
                    'stock_label'=> $stock['label'],
                    'stock_bar'  => $stock['bar'],
                    'unit_name'  => $product->unit?->name ?? 'pcs',
                ];
            })->values()->all(),
        ];
    }

    public function add(Request $request, Product $product)
    {
        $partner = $this->requireMitraPartner();

        abort_unless($product->is_active && $product->show_in_catalog, 404);

        $request->validate([
            'qty' => 'nullable|integer|min:1|max:9999',
        ]);

        $qty = (int) ($request->input('qty', 1));
        PartnerCartService::add($product->id, $qty);

        $msg = $product->name . ' ditambahkan ke keranjang.';

        if ($request->wantsJson()) {
            return response()->json([
                'ok'      => true,
                'count'   => PartnerCartService::count(),
                'message' => $msg,
                'cart'    => $this->cartJson($partner),
            ]);
        }

        return back()->with('toast_success', $msg);
    }

    public function index()
    {
        $partner = $this->requireMitraPartner();
        $cart = PartnerCartService::lines($partner);

        return view('partners.portal.cart', array_merge($this->apotekData(), [
            'partner'     => $partner,
            'cart'        => $cart,
            'priceLabel'  => PartnerPricingService::priceLabel($partner),
            'stockIssues' => $this->cartStockIssues($cart),
            'ppn'         => $this->orderTotalsSummary($partner, (float) $cart['subtotal']),
        ]));
    }

    public function update(Request $request)
    {
        $partner = $this->requireMitraPartner();

        $request->validate([
            'items'         => 'required|array',
            'items.*.id'    => 'required|integer',
            'items.*.qty'   => 'required|integer|min:0|max:9999',
        ]);

        foreach ($request->items as $row) {
            PartnerCartService::update((int) $row['id'], (int) $row['qty']);
        }

        if ($request->wantsJson()) {
            return response()->json([
                'ok'      => true,
                'message' => 'Keranjang diperbarui.',
                'cart'    => $this->cartJson($partner),
            ]);
        }

        return redirect()->route('mitra.cart')->with('toast_success', 'Keranjang diperbarui.');
    }

    public function remove(Request $request, Product $product)
    {
        $partner = $this->requireMitraPartner();
        PartnerCartService::remove($product->id);

        if ($request->wantsJson()) {
            return response()->json([
                'ok'      => true,
                'message' => 'Item dihapus dari keranjang.',
                'cart'    => $this->cartJson($partner),
            ]);
        }

        return redirect()->route('mitra.cart')->with('toast_success', 'Item dihapus dari keranjang.');
    }

    public function checkoutForm()
    {
        $partner = $this->requireMitraPartner();
        $cart = PartnerCartService::lines($partner);

        if (empty($cart['items'])) {
            return redirect()->route('mitra.cart')->with('toast_error', 'Keranjang masih kosong.');
        }

        $methods = [];
        if ($partner->allow_transfer) {
            $methods[PartnerOrder::PAY_TRANSFER] = PartnerOrder::paymentMethodOptions()[PartnerOrder::PAY_TRANSFER];
        }
        if ($partner->allow_cod) {
            $methods[PartnerOrder::PAY_COD] = PartnerOrder::paymentMethodOptions()[PartnerOrder::PAY_COD];
        }
        if ($partner->canUseInvoice()) {
            $methods[PartnerOrder::PAY_INVOICE] = PartnerOrder::paymentMethodOptions()[PartnerOrder::PAY_INVOICE]
                . ' (' . $partner->credit_days . ' hari)';
        }

        if (empty($methods)) {
            return redirect()->route('mitra.cart')->with('toast_error', 'Belum ada metode pembayaran yang diaktifkan untuk mitra Anda. Hubungi apotek.');
        }

        return view('partners.portal.checkout', array_merge($this->apotekData(), [
            'partner'     => $partner,
            'cart'        => $cart,
            'methods'     => $methods,
            'priceLabel'  => PartnerPricingService::priceLabel($partner),
            'stockIssues' => $this->cartStockIssues($cart),
            'ppn'         => $this->orderTotalsSummary($partner, (float) $cart['subtotal']),
        ]));
    }

    public function checkout(Request $request)
    {
        $partner = $this->requireMitraPartner();
        $cart = PartnerCartService::lines($partner);

        if (empty($cart['items'])) {
            return redirect()->route('mitra.cart')->with('toast_error', 'Keranjang masih kosong.');
        }

        $stockIssues = $this->cartStockIssues($cart);
        if (!empty($stockIssues)) {
            $names = collect($stockIssues)->pluck('name')->take(2)->implode(', ');
            $suffix = count($stockIssues) > 2 ? ' dan lainnya' : '';

            return redirect()->route('mitra.cart')->with(
                'toast_error',
                "Tidak dapat checkout: qty melebihi stok untuk {$names}{$suffix}. Sesuaikan jumlah di keranjang."
            );
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

        $request->validate([
            'payment_method'  => ['required', Rule::in($allowed)],
            'shipping_address'=> 'required|string|max:2000',
            'pic_name'        => 'required|string|max:150',
            'pic_phone'       => 'required|string|max:30',
            'notes'           => 'nullable|string|max:2000',
        ], [
            'payment_method.required'   => 'Pilih metode pembayaran.',
            'payment_method.in'         => 'Metode pembayaran tidak diizinkan untuk akun mitra Anda.',
            'shipping_address.required' => 'Alamat pengiriman wajib diisi.',
            'pic_name.required'         => 'Nama PIC wajib diisi.',
            'pic_phone.required'        => 'Telepon PIC wajib diisi.',
        ]);

        try {
            $totals = $this->orderTotalsSummary($partner, (float) $cart['subtotal']);

            $order = DB::transaction(function () use ($request, $partner, $cart, $totals) {
                foreach ($cart['items'] as $line) {
                    /** @var Product $product */
                    $product = Product::lockForUpdate()->find($line['product']->id);
                    if (!$product || !$product->is_active || !$product->show_in_catalog) {
                        throw new \RuntimeException('Produk "' . $line['product']->name . '" tidak tersedia.');
                    }
                    if ($product->stock < $line['qty']) {
                        throw new \RuntimeException(
                            "Stok \"{$product->name}\" tidak mencukupi. Tersedia: {$product->stock}, diminta: {$line['qty']}."
                        );
                    }
                }

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
                    'subtotal'            => $cart['subtotal'],
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
                    'notes'               => $request->notes,
                    'due_date'            => $dueDate,
                ]);

                foreach ($cart['items'] as $line) {
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
            return back()->with('toast_error', $e->getMessage())->withInput();
        }

        PartnerCartService::clear();

        ActivityLogService::log(
            'CREATE',
            'PO Mitra',
            "PO {$order->order_no} diajukan oleh {$partner->name} — " . number_format((float) $order->total, 0, ',', '.')
        );

        NotificationService::triggerPartnerOrderSubmitted($order);

        return redirect()
            ->route('mitra.orders.show', $order)
            ->with('toast_success', 'Purchase Order berhasil diajukan: ' . $order->order_no);
    }
}
