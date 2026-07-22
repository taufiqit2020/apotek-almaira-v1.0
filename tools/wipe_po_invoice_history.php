<?php
/**
 * Hapus riwayat PO Mitra + Invoice dari database.
 * Jalankan: php tools/wipe_po_invoice_history.php
 */

require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

echo "=== HAPUS RIWAYAT PO + INVOICE ===\n";

DB::beginTransaction();
try {
    $driver = DB::getDriverName();
    if ($driver === 'sqlite') {
        DB::statement('PRAGMA foreign_keys = OFF');
    } elseif ($driver === 'mysql') {
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
    }

    // 1) PO Mitra
    $poi = Schema::hasTable('partner_order_items') ? DB::table('partner_order_items')->count() : 0;
    $po = Schema::hasTable('partner_orders') ? DB::table('partner_orders')->count() : 0;
    if (Schema::hasTable('partner_order_items')) {
        DB::table('partner_order_items')->delete();
    }
    if (Schema::hasTable('partner_orders')) {
        DB::table('partner_orders')->delete();
    }
    echo "partner_order_items dihapus: {$poi}\n";
    echo "partner_orders dihapus     : {$po}\n";

    // Keranjang mitra (jika ada) — biar PO baru bersih
    foreach (['partner_carts', 'partner_cart_items', 'cart_items'] as $cartTable) {
        if (Schema::hasTable($cartTable)) {
            $n = DB::table($cartTable)->count();
            DB::table($cartTable)->delete();
            echo "{$cartTable} dihapus: {$n}\n";
        }
    }

    // 2) Invoice POS (sales payment_method = invoice) + itemnya
    $invoiceSaleIds = [];
    if (Schema::hasTable('sales')) {
        $invoiceSaleIds = DB::table('sales')
            ->where('payment_method', 'invoice')
            ->pluck('id')
            ->all();
    }

    $saleItemsDeleted = 0;
    if (! empty($invoiceSaleIds) && Schema::hasTable('sale_items')) {
        $saleItemsDeleted = DB::table('sale_items')->whereIn('sale_id', $invoiceSaleIds)->delete();
    }
    $salesDeleted = 0;
    if (! empty($invoiceSaleIds)) {
        $salesDeleted = DB::table('sales')->whereIn('id', $invoiceSaleIds)->delete();
    }
    echo "sale_items (invoice) dihapus: {$saleItemsDeleted}\n";
    echo "sales (invoice) dihapus     : {$salesDeleted}\n";

    // 3) Sales yang terkait partner (riwayat PO yang sudah jadi penjualan)
    if (Schema::hasTable('sales') && Schema::hasColumn('sales', 'partner_id')) {
        $partnerSaleIds = DB::table('sales')->whereNotNull('partner_id')->pluck('id')->all();
        if (! empty($partnerSaleIds)) {
            $psi = Schema::hasTable('sale_items')
                ? DB::table('sale_items')->whereIn('sale_id', $partnerSaleIds)->delete()
                : 0;
            $ps = DB::table('sales')->whereIn('id', $partnerSaleIds)->delete();
            echo "sale_items (partner) dihapus: {$psi}\n";
            echo "sales (partner) dihapus     : {$ps}\n";
        }
    }

    // Reset sqlite sequence
    if ($driver === 'sqlite') {
        foreach (['partner_orders', 'partner_order_items', 'sales', 'sale_items'] as $seq) {
            try {
                DB::statement("DELETE FROM sqlite_sequence WHERE name = ?", [$seq]);
            } catch (Throwable $e) {
                // ignore
            }
        }
        DB::statement('PRAGMA foreign_keys = ON');
    } elseif ($driver === 'mysql') {
        DB::statement('SET FOREIGN_KEY_CHECKS=1');
    }

    DB::commit();
} catch (Throwable $e) {
    DB::rollBack();
    fwrite(STDERR, 'ERROR: ' . $e->getMessage() . "\n");
    exit(1);
}

echo "\n=== SISA ===\n";
echo 'partner_orders = ' . (Schema::hasTable('partner_orders') ? DB::table('partner_orders')->count() : 0) . PHP_EOL;
echo 'partner_order_items = ' . (Schema::hasTable('partner_order_items') ? DB::table('partner_order_items')->count() : 0) . PHP_EOL;
echo 'sales invoice = ' . (Schema::hasTable('sales') ? DB::table('sales')->where('payment_method', 'invoice')->count() : 0) . PHP_EOL;
echo 'sales total = ' . (Schema::hasTable('sales') ? DB::table('sales')->count() : 0) . PHP_EOL;
echo "Selesai OK.\n";
