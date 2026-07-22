<?php
/**
 * Hapus SEMUA master produk, lalu impor ulang dari ../database.xlsx
 *
 * Jalankan: php tools/reimport_database_xlsx.php
 */

use App\Models\Product;
use App\Services\ProductImportService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$excelPath = realpath(__DIR__ . '/../../database.xlsx');
if (! $excelPath || ! is_file($excelPath)) {
    fwrite(STDERR, "ERROR: File database.xlsx tidak ditemukan di folder induk proyek.\n");
    exit(1);
}

echo "=== REIMPORT MASTER PRODUK ===\n";
echo "Sumber Excel : {$excelPath}\n";
echo "DB connection: " . config('database.default') . "\n";
echo "DB database  : " . config('database.connections.' . config('database.default') . '.database') . "\n";

$before = Product::withTrashed()->count();
echo "Produk sebelum hapus (termasuk soft-delete): {$before}\n";

DB::beginTransaction();
try {
    // Nonaktifkan FK sementara agar wipe aman di SQLite/MySQL.
    $driver = DB::getDriverName();
    if ($driver === 'sqlite') {
        DB::statement('PRAGMA foreign_keys = OFF');
    } elseif ($driver === 'mysql') {
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
    }

    $childTables = [
        'sale_items',
        'purchase_items',
        'stock_outs',
        'stock_opnames',
        'prescription_items',
        'partner_order_items',
    ];

    foreach ($childTables as $table) {
        if (! Schema::hasTable($table)) {
            echo "- skip (tidak ada tabel): {$table}\n";
            continue;
        }
        if (! Schema::hasColumn($table, 'product_id')) {
            echo "- skip (tanpa product_id): {$table}\n";
            continue;
        }

        if ($table === 'stock_opnames') {
            $deleted = DB::table($table)->delete();
            echo "- hapus {$table}: {$deleted} baris\n";
        } else {
            $updated = DB::table($table)->whereNotNull('product_id')->update(['product_id' => null]);
            echo "- null-kan product_id di {$table}: {$updated} baris\n";
        }
    }

    // Hapus permanen semua produk (termasuk yang soft-deleted).
    $hardDeleted = Product::withTrashed()->forceDelete();
    // forceDelete on query builder returns bool in some versions; recount instead.
    $remaining = Product::withTrashed()->count();
    echo "- sisa produk setelah wipe: {$remaining}\n";

    if ($remaining > 0) {
        // Fallback hapus raw jika SoftDeletes menghalangi.
        DB::table('products')->delete();
        $remaining = Product::withTrashed()->count();
        echo "- fallback DB::table delete, sisa: {$remaining}\n";
    }

    if ($driver === 'sqlite') {
        try {
            DB::statement("DELETE FROM sqlite_sequence WHERE name = 'products'");
        } catch (Throwable $e) {
            // ignore jika tidak ada sqlite_sequence
        }
        DB::statement('PRAGMA foreign_keys = ON');
    } elseif ($driver === 'mysql') {
        DB::statement('SET FOREIGN_KEY_CHECKS=1');
        try {
            DB::statement('ALTER TABLE products AUTO_INCREMENT = 1');
        } catch (Throwable $e) {
            // ignore
        }
    }

    DB::commit();
    echo "Wipe selesai.\n";
} catch (Throwable $e) {
    DB::rollBack();
    if (($driver ?? null) === 'sqlite') {
        DB::statement('PRAGMA foreign_keys = ON');
    } elseif (($driver ?? null) === 'mysql') {
        DB::statement('SET FOREIGN_KEY_CHECKS=1');
    }
    fwrite(STDERR, 'ERROR wipe: ' . $e->getMessage() . "\n");
    exit(1);
}

echo "\nMulai impor...\n";
$service = new ProductImportService();
$result = $service->import($excelPath);

$after = Product::count();
$trashed = Product::onlyTrashed()->count();

echo "\n=== HASIL ===\n";
echo "Berhasil : {$result['success_count']}\n";
echo "Gagal    : {$result['failed_count']}\n";
echo "Produk aktif sekarang : {$after}\n";
echo "Produk soft-deleted   : {$trashed}\n";

$info = collect($result['logs'])->firstWhere('status', 'info');
if ($info) {
    echo "Deteksi  : {$info['message']}\n";
}

$failures = collect($result['logs'])->where('status', 'failed')->take(10);
if ($failures->isNotEmpty()) {
    echo "\nContoh gagal:\n";
    foreach ($failures as $f) {
        echo "- [{$f['code']}] {$f['name']}: {$f['message']}\n";
    }
}

$samples = Product::orderBy('id')->take(5)->get(['id', 'code', 'name', 'purchase_price', 'sell_price', 'wholesale_price', 'het_price', 'stock']);
echo "\nSample 5 produk:\n";
foreach ($samples as $p) {
    echo sprintf(
        "- #%d %s | %s | beli=%s jual=%s grosir=%s het=%s stok=%d\n",
        $p->id,
        $p->code,
        $p->name,
        number_format((float) $p->purchase_price, 0, ',', '.'),
        number_format((float) $p->sell_price, 0, ',', '.'),
        number_format((float) $p->wholesale_price, 0, ',', '.'),
        number_format((float) $p->het_price, 0, ',', '.'),
        (int) $p->stock
    );
}

$logPath = storage_path('app/imports/reimport-' . date('Ymd-His') . '.json');
if (! is_dir(dirname($logPath))) {
    mkdir(dirname($logPath), 0775, true);
}
file_put_contents($logPath, json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
echo "\nLog lengkap: {$logPath}\n";

if ($result['failed_count'] > 0 || $after <= 0) {
    exit(2);
}

echo "Selesai OK.\n";
exit(0);
