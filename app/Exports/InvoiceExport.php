<?php

namespace App\Exports;

use App\Models\Sale;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\PageSetup;

class InvoiceExport implements FromView, ShouldAutoSize, WithEvents, WithTitle
{
    protected int $saleId;

    public function __construct(int $saleId)
    {
        $this->saleId = $saleId;
    }

    public function title(): string
    {
        $sale = Sale::find($this->saleId);
        return $sale ? 'Invoice ' . $sale->invoice_no : 'Invoice';
    }

    public function view(): View
    {
        $sale = Sale::with(['user', 'items.product'])->findOrFail($this->saleId);

        return view('invoices.excel', compact('sale'));
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $lastRow = $sheet->getHighestRow();

                $sheet->getPageSetup()->setOrientation(PageSetup::ORIENTATION_PORTRAIT);
                $sheet->getPageSetup()->setPaperSize(PageSetup::PAPERSIZE_A4);
                $sheet->getPageMargins()->setTop(0.5)->setBottom(0.5)->setLeft(0.5)->setRight(0.5);

                // Header perusahaan
                $sheet->mergeCells('A1:F1');
                $sheet->mergeCells('A2:F2');
                $sheet->mergeCells('A3:F3');
                $sheet->mergeCells('A4:F4');
                $sheet->mergeCells('A5:F5');
                $sheet->mergeCells('A7:F7');

                $sheet->getStyle('A1:F5')->applyFromArray([
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                    'font' => ['name' => 'Calibri', 'size' => 10],
                ]);
                $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14)->getColor()->setRGB('047857');
                $sheet->getStyle('A2')->getFont()->setBold(true)->setSize(11);
                $sheet->getStyle('A7')->getFont()->setBold(true)->setSize(12)->getColor()->setRGB('065F46');

                // Baris header tabel item (baris 13)
                $headerRow = 13;
                $sheet->getStyle("A{$headerRow}:F{$headerRow}")->applyFromArray([
                    'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'name' => 'Calibri', 'size' => 10],
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => '059669'],
                    ],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
                    'borders' => [
                        'allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => '047857']],
                    ],
                ]);

                // Baris item
                $firstItemRow = $headerRow + 1;
                $itemCount = Sale::withCount('items')->find($this->saleId)?->items_count ?? 0;
                $lastItemRow = $firstItemRow + max(0, $itemCount - 1);

                if ($itemCount > 0) {
                    $sheet->getStyle("A{$firstItemRow}:F{$lastItemRow}")->applyFromArray([
                        'font' => ['name' => 'Calibri', 'size' => 10],
                        'borders' => [
                            'allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'D1D5DB']],
                        ],
                    ]);
                    $sheet->getStyle("D{$firstItemRow}:F{$lastItemRow}")
                        ->getNumberFormat()
                        ->setFormatCode('#,##0');
                }

                // Total tagihan — cari dari bawah
                for ($row = $lastRow; $row >= 1; $row--) {
                    $label = (string) $sheet->getCell("E{$row}")->getValue();
                    if (str_contains(strtoupper($label), 'TOTAL TAGIHAN')) {
                        $sheet->getStyle("E{$row}:F{$row}")->applyFromArray([
                            'font' => ['bold' => true, 'size' => 11, 'color' => ['rgb' => '065F46']],
                            'fill' => [
                                'fillType' => Fill::FILL_SOLID,
                                'startColor' => ['rgb' => 'ECFDF5'],
                            ],
                        ]);
                        $sheet->getStyle("F{$row}")->getNumberFormat()->setFormatCode('#,##0');
                        break;
                    }
                }

                $sheet->getColumnDimension('A')->setWidth(6);
                $sheet->getColumnDimension('B')->setWidth(38);
                $sheet->getColumnDimension('C')->setWidth(14);
                $sheet->getColumnDimension('D')->setWidth(14);
                $sheet->getColumnDimension('E')->setWidth(10);
                $sheet->getColumnDimension('F')->setWidth(16);
            },
        ];
    }
}
