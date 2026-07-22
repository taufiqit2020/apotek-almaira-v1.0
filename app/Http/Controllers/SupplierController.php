<?php
namespace App\Http\Controllers;
use App\Models\Supplier;
use App\Services\ActivityLogService;
use Illuminate\Http\Request;
class SupplierController extends Controller {
    public function index() {
        $suppliers = Supplier::withCount('products')->orderBy('name')->paginate(15);
        return view('suppliers.index', compact('suppliers'));
    }
    public function create() { return view('suppliers.create'); }
    public function store(Request $request) {
        $v = $request->validate([
            'name' => 'required|string|max:150',
            'contact_person' => 'nullable|string|max:100',
            'phone' => 'nullable|string|max:30',
            'email' => 'nullable|email|max:100',
            'address' => 'nullable|string',
        ]);
        $v['is_active'] = true;
        $s = Supplier::create($v);
        ActivityLogService::created('Supplier', $s->name);
        return redirect()->route('suppliers.index')->with('toast_success', "Supplier {$s->name} berhasil ditambahkan!");
    }
    public function edit(Supplier $supplier) { return view('suppliers.edit', compact('supplier')); }
    public function update(Request $request, Supplier $supplier) {
        $v = $request->validate([
            'name' => 'required|string|max:150',
            'contact_person' => 'nullable|string|max:100',
            'phone' => 'nullable|string|max:30',
            'email' => 'nullable|email|max:100',
            'address' => 'nullable|string',
            'is_active' => 'boolean',
        ]);
        $v['is_active'] = $request->boolean('is_active');
        $supplier->update($v);
        ActivityLogService::updated('Supplier', $supplier->name);
        return redirect()->route('suppliers.index')->with('toast_success', "Supplier {$supplier->name} berhasil diperbarui!");
    }
    public function destroy(Supplier $supplier) {
        $name = $supplier->name;
        $supplier->delete();
        ActivityLogService::deleted('Supplier', $name);
        return redirect()->route('suppliers.index')->with('toast_success', "Supplier {$name} berhasil dihapus!");
    }
}
