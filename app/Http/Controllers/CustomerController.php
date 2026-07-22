<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Services\ActivityLogService;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    public function index(Request $request)
    {
        $query = Customer::latest();

        if ($request->search) {
            $query->where(function($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('phone', 'like', '%' . $request->search . '%');
            });
        }

        $customers = $query->paginate(20)->withQueryString();

        return view('customers.index', compact('customers'));
    }

    public function create()
    {
        return view('customers.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:150',
            'phone' => 'required|string|max:30|unique:customers,phone',
            'address' => 'nullable|string',
            'dob' => 'nullable|date',
            'nik' => 'nullable|string|max:20',
        ], [
            'name.required' => 'Nama pelanggan wajib diisi.',
            'phone.required' => 'Nomor HP wajib diisi.',
            'phone.unique' => 'Nomor HP sudah terdaftar.',
        ]);

        $customer = Customer::create($request->all());

        ActivityLogService::log(
            'CREATE',
            'CRM Pelanggan',
            "Mendaftarkan pelanggan baru: {$customer->name} ({$customer->phone})"
        );

        return redirect()->route('customers.index')->with('toast_success', 'Pelanggan baru berhasil didaftarkan!');
    }

    public function show(Customer $customer)
    {
        $sales = $customer->sales()->with('user')->latest()->paginate(10);
        
        $lifetimeSpend = $customer->sales()->where('status', 'completed')->sum('total');
        $lifetimeTransactions = $customer->sales()->where('status', 'completed')->count();

        return view('customers.show', compact('customer', 'sales', 'lifetimeSpend', 'lifetimeTransactions'));
    }

    public function edit(Customer $customer)
    {
        return view('customers.edit', compact('customer'));
    }

    public function update(Request $request, Customer $customer)
    {
        $request->validate([
            'name' => 'required|string|max:150',
            'phone' => 'required|string|max:30|unique:customers,phone,' . $customer->id,
            'address' => 'nullable|string',
            'dob' => 'nullable|date',
            'nik' => 'nullable|string|max:20',
            'is_active' => 'required|boolean',
        ]);

        $oldData = $customer->toArray();
        $customer->update($request->all());
        $newData = $customer->toArray();

        ActivityLogService::updated(
            'CRM Pelanggan',
            "Memperbarui data pelanggan {$customer->name}",
            $oldData,
            $newData
        );

        return redirect()->route('customers.index')->with('toast_success', 'Data pelanggan berhasil diperbarui!');
    }
    
    public function search(Request $request)
    {
        $query = trim((string) $request->get('q', ''));
        $limit = min(50, max(10, (int) $request->get('limit', 30)));

        $customers = Customer::active()
            ->when($query !== '', function ($q) use ($query) {
                $q->where(function ($inner) use ($query) {
                    $inner->where('name', 'like', "%{$query}%")
                        ->orWhere('phone', 'like', "%{$query}%");
                });
            })
            ->orderBy('name')
            ->limit($limit)
            ->get(['id', 'name', 'phone', 'points']);

        $customers->transform(function ($customer) {
            $customer->has_overdue_invoice = $customer->hasOverdueInvoice();
            return $customer;
        });

        return response()->json($customers);
    }

    /** Tambah pelanggan CRM cepat dari kasir (JSON). */
    public function quickStore(Request $request)
    {
        $validated = $request->validate([
            'name'  => 'required|string|max:150',
            'phone' => 'required|string|max:30|unique:customers,phone',
        ], [
            'name.required'  => 'Nama pelanggan wajib diisi.',
            'phone.required' => 'Nomor HP wajib diisi.',
            'phone.unique'   => 'Nomor HP sudah terdaftar di CRM.',
        ]);

        $customer = Customer::create([
            'name'  => $validated['name'],
            'phone' => $validated['phone'],
            'is_active' => true,
            'points' => 0,
        ]);

        ActivityLogService::log(
            'CREATE',
            'CRM Pelanggan',
            "Tambah cepat dari kasir: {$customer->name} ({$customer->phone})"
        );

        return response()->json([
            'success' => true,
            'message' => 'Pelanggan CRM berhasil ditambahkan.',
            'customer' => [
                'id'                  => $customer->id,
                'name'                => $customer->name,
                'phone'               => $customer->phone,
                'points'              => (int) $customer->points,
                'has_overdue_invoice' => false,
            ],
        ]);
    }
}
