<?php

namespace App\Observers;

use App\Models\Product;
use App\Services\ProductLiveSync;

class ProductObserver
{
    public function saved(Product $product): void
    {
        ProductLiveSync::bump();
    }

    public function deleted(Product $product): void
    {
        ProductLiveSync::bump();
    }

    public function restored(Product $product): void
    {
        ProductLiveSync::bump();
    }
}
