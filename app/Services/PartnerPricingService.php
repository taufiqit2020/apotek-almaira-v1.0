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
     * Info harga untuk tampilan katalog (mendukung mode auto: eceran + hint grosir).
     *
     * @return array{primary: float, label: ?string, secondary: ?float, note: ?string}
     */
    public static function catalogPriceInfo(Product $product, ?Partner $partner): array
    {
        if (!$partner) {
            return [
                'primary'   => (float) $product->sell_price,
                'label'     => null,
                'secondary' => null,
                'note'      => null,
            ];
        }

        $eceran = (float) $product->sell_price;
        $grosir = (float) $product->wholesale_price;
        if ($grosir <= 0) {
            $grosir = $eceran;
        }

        return match ($partner->price_mode) {
            Partner::PRICE_GROSIR => [
                'primary'   => $grosir,
                'label'     => 'Grosir',
                'secondary' => $grosir !== $eceran ? $eceran : null,
                'note'      => $grosir !== $eceran ? 'Eceran Rp ' . number_format($eceran, 0, ',', '.') : null,
            ],
            Partner::PRICE_AUTO => [
                'primary'   => $eceran,
                'label'     => 'Eceran',
                'secondary' => $grosir,
                'note'      => 'Qty ≥ ' . self::AUTO_GROSIR_MIN_QTY . ': Rp ' . number_format($grosir, 0, ',', '.') . ' (grosir)',
            ],
            default => [
                'primary'   => $eceran,
                'label'     => 'Eceran',
                'secondary' => null,
                'note'      => null,
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
