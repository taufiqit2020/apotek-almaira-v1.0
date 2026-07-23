<?php
/**
 * Terapkan HET Markup Grosir 5% ke SEMUA produk aktif.
 * Harga grosir = HPP × 1.05 (tidak melebihi harga jual).
 *
 * Jalankan: php tools/apply_wholesale_markup_5.php
 */

use App\Models\Product;
use App\Services\ActivityLogService;

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$markup = 5;
$updated = 0;
$skipped = 0;
$clamped = 0;

echo "=== TERAPKAN MARKUP GROSIR {$markup}% KE SEMUA PRODUK ===\n";
echo 'DB: ' . config('database.default') . ' / ' . config('database.connections.' . config('database.default') . '.database') . "\n\n";

Product::query()
    ->orderBy('id')
    ->chunkById(200, function ($products) use ($markup, &$updated, &$skipped, &$clamped) {
        foreach ($products as $product) {
            $purchase = (float) ($product->purchase_price ?? 0);
            $sell = (float) ($product->sell_price ?? 0);

            if ($purchase <= 0) {
                $skipped++;
                continue;
            }

            $wholesale = Product::calcWholesaleFromPurchase($purchase, $markup, $sell);
            if ($sell > 0 && $wholesale >= $sell) {
                $clamped++;
            }

            $product->update([
                'wholesale_markup' => $markup,
                'wholesale_price' => $wholesale,
            ]);
            $updated++;
        }
    });

echo "Updated : {$updated}\n";
echo "Skipped : {$skipped} (HPP 0)\n";
echo "Clamped : {$clamped} (grosir ditutup ke jual karena melebihi)\n";

$sample = Product::orderBy('id')->limit(5)->get(['code', 'name', 'purchase_price', 'sell_price', 'wholesale_price', 'wholesale_markup']);
echo "\nContoh:\n";
foreach ($sample as $p) {
    echo sprintf(
        "- %s | beli %s | jual %s | grosir %s | markup %s%%\n",
        $p->code,
        number_format((float) $p->purchase_price, 0, ',', '.'),
        number_format((float) $p->sell_price, 0, ',', '.'),
        number_format((float) $p->wholesale_price, 0, ',', '.'),
        (int) $p->wholesale_markup
    );
}

ActivityLogService::updated(
    'Produk',
    "Terapkan markup grosir {$markup}% ke {$updated} produk",
    null,
    ['markup' => $markup, 'updated' => $updated, 'skipped' => $skipped]
);

echo "\nDONE\n";
