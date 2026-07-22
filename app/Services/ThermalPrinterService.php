<?php

namespace App\Services;

use App\Models\Sale;
use App\Models\Setting;
use Exception;
use Mike42\Escpos\Printer;
use Mike42\Escpos\PrintConnectors\NetworkPrintConnector;
use Mike42\Escpos\PrintConnectors\FilePrintConnector;
use Mike42\Escpos\PrintConnectors\WindowsPrintConnector;

class ThermalPrinterService
{
    protected $printerName;
    protected $connection;
    protected $ip;
    protected $port;
    protected $footer1;
    protected $footer2;
    protected $footer3;
    protected $qrisNmid;

    public function __construct()
    {
        // Load settings from database
        $this->connection = Setting::get('printer_connection', 'LAN');
        $this->ip = Setting::get('printer_ip', '192.168.1.100');
        $this->port = Setting::get('printer_port', '9100');
        $this->footer1 = Setting::get('printer_footer_1', 'Terima kasih telah berbelanja');
        $this->footer2 = Setting::get('printer_footer_2', 'di Apotek Almaira Banjarbaru');
        $this->footer3 = Setting::get('printer_footer_3', 'Semoga lekas sembuh dan sehat!');
        $this->qrisNmid = Setting::get('qris_nmid', 'ID1026522359276');
        
        // USB Printer name defaults to "POS-80" unless overridden in settings or config
        $this->printerName = config('apotek.printer_name', 'POS-80');
    }

