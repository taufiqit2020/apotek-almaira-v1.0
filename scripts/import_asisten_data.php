<?php
// Konversi + import data produk dari file asisten (DATA APOTEK ALMAIRA (2).xlsx, Sheet 3)
// ke format template Master Produk Almaira, lalu jalankan ProductImportService.
//
// Jalankan: php scripts/import_asisten_data.php "C:\path\ke\file.xlsx" "Sheet 3"

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$src   = $argv[1] ?? 'C:\\Users\\USER\\Downloads\\DATA APOTEK ALMAIRA (2).xlsx';
$sheetName = $argv[2] ?? 'Sheet 3';

echo "Membaca: $src [$sheetName]\n";

$reader = IOFactory::createReaderForFile($src);
$reader->setReadDataOnly(true);
$ss = $reader->load($src);
$sheet = $ss->getSheetByName($sheetName);
if (!$sheet) {
    echo "Sheet tidak ditemukan.\n";
    exit(1);
}
$highestRow = $sheet->getHighestDataRow();

// Kolom sumber (Sheet 3): A NO, B KODE, C KATEGORI, D GOLONGAN, E BENTUK, F RUTE,
// G FUNGSI, H NAMA, I STOK, J SATUAN, K HPP, ... R HET, S HARGA JUAL, X EXPAYED
function cell($sheet, $colLetter, $row) {
    $v = $sheet->getCell($colLetter . $row)->getValue();
    return $v;
}

function isKeras($golongan): bool {
    $g = strtolower(trim((string) $golongan));
    return str_contains($g, 'keras') || str_contains($g, 'narkotika') || str_contains($g, 'psikotropika');
}

// Bangun spreadsheet baru sesuai TEMPLATE MASTER (header di baris 3, data mulai baris 4)
$out = new Spreadsheet();
$osheet = $out->getActiveSheet();
$osheet->setTitle('Template Import Produk');

// Header master (harus mengandung kata kunci agar importer deteksi 'master')
$headers = ['NAMA PRODUK *','KODE PRODUK','BARCODE','KATEGORI','SATUAN','SUPPLIER',
    'PABRIK / MERK','KOMPOSISI','DESKRIPSI / INDIKASI','BUTUH RESEP','HARGA BELI *',
    'HARGA JUAL *','HARGA GROSIR','HET MARKUP %','HET','STOK *','STOK MINIMUM','TANGGAL KADALUARSA'];
$colLetters = ['A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R'];
foreach ($headers as $i => $h) {
    $osheet->setCellValue($colLetters[$i] . '3', $h);
}

$outRow = 4;
$mapped = 0;
for ($r = 2; $r <= $highestRow; $r++) {
    $name = trim((string) cell($sheet, 'H', $r));
    if ($name === '') continue;

    $kode     = trim((string) cell($sheet, 'B', $r));
    $kategori = trim((string) cell($sheet, 'C', $r));
    $golongan = trim((string) cell($sheet, 'D', $r));
    $bentuk   = trim((string) cell($sheet, 'E', $r));
    $rute     = trim((string) cell($sheet, 'F', $r));
    $fungsi   = trim((string) cell($sheet, 'G', $r));
    $stok     = cell($sheet, 'I', $r);
    $satuan   = trim((string) cell($sheet, 'J', $r));
    $hpp      = cell($sheet, 'K', $r);
    $het      = cell($sheet, 'R', $r);
    $jual     = cell($sheet, 'S', $r);
    $exp      = cell($sheet, 'X', $r);

    // Deskripsi gabungan (fungsi + bentuk + rute + golongan) agar info tidak hilang
    $deskripsiParts = array_filter([
        $fungsi,
        $bentuk ? "Bentuk: {$bentuk}" : '',
        $rute ? "Rute: {$rute}" : '',
        $golongan ? "Golongan: {$golongan}" : '',
    ]);
    $deskripsi = implode(' | ', $deskripsiParts);

    $osheet->setCellValue('A' . $outRow, $name);
    $osheet->setCellValue('B' . $outRow, $kode);
    $osheet->setCellValue('C' . $outRow, '');
    $osheet->setCellValue('D' . $outRow, $kategori);
    $osheet->setCellValue('E' . $outRow, $satuan);
    $osheet->setCellValue('F' . $outRow, '');
    $osheet->setCellValue('G' . $outRow, '');
    $osheet->setCellValue('H' . $outRow, '');
    $osheet->setCellValue('I' . $outRow, $deskripsi);
    $osheet->setCellValue('J' . $outRow, isKeras($golongan) ? 'Ya' : 'Tidak');
    $osheet->setCellValue('K' . $outRow, is_numeric($hpp) ? $hpp : (string) $hpp);
    $osheet->setCellValue('L' . $outRow, is_numeric($jual) ? $jual : (string) $jual);
    $osheet->setCellValue('M' . $outRow, '');
    $osheet->setCellValue('N' . $outRow, '');
    $osheet->setCellValue('O' . $outRow, is_numeric($het) ? $het : (string) $het);
    $osheet->setCellValue('P' . $outRow, is_numeric($stok) ? $stok : (string) $stok);
    $osheet->setCellValue('Q' . $outRow, '');
    $osheet->setCellValue('R' . $outRow, $exp); // serial Excel / string — importer parseDate menangani
    $outRow++;
    $mapped++;
}

$tmp = storage_path('app/_import_asisten_' . date('YmdHis') . '.xlsx');
(new Xlsx($out))->save($tmp);
echo "Baris dipetakan: {$mapped}\n";
echo "File sementara: {$tmp}\n";

// Jalankan importer aplikasi (reuse semua logika: kategori/satuan auto, dedup kode, dll)
$service = new \App\Services\ProductImportService();
$result = $service->import($tmp);

echo "\n=== HASIL IMPORT ===\n";
echo "Berhasil : {$result['success_count']}\n";
echo "Gagal    : {$result['failed_count']}\n";

$created = 0; $updated = 0; $failed = 0;
foreach ($result['logs'] as $log) {
    if ($log['status'] === 'created') $created++;
    elseif ($log['status'] === 'updated') $updated++;
    elseif ($log['status'] === 'failed') { $failed++; echo "  GAGAL: {$log['code']} - {$log['name']} :: {$log['message']}\n"; }
}
echo "Produk baru : {$created}\n";
echo "Diperbarui  : {$updated}\n";

@unlink($tmp);
echo "Selesai.\n";
