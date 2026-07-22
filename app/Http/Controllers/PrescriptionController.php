<?php

namespace App\Http\Controllers;

use App\Models\Prescription;
use App\Models\PrescriptionItem;
use App\Models\Product;
use App\Services\ActivityLogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PrescriptionController extends Controller
{
    public function index(Request $request)
    {
        $query = Prescription::with('items')->latest();

        if ($request->search) {
            $query->where(function($q) use ($request) {
                $q->where('patient_name', 'like', '%' . $request->search . '%')
                  ->orWhere('doctor_name', 'like', '%' . $request->search . '%');
            });
        }

        if ($request->status) {
            $query->where('status', $request->status);
        }

        $prescriptions = $query->paginate(20)->withQueryString();

        return view('prescriptions.index', compact('prescriptions'));
    }

    public function create()
    {
        return view('prescriptions.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'doctor_name' => 'required|string|max:150',
            'doctor_sip' => 'nullable|string|max:50',
            'patient_name' => 'required|string|max:150',
            'prescription_date' => 'required|date',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.dosage' => 'nullable|string|max:100',
            'items.*.signa' => 'nullable|string|max:100',
            'items.*.quantity' => 'required|integer|min:1',
        ], [
            'doctor_name.required' => 'Nama dokter wajib diisi.',
            'patient_name.required' => 'Nama pasien wajib diisi.',
            'prescription_date.required' => 'Tanggal resep wajib diisi.',
            'items.required' => 'Item obat resep minimal harus 1.',
        ]);

        try {
            DB::transaction(function () use ($request) {
                $prescription = Prescription::create([
                    'doctor_name' => $request->doctor_name,
                    'doctor_sip' => $request->doctor_sip,
                    'patient_name' => $request->patient_name,
                    'prescription_date' => $request->prescription_date,
                    'status' => 'pending',
                ]);

                foreach ($request->items as $item) {
                    $product = Product::findOrFail($item['product_id']);
                    PrescriptionItem::create([
                        'prescription_id' => $prescription->id,
                        'product_id' => $product->id,
                        'product_name' => $product->name,
                        'dosage' => $item['dosage'] ?? '-',
                        'signa' => $item['signa'] ?? '-',
                        'quantity' => $item['quantity'],
                    ]);
                }

                ActivityLogService::log(
                    'CREATE',
                    'Resep Dokter',
                    "Mencatat resep baru dari Dr. {$prescription->doctor_name} untuk pasien {$prescription->patient_name}"
                );
            });

            return redirect()->route('prescriptions.index')->with('toast_success', 'Resep dokter berhasil dicatat!');
        } catch (\Exception $e) {
            return back()->withInput()->with('toast_error', 'Gagal menyimpan resep: ' . $e->getMessage());
        }
    }

    public function show(Prescription $prescription)
    {
        $prescription->load(['items.product', 'sales']);
        return view('prescriptions.show', compact('prescription'));
    }

    public function getJson(Prescription $prescription)
    {
        $prescription->load('items');
        return response()->json([
            'success' => true,
            'prescription' => $prescription
        ]);
    }

    public function edit(Prescription $prescription)
    {
        if ($prescription->status === 'processed') {
            return redirect()->route('prescriptions.index')->with('toast_error', 'Resep yang telah diproses tidak dapat diedit.');
        }
        $prescription->load('items.product');
        return view('prescriptions.edit', compact('prescription'));
    }

    public function update(Request $request, Prescription $prescription)
    {
        if ($prescription->status === 'processed') {
            return redirect()->route('prescriptions.index')->with('toast_error', 'Resep yang telah diproses tidak dapat diubah.');
        }

        $request->validate([
            'doctor_name' => 'required|string|max:150',
            'doctor_sip' => 'nullable|string|max:50',
            'patient_name' => 'required|string|max:150',
            'prescription_date' => 'required|date',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.dosage' => 'nullable|string|max:100',
            'items.*.signa' => 'nullable|string|max:100',
            'items.*.quantity' => 'required|integer|min:1',
        ], [
            'doctor_name.required' => 'Nama dokter wajib diisi.',
            'patient_name.required' => 'Nama pasien wajib diisi.',
            'prescription_date.required' => 'Tanggal resep wajib diisi.',
            'items.required' => 'Item obat resep minimal harus 1.',
        ]);

        try {
            DB::transaction(function () use ($request, $prescription) {
                $oldData = $prescription->toArray();
                
                $prescription->update([
                    'doctor_name' => $request->doctor_name,
                    'doctor_sip' => $request->doctor_sip,
                    'patient_name' => $request->patient_name,
                    'prescription_date' => $request->prescription_date,
                ]);

                // Delete old items
                $prescription->items()->delete();

                // Re-create new items
                foreach ($request->items as $item) {
                    $product = Product::findOrFail($item['product_id']);
                    PrescriptionItem::create([
                        'prescription_id' => $prescription->id,
                        'product_id' => $product->id,
                        'product_name' => $product->name,
                        'dosage' => $item['dosage'] ?? '-',
                        'signa' => $item['signa'] ?? '-',
                        'quantity' => $item['quantity'],
                    ]);
                }

                ActivityLogService::updated(
                    'Resep Dokter',
                    "Memperbarui resep dokter dari Dr. {$prescription->doctor_name} untuk pasien {$prescription->patient_name}",
                    $oldData,
                    $prescription->toArray()
                );
            });

            return redirect()->route('prescriptions.index')->with('toast_success', 'Resep dokter berhasil diperbarui!');
        } catch (\Exception $e) {
            return back()->withInput()->with('toast_error', 'Gagal memperbarui resep: ' . $e->getMessage());
        }
    }

    public function destroy(Prescription $prescription)
    {
        if ($prescription->status === 'processed') {
            return back()->with('toast_error', 'Resep yang telah diproses di kasir tidak dapat dihapus.');
        }

        try {
            DB::transaction(function () use ($prescription) {
                $oldData = $prescription->toArray();
                $prescription->items()->delete();
                $prescription->delete();

                ActivityLogService::deleted(
                    'Resep Dokter',
                    "Menghapus resep dokter dari Dr. {$prescription->doctor_name} untuk pasien {$prescription->patient_name}",
                    $oldData
                );
            });

            return redirect()->route('prescriptions.index')->with('toast_success', 'Resep dokter berhasil dihapus!');
        } catch (\Exception $e) {
            return back()->with('toast_error', 'Gagal menghapus resep: ' . $e->getMessage());
        }
    }
}
