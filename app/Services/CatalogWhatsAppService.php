<?php

namespace App\Services;

use App\Models\Product;

class CatalogWhatsAppService
{
    /**
     * Susun pesan WhatsApp yang rapi + tautan foto & detail produk.
     * (WhatsApp wa.me hanya mendukung teks; gambar dikirim via URL / Web Share.)
     */
    public static function buildMessage(
        Product $product,
        string $apotekName = 'Apotek Almaira',
        ?float $displayPrice = null,
        ?string $stockState = null,
    ): string {
        $product->loadMissing(['category', 'unit']);

        $price = $displayPrice ?? (float) $product->sell_price;
        $priceLabel = 'Rp '.number_format($price, 0, ',', '.');

        $badge = $product->stockBadge();
        $stockState ??= $badge['state'];
        $stockLabel = $badge['label'];
        if ($stockState === 'habis') {
            $stockLabel = 'Habis — mohon info ketersediaan';
        }

        $category = $product->category?->name ?? 'Umum';
        $unit = $product->unit?->name ?? 'pcs';
        $code = $product->code ?: '-';
        $detailUrl = route('catalog.show', $product);
        $imageUrl = $product->image_url;

        $lines = [
            "🌿 *{$apotekName}*",
            '━━━━━━━━━━━━━━━━',
            'Halo Admin, saya ingin memesan / menanyakan produk berikut:',
            '',
            "📦 *{$product->name}*",
            "🏷️ Kode: `{$code}`",
            "📂 Kategori: {$category}",
            "⚖️ Satuan: per {$unit}",
            "💰 Harga katalog: *{$priceLabel}*",
            "📊 Status: {$stockLabel}",
            '',
            '🖼️ *Foto produk:*',
            $imageUrl,
            '',
            '🔗 *Detail produk:*',
            $detailUrl,
            '',
            'Mohon konfirmasi ketersediaan dan langkah selanjutnya.',
            'Terima kasih 🙏',
        ];

        return implode("\n", $lines);
    }

    public static function waUrl(string $phoneRaw, string $message): string
    {
        $waNumber = preg_replace('/\D/', '', $phoneRaw);
        if (str_starts_with($waNumber, '0')) {
            $waNumber = '62'.substr($waNumber, 1);
        }

        return 'https://wa.me/'.$waNumber.'?text='.rawurlencode($message);
    }

    /**
     * Payload untuk tombol WA (teks + URL fallback + data share gambar).
     *
     * @return array{message: string, href: string, image_url: string, detail_url: string, product_name: string, wa_number: string}
     */
    public static function buttonPayload(
        Product $product,
        string $phoneRaw,
        string $apotekName = 'Apotek Almaira',
        ?float $displayPrice = null,
        ?string $stockState = null,
    ): array {
        $message = self::buildMessage($product, $apotekName, $displayPrice, $stockState);

        $waNumber = preg_replace('/\D/', '', $phoneRaw);
        if (str_starts_with($waNumber, '0')) {
            $waNumber = '62'.substr($waNumber, 1);
        }

        return [
            'message' => $message,
            'href' => self::waUrl($phoneRaw, $message),
            'image_url' => $product->image_url,
            'detail_url' => route('catalog.show', $product),
            'product_name' => $product->name,
            'wa_number' => $waNumber,
        ];
    }
}
