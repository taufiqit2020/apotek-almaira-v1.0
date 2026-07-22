<?php

namespace App\Http\Controllers;

use App\Models\PartnerOrder;
use App\Models\Sale;
use App\Services\ActivityLogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class CreditController extends Controller
{
    public function index(Request $request)
    {
        $tab = $request->get('tab', 'kredit');
        $now = now();

        $filterMonth = (int) $request->input('month', $tab === 'lunas' ? $now->month : $now->month);
        $filterYear  = (int) $request->input('year', $now->year);

        $posCredits   = Sale::unpaidInvoices()->with('customer')->latest('sold_at')->get();
        $mitraCredits = PartnerOrder::creditUnpaid()->with('partner')->latest()->get();

        $posPaidCountMonth = Sale::paidInvoices()
            ->whereMonth('settled_at', $now->month)
            ->whereYear('settled_at', $now->year)
            ->count();

        $mitraPaidCountMonth = PartnerOrder::creditPaid()
            ->whereMonth('settled_at', $now->month)
            ->whereYear('settled_at', $now->year)
            ->count();

        $posPaid = Sale::paidInvoices()
            ->whereMonth('settled_at', $filterMonth)
            ->whereYear('settled_at', $filterYear)
            ->with('settledBy')
            ->latest('settled_at')
            ->limit(100)
            ->get();

        $mitraPaid = PartnerOrder::creditPaid()
            ->whereMonth('settled_at', $filterMonth)
            ->whereYear('settled_at', $filterYear)
            ->with(['partner', 'settler'])
            ->latest('settled_at')
            ->limit(100)
            ->get();

        $paidTimeline = collect();
        $paidTotalAmount = 0;

        if ($tab === 'lunas') {
            foreach ($posPaid as $s) {
                $paidTimeline->push([
                    'type'        => 'pos',
                    'ref'         => $s->invoice_no,
                    'title'       => $s->invoice_no,
                    'subtitle'    => $s->customer_name ?? $s->customer?->name ?? '—',
                    'settled_at'  => $s->settled_at,
                    'method'      => $s->settlement_method,
                    'total'       => (float) $s->total,
                    'proof'       => null,
                    'settler'     => $s->settledBy?->name,
                ]);
            }

            foreach ($mitraPaid as $o) {
                $paidTimeline->push([
                    'type'        => 'mitra',
                    'ref'         => $o->order_no,
                    'title'       => $o->order_no,
                    'subtitle'    => $o->partner?->name ?? '—',
                    'settled_at'  => $o->settled_at,
                    'method'      => $o->settlement_method,
                    'total'       => (float) $o->total,
                    'proof'       => $o->settlement_proof,
                    'settler'     => $o->settler?->name,
                ]);
            }

            $paidTimeline = $paidTimeline
                ->sortByDesc(fn ($row) => $row['settled_at']?->timestamp ?? 0)
                ->values();

            $paidTotalAmount = $paidTimeline->sum('total');
        }

        $totalKredit  = $posCredits->sum('total') + $mitraCredits->sum('total');
        $countKredit  = $posCredits->count() + $mitraCredits->count();
        $overdueCount = Sale::overdueInvoices()->count() + PartnerOrder::creditOverdue()->count();
        $highlightRef = $request->get('ref');

        return view('credits.index', compact(
            'tab', 'posCredits', 'mitraCredits', 'posPaid', 'mitraPaid',
            'totalKredit', 'countKredit', 'overdueCount',
            'filterMonth', 'filterYear',
            'posPaidCountMonth', 'mitraPaidCountMonth',
            'paidTimeline', 'paidTotalAmount', 'highlightRef'
        ));
    }

    public function payMitra(Request $request, PartnerOrder $partnerOrder)
    {
        if ($partnerOrder->payment_method !== PartnerOrder::PAY_INVOICE) {
            return back()->with('toast_error', 'PO ini bukan metode Invoice/Kredit.');
        }
        if ($partnerOrder->payment_status === PartnerOrder::PAYMENT_PAID) {
            return back()->with('toast_error', 'Kredit sudah dilunasi.');
        }

        $request->validate([
            'settlement_method' => 'required|in:cash,transfer',
            'settlement_proof'  => 'required_if:settlement_method,transfer|nullable|file|mimes:jpg,jpeg,png,webp,pdf|max:4096',
        ], [
            'settlement_proof.required_if' => 'Unggah bukti transfer jika metode pelunasan Transfer.',
            'settlement_proof.mimes'       => 'Bukti transfer: JPG, PNG, WEBP, atau PDF.',
            'settlement_proof.max'         => 'Ukuran bukti transfer maksimal 4 MB.',
        ]);

        $proofPath = null;
        $proofAt   = null;

        if ($request->settlement_method === 'transfer') {
            if ($partnerOrder->settlement_proof) {
                Storage::disk('public')->delete($partnerOrder->settlement_proof);
            }
            $proofPath = $request->file('settlement_proof')->store('partner-settlement-proofs', 'public');
            $proofAt   = now();
        } elseif ($partnerOrder->settlement_proof) {
            Storage::disk('public')->delete($partnerOrder->settlement_proof);
        }

        $settledAt = now();

        $partnerOrder->update([
            'payment_status'      => PartnerOrder::PAYMENT_PAID,
            'settlement_method'   => $request->settlement_method,
            'settlement_proof'    => $proofPath,
            'settlement_proof_at' => $proofAt,
            'settled_at'          => $settledAt,
            'settled_by'          => Auth::id(),
        ]);

        ActivityLogService::log(
            'UPDATE',
            'Kredit Mitra',
            "Pelunasan kredit PO {$partnerOrder->order_no} — Rp " . number_format((float) $partnerOrder->total, 0, ',', '.')
        );

        $methodLabel = $request->settlement_method === 'cash' ? 'Tunai' : 'Transfer';
        $message = "PO {$partnerOrder->order_no} dilunasi ({$methodLabel}).";

        if ($request->input('redirect') === 'invoices') {
            return redirect()->route('invoices.index', ['filterStatus' => 'paid'])
                ->with('toast_success', $message . ' Masuk daftar Invoice Lunas.');
        }

        return redirect()->route('credits.index', [
            'tab'   => 'lunas',
            'month' => $settledAt->month,
            'year'  => $settledAt->year,
            'ref'   => $partnerOrder->order_no,
        ])->with('toast_success', "PO {$partnerOrder->order_no} dilunasi ({$methodLabel}). Masuk daftar Invoice Lunas.");
    }
}
