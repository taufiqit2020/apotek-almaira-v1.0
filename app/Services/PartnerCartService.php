<?php

namespace App\Services;

use App\Models\Partner;
use App\Models\Product;
use Illuminate\Support\Facades\Session;

class PartnerCartService
{
    public const SESSION_KEY = 'mitra_cart';

    public static function all(): array
    {
        return Session::get(self::SESSION_KEY, []);
    }

    public static function count(): int
    {
        return (int) array_sum(array_column(self::all(), 'qty'));
    }

    public static function add(int $productId, int $qty = 1): void
    {
        $qty = max(1, $qty);
        $cart = self::all();
        $cart[$productId] = [
            'product_id' => $productId,
            'qty'        => ($cart[$productId]['qty'] ?? 0) + $qty,
        ];
        Session::put(self::SESSION_KEY, $cart);
    }

    public static function update(int $productId, int $qty): void
    {
        $cart = self::all();
        if ($qty <= 0) {
            unset($cart[$productId]);
        } else {
            $cart[$productId] = ['product_id' => $productId, 'qty' => $qty];
        }
        Session::put(self::SESSION_KEY, $cart);
    }

    public static function remove(int $productId): void
    {
        $cart = self::all();
        unset($cart[$productId]);
        Session::put(self::SESSION_KEY, $cart);
    }

    public static function clear(): void
    {
        Session::forget(self::SESSION_KEY);
    }

    /**
     * @return array{items: array, subtotal: float, count: int}
     */
    public static function lines(Partner $partner): array
    {
        $cart = self::all();
        if (empty($cart)) {
            return ['items' => [], 'subtotal' => 0.0, 'count' => 0];
        }

        $ids = array_keys($cart);
        $products = Product::with('unit')
            ->whereIn('id', $ids)
            ->active()
            ->inCatalog()
            ->get()
            ->keyBy('id');

        $items = [];
        $subtotal = 0.0;
        $count = 0;

        foreach ($cart as $productId => $row) {
            $product = $products->get($productId);
            if (!$product) {
                continue;
            }

            $qty = max(1, (int) ($row['qty'] ?? 1));
            $priced = PartnerPricingService::resolve($product, $partner, $qty);
            $lineSubtotal = $priced['unit_price'] * $qty;
            $subtotal += $lineSubtotal;
            $count += $qty;

            $items[] = [
                'product'     => $product,
                'product_id'  => $product->id,
                'qty'         => $qty,
                'price_type'  => $priced['price_type'],
                'unit_price'  => $priced['unit_price'],
                'sell_price'  => (float) $product->sell_price,
                'subtotal'    => $lineSubtotal,
            ];
        }

        return [
            'items'    => $items,
            'subtotal' => $subtotal,
            'count'    => $count,
        ];
    }

    /**
     * Harga keranjang untuk pembayaran Invoice: selalu dari harga jual + markup.
     *
     * @param  array{items: array, subtotal: float, count: int}  $cart
     * @return array{items: array, subtotal: float, count: int}
     */
    public static function applyInvoicePricing(array $cart): array
    {
        $items = [];
        $subtotal = 0.0;
        $count = 0;

        foreach ($cart['items'] ?? [] as $line) {
            $product = $line['product'] ?? null;
            $qty = max(1, (int) ($line['qty'] ?? 1));
            $sell = (float) ($line['sell_price'] ?? ($product?->sell_price ?? 0));
            $unit = InvoicePricingService::unitFromSellPrice($sell);
            $lineSubtotal = $unit * $qty;
            $subtotal += $lineSubtotal;
            $count += $qty;

            $items[] = array_merge($line, [
                'price_type'     => 'eceran',
                'unit_price'     => $unit,
                'sell_price'     => $sell,
                'subtotal'       => $lineSubtotal,
                'invoice_priced' => true,
            ]);
        }

        return [
            'items'    => $items,
            'subtotal' => $subtotal,
            'count'    => $count,
        ];
    }
}
