<?php

namespace App\Services;

use App\Models\Partner;
use App\Models\Product;

class PartnerPricingService
{
    public const AUTO_GROSIR_MIN_QTY = 10;

    /**
     * @return array{price_type: string, unit_price: float}
     */
    public static function resolve(Product $product, Partner $partner, int $qty = 1): array
    {
        $mode = $partner->price_mode ?: Partner::PRICE_ECERAN;
        $eceran = (float) $product->sell_price;
        $grosir = (float) $product->wholesale_price;
        if ($grosir <= 0) {
            $grosir = $eceran;
        }

        if ($mode === Partner::PRICE_GROSIR) {
            return ['price_type' => 'grosir', 'unit_price' => $grosir];
        }

        if ($mode === Partner::PRICE_AUTO) {
            if ($qty >= self::AUTO_GROSIR_MIN_QTY) {
                return ['price_type' => 'grosir', 'unit_price' => $grosir];
            }
            return ['price_type' => 'eceran', 'unit_price' => $eceran];
        }

        return ['price_type' => 'eceran', 'unit_price' => $eceran];
    }

    public static function displayPrice(Product $product, ?Partner $partner): float
    {
        if (!$partner) {
            return (float) $product->sell_price;
        }

        return self::resolve($product, $partner, 1)['unit_price'];
    }

    /**
     * Info harga untuk tampilan katalog (eceran + grosir).
     *
     * @return array{
     *   primary: float,
     *   label: ?string,
     *   secondary: ?float,
     *   secondary_label: ?string,
     *   note: ?string
     * }
     */
    public static function catalogPriceInfo(Product $product, ?Partner $partner): array
    {
        $eceran = (float) $product->sell_price;
        $grosir = (float) $product->wholesale_price;
        $hasGrosir = $grosir > 0;

        if (! $partner) {
            return [
                'primary' => $eceran,
                'label' => 'Eceran',
                'secondary' => $hasGrosir ? $grosir : null,
                'secondary_label' => $hasGrosir ? 'Grosir' : null,
                'note' => null,
            ];
        }

        return match ($partner->price_mode) {
            Partner::PRICE_GROSIR => [
                'primary' => $hasGrosir ? $grosir : $eceran,
                'label' => 'Grosir',
                'secondary' => $eceran > 0 && (! $hasGrosir || abs($eceran - $grosir) > 0.009) ? $eceran : null,
                'secondary_label' => 'Eceran',
                'note' => null,
            ],
            Partner::PRICE_AUTO => [
                'primary' => $eceran,
                'label' => 'Eceran',
                'secondary' => $hasGrosir ? $grosir : null,
                'secondary_label' => $hasGrosir ? 'Grosir' : null,
                'note' => $hasGrosir
                    ? 'Qty ≥ '.self::AUTO_GROSIR_MIN_QTY.' otomatis pakai harga grosir'
                    : null,
            ],
            default => [
                'primary' => $eceran,
                'label' => 'Eceran',
                'secondary' => $hasGrosir ? $grosir : null,
                'secondary_label' => $hasGrosir ? 'Grosir' : null,
                'note' => null,
            ],
        };
    }

    public static function priceLabel(?Partner $partner): string
    {
        if (!$partner) {
            return 'Eceran';
        }

        return match ($partner->price_mode) {
            Partner::PRICE_GROSIR => 'Grosir',
            Partner::PRICE_AUTO   => 'Otomatis (qty ≥ ' . self::AUTO_GROSIR_MIN_QTY . ' → grosir)',
            default               => 'Eceran',
        };
    }
}
