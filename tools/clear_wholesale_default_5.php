<?php

/**
 * Set default HET Markup Grosir ke 0 (manual), tanpa fallback 5%.
 * Jalankan: php tools/clear_wholesale_default_5.php
 */

require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Setting;

Setting::set('product_wholesale_markup_default', '0');

echo 'product_wholesale_markup_default=' . Setting::get('product_wholesale_markup_default') . PHP_EOL;
echo 'wholesaleMarkupDefault()=' . Setting::wholesaleMarkupDefault() . PHP_EOL;
