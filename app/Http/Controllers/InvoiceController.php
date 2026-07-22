<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use App\Services\ActivityLogService;
use App\Services\InvoiceReceivableService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class InvoiceController extends Controller
{
    /**
     * Halaman manajemen tagihan invoice.
     */
    public function index()
    {
        $stats = InvoiceReceivableService::stats();

        return view('invoices.index', [
            'totalUnpaid'   => $stats['total_unpaid'],
            'totalOverdue'  => $stats['total_overdue'],
            'totalPaid'     => $stats['total_paid_month'],
            'amountUnpaid'  => $stats['amount_unpaid'],
            'amountOverdue' => $stats['amount_overdue'],
        ]);
    }

    /**
     * Lunasin invoice — dipanggil dari halaman show atau invoices.index.
     */
    public function pay(Request $request, Sale $sale)
    {
        if (strtolower($sale->payment_method) !== 'invoice') {
            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'message' => 'Transaksi ini bukan invoice.'], 422);
            }
            return back()->with('toast_error', 'Transaksi ini bukan metode pembayaran Invoice.');
        }

        if ($sale->payment_status === 'paid') {
            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'message' => 'Invoice sudah dilunasi.'], 422);
            }
            return back()->with('toast_error', 'Tagihan invoice ini sudah dilunasi sebelumnya.');
        }

        $request->validate([
            'settlement_method' => 'required|in:cash,transfer',
        ], [
            'settlement_method.required' => 'Metode pelunasan wajib dipilih.',
        ]);

        $sale->update([
            'payment_status'    => 'paid',
            'settlement_method' => $request->settlement_method,
            'settled_at'        => now(),
            'settled_by'        => Auth::id(),
        ]);

        $methodLabel = $request->settlement_method === 'cash' ? 'Tunai' : 'Transfer';

        ActivityLogService::log(
            'UPDATE',
            'Invoice',
            "Pelunasan invoice {$sale->invoice_no} sebesar Rp " .
            number_format($sale->total, 0, ',', '.') . " — Metode: {$methodLabel}"
        );

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => "Invoice {$sale->invoice_no} berhasil dilunasi dengan metode {$methodLabel}!",
            ]);
        }

        return back()->with('toast_success', "Invoice {$sale->invoice_no} berhasil dilunasi!");
    }

    /**
     * Cetak Invoice format Dot Matrix (Epson LX-310)
     */
    public function print(Sale $sale)
    {
        if (strtolower($sale->payment_method) !== 'invoice') {
            return back()->with('toast_error', 'Transaksi ini bukan metode pembayaran Invoice.');
        }

        $sale->load(['user', 'items.product']);
        return view('invoices.print', compact('sale'));
    }

    public function export(Sale $sale)
    {
        if (strtolower($sale->payment_method) !== 'invoice') {
            return back()->with('toast_error', 'Transaksi ini bukan metode pembayaran Invoice.');
        }

        $sale->load(['user', 'items.product']);

        $safeName = preg_replace('/[^A-Za-z0-9_\-]/', '_', $sale->invoice_no);
        $fileName = 'Invoice_' . $safeName . '.xlsx';

        return \Maatwebsite\Excel\Facades\Excel::download(
            new \App\Exports\InvoiceExport($sale->id),
            $fileName,
            \Maatwebsite\Excel\Excel::XLSX
        );
    }
}
