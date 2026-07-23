<?php

namespace App\Services;

use App\Models\Partner;
use App\Models\Product;
use Illuminate\Support\Facades\Cache;

class ProductLiveSync
{
    public const CACHE_KEY = 'products_live_revision';

    public static function revision(): int
    {
        return (int) Cache::get(self::CACHE_KEY, 0);
    }

    public static function bump(): int
    {
        $rev = (int) now()->timestamp;
        Cache::forever(self::CACHE_KEY, $rev);

        return $rev;
    }

    /**
     * Snapshot ringan untuk patch UI tanpa reload penuh.
     *
     * @param  list<int>  $ids
     * @return array{revision: int, products: array<string, array<string, mixed>>}
     */
    public static function snapshot(array $ids, ?Partner $partner = null): array
    {
        $ids = array_values(array_unique(array_filter(array_map('intval', $ids))));
        $products = [];

        if ($ids === []) {
            return ['revision' => self::revision(), 'products' => []];
        }

        Product::query()
            ->whereIn('id', $ids)
            ->get()
            ->each(function (Product $product) use (&$products, $partner) {
                $badge = $product->stockBadge();
                $price = PartnerPricingService::catalogPriceInfo($product, $partner);
                $sell = (float) $product->sell_price;
                $grosir = (float) $product->wholesale_price;
                $beli = (float) $product->purchase_price;

                $products[(string) $product->id] = [
                    'id' => $product->id,
                    'name' => $product->name,
                    'is_active' => (bool) $product->is_active,
                    'show_in_catalog' => (bool) $product->show_in_catalog,
                    'stock' => (int) $product->stock,
                    'stock_min' => (int) $product->stock_min,
                    'unit' => $product->unit?->name ?? 'pcs',
                    'purchase_price' => $beli,
                    'sell_price' => $sell,
                    'wholesale_price' => $grosir,
                    'purchase_formatted' => self::rp($beli),
                    'sell_formatted' => self::rp($sell),
                    'wholesale_formatted' => $grosir > 0 ? self::rp($grosir) : '—',
                    'exceeds_het' => $product->exceedsHet(),
                    'stock_state' => $badge['state'],
                    'stock_short' => $badge['short'],
                    'stock_label' => $badge['label'],
                    'stock_chip' => $badge['chip'],
                    'stock_dot' => $badge['dot'],
                    'price_primary' => (float) $price['primary'],
                    'price_primary_formatted' => self::rp((float) $price['primary']),
                    'price_primary_label' => $price['label'],
                    'price_secondary' => $price['secondary'],
                    'price_secondary_formatted' => $price['secondary'] !== null ? self::rp((float) $price['secondary']) : null,
                    'price_secondary_label' => $price['secondary_label'] ?? null,
                    'price_note' => $price['note'],
                ];
            });

        return [
            'revision' => self::revision(),
            'products' => $products,
        ];
    }

    private static function rp(float $amount): string
    {
        return 'Rp '.number_format(round($amount), 0, ',', '.');
    }
}
