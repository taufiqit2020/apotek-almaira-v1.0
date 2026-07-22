<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\StockOpname;
use App\Services\ActivityLogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StockOpnameController extends Controller
{
    public function index(Request $request)
    {
        $query = StockOpname::with(['product', 'user'])->latest();

        if ($request->search) {
            $query->whereHas('product', function ($q) use ($request) {
                $q->searchKeyword($request->search, 'ops');
            });
        }

        if ($request->start_date && $request->end_date) {
            $query->whereBetween('created_at', [$request->start_date . ' 00:00:00', $request->end_date . ' 23:59:59']);
        }

        $stockOpnames = $query->paginate(20)->withQueryString();

        return view('stock_opnames.index', compact('stockOpnames'));
    }

    public function create()
    {
        $categories = \App\Models\Category::active()->orderBy('name')->get();
        return view('stock_opnames.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.physical_stock' => 'required|integer|min:0',
            'items.*.notes' => 'nullable|string',
        ], [
            'items.required' => 'Daftar produk audit tidak boleh kosong.',
            'items.*.product_id.required' => 'Produk wajib dipilih.',
            'items.*.physical_stock.required' => 'Stok fisik wajib diisi.',
            'items.*.physical_stock.integer' => 'Stok fisik harus berupa angka.',
            'items.*.physical_stock.min' => 'Stok fisik minimal 0.',
        ]);

        try {
            DB::transaction(function () use ($request) {
                foreach ($request->items as $item) {
                    $product = Product::findOrFail($item['product_id']);
                    $systemStock = $product->stock;
                    $physicalStock = (int) $item['physical_stock'];
                    $difference = $physicalStock - $systemStock;

                    StockOpname::create([
                        'product_id' => $product->id,
                        'user_id' => auth()->id(),
                        'system_stock' => $systemStock,
                        'physical_stock' => $physicalStock,
                        'difference' => $difference,
                        'notes' => $item['notes'] ?? null,
                    ]);

                    // Update product stock to match physical stock
                    $product->update([
                        'stock' => $physicalStock,
                    ]);

                    $diffSign = $difference >= 0 ? '+' : '';
                    ActivityLogService::log(
                        'UPDATE',
                        'Stok Opname',
                        "Penyesuaian stok produk (Batch): {$product->name} (Sistem: {$systemStock}, Fisik: {$physicalStock}, Selisih: {$diffSign}{$difference})"
                    );
                }
            });

            return redirect()->route('stock-opnames.index')->with('toast_success', 'Stok opname batch berhasil disesuaikan dan disimpan!');
        } catch (\Exception $e) {
            return back()->withInput()->with('toast_error', 'Gagal menyimpan stok opname: ' . $e->getMessage());
        }
    }
}
