<?php

namespace App\Services;

use App\Models\Category;
use App\Models\Product;
use App\Models\Supplier;
use App\Models\Unit;
use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\IOFactory;

class ProductImportService
{
    /**
     * Parse angka format Indonesia atau Inggris menjadi float.
     * Menangani: "1.250,00" | "1,250.00" | "Rp 3.000" | 3000 | "3000"
     */
    public static function parseNumber($val): float
    {
        if ($val === null || $val === '') {
            return 0.0;
        }
        if (is_numeric($val)) {
            return (float) $val;
        }
        $clean = trim((string) $val);
        $clean = preg_replace('/[^\d,.-]/', '', $clean);

        if ($clean === '' || $clean === '-') {
            return 0.0;
        }

        if (strpos($clean, '.') !== false && strpos($clean, ',') !== false) {
            if (strrpos($clean, '.') > strrpos($clean, ',')) {
                $clean = str_replace(',', '', $clean);
            } else {
                $clean = str_replace('.', '', $clean);
                $clean = str_replace(',', '.', $clean);
            }
        } elseif (strpos($clean, ',') !== false) {
            if (preg_match('/,\d{3}$/', $clean)) {
                $clean = str_replace(',', '', $clean);
            } else {
                $clean = str_replace(',', '.', $clean);
            }
        } elseif (strpos($clean, '.') !== false) {
            if (preg_match('/\.\d{3}$/', $clean) && substr_count($clean, '.') === 1) {
                $clean = str_replace('.', '', $clean);
            }
        }

        return is_numeric($clean) ? (float) $clean : 0.0;
    }

    /**
     * Parse tanggal kadaluarsa dari berbagai format ke Y-m-d.
     */
    public static function parseDate($val): ?string
    {
        if ($val === null || $val === '') {
            return null;
        }

        $str = trim((string) $val);

        if (in_array(strtolower($str), ['n/a', '-', '0', 'null', 'kosong', 'tidak ada'])) {
            return null;
        }

        if (is_numeric($val)) {
            try {
                $date = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject((float) $val);
                return $date->format('Y-m-d');
            } catch (\Exception $e) {
                return null;
            }
        }

        $clean = strtolower($str);

        if (preg_match('/^(\d{4})-(\d{1,2})-(\d{1,2})$/', $str, $m)) {
            return sprintf('%04d-%02d-%02d', $m[1], $m[2], $m[3]);
        }

        if (preg_match('/^(\d{1,2})[\/\-](\d{1,2})[\/\-](\d{4})$/', $str, $m)) {
            return sprintf('%04d-%02d-%02d', $m[3], $m[2], $m[1]);
        }

        $months = [
            'januari' => '01', 'jan' => '01',
            'februari' => '02', 'feb' => '02',
            'maret' => '03', 'mar' => '03',
            'april' => '04', 'apr' => '04',
            'mei' => '05',
            'juni' => '06', 'jun' => '06',
            'juli' => '07', 'jul' => '07',
            'agustus' => '08', 'agust' => '08', 'ags' => '08', 'aug' => '08',
            'september' => '09', 'sept' => '09', 'sep' => '09',
            'oktober' => '10', 'okt' => '10', 'oct' => '10',
            'november' => '11', 'nov' => '11',
            'desember' => '12', 'des' => '12', 'dec' => '12',
        ];

        $ts = strtotime($clean);
        if ($ts !== false) {
            return date('Y-m-d', $ts);
        }

        if (preg_match('/(\d{1,2})\s+([a-z]+)\s+(\d{4})/', $clean, $m)) {
            $day = str_pad($m[1], 2, '0', STR_PAD_LEFT);
            $year = $m[3];
            foreach ($months as $name => $num) {
                if (str_starts_with($m[2], $name) || str_starts_with($name, $m[2])) {
                    return "{$year}-{$num}-{$day}";
                }
            }
        }

        if (preg_match('/([a-z]+)\s+(\d{4})/', $clean, $m)) {
            $year = $m[2];
            foreach ($months as $name => $num) {
                if (str_starts_with($m[1], $name) || str_starts_with($name, $m[1])) {
                    return "{$year}-{$num}-01";
                }
            }
        }

        return null;
    }

