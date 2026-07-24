<?php

namespace App\Http\Controllers;

use App\Models\PartnerOrder;
use App\Models\Product;
use App\Models\Setting;
use App\Services\ActivityLogService;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class PartnerOrderAdminController extends Controller
{
    public function index(Request $request)
    {
        $orders = PartnerOrder::with(['partner', 'user'])
            ->withCount('items')
            ->when($request->status, fn ($q) => $q->where('status', $request->status))
            ->when($request->payment_status, fn ($q) => $q->where('payment_status', $request->payment_status))
            ->when($request->search, function ($q) use ($request) {
                $s = $request->search;
                $q->where(function ($qq) use ($s) {
                    $qq->where('order_no', 'like', "%{$s}%")
                        ->orWhereHas('partner', fn ($p) => $p->where('name', 'like', "%{$s}%")->orWhere('code', 'like', "%{$s}%"));
                });
            })
            ->latest()
            ->paginate(15)
            ->withQueryString();

        $openCount = PartnerOrder::whereIn('status', [
            PartnerOrder::STATUS_SUBMITTED,
            PartnerOrder::STATUS_CONFIRMED,
        ])->count();

        return view('partners.orders.index', compact('orders', 'openCount'));
    }

    public function show(PartnerOrder $partnerOrder)
    {
        $partnerOrder->load(['partner', 'user', 'items.product.category', 'items.product.unit', 'confirmer']);

        return view('partners.orders.show', [
            'order'       => $partnerOrder,
            'bankName'    => Setting::get('bank_name', 'BNI'),
            'bankAccount' => Setting::get('bank_account', '2050169349'),
            'bankHolder'  => Setting::get('bank_holder', 'PT NUR MADANI FARMA'),
        ]);
    }

    public function confirm(PartnerOrder $partnerOrder)
    {
        if ($partnerOrder->status !== PartnerOrder::STATUS_SUBMITTED) {
            return back()->with('toast_error', 'Hanya PO berstatus diajukan yang dapat dikonfirmasi.');
        }

        $partnerOrder->update([
            'status'       => PartnerOrder::STATUS_CONFIRMED,
            'confirmed_by' => Auth::id(),
            'confirmed_at' => now(),
        ]);

        ActivityLogService::log('UPDATE', 'PO Mitra', "PO {$partnerOrder->order_no} dikonfirmasi");

        return back()->with('toast_success', 'PO dikonfirmasi.');
    }

    /** Admin unggah / ganti bukti transfer (untuk PO Transfer, termasuk dari kasir). */
    public function uploadProof(Request $request, PartnerOrder $partnerOrder)
    {
        if (!$partnerOrder->canUploadProof()) {
            return back()->with('toast_error', 'Bukti transfer tidak dapat diunggah untuk PO ini.');
        }

        $request->validate([
            'transfer_proof' => 'required|file|mimes:jpg,jpeg,png,webp,pdf|max:4096',
        ], [
            'transfer_proof.required' => 'Pilih file bukti transfer.',
            'transfer_proof.mimes'    => 'Format bukti: JPG, PNG, WEBP, atau PDF.',
            'transfer_proof.max'      => 'Ukuran maksimal 4 MB.',
        ]);

        if ($partnerOrder->transfer_proof) {
            Storage::disk('public')->delete($partnerOrder->transfer_proof);
        }

        $path = $request->file('transfer_proof')->store('partner-transfer-proofs', 'public');

        $partnerOrder->update([
            'transfer_proof'    => $path,
            'transfer_proof_at' => now(),
            'payment_status'    => PartnerOrder::PAYMENT_AWAITING,
        ]);

        ActivityLogService::log(
            'UPDATE',
            'PO Mitra',
            "Bukti transfer diunggah (admin) untuk {$partnerOrder->order_no}"
        );

        return back()->with('toast_success', 'Bukti transfer berhasil dilampirkan. Status: Menunggu Konfirmasi.');
    }

    public function markPaid(PartnerOrder $partnerOrder)
    {
        if ($partnerOrder->status === PartnerOrder::STATUS_CANCELLED) {
            return back()->with('toast_error', 'PO sudah dibatalkan.');
        }

        if ($partnerOrder->payment_status === PartnerOrder::PAYMENT_PAID) {
            return back()->with('toast_error', 'Pembayaran sudah lunas.');
        }

        $settledAt = now();

        $partnerOrder->update([
            'payment_status'    => PartnerOrder::PAYMENT_PAID,
            'settled_at'        => $partnerOrder->settled_at ?? $settledAt,
            'settled_by'        => $partnerOrder->settled_by ?? Auth::id(),
            'settlement_method' => $partnerOrder->settlement_method ?? 'transfer',
        ]);

        ActivityLogService::log('UPDATE', 'PO Mitra', "Pembayaran PO {$partnerOrder->order_no} ditandai lunas");

        if ($partnerOrder->payment_method === PartnerOrder::PAY_INVOICE) {
            return redirect()->route('credits.index', [
                'tab'   => 'lunas',
                'month' => $settledAt->month,
                'year'  => $settledAt->year,
                'ref'   => $partnerOrder->order_no,
            ])->with('toast_success', "PO {$partnerOrder->order_no} dilunasi. Masuk daftar Invoice Lunas.");
        }

        return back()->with('toast_success', 'Pembayaran ditandai lunas.');
    }

    public function fulfill(PartnerOrder $partnerOrder)
    {
        if (!in_array($partnerOrder->status, [PartnerOrder::STATUS_SUBMITTED, PartnerOrder::STATUS_CONFIRMED], true)) {
            return back()->with('toast_error', 'PO tidak dapat diselesaikan dari status saat ini.');
        }

        $partnerOrder->load('items');

        try {
            DB::transaction(function () use ($partnerOrder) {
                foreach ($partnerOrder->items as $item) {
                    if (!$item->product_id) {
                        throw new \RuntimeException("Item \"{$item->product_name}\" tidak terhubung ke produk aktif. Perbaiki data PO terlebih dahulu.");
                    }

                    $product = Product::lockForUpdate()->find($item->product_id);
                    if (!$product) {
                        throw new \RuntimeException("Produk \"{$item->product_name}\" tidak ditemukan di master.");
                    }

                    if ($product->stock < $item->quantity) {
                        throw new \RuntimeException(
                            "Stok tidak mencukupi untuk \"{$product->name}\". Tersedia: {$product->stock}, diminta: {$item->quantity}."
                        );
                    }

                    $product->decrement('stock', $item->quantity);
                    $product->refresh();
                    if ($product->stock <= $product->stock_min) {
                        NotificationService::triggerStockAlert($product);
                    }
                }

                $partnerOrder->update([
                    'status'       => PartnerOrder::STATUS_FULFILLED,
                    'fulfilled_at' => now(),
                    'confirmed_by' => $partnerOrder->confirmed_by ?: Auth::id(),
                    'confirmed_at' => $partnerOrder->confirmed_at ?: now(),
                ]);
            });
        } catch (\Throwable $e) {
            return back()->with('toast_error', $e->getMessage());
        }

        ActivityLogService::log('UPDATE', 'PO Mitra', "PO {$partnerOrder->order_no} diselesaikan — stok dipotong");

        return back()->with('toast_success', 'PO selesai. Stok produk telah dipotong.');
    }

    public function cancel(Request $request, PartnerOrder $partnerOrder)
    {
        if ($partnerOrder->status === PartnerOrder::STATUS_FULFILLED) {
            return back()->with('toast_error', 'PO yang sudah selesai tidak dapat dibatalkan.');
        }
        if ($partnerOrder->status === PartnerOrder::STATUS_CANCELLED) {
            return back()->with('toast_error', 'PO sudah dibatalkan.');
        }

        $request->validate([
            'cancel_reason' => 'required|string|max:255',
            'admin_notes'   => 'nullable|string|max:2000',
        ]);

        $partnerOrder->update([
            'status'         => PartnerOrder::STATUS_CANCELLED,
            'payment_status' => PartnerOrder::PAYMENT_CANCELLED,
            'cancel_reason'  => $request->cancel_reason,
            'admin_notes'    => $request->admin_notes,
            'cancelled_at'   => now(),
        ]);

        ActivityLogService::log('CANCEL', 'PO Mitra', "PO {$partnerOrder->order_no} dibatalkan admin: {$request->cancel_reason}");

        return back()->with('toast_success', 'PO dibatalkan.');
    }

    /** Guard: akses hanya Kepala IT — selain itu tampilkan halaman unauthorized cantik. */
    private function requireKepalaIt(): void
    {
        if (! Auth::user()?->isKepalaIt()) {
            abort(403, 'akses_kepala_it');
        }
    }

    /** Halaman edit PO (Kepala IT only). */
    public function edit(PartnerOrder $partnerOrder)
    {
        $this->requireKepalaIt();

        $partnerOrder->load(['partner', 'user', 'items.product.category', 'items.product.unit']);

        $products = Product::with(['category', 'unit'])
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('partners.orders.edit', compact('partnerOrder', 'products'));
    }

    /** Update header PO: catatan admin, due date, catatan internal (Kepala IT only). */
    public function update(Request $request, PartnerOrder $partnerOrder)
    {
        $this->requireKepalaIt();

        $data = $request->validate([
            'admin_notes' => 'nullable|string|max:2000',
            'due_date'    => 'nullable|date',
            'notes'       => 'nullable|string|max:2000',
        ]);

        $partnerOrder->update(array_filter($data, fn ($v) => $v !== null));

        ActivityLogService::log('UPDATE', 'PO Mitra', "PO {$partnerOrder->order_no} diedit oleh Kepala IT");

        return redirect()->route('partner-orders.edit', $partnerOrder)
            ->with('toast_success', 'Data PO berhasil diperbarui.');
    }

    /** Hapus PO sepenuhnya (Kepala IT only). */
    public function destroy(PartnerOrder $partnerOrder)
    {
        $this->requireKepalaIt();

        $orderNo = $partnerOrder->order_no;
        $partnerOrder->items()->delete();
        $partnerOrder->delete();

        ActivityLogService::log('DELETE', 'PO Mitra', "PO {$orderNo} dihapus permanen oleh Kepala IT");

        return redirect()->route('partner-orders.index')
            ->with('toast_success', "PO {$orderNo} berhasil dihapus permanen.");
    }

    /** Tambah item baru ke PO (Kepala IT only). */
    public function addItem(Request $request, PartnerOrder $partnerOrder)
    {
        $this->requireKepalaIt();

        $data = $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity'   => 'required|integer|min:1',
            'unit_price' => 'required|numeric|min:0',
            'price_type' => 'required|in:eceran,grosir,invoice',
        ]);

        $product  = Product::with(['unit'])->findOrFail($data['product_id']);
        $subtotal = $data['quantity'] * $data['unit_price'];

        $partnerOrder->items()->create([
            'product_id'   => $product->id,
            'product_name' => $product->name,
            'product_code' => $product->code ?? '',
            'unit_name'    => $product->unit?->name ?? '',
            'price_type'   => $data['price_type'],
            'unit_price'   => $data['unit_price'],
            'quantity'     => $data['quantity'],
            'subtotal'     => $subtotal,
        ]);

        // Recalculate totals
        $this->recalcOrderTotals($partnerOrder);

        ActivityLogService::log('UPDATE', 'PO Mitra', "Item '{$product->name}' ditambahkan ke PO {$partnerOrder->order_no}");

        return redirect()->route('partner-orders.edit', $partnerOrder)
            ->with('toast_success', "Item \"{$product->name}\" berhasil ditambahkan.");
    }

    /** Hapus item dari PO (Kepala IT only). */
    public function removeItem(PartnerOrder $partnerOrder, \App\Models\PartnerOrderItem $item)
    {
        $this->requireKepalaIt();

        if ($item->partner_order_id !== $partnerOrder->id) {
            return back()->with('toast_error', 'Item tidak ditemukan di PO ini.');
        }

        $name = $item->product_name;
        $item->delete();

        $this->recalcOrderTotals($partnerOrder);

        ActivityLogService::log('UPDATE', 'PO Mitra', "Item '{$name}' dihapus dari PO {$partnerOrder->order_no}");

        return redirect()->route('partner-orders.edit', $partnerOrder)
            ->with('toast_success', "Item \"{$name}\" berhasil dihapus.");
    }

    /** Hitung ulang subtotal & total PO setelah perubahan item. */
    private function recalcOrderTotals(PartnerOrder $partnerOrder): void
    {
        $partnerOrder->refresh();
        $items    = $partnerOrder->items;
        $subtotal = $items->sum('subtotal');

        $ppnEnabled = (bool) $partnerOrder->ppn_enabled;
        $ppnPct     = (float) ($partnerOrder->ppn_percent ?? 11);
        $ppnAmt     = $ppnEnabled ? round($subtotal * $ppnPct / 100, 2) : 0;
        $discAmt    = (float) ($partnerOrder->discount_amount ?? 0);
        $total      = $subtotal - $discAmt + $ppnAmt;

        $partnerOrder->update([
            'subtotal'   => $subtotal,
            'ppn_amount' => $ppnAmt,
            'total'      => $total,
        ]);
    }

    public function updateNotes(Request $request, PartnerOrder $partnerOrder)
    {
        $request->validate(['admin_notes' => 'nullable|string|max:2000']);
        $partnerOrder->update(['admin_notes' => $request->admin_notes]);

        return back()->with('toast_success', 'Catatan admin disimpan.');
    }

    public function printSuratJalan(Request $request, PartnerOrder $partnerOrder)
    {
        return $this->renderPrint($request, $partnerOrder, 'surat-jalan');
    }

    public function printPenjualan(Request $request, PartnerOrder $partnerOrder)
    {
        return $this->renderPrint($request, $partnerOrder, 'penjualan');
    }

    private function renderPrint(Request $request, PartnerOrder $partnerOrder, string $type)
    {
        if ($partnerOrder->status === PartnerOrder::STATUS_CANCELLED) {
            return back()->with('toast_error', 'PO dibatalkan — dokumen tidak dapat dicetak.');
        }

        $entity  = $request->query('entity', 'pt') === 'apotek' ? 'apotek' : 'pt';
        $printer = $request->query('printer', 'a4');
        if (!in_array($printer, ['a4', 'thermal', 'dotmatrix'], true)) {
            $printer = 'a4';
        }

        $partnerOrder->load(['partner', 'user', 'items.product.category', 'items.product.unit', 'confirmer', 'settler']);

        $viewMap = [
            'penjualan' => [
                'a4'        => 'partners.orders.print.penjualan',
                'thermal'   => 'partners.orders.print.thermal.penjualan',
                'dotmatrix' => 'partners.orders.print.dotmatrix.penjualan',
            ],
            'surat-jalan' => [
                'a4'        => 'partners.orders.print.surat-jalan',
                'thermal'   => 'partners.orders.print.thermal.surat-jalan',
                'dotmatrix' => 'partners.orders.print.dotmatrix.surat-jalan',
            ],
        ];

        $view = $viewMap[$type][$printer];

        $docTitle = $type === 'penjualan' ? 'Faktur Penjualan' : 'Surat Jalan';

        $buildUrl = fn (string $e, string $p) => route("partner-orders.print.{$type}", [
            'partnerOrder' => $partnerOrder,
            'entity'       => $e,
            'printer'      => $p,
        ]);

        $branding = $this->printBranding();

        return view($view, array_merge($this->printContext($entity, $branding), [
            'order'    => $partnerOrder,
            'entity'   => $entity,
            'printer'  => $printer,
            'docTitle' => $docTitle,
            'branding' => $branding,
            'bankName'    => Setting::get('bank_name', 'BNI'),
            'bankAccount' => Setting::get('bank_account', '2050169349'),
            'bankHolder'  => Setting::get('bank_holder', 'PT NUR MADANI FARMA'),
            'entitySwitchUrls' => [
                'pt'     => $buildUrl('pt', $printer),
                'apotek' => $buildUrl('apotek', $printer),
            ],
            'printerSwitchUrls' => [
                'a4'        => $buildUrl($entity, 'a4'),
                'thermal'   => $buildUrl($entity, 'thermal'),
                'dotmatrix' => $buildUrl($entity, 'dotmatrix'),
            ],
        ]));
    }

    /** @return array<string, mixed> */
    private function printContext(string $entity, array $branding): array
    {
        $isPT        = $entity === 'pt';
        $ptAddress   = $branding['pt_address'] ?? '';
        $apotekAddress = $branding['apotek_address'] ?? '';

        return [
            'isPT'        => $isPT,
            'entityLabel' => $isPT ? 'PT. NUR MADANI FARMA' : 'APOTEK ALMAIRA',
            'address'     => $isPT ? $ptAddress : $apotekAddress,
            'phone'       => $branding['apotek_phone'] ?? '0851-6665-7070',
            'website'     => $branding['pt_website'] ?? 'www.ptutamamadaniraya.com',
            'instagram'   => $branding['pt_ig'] ?? '@apotekalmaira',
            'headerName'  => $isPT
                ? strtoupper($branding['pt_name'] ?? 'PT NUR MADANI FARMA')
                : strtoupper($branding['apotek_name'] ?? 'APOTEK ALMAIRA'),
            'subName'     => $isPT ? 'APOTEK ALMAIRA' : 'PT NUR MADANI FARMA',
            'addressLine' => $isPT ? Str::before($ptAddress, "\n") : $apotekAddress,
            'kopTagline'  => $branding['pt_tagline'] ?? 'Distributor & Mitra Pengadaan Alat Kesehatan & Farmasi',
        ];
    }

    /** @return array<string, string> */
    private function printBranding(): array
    {
        $ptPhone = Setting::get('apotek_phone', '0851-6665-7070');
        $ptEmail = Setting::get('company_email', 'ptnurmadanifarma@gmail.com');
        $ptIg    = Setting::get('company_instagram', '@apotekalmaira');
        $ptWeb   = Setting::get('company_website', 'www.ptnurmadanifarma.com');

        return [
            // Kop cetak LX-310/A4 — teks tetap agar jelas & konsisten (jangan pakai tagline landing).
            'pt_name'        => 'PT. Nur Madani Farma',
            'pt_tagline'     => 'Distributor & Mitra Pengadaan Alat Kesehatan & Farmasi',
            'pt_address'     => Setting::get(
                'company_office_address',
                'Jl. Panglima Batur No. 16, Kel. Komet, Kec. Banjarbaru Utara, Kota Banjarbaru, Kalsel 70714'
            ),
            'apotek_name'    => 'Apotek Almaira',
            'apotek_address' => Setting::get(
                'apotek_address',
                'Jl. Nuri No. 14 RT/RW 001/005, Kel. Komet, Kec. Banjarbaru Utara, Kota Banjarbaru, Kalsel 70714'
            ),
            'apotek_phone'   => $ptPhone,
            'pt_email'       => $ptEmail,
            'pt_ig'          => $ptIg,
            'pt_website'     => $ptWeb,
        ];
    }
}
