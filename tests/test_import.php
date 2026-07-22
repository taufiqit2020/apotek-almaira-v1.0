<?php

require __DIR__ . '/../vendor/autoload.php';

// Bootstrap Laravel app
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Services\ProductImportService;

$filePath = __DIR__ . '/../storage/app/test_import.xlsx';

if (!file_exists($filePath)) {
    echo "ERROR: Test file not found at: $filePath\n";
    exit(1);
}

echo "Testing import with file: $filePath\n";
echo "---\n";

try {
    $service = new ProductImportService();
    $result = $service->import($filePath);

    echo "SUCCESS COUNT: " . $result['success_count'] . "\n";
    echo "FAILED COUNT:  " . $result['failed_count'] . "\n";
    echo "---\n";
    echo "LOG DETAILS:\n";
    foreach ($result['logs'] as $log) {
        echo "[" . strtoupper($log['status']) . "] " . $log['code'] . " - " . $log['name'] . " => " . $log['message'] . "\n";
    }
} catch (\Throwable $e) {
    echo "EXCEPTION: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . " Line: " . $e->getLine() . "\n";
    echo "TRACE:\n" . $e->getTraceAsString() . "\n";
}