    public static function parseBool($val): bool
    {
        if ($val === null || $val === '') {
            return false;
        }
        if (is_bool($val)) {
            return $val;
        }
        if (is_numeric($val)) {
            return (int) $val === 1;
        }

        $str = strtolower(trim((string) $val));
        return in_array($str, ['ya', 'yes', 'y', 'true', '1', 'resep', 'obat keras'], true);
    }

    /**
     * Deteksi versi template:
     * - database_kandungan: database.xlsx terbaru (kandungan + nama setelah fungsi)
     * - databaseproduk: format baru (kandungan + bentuk + rute + indikasi, nama di kolom awal)
     * - database: file master stok lama (database.xlsx)
     * - master  : kolom mengikuti form Master Produk (A-R)
     * - v9      : template 9 kolom lama (A-I)
     * - legacy  : template sangat lama
     */
    private function detectTemplateVersion(array $headerRow): string
    {
        $joined = strtolower(implode(' | ', array_map(fn ($v) => trim((string) $v), $headerRow)));

        if ($this->isDatabaseKandunganWorkbookHeader($joined)) {
            return 'database_kandungan';
        }

        if ($this->isDatabaseProdukWorkbookHeader($joined)) {
            return 'databaseproduk';
        }

        if ($this->isDatabaseWorkbookHeader($joined)) {
            return 'database';
        }

        if (
            str_contains($joined, 'barcode') ||
            str_contains($joined, 'supplier') ||
            str_contains($joined, 'butuh resep') ||
            str_contains($joined, 'harga grosir') ||
            str_contains($joined, 'pabrik') ||
            str_contains($joined, 'komposisi')
        ) {
            return 'master';
        }

        $colG = isset($headerRow[6]) ? strtolower(trim((string) $headerRow[6])) : '';
        if (str_contains($colG, 'abaikan') || str_contains($colG, 'ignore')) {
            return 'legacy';
        }

        return 'v9';
    }

    /**
     * Format database.xlsx 2026 (Sheet 4):
     * NO | KODE | KATEGORI | GOLONGAN | KANDUNGAN | BENTUK | RUTE | FUNGSI | NAMA | STOK | ...
     */
    private function isDatabaseKandunganWorkbookHeader(string $joined): bool
    {
        if (! str_contains($joined, 'kandungan') || ! str_contains($joined, 'kategori')) {
            return false;
        }

        if (str_contains($joined, 'harga jual apotek') || str_contains($joined, 'jumlah stok')) {
            return true;
        }

        $posKategori = strpos($joined, 'kategori');
        $posKandungan = strpos($joined, 'kandungan');
        $posNama = strpos($joined, 'nama');

        return $posKategori !== false
            && $posKandungan !== false
            && $posNama !== false
            && $posKategori < $posKandungan
            && $posNama > $posKandungan;
    }

    private function isDatabaseProdukWorkbookHeader(string $joined): bool
    {
        if ($this->isDatabaseKandunganWorkbookHeader($joined)) {
            return false;
        }

        return str_contains($joined, 'kandungan')
            && (str_contains($joined, 'bentuk sediaan') || str_contains($joined, 'fungsi/indikasi') || str_contains($joined, 'rute'));
    }

    private function isDatabaseWorkbookHeader(string $joined): bool
    {
        if ($this->isDatabaseProdukWorkbookHeader($joined)) {
            return false;
        }

        return (
            str_contains($joined, 'golongan') &&
            (str_contains($joined, 'bentuk sediaan') || str_contains($joined, 'fungsi'))
        ) || (
            str_contains($joined, 'harga jual apotek') ||
            str_contains($joined, 'tanggal expayed') ||
            str_contains($joined, 'hpp per pcs')
        );
    }

    private function requiresPrescriptionFromGolongan(?string $golongan): bool
    {
        $golonganLower = strtolower((string) $golongan);

        return str_contains($golonganLower, 'keras')
            || str_contains($golonganLower, 'narkotika')
            || str_contains($golonganLower, 'psikotropika');
    }

