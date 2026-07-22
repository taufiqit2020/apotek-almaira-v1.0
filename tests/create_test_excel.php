<?php

require __DIR__ . '/../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

$s = new Spreadsheet();
$sh = $s->getActiveSheet();

// Row 1: Title
$sh->setCellValue('A1', 'TEMPLATE DATABASE PRODUK - APOTEK ALMAIRA');

// Row 2: Instructions
$sh->setCellValue('A2', 'Data diisi mulai dari Baris 4. Jangan mengubah susunan kolom.');

// Row 3: Headers
$sh->setCellValue('A3', 'KODE PRODUK (Wajib/Unik)');
$sh->setCellValue('B3', 'KATEGORI');
$sh->setCellValue('C3', 'NAMA PRODUK (Wajib)');
$sh->setCellValue('D3', 'STOK AWAL');
$sh->setCellValue('E3', 'SATUAN (Tablet/Strip/Pcs)');
$sh->setCellValue('F3', 'HARGA BELI (HPP)');
$sh->setCellValue('G3', '[Abaikan]');
$sh->setCellValue('H3', '[Abaikan]');
$sh->setCellValue('I3', '[Abaikan]');
$sh->setCellValue('J3', '[Abaikan]');
$sh->setCellValue('K3', '[Abaikan]');
$sh->setCellValue('L3', '[Abaikan]');
$sh->setCellValue('M3', 'HET');
$sh->setCellValue('N3', 'HARGA JUAL (Eceran)');
$sh->setCellValue('O3', '[Abaikan]');
$sh->setCellValue('P3', '[Abaikan]');
$sh->setCellValue('Q3', '[Abaikan]');
$sh->setCellValue('R3', '[Abaikan]');
$sh->setCellValue('S3', 'TANGGAL KADALUARSA');

// Row 4: Data row 1
$sh->setCellValue('A4', 'TEST-001');
$sh->setCellValue('B4', 'Analgesik');
$sh->setCellValue('C4', 'Paracetamol 500mg Test');
$sh->setCellValue('D4', 50);
$sh->setCellValue('E4', 'Tablet');
$sh->setCellValue('F4', 1500);
$sh->setCellValue('M4', 2500);
$sh->setCellValue('N4', 2000);
$sh->setCellValue('S4', '2027-12-31');

// Row 5: Data row 2 with Indonesian number format
$sh->setCellValue('A5', 'TEST-002');
$sh->setCellValue('B5', 'Antibiotik');
$sh->setCellValue('C5', 'Amoxicillin 500mg Test');
$sh->setCellValue('D5', 100);
$sh->setCellValue('E5', 'Kapsul');
$sh->setCellValue('F5', 3000);
$sh->setCellValue('M5', 4500);
$sh->setCellValue('N5', 4000);
$sh->setCellValue('S5', '2028-06-30');

$w = new Xlsx($s);
$outPath = __DIR__ . '/../storage/app/test_import.xlsx';
$w->save($outPath);
echo "File created at: " . $outPath . "\n";
