<?php
/**
 * Export master data from local DB to JSON for Hostinger import.
 * Run: php tools/export_master_for_hostinger.php
 */

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

$tables = [
    'categories',
    'units',
    'products',
    'settings',
    'job_positions',
    'employees',
    'partners',
    'suppliers',
];

$payload = [
    'exported_at' => now()->toIso8601String(),
    'source' => config('database.default'),
    'tables' => [],
];

foreach ($tables as $table) {
    if (! Schema::hasTable($table)) {
        echo "skip missing: {$table}\n";
        continue;
    }
    $rows = DB::table($table)->get()->map(fn ($r) => (array) $r)->all();
    $payload['tables'][$table] = $rows;
    echo "{$table}: " . count($rows) . "\n";
}

$outDir = storage_path('app');
if (! is_dir($outDir)) {
    mkdir($outDir, 0775, true);
}

$out = $outDir . DIRECTORY_SEPARATOR . 'hostinger-master-export.json';
file_put_contents($out, json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
echo "Wrote: {$out}\n";
echo 'Size: ' . round(filesize($out) / 1024, 1) . " KB\n";