    private function parseMarginPercent($raw): int
    {
        if (is_string($raw) && str_contains($raw, '%')) {
            return max(0, min(30, (int) round(self::parseNumber(str_replace('%', '', $raw)))));
        }

        $marginRaw = self::parseNumber($raw ?? 0);
        if ($marginRaw > 0 && $marginRaw < 1) {
            return max(0, min(30, (int) round($marginRaw * 100)));
        }
        if ($marginRaw >= 1) {
            return max(0, min(30, (int) round($marginRaw)));
        }

        return 0;
    }

    private function emptyMappedRow(): array
    {
        return [
            'name' => null,
            'code' => null,
            'barcode' => null,
            'categoryName' => null,
            'unitName' => null,
            'supplierName' => null,
            'manufacturer' => null,
            'drugClass' => null,
            'dosageForm' => null,
            'route' => null,
            'composition' => null,
            'description' => null,
            'requiresPrescription' => false,
            'purchasePrice' => 0.0,
            'sellPrice' => 0.0,
            'wholesalePrice' => 0.0,
            'hetMarkup' => 0,
            'wholesaleMarkup' => 0,
            'hetPrice' => 0.0,
            'stock' => 0,
            'stockMin' => 10,
            'expiredDate' => null,
        ];
    }

    private function resolveCategory(?string $categoryName): ?int
    {
        if (empty($categoryName)) {
            return null;
        }

        try {
            $category = Category::firstOrCreate(
                ['name' => $categoryName],
                ['slug' => Str::slug($categoryName), 'is_active' => true]
            );
            return $category->id;
        } catch (\Exception $e) {
            return Category::where('name', $categoryName)->value('id');
        }
    }

    private function resolveUnit(?string $unitName): ?int
    {
        // Default Pcs jika satuan kosong — agar POS/katalog tetap konsisten.
        if (empty($unitName)) {
            $unitName = 'Pcs';
        }

        $unitFormatted = ucfirst(strtolower(trim($unitName)));
        try {
            $unit = Unit::firstOrCreate(
                ['name' => $unitFormatted],
                ['symbol' => strtolower(substr($unitFormatted, 0, 6))]
            );
            return $unit->id;
        } catch (\Exception $e) {
            return Unit::where('name', $unitFormatted)->value('id');
        }
    }

    private function resolveSupplier(?string $supplierName): ?int
    {
        if (empty($supplierName)) {
            return null;
        }

        try {
            $supplier = Supplier::firstOrCreate(
                ['name' => $supplierName],
                ['is_active' => true]
            );
            return $supplier->id;
        } catch (\Exception $e) {
            return Supplier::where('name', $supplierName)->value('id');
        }
    }

