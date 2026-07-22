<?php
namespace App\Http\Controllers;
use App\Models\Category;
use App\Services\ActivityLogService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
class CategoryController extends Controller {
    public function index(Request $request) {
        $query = Category::withCount('products')->orderBy('name');
        if ($request->search) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }
        $categories = $query->paginate(20)->withQueryString();
        return view('categories.index', compact('categories'));
    }
    public function store(Request $request) {
        $v = $request->validate(['name'=>'required|string|max:100|unique:categories']);
        $v['slug'] = Str::slug($v['name']);
        $v['is_active'] = true;
        $cat = Category::create($v);
        ActivityLogService::created('Kategori', $cat->name);
        return back()->with('toast_success', "Kategori {$cat->name} berhasil ditambahkan!");
    }
    public function update(Request $request, Category $category) {
        $v = $request->validate(['name'=>'required|string|max:100|unique:categories,name,'.$category->id]);
        $v['slug'] = Str::slug($v['name']);
        $category->update($v);
        ActivityLogService::updated('Kategori', $category->name);
        return back()->with('toast_success', "Kategori berhasil diperbarui!");
    }
    public function destroy(Category $category) {
        if ($category->products()->count() > 0) {
            return back()->with('toast_error', "Kategori tidak bisa dihapus karena masih memiliki {$category->products()->count()} produk!");
        }
        $name = $category->name;
        $category->delete();
        ActivityLogService::deleted('Kategori', $name);
        return back()->with('toast_success', "Kategori {$name} berhasil dihapus!");
    }
    public function toggleStatus(Category $category) {
        $category->update(['is_active' => !$category->is_active]);
        return back()->with('toast_success', 'Status kategori diperbarui!');
    }
}
