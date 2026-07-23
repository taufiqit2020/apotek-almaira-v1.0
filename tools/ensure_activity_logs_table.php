<?php

/**
 * Pastikan tabel activity_logs ada (perbaikan backup & log aktivitas).
 * Jalankan: php tools/ensure_activity_logs_table.php
 */

require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

if (Schema::hasTable('activity_logs')) {
    $needsOld = ! Schema::hasColumn('activity_logs', 'old_data');
    $needsNew = ! Schema::hasColumn('activity_logs', 'new_data');

    if ($needsOld || $needsNew) {
        Schema::table('activity_logs', function (Blueprint $table) use ($needsOld, $needsNew) {
            if ($needsOld) {
                $table->json('old_data')->nullable()->after('user_agent');
            }
            if ($needsNew) {
                $table->json('new_data')->nullable()->after('old_data');
            }
        });
        echo "activity_logs: columns old_data/new_data added\n";
    } else {
        echo "activity_logs: OK (already exists)\n";
    }
    exit(0);
}

Schema::create('activity_logs', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
    $table->string('action', 100);
    $table->string('module', 100)->nullable();
    $table->text('description')->nullable();
    $table->string('ip_address', 45)->nullable();
    $table->text('user_agent')->nullable();
    $table->json('old_data')->nullable();
    $table->json('new_data')->nullable();
    $table->timestamp('created_at')->useCurrent();
});

echo "activity_logs: CREATED\n";
