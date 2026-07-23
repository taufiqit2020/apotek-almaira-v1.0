<?php

namespace App\Exports;

use App\Models\Category;
use App\Models\Product;
use App\Models\Setting;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ProductsExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithTitle, ShouldAutoSize, WithEvents
{
    protected Collection $products;

    protected string $filterLabel;

    protected int $rowNumber = 0;

    public function __construct(Collection $products, string $filterLabel = 'Semua data')
    {
        $this->products = $products;
        $this->filterLabel = $filterLabel;
    }

    public function title(): string
    {
        return 'Master Produk';
    }

    public function collection(): Collection
    {
        return $this->products;
    }

    public function headings(): array
    {
        return [
            'No',
            'Kode',
            'Barcode',
            'Nama Produk',
            'Kategori',
            'Satuan',
            'Supplier',
            'Pabrik / Merk',
            'Komposisi',
            'Deskripsi / Indikasi',
            'Butuh Resep',
            'Harga Beli',
            'Harga Jual',
            'Harga Grosir',
            'Markup Jual %',
            'Markup Grosir %',
            'HET',
            'Stok',
            'Stok Min',
            'Expired',
            'Status',
            'E-Catalog',
            'Melebihi HET',
        ];
    }

    /**
     * @param  Product  $product
     */
    public function map($product): array
    {
        $this->rowNumber++;
        $het = (float) ($product->het_price ?? 0);
        $sell = (float) ($product->sell_price ?? 0);
        $exceedsHet = $het > 0 && $sell > $het;

        return [
            $this->rowNumber,
            $product->code ?: '—',
            $product->barcode ?: '—',
            $product->name,
            $product->category?->name ?: '—',
            $product->unit?->name ?: '—',
            $product->supplier?->name ?: '—',
            $product->manufacturer ?: '—',
            $product->composition ?: '—',
            $product->description ?: '—',
            $product->requires_prescription ? 'Ya' : 'Tidak',
            (float) ($product->purchase_price ?? 0),
            $sell,
            (float) ($product->wholesale_price ?? 0),
            (int) ($product->het_markup ?? 0),
            (int) ($product->wholesale_markup ?? 0),
            $het,
            (int) ($product->stock ?? 0),
            (int) ($product->stock_min ?? 0),
            $product->expired_date ? $product->expired_date->format('Y-m-d') : '—',
            $product->is_active ? 'Aktif' : 'Nonaktif',
            $product->show_in_catalog ? 'Ya' : 'Tidak',
            $exceedsHet ? 'Ya' : 'Tidak',
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $apotek = Setting::get('apotek_name', 'Apotek Almaira');
                $exportedAt = now()->timezone('Asia/Makassar')->format('d/m/Y H:i');
                $count = $this->products->count();
                $lastCol = 'W';
                $headerRow = 5;
                $lastDataRow = $headerRow + max($count, 1);

                // Sisipkan baris judul di atas heading
                $sheet->insertNewRowBefore(1, 4);

                $sheet->mergeCells("A1:{$lastCol}1");
                $sheet->setCellValue('A1', 'MASTER PRODUK — '.strtoupper($apotek));
                $sheet->getStyle('A1')->applyFromArray([
                    'font' => ['bold' => true, 'size' => 14, 'color' => ['rgb' => 'FFFFFF'], 'name' => 'Calibri'],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '047857']],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                        'vertical' => Alignment::VERTICAL_CENTER,
                    ],
                ]);
                $sheet->getRowDimension(1)->setRowHeight(28);

                $sheet->mergeCells("A2:{$lastCol}2");
                $sheet->setCellValue('A2', 'Filter: '.$this->filterLabel.'  ·  Total: '.number_format($count, 0, ',', '.').' produk  ·  Diekspor: '.$exportedAt);
                $sheet->getStyle('A2')->applyFromArray([
                    'font' => ['size' => 10, 'color' => ['rgb' => '065F46'], 'name' => 'Calibri'],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'D1FAE5']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT, 'vertical' => Alignment::VERTICAL_CENTER, 'indent' => 1],
                ]);
                $sheet->getRowDimension(2)->setRowHeight(20);

                $sheet->mergeCells("A3:{$lastCol}3");
                $sheet->setCellValue('A3', 'Data mengikuti filter Master Produk saat unduh. Kolom harga dalam Rupiah (angka).');
                $sheet->getStyle('A3')->applyFromArray([
                    'font' => ['italic' => true, 'size' => 9, 'color' => ['rgb' => '64748B'], 'name' => 'Calibri'],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'F8FAFC']],
                ]);

                // Header tabel (baris 5 setelah insert)
                $sheet->getStyle("A{$headerRow}:{$lastCol}{$headerRow}")->applyFromArray([
                    'font' => ['bold' => true, 'size' => 9, 'color' => ['rgb' => 'FFFFFF'], 'name' => 'Calibri'],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '059669']],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                        'vertical' => Alignment::VERTICAL_CENTER,
                        'wrapText' => true,
                    ],
                    'borders' => [
                        'allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => '047857']],
                    ],
                ]);
                $sheet->getRowDimension($headerRow)->setRowHeight(32);

                if ($count > 0) {
                    $dataStart = $headerRow + 1;
                    $dataEnd = $headerRow + $count;

                    $sheet->getStyle("A{$dataStart}:{$lastCol}{$dataEnd}")->applyFromArray([
                        'font' => ['size' => 9, 'name' => 'Calibri', 'color' => ['rgb' => '0F172A']],
                        'borders' => [
                            'allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'CBD5E1']],
                        ],
                        'alignment' => ['vertical' => Alignment::VERTICAL_CENTER],
                    ]);

                    // Zebra rows
                    for ($r = $dataStart; $r <= $dataEnd; $r++) {
                        if (($r - $dataStart) % 2 === 1) {
                            $sheet->getStyle("A{$r}:{$lastCol}{$r}")->getFill()
                                ->setFillType(Fill::FILL_SOLID)
                                ->getStartColor()->setRGB('F0FDF4');
                        }
                    }

                    // Format angka harga
                    foreach (['L', 'M', 'N', 'Q'] as $col) {
                        $sheet->getStyle("{$col}{$dataStart}:{$col}{$dataEnd}")
                            ->getNumberFormat()
                            ->setFormatCode('#,##0');
                    }

                    // Highlight Melebihi HET = Ya
                    for ($r = $dataStart; $r <= $dataEnd; $r++) {
                        if ((string) $sheet->getCell("W{$r}")->getValue() === 'Ya') {
                            $sheet->getStyle("W{$r}")->applyFromArray([
                                'font' => ['bold' => true, 'color' => ['rgb' => '9F1239']],
                                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'FFE4E6']],
                            ]);
                        }
                    }

                    $lastDataRow = $dataEnd;
                }

                $sheet->freezePane('A6');
                $sheet->setAutoFilter("A{$headerRow}:{$lastCol}{$headerRow}");
                $sheet->getStyle("A1:{$lastCol}{$lastDataRow}")->getAlignment()->setWrapText(false);
            },
        ];
    }

    /**
     * Bangun query produk sama dengan filter Master Produk (Livewire).
     *
     * @return array{0: \Illuminate\Database\Eloquent\Builder, 1: string}
     */
    public static function filteredQuery(?string $search, ?string $categoryId, string $status = 'active'): array
    {
        $search = trim((string) $search);
        $categoryId = trim((string) $categoryId);
        $status = $status !== '' ? $status : 'active';

        $query = Product::with(['category', 'unit', 'supplier'])->latest();
        $query->searchKeyword($search, 'ops');

        if ($categoryId !== '') {
            $query->where('category_id', $categoryId);
        }

        $parts = [];
        if ($search !== '') {
            $parts[] = 'Cari "'.$search.'"';
        }
        if ($categoryId !== '') {
            $catName = Category::find($categoryId)?->name;
            $parts[] = 'Kategori: '.($catName ?: '#'.$categoryId);
        }

        if ($status === 'active') {
            $query->where('is_active', true);
            $parts[] = 'Status: Aktif';
        } elseif ($status === 'inactive') {
            $query->where('is_active', false);
            $parts[] = 'Status: Nonaktif';
        } elseif ($status === 'low_stock') {
            $query->where('is_active', true)->whereColumn('stock', '<=', 'stock_min');
            $parts[] = 'Filter: Stok Kritis';
        } elseif ($status === 'exceed_het') {
            $query->where('is_active', true)->exceedsHet();
            $parts[] = 'Filter: Melebihi HET';
        } else {
            $parts[] = 'Status: Semua';
        }

        $label = $parts !== [] ? implode(' · ', $parts) : 'Semua data';

        return [$query, $label];
    }
}
