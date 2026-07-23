<?php

namespace App\Livewire\Products;

use App\Models\Category;
use App\Models\Product;
use App\Services\ActivityLogService;
use App\Services\ProductLiveSync;
use Livewire\Attributes\On;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class ProductTable extends Component
{
    use WithPagination;

    #[Url(as: 'q', keep: true)]
    public string $search = '';

    #[Url(as: 'cat', keep: true)]
    public string $categoryFilter = '';

    #[Url(as: 'status', keep: true)]
    public string $statusFilter = 'active';

    public int $perPage = 10;

    /** @var array<int> ID produk yang sedang dicentang untuk aksi massal */
    public array $selected = [];

    /** Persen penyesuaian harga massal (boleh negatif untuk menurunkan). */
    public string $bulkPricePercent = '10';

    /**
     * Markup grosir massal (%).
     * Jika diisi: Sync Grosir menerapkan nilai ini ke semua produk terpilih.
     * Jika kosong: pakai wholesale_markup masing-masing produk.
     */
    public string $bulkWholesaleMarkup = '';

    public function mount(): void
    {
        $default = (int) \App\Models\Setting::wholesaleMarkupDefault();
        if ($default > 0) {
            $this->bulkWholesaleMarkup = (string) $default;
        }
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
        $this->selected = [];
    }

    public function updatedCategoryFilter(): void
    {
        $this->resetPage();
        $this->selected = [];
    }

    public function updatedStatusFilter(): void
    {
        $this->resetPage();
        $this->selected = [];
    }

    public function toggleSelectPage(): void
    {
        $ids = $this->currentPageIds();
        if ($this->isAllOnPageSelected($ids)) {
            $this->selected = array_values(array_diff($this->selected, $ids));
        } else {
            $this->selected = array_values(array_unique(array_merge($this->selected, $ids)));
        }
    }

    public function toggleSelected(int $productId): void
    {
        if (in_array($productId, $this->selected, true)) {
            $this->selected = array_values(array_diff($this->selected, [$productId]));
        } else {
            $this->selected = array_values([...$this->selected, $productId]);
        }
    }

    /** Centang semua produk sesuai filter saat ini (bukan hanya halaman aktif). */
    public function selectAllFiltered(): void
    {
        $ids = $this->buildQuery()->orderBy('id')->limit(5000)->pluck('id')->map(fn ($id) => (int) $id)->all();
        $this->selected = $ids;

        $this->dispatch('toast', type: 'info', message: count($ids).' produk dipilih sesuai filter saat ini.');
    }

    public function clearFilters(): void
    {
        $this->search = '';
        $this->categoryFilter = '';
        $this->statusFilter = 'active';
        $this->resetPage();
        $this->selected = [];
    }

    public function clearSelection(): void
    {
        $this->selected = [];
    }

    /**
     * Tampil/sembunyikan satu produk di E-Catalog (toggle cepat dari tabel).
     */
    public function toggleCatalog(int $productId): void
    {
        $product = Product::find($productId);
        if (! $product) {
            return;
        }

        $product->update(['show_in_catalog' => ! $product->show_in_catalog]);

        $this->dispatch('toast', type: 'success', message: $product->show_in_catalog
            ? "{$product->name} tampil di E-Catalog"
            : "{$product->name} disembunyikan dari E-Catalog");
        $this->notifyLiveViews();
    }

    /**
     * Aksi massal: tampilkan produk yang dicentang ke E-Catalog.
     */
    public function bulkShowCatalog(): void
    {
        if (empty($this->selected)) {
            return;
        }
        $count = Product::whereIn('id', $this->selected)->update(['show_in_catalog' => true]);
        $this->dispatch('toast', type: 'success', message: "{$count} produk ditampilkan di E-Catalog");
        $this->notifyLiveViews();
        $this->clearSelection();
    }

    /**
     * Aksi massal: sembunyikan produk yang dicentang dari E-Catalog.
     */
    public function bulkHideCatalog(): void
    {
        if (empty($this->selected)) {
            return;
        }
        $count = Product::whereIn('id', $this->selected)->update(['show_in_catalog' => false]);
        $this->dispatch('toast', type: 'success', message: "{$count} produk disembunyikan dari E-Catalog");
        $this->notifyLiveViews();
        $this->clearSelection();
    }

    /**
     * Naik/turun harga jual + grosir untuk produk terpilih.
     * Jika Markup Grosir massal dipilih, grosir dihitung ulang = jual − markup%.
     */
    public function bulkAdjustPrices(): void
    {
        if (empty($this->selected)) {
            $this->dispatch('toast', type: 'warning', message: 'Centang produk terlebih dahulu, atau gunakan "Pilih semua hasil filter".');

            return;
        }

        $percent = (float) str_replace(',', '.', trim($this->bulkPricePercent));
        if ($percent == 0.0 || $percent < -90 || $percent > 500) {
            $this->dispatch('toast', type: 'error', message: 'Persentase tidak valid. Gunakan nilai selain 0, antara -90% s/d 500%.');

            return;
        }

        $factor = 1 + ($percent / 100);
        $updated = 0;
        $skipped = 0;
        $exceedHet = 0;
        $chosenMarkup = (int) $this->bulkWholesaleMarkup;

        Product::whereIn('id', $this->selected)
            ->orderBy('id')
            ->chunkById(200, function ($products) use ($factor, $chosenMarkup, &$updated, &$skipped, &$exceedHet) {
                foreach ($products as $product) {
                    $sell = (float) $product->sell_price;
                    $wholesale = (float) $product->wholesale_price;
                    $wsMarkup = $chosenMarkup > 0
                        ? $chosenMarkup
                        : (int) ($product->wholesale_markup ?? 0);

                    if ($sell <= 0 && $wholesale <= 0) {
                        $skipped++;
                        continue;
                    }

                    $newSell = $sell > 0 ? (float) round($sell * $factor) : $sell;
                    if ($wsMarkup > 0 && $newSell > 0) {
                        $newWholesale = Product::calcWholesaleFromSell($newSell, $wsMarkup);
                    } else {
                        $newWholesale = $wholesale > 0 ? (float) round($wholesale * $factor) : $wholesale;
                    }

                    $normalized = Product::normalizeSellAgainstHet(
                        $newSell,
                        $newWholesale,
                        (float) ($product->het_price ?? 0),
                    );

                    if (! empty($normalized['exceeds_het'])) {
                        $exceedHet++;
                    }

                    $payload = [
                        'sell_price' => $normalized['sell_price'],
                        'wholesale_price' => $normalized['wholesale_price'],
                    ];
                    if ($chosenMarkup > 0) {
                        $payload['wholesale_markup'] = $chosenMarkup;
                    }

                    $product->update($payload);
                    $updated++;
                }
            });

        $sign = $percent > 0 ? '+'.$percent : (string) $percent;
        $msg = "Harga {$updated} produk disesuaikan {$sign}% (jual & grosir).";
        if ($chosenMarkup > 0) {
            $msg .= " Markup grosir diset {$chosenMarkup}%.";
        }
        if ($skipped > 0) {
            $msg .= " {$skipped} dilewati (harga 0).";
        }
        if ($exceedHet > 0) {
            $msg .= " {$exceedHet} tetap melebihi HET (harga jual tetap dipakai).";
        }

        ActivityLogService::updated(
            'Produk',
            "Bulk harga {$sign}% pada {$updated} produk",
            null,
            ['percent' => $percent, 'wholesale_markup' => $chosenMarkup, 'updated' => $updated, 'exceed_het' => $exceedHet, 'ids_count' => count($this->selected)]
        );

        $this->dispatch('toast', type: 'success', message: $msg);
        $this->notifyLiveViews();
        $this->clearSelection();
    }

    /** Terapkan markup grosir pilihan ke harga jual → harga grosir (konsisten). */
    public function syncWholesalePrices(): void
    {
        if (empty($this->selected)) {
            $this->dispatch('toast', type: 'warning', message: 'Centang produk terlebih dahulu.');

            return;
        }

        $chosenMarkup = (int) $this->bulkWholesaleMarkup;
        $allowed = \App\Models\Setting::wholesaleMarkupOptions();
        if ($chosenMarkup > 0 && ! in_array($chosenMarkup, $allowed, true)) {
            $this->dispatch('toast', type: 'error', message: 'Markup grosir tidak ada di opsi Pengaturan (1–30%).');

            return;
        }

        $synced = 0;
        $skipped = 0;
        $fallbackMarkup = \App\Models\Setting::wholesaleMarkupDefault();

        Product::whereIn('id', $this->selected)
            ->orderBy('id')
            ->chunkById(200, function ($products) use (&$synced, &$skipped, $chosenMarkup, $fallbackMarkup) {
                foreach ($products as $product) {
                    $sell = (float) $product->sell_price;
                    if ($sell <= 0) {
                        $skipped++;
                        continue;
                    }

                    // Prioritas: pilihan massal → markup produk → default pengaturan
                    if ($chosenMarkup > 0) {
                        $markup = $chosenMarkup;
                    } else {
                        $markup = (int) ($product->wholesale_markup ?? 0);
                        if ($markup <= 0) {
                            $markup = $fallbackMarkup;
                        }
                    }

                    if ($markup <= 0) {
                        $skipped++;
                        continue;
                    }

                    $wholesale = Product::calcWholesaleFromSell($sell, $markup);
                    $normalized = Product::normalizeSellAgainstHet(
                        $sell,
                        $wholesale,
                        (float) ($product->het_price ?? 0),
                    );

                    $product->update([
                        'wholesale_markup' => $markup,
                        'wholesale_price' => $normalized['wholesale_price'],
                    ]);
                    $synced++;
                }
            });

        ActivityLogService::updated(
            'Produk',
            "Sync grosir pada {$synced} produk",
            null,
            [
                'synced' => $synced,
                'skipped' => $skipped,
                'ids_count' => count($this->selected),
                'chosen_markup' => $chosenMarkup,
                'fallback_markup' => $fallbackMarkup,
            ]
        );

        if ($synced > 0) {
            $label = $chosenMarkup > 0 ? "{$chosenMarkup}%" : 'markup produk/pengaturan';
            $message = "{$synced} produk: harga grosir = jual − {$label}.";
            if ($skipped > 0) {
                $message .= " {$skipped} dilewati.";
            }
        } else {
            $message = 'Tidak ada produk yang di-sync. Pilih Markup Grosir (1–30%) di bar aksi, lalu klik Sync Grosir lagi.';
        }

        $this->dispatch(
            'toast',
            type: $synced > 0 ? 'success' : 'warning',
            message: $message
        );
        $this->notifyLiveViews();
        $this->clearSelection();
    }

    /** @deprecated Alias untuk kompatibilitas tombol lama. */
    public function fixSelectedAgainstHet(): void
    {
        $this->syncWholesalePrices();
    }

    /** Soft refresh saat produk berubah di tab/halaman lain. */
    #[On('products-live-refresh')]
    public function softLiveRefresh(): void
    {
        // Properti filter & seleksi tetap — hanya render ulang data terbaru.
    }

    private function notifyLiveViews(): void
    {
        ProductLiveSync::bump();
        $this->js('window.AlmairaLiveSync && window.AlmairaLiveSync.notify()');
    }

    private function currentPageIds(): array
    {
        return $this->buildQuery()->select('id')
            ->forPage($this->getPage(), $this->perPage)
            ->pluck('id')
            ->toArray();
    }

    private function isAllOnPageSelected(array $currentIds): bool
    {
        return count($currentIds) > 0 && count(array_diff($currentIds, $this->selected)) === 0;
    }

    private function buildQuery()
    {
        $query = Product::with(['category', 'unit', 'supplier'])->latest();

        $query->searchKeyword($this->search, 'ops');

        if ($this->categoryFilter) {
            $query->where('category_id', $this->categoryFilter);
        }

        if ($this->statusFilter === 'active') {
            $query->where('is_active', true);
        } elseif ($this->statusFilter === 'inactive') {
            $query->where('is_active', false);
        } elseif ($this->statusFilter === 'low_stock') {
            $query->where('is_active', true)->whereColumn('stock', '<=', 'stock_min');
        } elseif ($this->statusFilter === 'exceed_het') {
            $query->where('is_active', true)->exceedsHet();
        }

        return $query;
    }

    public function render()
    {
        $products = $this->buildQuery()->paginate($this->perPage);
        $categories = Category::active()->orderBy('name')->get();
        $lowStockCount = Product::active()->lowStock()->count();
        $catalogCount = Product::active()->inCatalog()->count();
        $exceedHetCount = Product::active()->exceedsHet()->count();
        $wholesaleMarkupOptions = \App\Models\Setting::wholesaleMarkupOptions();

        $currentIds = $products->pluck('id')->toArray();
        $isAllOnPageSelected = $this->isAllOnPageSelected($currentIds);

        return view('livewire.products.product-table', compact(
            'products',
            'categories',
            'lowStockCount',
            'catalogCount',
            'exceedHetCount',
            'isAllOnPageSelected',
            'wholesaleMarkupOptions',
        ));
    }
}