    /**
     * Mapping database.xlsx terbaru (header baris 1, data mulai baris 2):
     * A NO, B KODE, C KATEGORI, D GOLONGAN, E KANDUNGAN, F BENTUK SEDIAAN, G RUTE,
     * H FUNGSI/INDIKASI, I NAMA, J STOK, K SATUAN, L HPP, M MARGIN, N HPP+MARGIN,
     * O HET, P HARGA JUAL, Q-S FAKTUR..., T TANGGAL EXPAYED
     */
    private function mapDatabaseKandunganRow(array $rowData): array
    {
        $mapped = $this->emptyMappedRow();

        $golongan = isset($rowData[3]) ? trim((string) $rowData[3]) : null;
        $purchasePrice = self::parseNumber($rowData[11] ?? 0);
        $pricedWithMargin = self::parseNumber($rowData[13] ?? 0);
        $hetPrice = self::parseNumber($rowData[14] ?? 0);
        $sellPrice = self::parseNumber($rowData[15] ?? 0);
        $hetMarkup = $this->parseMarginPercent($rowData[12] ?? 0);

        if ($sellPrice <= 0.0 && $pricedWithMargin > 0) {
            $sellPrice = $pricedWithMargin;
        }
        if ($sellPrice <= 0.0 && $purchasePrice > 0) {
            $sellPrice = round($purchasePrice * (1 + ($hetMarkup / 100)));
        }

        // Grosir otomatis dari HPP + markup grosir (selaras form master produk).
        $wholesalePrice = $purchasePrice > 0 && $hetMarkup > 0
            ? Product::calcWholesaleFromPurchase($purchasePrice, (int) $hetMarkup, $sellPrice)
            : 0.0;
        if ($wholesalePrice <= 0 && $sellPrice > 0) {
            $wholesalePrice = (float) max(0, $sellPrice - 1);
        }

        $mapped['name'] = isset($rowData[8]) ? trim((string) $rowData[8]) : null;
        $mapped['code'] = isset($rowData[1]) ? trim((string) $rowData[1]) : null;
        $mapped['categoryName'] = isset($rowData[2]) ? trim((string) $rowData[2]) : null;
        $mapped['drugClass'] = $golongan ?: null;
        $mapped['composition'] = isset($rowData[4]) ? trim((string) $rowData[4]) : null;
        $mapped['dosageForm'] = isset($rowData[5]) ? trim((string) $rowData[5]) : null;
        $mapped['route'] = isset($rowData[6]) ? trim((string) $rowData[6]) : null;
        $mapped['description'] = isset($rowData[7]) ? trim((string) $rowData[7]) : null;
        $mapped['requiresPrescription'] = $this->requiresPrescriptionFromGolongan($golongan);
        $mapped['unitName'] = isset($rowData[10]) ? trim((string) $rowData[10]) : null;
        $mapped['purchasePrice'] = $purchasePrice;
        $mapped['sellPrice'] = $sellPrice;
        $mapped['wholesalePrice'] = $wholesalePrice;
        $mapped['hetMarkup'] = $hetMarkup;
        $mapped['wholesaleMarkup'] = $hetMarkup;
        $mapped['hetPrice'] = $hetPrice;
        $mapped['stock'] = (int) self::parseNumber($rowData[9] ?? 0);
        $mapped['expiredDate'] = self::parseDate($rowData[19] ?? null);

        return $mapped;
    }

    /**
     * Mapping file databaseproduk.xlsx (header baris 1, data mulai baris 2):
     * A NO, B KODE, C NAMA, D KATEGORI, E GOLONGAN, F KANDUNGAN,
     * G BENTUK SEDIAAN, H RUTE, I FUNGSI/INDIKASI, J NAMA (alt),
     * K STOK, L SATUAN, M HPP, N MARGIN, O HPP+MARGIN,
     * P-S FAKTUR..., T HET, U HARGA JUAL, V KET, W-Y FAKTUR..., Z EXPAYED
     */
    private function mapDatabaseProdukRow(array $rowData): array
    {
        $mapped = $this->emptyMappedRow();

        $namePrimary = isset($rowData[2]) ? trim((string) $rowData[2]) : '';
        $nameAlt = isset($rowData[9]) ? trim((string) $rowData[9]) : '';
        $golongan = isset($rowData[4]) ? trim((string) $rowData[4]) : null;
        $purchasePrice = self::parseNumber($rowData[12] ?? 0);
        $pricedWithMargin = self::parseNumber($rowData[14] ?? 0);
        $hetPrice = self::parseNumber($rowData[19] ?? 0);
        $sellPrice = self::parseNumber($rowData[20] ?? 0);
        $hetMarkup = $this->parseMarginPercent($rowData[13] ?? 0);

        if ($sellPrice <= 0.0 && $pricedWithMargin > 0) {
            $sellPrice = $pricedWithMargin;
        }
        if ($sellPrice <= 0.0 && $purchasePrice > 0) {
            $sellPrice = round($purchasePrice * (1 + ($hetMarkup / 100)));
        }

        $mapped['name'] = $namePrimary !== '' ? $namePrimary : ($nameAlt !== '' ? $nameAlt : null);
        $mapped['code'] = isset($rowData[1]) ? trim((string) $rowData[1]) : null;
        $mapped['categoryName'] = isset($rowData[3]) ? trim((string) $rowData[3]) : null;
        $mapped['drugClass'] = $golongan ?: null;
        $mapped['composition'] = isset($rowData[5]) ? trim((string) $rowData[5]) : null;
        $mapped['dosageForm'] = isset($rowData[6]) ? trim((string) $rowData[6]) : null;
        $mapped['route'] = isset($rowData[7]) ? trim((string) $rowData[7]) : null;
        $mapped['description'] = isset($rowData[8]) ? trim((string) $rowData[8]) : null;
        $mapped['requiresPrescription'] = $this->requiresPrescriptionFromGolongan($golongan);
        $mapped['unitName'] = isset($rowData[11]) ? trim((string) $rowData[11]) : null;
        $mapped['purchasePrice'] = $purchasePrice;
        $mapped['sellPrice'] = $sellPrice;
        $mapped['wholesalePrice'] = ($purchasePrice > 0 && $hetMarkup > 0)
            ? Product::calcWholesaleFromPurchase($purchasePrice, (int) $hetMarkup, $sellPrice)
            : $sellPrice;
        $mapped['hetMarkup'] = $hetMarkup;
        $mapped['wholesaleMarkup'] = $hetMarkup;
        $mapped['hetPrice'] = $hetPrice;
        $mapped['stock'] = (int) self::parseNumber($rowData[10] ?? 0);
        $mapped['expiredDate'] = self::parseDate($rowData[25] ?? null);

        return $mapped;
    }

