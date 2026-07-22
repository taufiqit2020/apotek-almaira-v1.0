<?php

namespace App\Livewire\Catalog;

use App\Models\Category;
use App\Models\Product;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class CatalogGrid extends Component
{
    use WithPagination;

    #[Url(as: 'q', keep: true)]
    public string $search = '';

    #[Url(as: 'cat', keep: true)]
    public string $categoryFilter = '';

    public int $perPage = 10;

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedCategoryFilter(): void
    {
        $this->resetPage();
    }

    public function selectCategory(string $categoryId): void
    {
        $this->categoryFilter = $this->categoryFilter === $categoryId ? '' : $categoryId;
        $this->resetPage();
    }

    public function resetFilters(): void
    {
        $this->search = '';
        $this->categoryFilter = '';
        $this->resetPage();
    }

    public function render()
    {
        $query = Product::with(['category', 'unit'])
            ->active()
            ->inCatalog();

        $query->searchKeyword($this->search, 'catalog');

        if ($this->categoryFilter) {
            $query->where('category_id', $this->categoryFilter);
        }

        $products = $query
            ->orderBy('catalog_order')
            ->orderBy('name')
            ->paginate($this->perPage);

        $categories = Category::active()
            ->whereHas('products', fn ($q) => $q->active()->inCatalog())
            ->orderBy('name')
            ->get();

        $totalCatalog = Product::active()->inCatalog()->count();

        $partner = null;
        $user = auth()->user();
        if ($user && $user->isMitra() && $user->partner?->isApproved()) {
            $partner = $user->partner;
        }

        return view('livewire.catalog.catalog-grid', compact('products', 'categories', 'totalCatalog', 'partner'));
    }
}
