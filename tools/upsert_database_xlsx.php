<?php
/**
 * Upsert master produk dari ../database.xlsx (tanpa wipe).
 * - Harga jual dari Excel tetap dipakai meski > HET
 * - Grosir dihitung otomatis dari markup
 *
 * Jalankan: php tools/upsert_database_xlsx.php
 */

use App\Models\Product;
use App\Services\ProductImportService;

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$candidates = array_filter([
    $argv[1] ?? null,
    realpath(__DIR__ . '/../../database.xlsx') ?: null,
    realpath(__DIR__ . '/../database.xlsx') ?: null,
    realpath(__DIR__ . '/../storage/app/database.xlsx') ?: null,
]);

$excelPath = null;
foreach ($candidates as $candidate) {
    if (is_string($candidate) && is_file($candidate)) {
        $excelPath = $candidate;
        break;
    }
}

if (! $excelPath) {
    fwrite(STDERR, "ERROR: File database.xlsx tidak ditemukan.\n");
    fwrite(STDERR, "Letakkan di folder induk proyek, root app, storage/app/, atau berikan path sebagai argumen.\n");
    exit(1);
}

echo "=== UPSERT MASTER PRODUK (data real) ===\n";
echo "Sumber Excel : {$excelPath}\n";
echo "DB connection: " . config('database.default') . "\n";
echo "DB database  : " . config('database.connections.' . config('database.default') . '.database') . "\n";

$before = Product::count();
echo "Produk sebelum: {$before}\n\n";

$service = new ProductImportService();
$result = $service->import($excelPath);

$after = Product::count();
$exceed = Product::active()->exceedsHet()->count();

echo "\n=== HASIL ===\n";
echo "success : {$result['success_count']}\n";
echo "failed  : {$result['failed_count']}\n";
echo "Produk sesudah: {$after}\n";
echo "Melebihi HET  : {$exceed}\n";

$sample = Product::active()->exceedsHet()->orderBy('name')->limit(8)->get(['code', 'name', 'sell_price', 'het_price', 'wholesale_price', 'het_markup']);
if ($sample->isNotEmpty()) {
    echo "\nContoh melebihi HET (jual tetap):\n";
    foreach ($sample as $p) {
        echo sprintf(
            "- %s | jual %s | HET %s | grosir %s | markup %s%%\n",
            $p->code,
            number_format((float) $p->sell_price, 0, ',', '.'),
            number_format((float) $p->het_price, 0, ',', '.'),
            number_format((float) $p->wholesale_price, 0, ',', '.'),
            (int) $p->het_markup
        );
    }
}

$norages = Product::where('code', 'INJ-0001')->orWhere('name', 'like', 'NORAGES%')->first();
if ($norages) {
    echo "\nCek NORAGES:\n";
    echo sprintf(
        "  code=%s jual=%s HET=%s grosir=%s exceeds=%s\n",
        $norages->code,
        number_format((float) $norages->sell_price, 0, ',', '.'),
        number_format((float) $norages->het_price, 0, ',', '.'),
        number_format((float) $norages->wholesale_price, 0, ',', '.'),
        $norages->exceedsHet() ? 'YES' : 'no'
    );
}

if (! empty($result['logs'])) {
    $fails = array_filter($result['logs'], fn ($l) => ($l['status'] ?? '') === 'failed');
    if ($fails) {
        echo "\nGagal:\n";
        foreach (array_slice($fails, 0, 15) as $f) {
            echo "- {$f['code']} {$f['name']}: {$f['message']}\n";
        }
    }
}

echo "\nDONE\n";