    /**
     * Mapping file database.xlsx lama (header baris 1, data mulai baris 2):
     * A NO, B KODE, C KATEGORI, D GOLONGAN, E BENTUK SEDIAAN, F RUTE,
     * G FUNGSI/INDIKASI, H NAMA, I STOK PCS, J SATUAN, K HPP PCS,
     * L MARGIN, M HPP+MARGIN, N-Q FAKTUR..., R HET, S HARGA JUAL,
     * T KET, U-W FAKTUR..., X TANGGAL EXPAYED
     */
    private function mapDatabaseRow(array $rowData): array
    {
        $mapped = $this->emptyMappedRow();

        $golongan = isset($rowData[3]) ? trim((string) $rowData[3]) : null;
        $purchasePrice = self::parseNumber($rowData[10] ?? 0);
        $pricedWithMargin = self::parseNumber($rowData[12] ?? 0);
        $hetPrice = self::parseNumber($rowData[17] ?? 0);
        $sellPrice = self::parseNumber($rowData[18] ?? 0);
        $hetMarkup = $this->parseMarginPercent($rowData[11] ?? 0);

        if ($sellPrice <= 0.0 && $pricedWithMargin > 0) {
            $sellPrice = $pricedWithMargin;
        }
        if ($sellPrice <= 0.0 && $purchasePrice > 0) {
            $sellPrice = round($purchasePrice * (1 + ($hetMarkup / 100)));
        }

        $wholesalePrice = $purchasePrice > 0 && $hetMarkup > 0
            ? Product::calcWholesaleFromPurchase($purchasePrice, (int) $hetMarkup, $sellPrice)
            : $sellPrice;

        $mapped['name'] = isset($rowData[7]) ? trim((string) $rowData[7]) : null;
        $mapped['code'] = isset($rowData[1]) ? trim((string) $rowData[1]) : null;
        $mapped['categoryName'] = isset($rowData[2]) ? trim((string) $rowData[2]) : null;
        $mapped['drugClass'] = $golongan ?: null;
        $mapped['dosageForm'] = isset($rowData[4]) ? trim((string) $rowData[4]) : null;
        $mapped['route'] = isset($rowData[5]) ? trim((string) $rowData[5]) : null;
        $mapped['description'] = isset($rowData[6]) ? trim((string) $rowData[6]) : null;
        $mapped['requiresPrescription'] = $this->requiresPrescriptionFromGolongan($golongan);
        $mapped['unitName'] = isset($rowData[9]) ? trim((string) $rowData[9]) : null;
        $mapped['purchasePrice'] = $purchasePrice;
        $mapped['sellPrice'] = $sellPrice;
        $mapped['wholesalePrice'] = $wholesalePrice;
        $mapped['hetMarkup'] = $hetMarkup;
        $mapped['wholesaleMarkup'] = $hetMarkup;
        $mapped['hetPrice'] = $hetPrice;
        $mapped['stock'] = (int) self::parseNumber($rowData[8] ?? 0);
        $mapped['expiredDate'] = self::parseDate($rowData[23] ?? null);

        return $mapped;
    }

