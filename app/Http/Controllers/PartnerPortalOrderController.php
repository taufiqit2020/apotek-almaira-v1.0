<?php

namespace App\Http\Controllers;

use App\Models\Partner;
use App\Models\PartnerOrder;
use App\Models\Setting;
use App\Services\ActivityLogService;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class PartnerPortalOrderController extends Controller
{
    private function apotekData(): array
    {
        return [
            'apotekName'    => Setting::get('apotek_name', 'Apotek Almaira'),
            'apotekAddress' => Setting::get('apotek_address', 'Jl. Panglima Batur No. 16, Kel. Komet, Kec. Banjarbaru Utara, Kota Banjarbaru, Kalsel 70714'),
            'apotekPhone'   => Setting::get('apotek_phone', '0851-6665-7070'),
            'bankName'      => Setting::get('bank_name', 'BNI'),
            'bankAccount'   => Setting::get('bank_account', '2050169349'),
            'bankHolder'    => Setting::get('bank_holder', 'PT NUR MADANI FARMA'),
        ];
    }

    private function requireMitraPartner(): Partner
    {
        $user = Auth::user();
        abort_unless($user && $user->isMitra(), 403);
        $partner = $user->partner;
        abort_unless($partner && $partner->isApproved(), 403, 'Akun mitra belum aktif.');

        return $partner;
    }

    private function findOwnOrder(Partner $partner, PartnerOrder $order): PartnerOrder
    {
        abort_unless($order->partner_id === $partner->id, 404);
        return $order->load(['items.product.category', 'items.product.unit', 'partner']);
    }

    public function index(Request $request)
    {
        $partner = $this->requireMitraPartner();

        $orders = PartnerOrder::withCount('items')
            ->where('partner_id', $partner->id)
            ->when($request->status, fn ($q) => $q->where('status', $request->status))
            ->latest()
            ->paginate(10)
            ->withQueryString();

        $stats = [
            'total'     => PartnerOrder::where('partner_id', $partner->id)->count(),
            'open'      => PartnerOrder::where('partner_id', $partner->id)->whereIn('status', [PartnerOrder::STATUS_SUBMITTED, PartnerOrder::STATUS_CONFIRMED])->count(),
            'fulfilled' => PartnerOrder::where('partner_id', $partner->id)->where('status', PartnerOrder::STATUS_FULFILLED)->count(),
        ];

        return view('partners.portal.orders-index', array_merge($this->apotekData(), [
            'partner' => $partner,
            'orders'  => $orders,
            'stats'   => $stats,
        ]));
    }

    public function show(PartnerOrder $order)
    {
        $partner = $this->requireMitraPartner();
        $order = $this->findOwnOrder($partner, $order);

        return view('partners.portal.order-show', array_merge($this->apotekData(), [
            'partner' => $partner,
            'order'   => $order,
        ]));
    }

    public function uploadProof(Request $request, PartnerOrder $order)
    {
        $partner = $this->requireMitraPartner();
        $order = $this->findOwnOrder($partner, $order);

        if (!$order->canUploadProof()) {
            return back()->with('toast_error', 'Bukti transfer tidak dapat diunggah untuk PO ini.');
        }

        $request->validate([
            'transfer_proof' => 'required|file|mimes:jpg,jpeg,png,webp,pdf|max:4096',
        ], [
            'transfer_proof.required' => 'Pilih file bukti transfer.',
            'transfer_proof.mimes'    => 'Format bukti: JPG, PNG, WEBP, atau PDF.',
            'transfer_proof.max'      => 'Ukuran maksimal 4 MB.',
        ]);

        if ($order->transfer_proof) {
            Storage::disk('public')->delete($order->transfer_proof);
        }

        $path = $request->file('transfer_proof')->store('partner-transfer-proofs', 'public');

        $order->update([
            'transfer_proof'    => $path,
            'transfer_proof_at' => now(),
            'payment_status'    => PartnerOrder::PAYMENT_AWAITING,
        ]);

        ActivityLogService::log('UPDATE', 'PO Mitra', "Bukti transfer diunggah untuk {$order->order_no}");

        $order->loadMissing('partner');
        NotificationService::triggerPartnerTransferProof($order);

        return back()->with('toast_success', 'Bukti transfer berhasil diunggah. Menunggu konfirmasi apotek.');
    }

    public function cancel(Request $request, PartnerOrder $order)
    {
        $partner = $this->requireMitraPartner();
        $order = $this->findOwnOrder($partner, $order);

        if (!$order->canBeCancelledByPartner()) {
            return back()->with('toast_error', 'PO ini tidak dapat dibatalkan.');
        }

        $request->validate([
            'cancel_reason' => 'nullable|string|max:255',
        ]);

        $order->update([
            'status'         => PartnerOrder::STATUS_CANCELLED,
            'payment_status' => PartnerOrder::PAYMENT_CANCELLED,
            'cancel_reason'  => $request->cancel_reason ?: 'Dibatalkan oleh mitra',
            'cancelled_at'   => now(),
        ]);

        ActivityLogService::log('CANCEL', 'PO Mitra', "PO {$order->order_no} dibatalkan oleh mitra");

        return redirect()->route('mitra.orders.show', $order)->with('toast_success', 'PO dibatalkan.');
    }
}
