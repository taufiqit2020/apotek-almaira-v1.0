<?php
/**
 * Terapkan HET Markup Grosir 5% ke SEMUA produk.
 * Harga grosir = Harga jual − 5% (bukan dari harga beli).
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

echo "=== TERAPKAN MARKUP GROSIR {$markup}% DARI HARGA JUAL ===\n";
echo 'DB: ' . config('database.default') . ' / ' . config('database.connections.' . config('database.default') . '.database') . "\n\n";

Product::query()
    ->orderBy('id')
    ->chunkById(200, function ($products) use ($markup, &$updated, &$skipped) {
        foreach ($products as $product) {
            $sell = (float) ($product->sell_price ?? 0);
            if ($sell <= 0) {
                $skipped++;
                continue;
            }

            $wholesale = Product::calcWholesaleFromSell($sell, $markup);
            $product->update([
                'wholesale_markup' => $markup,
                'wholesale_price' => $wholesale,
            ]);
            $updated++;
        }
    });

echo "Updated : {$updated}\n";
echo "Skipped : {$skipped} (harga jual 0)\n";

$sample = Product::orderBy('id')->limit(5)->get(['code', 'name', 'purchase_price', 'sell_price', 'wholesale_price', 'wholesale_markup']);
echo "\nContoh (grosir = jual − {$markup}%):\n";
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
    "Terapkan markup grosir {$markup}% dari harga jual ke {$updated} produk",
    null,
    ['markup' => $markup, 'updated' => $updated, 'skipped' => $skipped, 'base' => 'sell_price']
);

echo "\nDONE\n";
