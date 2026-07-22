<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use App\Models\Setting;
use App\Services\PartnerPricingService;
use Illuminate\Support\Facades\Auth;

class CatalogController extends Controller
{
    /**
     * Halaman E-Catalog publik — tanpa login, bisa dibagikan ke pelanggan.
     * Pencarian & filter kategori ditangani secara live oleh Livewire\Catalog\CatalogGrid.
     */
    public function index()
    {
        $apotekName = Setting::get('apotek_name', 'Apotek Almaira');
        $apotekAddress = Setting::get('apotek_address', 'Jl. Panglima Batur No. 16, Kel. Komet, Kec. Banjarbaru Utara, Kota Banjarbaru, Kalsel 70714');
        $apotekPhone = Setting::get('apotek_phone', '0851-6665-7070');

        $categories = Category::active()
            ->whereHas('products', fn ($q) => $q->active()->inCatalog())
            ->orderBy('name')
            ->get();

        return view('catalog.index', compact('apotekName', 'apotekAddress', 'apotekPhone', 'categories'));
    }

    /**
     * Detail produk publik dari e-catalog.
     */
    public function show(Product $product)
    {
        abort_unless($product->is_active && $product->show_in_catalog, 404);

        $product->load(['category', 'unit']);

        $apotekName = Setting::get('apotek_name', 'Apotek Almaira');
        $apotekAddress = Setting::get('apotek_address', 'Jl. Panglima Batur No. 16, Kel. Komet, Kec. Banjarbaru Utara, Kota Banjarbaru, Kalsel 70714');
        $apotekPhone = Setting::get('apotek_phone', '0851-6665-7070');

        $related = Product::with(['category', 'unit'])
            ->active()
            ->inCatalog()
            ->where('id', '!=', $product->id)
            ->when($product->category_id, fn ($q) => $q->where('category_id', $product->category_id))
            ->orderBy('name')
            ->limit(4)
            ->get();

        $partner = null;
        $user = Auth::user();
        if ($user && $user->isMitra() && $user->partner?->isApproved()) {
            $partner = $user->partner;
        }
        $displayPrice = PartnerPricingService::displayPrice($product, $partner);
        $priceLabel = PartnerPricingService::priceLabel($partner);

        return view('catalog.show', compact(
            'product', 'related', 'apotekName', 'apotekAddress', 'apotekPhone',
            'partner', 'displayPrice', 'priceLabel'
        ));
    }
}