    private function mapMasterRow(array $rowData): array
    {
        // A NAMA, B KODE, C BARCODE, D KATEGORI, E SATUAN, F SUPPLIER,
        // G PABRIK, H KOMPOSISI/KANDUNGAN, I DESKRIPSI/INDIKASI, J BUTUH RESEP,
        // K HARGA BELI, L HARGA JUAL, M HARGA GROSIR, N HET MARKUP,
        // O HET, P STOK, Q STOK MIN, R KADALUARSA, S GOLONGAN, T BENTUK, U RUTE
        $mapped = $this->emptyMappedRow();

        $sellPrice = self::parseNumber($rowData[11] ?? 0);
        $wholesalePrice = self::parseNumber($rowData[12] ?? 0);
        $stockMin = (int) self::parseNumber($rowData[16] ?? 10);
        if ($stockMin <= 0) {
            $stockMin = 10;
        }
        if ($wholesalePrice <= 0.0 && $sellPrice > 0) {
            $wholesalePrice = $sellPrice;
        }

        $mapped['name'] = isset($rowData[0]) ? trim((string) $rowData[0]) : null;
        $mapped['code'] = isset($rowData[1]) ? trim((string) $rowData[1]) : null;
        $mapped['barcode'] = isset($rowData[2]) ? trim((string) $rowData[2]) : null;
        $mapped['categoryName'] = isset($rowData[3]) ? trim((string) $rowData[3]) : null;
        $mapped['unitName'] = isset($rowData[4]) ? trim((string) $rowData[4]) : null;
        $mapped['supplierName'] = isset($rowData[5]) ? trim((string) $rowData[5]) : null;
        $mapped['manufacturer'] = isset($rowData[6]) ? trim((string) $rowData[6]) : null;
        $mapped['composition'] = isset($rowData[7]) ? trim((string) $rowData[7]) : null;
        $mapped['description'] = isset($rowData[8]) ? trim((string) $rowData[8]) : null;
        $mapped['requiresPrescription'] = self::parseBool($rowData[9] ?? false);
        $mapped['purchasePrice'] = self::parseNumber($rowData[10] ?? 0);
        $mapped['sellPrice'] = $sellPrice;
        $mapped['wholesalePrice'] = $wholesalePrice;
        $hetMarkup = (int) self::parseNumber($rowData[13] ?? 0);
        $mapped['hetMarkup'] = $hetMarkup;
        $mapped['wholesaleMarkup'] = $hetMarkup;
        $mapped['hetPrice'] = self::parseNumber($rowData[14] ?? 0);
        $mapped['stock'] = (int) self::parseNumber($rowData[15] ?? 0);
        $mapped['stockMin'] = $stockMin;
        $mapped['expiredDate'] = self::parseDate($rowData[17] ?? null);
        $mapped['drugClass'] = isset($rowData[18]) ? trim((string) $rowData[18]) : null;
        $mapped['dosageForm'] = isset($rowData[19]) ? trim((string) $rowData[19]) : null;
        $mapped['route'] = isset($rowData[20]) ? trim((string) $rowData[20]) : null;

        return $mapped;
    }

    private function mapV9Row(array $rowData): array
    {
        $mapped = $this->emptyMappedRow();
        $sell = self::parseNumber($rowData[7] ?? 0);

        $mapped['name'] = isset($rowData[2]) ? trim((string) $rowData[2]) : null;
        $mapped['code'] = isset($rowData[0]) ? trim((string) $rowData[0]) : null;
        $mapped['categoryName'] = isset($rowData[1]) ? trim((string) $rowData[1]) : null;
        $mapped['unitName'] = isset($rowData[4]) ? trim((string) $rowData[4]) : null;
        $mapped['purchasePrice'] = self::parseNumber($rowData[5] ?? 0);
        $mapped['sellPrice'] = $sell;
        $mapped['wholesalePrice'] = $sell;
        $mapped['hetPrice'] = self::parseNumber($rowData[6] ?? 0);
        $mapped['stock'] = (int) self::parseNumber($rowData[3] ?? 0);
        $mapped['expiredDate'] = self::parseDate($rowData[8] ?? null);

        return $mapped;
    }

