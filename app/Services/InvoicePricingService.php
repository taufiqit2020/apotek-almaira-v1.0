<?php

namespace App\Services;

use App\Models\Setting;

/**
 * Harga khusus pembayaran Invoice: naik dari harga jual (eceran).
 * Default +5%. Digunakan di POS CRM Invoice, PO Mitra Invoice, dan portal mitra.
 */
class InvoicePricingService
{
    public const DEFAULT_MARKUP_PERCENT = 5;

    public static function markupPercent(): int
    {
        $pct = (int) Setting::get('invoice_price_markup_percent', (string) self::DEFAULT_MARKUP_PERCENT);
        if ($pct < 0) {
            return 0;
        }
        if ($pct > 100) {
            return 100;
        }

        return $pct;
    }

    public static function isInvoicePayment(?string $method): bool
    {
        $m = strtolower(trim((string) $method));

        return in_array($m, ['invoice', 'pay_invoice'], true);
    }

    /** Harga unit invoice = round(harga jual × (1 + markup%/100)). */
    public static function unitFromSellPrice(float $sellPrice): float
    {
        $sell = max(0.0, $sellPrice);
        $pct = self::markupPercent();
        if ($sell <= 0 || $pct <= 0) {
            return (float) round($sell);
        }

        return (float) round($sell * (1 + $pct / 100));
    }

    /**
     * Jika metode invoice → harga dari jual + markup; selain itu kembalikan base.
     */
    public static function resolveUnitPrice(float $sellPrice, float $basePrice, ?string $paymentMethod): float
    {
        if (self::isInvoicePayment($paymentMethod)) {
            return self::unitFromSellPrice($sellPrice);
        }

        return (float) round(max(0, $basePrice));
    }
}