    /**
     * Print a sale receipt.
     * Returns array ['success' => bool, 'message' => string]
     */
    public function printReceipt(Sale $sale)
    {
        $connector = null;

        try {
            if ($this->connection === 'LAN') {
                if (empty($this->ip)) {
                    throw new Exception("IP Address printer LAN belum diatur!");
                }
                $connector = new NetworkPrintConnector($this->ip, $this->port, 5); // 5s timeout
            } elseif ($this->connection === 'USB') {
                // WindowsPrintConnector is standard for Windows USB printing
                $connector = new WindowsPrintConnector($this->printerName);
            } else {
                // Serial connection
                $connector = new FilePrintConnector("COM1");
            }

            $printer = new Printer($connector);
            
            // 80mm columns width: usually 48 characters
            $w = 48;

            /* 1. Header */
            $printer->setJustification(Printer::JUSTIFY_CENTER);
            $printer->selectPrintMode(Printer::MODE_DOUBLE_WIDTH | Printer::MODE_DOUBLE_HEIGHT);
            $printer->text(Setting::get('apotek_name', 'APOTEK ALMAIRA') . "\n");
            
            $printer->selectPrintMode(Printer::MODE_FONT_A);
            $printer->text("Telp/WA: " . Setting::get('apotek_phone', '0851-6665-7070') . "\n");
            $printer->text(Setting::get('apotek_address', 'Jl. Nuri No.14 RT/RW 001/005, Kel. Komet, Banjarbaru') . "\n");
            $printer->text(str_repeat("=", $w) . "\n");

            /* 2. Metadata */
            $printer->setJustification(Printer::JUSTIFY_LEFT);
            $printer->text($this->formatRow($sale->document_label, $sale->invoice_no, $w) . "\n");
            $printer->text($this->formatRow("Tanggal", $sale->sold_at->format('d/m/Y H:i'), $w) . "\n");
            $printer->text($this->formatRow("Kasir", $sale->user?->name ?? 'Kasir', $w) . "\n");
            $printer->text($this->formatRow("Pelanggan", $sale->customer_name ?: 'Umum', $w) . "\n");
            $printer->text(str_repeat("-", $w) . "\n");

            /* 3. Items Headers */
            $printer->text(str_pad("NAMA PRODUK", 28, " ") . str_pad("QTY", 6, " ", STR_PAD_BOTH) . str_pad("TOTAL", 14, " ", STR_PAD_LEFT) . "\n");
            $printer->text(str_repeat("-", $w) . "\n");

            /* 4. Items List */
            foreach ($sale->items as $item) {
                // Print product name (can wrap)
                $nameLines = wordwrap($item->product_name, 28, "\n", true);
                $lines = explode("\n", $nameLines);
                
                // First line of name
                $firstLine = array_shift($lines);
                $printer->text(str_pad($firstLine, 28, " "));
                
                // Print qty and subtotal on the first line
                $qtyStr = $item->quantity . "x";
                $subtotalStr = number_format($item->subtotal, 0, ',', '.');
                $printer->text(str_pad($qtyStr, 6, " ", STR_PAD_BOTH) . str_pad($subtotalStr, 14, " ", STR_PAD_LEFT) . "\n");
                
                // Print remaining name lines if wrapped
                foreach ($lines as $line) {
                    $printer->text(str_pad($line, 28, " ") . "\n");
                }

                // Print detail price and discount
                $priceStr = "  @Rp " . number_format($item->unit_price, 0, ',', '.');
                $priceTypeLabel = $item->price_type === 'wholesale' ? 'Grosir' : 'Eceran';
                $priceStr .= " ({$priceTypeLabel})";
                
                if ($item->discount_percent > 0) {
                    $priceStr .= " Disk:" . number_format($item->discount_percent, 1, ',', '.') . "%";
                }
                $printer->text($priceStr . "\n");
            }
            $printer->text(str_repeat("-", $w) . "\n");

            /* 5. Totals Summary */
            $printer->text($this->formatRow("Subtotal", "Rp " . number_format($sale->subtotal, 0, ',', '.'), $w) . "\n");
            
            if ($sale->discount_amount > 0) {
                $discLabel = "Diskon (" . number_format($sale->discount_percent, 1, ',', '.') . "%)";
                $printer->text($this->formatRow($discLabel, "-Rp " . number_format($sale->discount_amount, 0, ',', '.'), $w) . "\n");
            }
            
            if ($sale->ppn_active) {
                $ppnLabel = "PPN (" . number_format($sale->ppn_percent, 1, ',', '.') . "%)";
                $printer->text($this->formatRow($ppnLabel, "Rp " . number_format($sale->ppn_amount, 0, ',', '.'), $w) . "\n");
                $printer->text(str_pad($sale->ppn_bearer === 'Ditanggung Penjual' ? "  *Ditanggung Penjual (Absorbed)" : "  *Ditanggung Pembeli (Added)", $w) . "\n");
            }
            $printer->text(str_repeat("=", $w) . "\n");

            /* 6. Grand Total & Payment info */
            $printer->selectPrintMode(Printer::MODE_DOUBLE_WIDTH);
            $printer->text($this->formatRow("TOTAL", "Rp " . number_format($sale->total, 0, ',', '.'), $w / 2) . "\n");
            $printer->selectPrintMode(Printer::MODE_FONT_A);
            
            $printer->text(str_repeat("-", $w) . "\n");
            $printer->text($this->formatRow("Metode Bayar", $sale->payment_method, $w) . "\n");

            if ($sale->payment_method === 'Tunai') {
                $printer->text($this->formatRow("Bayar (Tunai)", "Rp " . number_format($sale->cash_received, 0, ',', '.'), $w) . "\n");
                $printer->text($this->formatRow("Kembali", "Rp " . number_format($sale->change_amount, 0, ',', '.'), $w) . "\n");
            } else {
                $printer->setJustification(Printer::JUSTIFY_CENTER);
                $printer->text("QRIS BNI Wondr - NMID: " . $this->qrisNmid . "\n");
                $printer->setJustification(Printer::JUSTIFY_LEFT);
            }

            if ($sale->notes) {
                $printer->text(str_repeat("-", $w) . "\n");
                $printer->text("Catatan: " . $sale->notes . "\n");
            }
            $printer->text(str_repeat("=", $w) . "\n");

            /* 7. Footer */
            $printer->setJustification(Printer::JUSTIFY_CENTER);
            if (!empty($this->footer1)) $printer->text($this->footer1 . "\n");
            if (!empty($this->footer2)) $printer->text($this->footer2 . "\n");
            if (!empty($this->footer3)) $printer->text($this->footer3 . "\n");
            $printer->text(str_repeat("=", $w) . "\n");
            
            // Feed paper and cut
            $printer->feed(3);
            $printer->cut();
            $printer->close();

            return [
                'success' => true,
                'message' => 'Struk berhasil dicetak!'
            ];

        } catch (Exception $e) {
            if ($printer ?? null) {
                $printer->close();
            }
            return [
                'success' => false,
                'message' => 'Gagal mencetak struk: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Helper to format a row with left-aligned key and right-aligned value.
     */
    protected function formatRow($key, $value, $width)
    {
        $spaces = $width - strlen($key) - strlen($value);
        if ($spaces < 0) {
            $spaces = 0;
        }
        return $key . str_repeat(" ", $spaces) . $value;
    }
}
