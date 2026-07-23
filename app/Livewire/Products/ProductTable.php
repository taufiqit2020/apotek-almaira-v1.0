<?php

namespace App\Livewire\Products;

use App\Models\Category;
use App\Models\Product;
use App\Services\ActivityLogService;
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
        $this->clearSelection();
    }

    /**
     * Naik/turun harga jual + grosir untuk produk terpilih.
     * Harga beli, HET, dan markup tidak diubah. Rasio grosir relatif tetap (× faktor yang sama).
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
        $hetAdjusted = 0;

        Product::whereIn('id', $this->selected)
            ->orderBy('id')
            ->chunkById(200, function ($products) use ($factor, &$updated, &$skipped, &$hetAdjusted) {
                foreach ($products as $product) {
                    $sell = (float) $product->sell_price;
                    $wholesale = (float) $product->wholesale_price;

                    if ($sell <= 0 && $wholesale <= 0) {
                        $skipped++;
                        continue;
                    }

                    $newSell = $sell > 0 ? (float) round($sell * $factor) : $sell;
                    $newWholesale = $wholesale > 0 ? (float) round($wholesale * $factor) : $wholesale;

                    $normalized = Product::normalizeSellAgainstHet(
                        $newSell,
                        $newWholesale,
                        (float) ($product->het_price ?? 0),
                    );

                    if ($normalized['adjusted'] && $newSell > (float) ($product->het_price ?? 0)) {
                        $hetAdjusted++;
                    }

                    $product->update([
                        'sell_price' => $normalized['sell_price'],
                        'wholesale_price' => $normalized['wholesale_price'],
                    ]);
                    $updated++;
                }
            });

        $sign = $percent > 0 ? '+'.$percent : (string) $percent;
        $msg = "Harga {$updated} produk disesuaikan {$sign}% (jual & grosir).";
        if ($skipped > 0) {
            $msg .= " {$skipped} dilewati (harga 0).";
        }
        if ($hetAdjusted > 0) {
            $msg .= " {$hetAdjusted} jual otomatis diturunkan ke HET.";
        }

        ActivityLogService::updated(
            'Produk',
            "Bulk harga {$sign}% pada {$updated} produk",
            null,
            ['percent' => $percent, 'updated' => $updated, 'het_adjusted' => $hetAdjusted, 'ids_count' => count($this->selected)]
        );

        $this->dispatch('toast', type: 'success', message: $msg);
        $this->clearSelection();
    }

    /** Perbaiki harga jual produk terpilih yang melebihi HET (tutup ke HET). */
    public function fixSelectedAgainstHet(): void
    {
        if (empty($this->selected)) {
            // Jika filter melebihi HET & belum ada centang → pilih semua hasil filter dulu
            if ($this->statusFilter === 'exceed_het') {
                $this->selectAllFiltered();
            }
            if (empty($this->selected)) {
                $this->dispatch('toast', type: 'warning', message: 'Centang produk yang ingin diperbaiki, atau filter Melebihi HET lalu klik lagi.');

                return;
            }
        }

        $fixed = 0;
        Product::whereIn('id', $this->selected)
            ->orderBy('id')
            ->chunkById(200, function ($products) use (&$fixed) {
                foreach ($products as $product) {
                    if (! $product->exceedsHet()) {
                        continue;
                    }
                    $normalized = Product::normalizeSellAgainstHet(
                        (float) $product->sell_price,
                        (float) ($product->wholesale_price ?? 0),
                        (float) ($product->het_price ?? 0),
                    );
                    if ($normalized['adjusted']) {
                        $product->update([
                            'sell_price' => $normalized['sell_price'],
                            'wholesale_price' => $normalized['wholesale_price'],
                        ]);
                        $fixed++;
                    }
                }
            });

        ActivityLogService::updated(
            'Produk',
            "Perbaiki HET pada {$fixed} produk",
            null,
            ['fixed' => $fixed, 'ids_count' => count($this->selected)]
        );

        $this->dispatch(
            'toast',
            type: $fixed > 0 ? 'success' : 'info',
            message: $fixed > 0
                ? "{$fixed} produk diperbaiki: harga jual disesuaikan ke HET."
                : 'Tidak ada produk terpilih yang melebihi HET.'
        );
        $this->clearSelection();
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

        $currentIds = $products->pluck('id')->toArray();
        $isAllOnPageSelected = $this->isAllOnPageSelected($currentIds);

        return view('livewire.products.product-table', compact(
            'products',
            'categories',
            'lowStockCount',
            'catalogCount',
            'exceedHetCount',
            'isAllOnPageSelected',
        ));
    }
}