    private function mapLegacyRow(array $rowData): array
    {
        $mapped = $this->emptyMappedRow();
        $sell = self::parseNumber($rowData[13] ?? 0);

        $mapped['name'] = isset($rowData[2]) ? trim((string) $rowData[2]) : null;
        $mapped['code'] = isset($rowData[0]) ? trim((string) $rowData[0]) : null;
        $mapped['categoryName'] = isset($rowData[1]) ? trim((string) $rowData[1]) : null;
        $mapped['unitName'] = isset($rowData[4]) ? trim((string) $rowData[4]) : null;
        $mapped['purchasePrice'] = self::parseNumber($rowData[5] ?? 0);
        $mapped['sellPrice'] = $sell;
        $mapped['wholesalePrice'] = $sell;
        $mapped['hetPrice'] = self::parseNumber($rowData[12] ?? 0);
        $mapped['stock'] = (int) self::parseNumber($rowData[3] ?? 0);
        $mapped['expiredDate'] = self::parseDate($rowData[18] ?? null);

        return $mapped;
    }

    public function import(string $filePath): array
    {
        $spreadsheet = IOFactory::load($filePath);
        $sheet = $spreadsheet->getSheet(0);
        $highestRow = $sheet->getHighestRow();
        $highestColumn = $sheet->getHighestColumn();

        $successCount = 0;
        $failedCount = 0;
        $logs = [];

        // database_kandungan / databaseproduk / database.xlsx: header di baris 1, data dari baris 2.
        // Template master/v9/legacy: header di baris 3, data dari baris 4.
        $headerRow1 = $sheet->rangeToArray('A1:'.$highestColumn.'1', null, true, false)[0] ?? [];
        $headerJoined1 = strtolower(implode(' | ', array_map(fn ($v) => trim((string) $v), $headerRow1)));

        if ($this->isDatabaseKandunganWorkbookHeader($headerJoined1)) {
            $templateVersion = 'database_kandungan';
            $startRow = 2;
            $logs[] = [
                'status' => 'info',
                'code' => '-',
                'name' => 'Deteksi format',
                'message' => 'Format database.xlsx terbaru terdeteksi (kandungan + nama setelah fungsi).',
            ];
        } elseif ($this->isDatabaseProdukWorkbookHeader($headerJoined1)) {
            $templateVersion = 'databaseproduk';
            $startRow = 2;
            $logs[] = [
                'status' => 'info',
                'code' => '-',
                'name' => 'Deteksi format',
                'message' => 'Format databaseproduk.xlsx terdeteksi (kandungan, bentuk sediaan, rute, indikasi).',
            ];
        } elseif ($this->isDatabaseWorkbookHeader($headerJoined1)) {
            $templateVersion = 'database';
            $startRow = 2;
            $logs[] = [
                'status' => 'info',
                'code' => '-',
                'name' => 'Deteksi format',
                'message' => 'Format database.xlsx terdeteksi (header baris 1).',
            ];
        } else {
            $headerRow = $sheet->rangeToArray('A3:'.$highestColumn.'3', null, true, false)[0];
            $templateVersion = $this->detectTemplateVersion($headerRow);
            $startRow = 4;
        }

        for ($row = $startRow; $row <= $highestRow; $row++) {
            $rowData = $sheet->rangeToArray('A' . $row . ':' . $highestColumn . $row, null, true, false)[0];

            $mapped = match ($templateVersion) {
                'database_kandungan' => $this->mapDatabaseKandunganRow($rowData),
                'databaseproduk' => $this->mapDatabaseProdukRow($rowData),
                'database' => $this->mapDatabaseRow($rowData),
                'master' => $this->mapMasterRow($rowData),
                'legacy' => $this->mapLegacyRow($rowData),
                default => $this->mapV9Row($rowData),
            };

            $name = $mapped['name'];
            $code = $mapped['code'];

            if (empty($name)) {
                continue;
            }

            if (str_contains(strtolower($name), 'kosongkan baris') || str_contains(strtolower($name), 'contoh data')) {
                continue;
            }

            if (empty($code)) {
                $code = 'PRD-' . strtoupper(Str::random(6));
            }

            $purchasePrice = $mapped['purchasePrice'];
            $sellPrice = $mapped['sellPrice'];
            $hetPrice = $mapped['hetPrice'];
            $wholesalePrice = $mapped['wholesalePrice'];

            if ($sellPrice <= 0.0) {
                $sellPrice = $hetPrice > 0 ? $hetPrice : round($purchasePrice * 1.25);
            }
            // HET kosong di Excel = biarkan 0 (jangan diisi otomatis),
            // agar tanda "Melebihi HET" hanya dari data real.
            $markup = (int) ($mapped['hetMarkup'] ?? 0);
            $wholesaleMarkup = (int) ($mapped['wholesaleMarkup'] ?? $markup);
            if ($wholesaleMarkup > 0 && $purchasePrice > 0) {
                $wholesalePrice = Product::calcWholesaleFromPurchase($purchasePrice, $wholesaleMarkup, $sellPrice);
            } elseif ($markup > 0 && $purchasePrice > 0) {
                $wholesalePrice = Product::calcWholesaleFromPurchase($purchasePrice, $markup, $sellPrice);
                $wholesaleMarkup = $markup;
            } elseif ($wholesalePrice <= 0.0) {
                $wholesalePrice = $sellPrice;
            }

            $normalized = Product::normalizeSellAgainstHet($sellPrice, $wholesalePrice, $hetPrice);
            $sellPrice = $normalized['sell_price'];
            $wholesalePrice = $normalized['wholesale_price'];

            $category_id = $this->resolveCategory($mapped['categoryName']);
            $unit_id = $this->resolveUnit($mapped['unitName']);
            $supplier_id = $this->resolveSupplier($mapped['supplierName']);

            try {
                $existing = Product::withTrashed()->where('code', $code)->first();

                $productData = [
                    'name' => $name,
                    'barcode' => $mapped['barcode'] ?: null,
                    'category_id' => $category_id,
                    'unit_id' => $unit_id,
                    'supplier_id' => $supplier_id,
                    'composition' => $mapped['composition'] ?: null,
                    'description' => $mapped['description'] ?: null,
                    'drug_class' => $mapped['drugClass'] ?: null,
                    'dosage_form' => $mapped['dosageForm'] ?: null,
                    'route' => $mapped['route'] ?: null,
                    'requires_prescription' => $mapped['requiresPrescription'],
                    'purchase_price' => $purchasePrice,
                    'sell_price' => $sellPrice,
                    'wholesale_price' => $wholesalePrice,
                    'het_markup' => max(0, min(30, $mapped['hetMarkup'])),
                    'wholesale_markup' => max(0, min(30, $mapped['wholesaleMarkup'] ?? $mapped['hetMarkup'])),
                    'het_price' => $hetPrice,
                    'stock' => $mapped['stock'],
                    'stock_min' => $mapped['stockMin'],
                    'expired_date' => $mapped['expiredDate'],
                    'is_active' => true,
                ];

                // Pabrik tidak selalu ada di Excel — jangan hapus nilai yang sudah terisi.
                if (! empty($mapped['manufacturer'])) {
                    $productData['manufacturer'] = $mapped['manufacturer'];
                } elseif (! $existing) {
                    $productData['manufacturer'] = null;
                }

                if ($existing) {
                    if ($existing->trashed()) {
                        $existing->restore();
                    }
                    $existing->update($productData);
                    $logs[] = [
                        'status' => 'updated',
                        'code' => $code,
                        'name' => $name,
                        'message' => "Diperbarui. Stok: {$mapped['stock']}, Harga Jual: " . number_format($sellPrice, 0, ',', '.'),
                    ];
                } else {
                    Product::create(array_merge($productData, ['code' => $code]));
                    $logs[] = [
                        'status' => 'created',
                        'code' => $code,
                        'name' => $name,
                        'message' => "Produk baru dibuat. Stok: {$mapped['stock']}, Harga Jual: " . number_format($sellPrice, 0, ',', '.'),
                    ];
                }
                $successCount++;
            } catch (\Exception $e) {
                $failedCount++;
                $logs[] = [
                    'status' => 'failed',
                    'code' => $code ?? '-',
                    'name' => $name ?? '(tidak ada nama)',
                    'message' => 'Gagal: ' . $e->getMessage(),
                ];
            }
        }

        return [
            'success_count' => $successCount,
            'failed_count' => $failedCount,
            'logs' => $logs,
        ];
    }
}
