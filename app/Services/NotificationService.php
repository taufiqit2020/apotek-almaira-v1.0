<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Support\Facades\Log;

class NotificationService
{
    public static function sendWa($message)
    {
        $enabled = Setting::get('notif_alert_wa', 'false') === 'true';
        if (!$enabled) return;

        $target = Setting::get('notif_wa_number', '0851-6665-7070');
        
        Log::info("🔔 [SIMULATION WA WEBHOOK] Sending to {$target}:");
        Log::info("--------------------------------------------------");
        Log::info($message);
        Log::info("--------------------------------------------------");
    }

    public static function sendEmail($subject, $message)
    {
        $enabled = Setting::get('notif_alert_email', 'false') === 'true';
        if (!$enabled) return;

        $target = Setting::get('notif_email_address', 'owner@apotekalmaira.com');

        Log::info("📧 [SIMULATION EMAIL] Sending to {$target}:");
        Log::info("Subject: {$subject}");
        Log::info("--------------------------------------------------");
        Log::info($message);
        Log::info("--------------------------------------------------");
    }
    
    public static function triggerStockAlert($product)
    {
        $stock = $product->stock;
        $min = $product->stock_min;
        
        $msg = "⚠️ [ALERT STOK KRITIS] Produk '{$product->name}' (Kode: {$product->code}) saat ini tersisa {$stock} pcs (Batas minimum: {$min} pcs). Segera lakukan reorder!";
        
        if (Setting::get('notif_alert_stock', 'true') === 'true') {
            self::sendWa($msg);
            self::sendEmail("Apotek Almaira - Alert Stok Kritis: {$product->name}", $msg);
        }
    }

    public static function triggerBackupAlert($filename, $size)
    {
        $msg = "💾 [SYSTEM BACKUP SUCCESS] Database backup berhasil dibuat dengan nama file: {$filename} (Ukuran: {$size} bytes) pada " . date('d-m-Y H:i:s');
        
        if (Setting::get('notif_alert_backup', 'true') === 'true') {
            self::sendWa($msg);
            self::sendEmail("Apotek Almaira - Backup Database Berhasil", $msg);
        }
    }

    public static function triggerPartnerOrderSubmitted($order): void
    {
        $partnerName = $order->partner?->name ?? 'Mitra';
        $total = number_format((float) $order->total, 0, ',', '.');
        $method = $order->payment_method_label;
        $msg = "📦 [PO MITRA BARU] {$order->order_no} dari {$partnerName}. Total Rp {$total}. Bayar: {$method}. Segera cek di menu PO Mitra.";

        self::sendWa($msg);
        self::sendEmail("Apotek Almaira - PO Mitra Baru: {$order->order_no}", $msg);
    }

    public static function triggerPartnerTransferProof($order): void
    {
        $partnerName = $order->partner?->name ?? 'Mitra';
        $total = number_format((float) $order->total, 0, ',', '.');
        $msg = "💳 [BUKTI TRANSFER PO] {$order->order_no} ({$partnerName}) — Rp {$total}. Mitra telah mengunggah bukti transfer. Mohon konfirmasi pembayaran.";

        self::sendWa($msg);
        self::sendEmail("Apotek Almaira - Bukti Transfer PO: {$order->order_no}", $msg);
    }
}
