<?php
/**
 * Import master JSON into current DB (Hostinger MySQL).
 * Run: php tools/import_master_on_hostinger.php [path-to-json]
 *
 * Preserves users/roles. Replaces listed master tables.
 */

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

$path = $argv[1] ?? storage_path('app/hostinger-master-export.json');
if (! is_file($path)) {
    fwrite(STDERR, "ERROR: JSON not found: {$path}\n");
    exit(1);
}

$payload = json_decode(file_get_contents($path), true);
if (! is_array($payload) || ! isset($payload['tables']) || ! is_array($payload['tables'])) {
    fwrite(STDERR, "ERROR: invalid export JSON\n");
    exit(1);
}

$order = [
    'categories',
    'units',
    'suppliers',
    'job_positions',
    'employees',
    'partners',
    'products',
    'settings',
];

echo "=== IMPORT MASTER → " . config('database.default') . " / " . config('database.connections.' . config('database.default') . '.database') . " ===\n";
echo "Source file: {$path}\n";
echo "Exported at: " . ($payload['exported_at'] ?? '-') . "\n";

DB::beginTransaction();
try {
    $driver = DB::getDriverName();
    if ($driver === 'mysql') {
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
    } elseif ($driver === 'sqlite') {
        DB::statement('PRAGMA foreign_keys = OFF');
    }

    // Clear dependent product refs first (fresh production should be empty, but be safe).
    foreach (['sale_items', 'purchase_items', 'stock_outs', 'stock_opnames', 'prescription_items', 'partner_order_items'] as $child) {
        if (Schema::hasTable($child) && Schema::hasColumn($child, 'product_id')) {
            if ($child === 'stock_opnames') {
                DB::table($child)->delete();
            } else {
                DB::table($child)->whereNotNull('product_id')->update(['product_id' => null]);
            }
        }
    }

    foreach (array_reverse($order) as $table) {
        if (! Schema::hasTable($table)) {
            echo "skip missing table: {$table}\n";
            continue;
        }
        if (! array_key_exists($table, $payload['tables'])) {
            echo "skip missing in export: {$table}\n";
            continue;
        }
        DB::table($table)->delete();
        echo "cleared {$table}\n";
    }

    foreach ($order as $table) {
        if (! Schema::hasTable($table) || ! isset($payload['tables'][$table])) {
            continue;
        }
        $rows = $payload['tables'][$table];
        if ($rows === []) {
            echo "{$table}: 0 rows\n";
            continue;
        }

        $columns = Schema::getColumnListing($table);
        $chunk = [];
        $inserted = 0;
        foreach ($rows as $row) {
            $clean = [];
            foreach ($columns as $col) {
                if (array_key_exists($col, $row)) {
                    $clean[$col] = $row[$col];
                }
            }
            if ($clean === []) {
                continue;
            }
            $chunk[] = $clean;
            if (count($chunk) >= 100) {
                DB::table($table)->insert($chunk);
                $inserted += count($chunk);
                $chunk = [];
            }
        }
        if ($chunk !== []) {
            DB::table($table)->insert($chunk);
            $inserted += count($chunk);
        }
        echo "{$table}: inserted {$inserted}\n";
    }

    if ($driver === 'mysql') {
        DB::statement('SET FOREIGN_KEY_CHECKS=1');
    } elseif ($driver === 'sqlite') {
        DB::statement('PRAGMA foreign_keys = ON');
    }

    DB::commit();
} catch (Throwable $e) {
    DB::rollBack();
    fwrite(STDERR, 'FAIL: ' . $e->getMessage() . "\n");
    exit(1);
}

echo "=== DONE ===\n";
foreach ($order as $table) {
    if (Schema::hasTable($table)) {
        echo "{$table}=" . DB::table($table)->count() . "\n";
    }
}
