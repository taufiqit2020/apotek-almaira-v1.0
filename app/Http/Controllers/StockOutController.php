<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\StockOut;
use App\Services\ActivityLogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StockOutController extends Controller
{
    public function index(Request $request)
    {
        $query = StockOut::with(['product', 'user'])->latest();

        if ($request->search) {
            $query->where(function ($outer) use ($request) {
                $outer->where('product_name', 'like', '%' . $request->search . '%')
                    ->orWhereHas('product', fn ($q) => $q->searchKeyword($request->search, 'ops'));
            });
        }

        if ($request->reason) {
            $query->where('reason', $request->reason);
        }

        if ($request->start_date && $request->end_date) {
            $query->whereBetween('out_date', [$request->start_date, $request->end_date]);
        }

        $stockOuts = $query->paginate(20)->withQueryString();

        return view('stock_outs.index', compact('stockOuts'));
    }

    public function create()
    {
        return view('stock_outs.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1',
            'reason' => 'required|in:expired,damaged,returned,other',
            'notes' => 'required_if:reason,other|nullable|string',
            'out_date' => 'required|date',
        ], [
            'product_id.required' => 'Produk wajib dipilih.',
            'quantity.required' => 'Jumlah barang keluar wajib diisi.',
            'reason.required' => 'Alasan barang keluar wajib dipilih.',
            'notes.required_if' => 'Catatan wajib diisi jika alasan adalah Lainnya.',
            'out_date.required' => 'Tanggal barang keluar wajib diisi.',
        ]);

        try {
            DB::transaction(function () use ($request) {
                $product = Product::findOrFail($request->product_id);

                if ($product->stock < $request->quantity) {
                    throw new \Exception("Stok produk {$product->name} tidak mencukupi! Stok saat ini: {$product->stock}.");
                }

                $stockOut = StockOut::create([
                    'product_id' => $product->id,
                    'user_id' => auth()->id(),
                    'product_name' => $product->name,
                    'quantity' => $request->quantity,
                    'reason' => $request->reason,
                    'notes' => $request->notes,
                    'out_date' => $request->out_date,
                ]);

                // Decrement product stock
                $product->update([
                    'stock' => $product->stock - $request->quantity,
                ]);

                $reasonText = match($request->reason) {
                    'expired' => 'Kadaluarsa',
                    'damaged' => 'Rusak',
                    'returned' => 'Retur',
                    'other' => 'Lainnya'
                };

                ActivityLogService::log(
                    'DELETE',
                    'Barang Keluar',
                    "Mencatat barang keluar: {$product->name} ({$request->quantity} pcs) — Alasan: {$reasonText}"
                );
            });

            return redirect()->route('stock-outs.index')->with('toast_success', 'Barang keluar berhasil dicatat dan stok telah berkurang!');
        } catch (\Exception $e) {
            return back()->withInput()->with('toast_error', $e->getMessage());
        }
    }
}
