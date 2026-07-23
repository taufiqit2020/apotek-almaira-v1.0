<?php
namespace App\Http\Controllers;
use App\Models\Category;
use App\Models\Product;
use App\Models\Supplier;
use App\Models\Unit;
use App\Services\ActivityLogService;
use Illuminate\Http\Request;
class ProductController extends Controller {
    public function index() {
        // Query logic sepenuhnya dipindahkan ke Livewire\Products\ProductTable component
        // View hanya berisi header + <livewire:products.product-table />
        return view('products.index');
    }
    public function create() {
        $categories = Category::active()->orderBy('name')->get();
        $suppliers = Supplier::active()->orderBy('name')->get();
        $units = Unit::orderBy('name')->get();
        return view('products.create', compact('categories','suppliers','units'));
    }
    public function store(Request $request) {
        $v = $request->validate([
            'name' => 'required|string|max:200',
            'barcode' => 'nullable|string|max:50|unique:products',
            'code' => 'nullable|string|max:50|unique:products',
            'category_id' => 'nullable|exists:categories,id',
            'unit_id' => 'nullable|exists:units,id',
            'supplier_id' => 'nullable|exists:suppliers,id',
            'description' => 'nullable|string',
            'composition' => 'nullable|string|max:500',
            'manufacturer' => 'nullable|string|max:150',
            'drug_class' => 'nullable|string|max:100',
            'dosage_form' => 'nullable|string|max:100',
            'route' => 'nullable|string|max:100',
            'requires_prescription' => 'boolean',
            'purchase_price' => 'required|numeric|min:0',
            'sell_price' => 'required|numeric|min:0',
            'wholesale_price' => 'nullable|numeric|min:0',
            'het_price' => 'nullable|numeric|min:0',
            'het_markup' => 'nullable|integer|in:0,5,10,15,20,25,30',
            'stock' => 'required|integer|min:0',
            'stock_min' => 'required|integer|min:0',
            'expired_date' => 'nullable|date',
            'images' => 'nullable|array|max:6',
            'images.*' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
        ]);
        $v['requires_prescription'] = $request->boolean('requires_prescription');
        $v['is_active'] = true;
        $v['wholesale_price'] = $v['wholesale_price'] ?? 0;
        $v['het_price'] = $v['het_price'] ?? 0;
        $v['het_markup'] = $v['het_markup'] ?? 0;
        $normalized = Product::normalizeSellAgainstHet(
            (float) $v['sell_price'],
            (float) $v['wholesale_price'],
            (float) $v['het_price'],
        );
        $v['sell_price'] = $normalized['sell_price'];
        $v['wholesale_price'] = $normalized['wholesale_price'];

        // Handle Image Uploads
        $imagePaths = [];
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $index => $file) {
                if ($file && $file->isValid()) {
                    $filename = 'prod_' . time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
                    $file->move(public_path('uploads/products'), $filename);
                    $imagePaths[$index] = 'uploads/products/' . $filename;
                }
            }
        }
        ksort($imagePaths);
        $v['images'] = array_values($imagePaths);

        $p = Product::create($v);
        ActivityLogService::created('Produk', $p->name, $p->toArray());
        return $this->redirectToProductsIndex($request, "Produk {$p->name} berhasil ditambahkan!");
    }
    public function edit(Request $request, Product $product) {
        $product->loadMissing('category');
        $categories = Category::active()->orderBy('name')->get();
        $suppliers = Supplier::active()->orderBy('name')->get();
        $units = Unit::orderBy('name')->get();
        $listQuery = $this->productsIndexParams($request);
        return view('products.edit', compact('product','categories','suppliers','units','listQuery'));
    }
    public function update(Request $request, Product $product) {
        $v = $request->validate([
            'name' => 'required|string|max:200',
            'barcode' => 'nullable|string|max:50|unique:products,barcode,'.$product->id,
            'code' => 'nullable|string|max:50|unique:products,code,'.$product->id,
            'category_id' => 'nullable|exists:categories,id',
            'unit_id' => 'nullable|exists:units,id',
            'supplier_id' => 'nullable|exists:suppliers,id',
            'description' => 'nullable|string',
            'composition' => 'nullable|string|max:500',
            'manufacturer' => 'nullable|string|max:150',
            'drug_class' => 'nullable|string|max:100',
            'dosage_form' => 'nullable|string|max:100',
            'route' => 'nullable|string|max:100',
            'requires_prescription' => 'boolean',
            'purchase_price' => 'required|numeric|min:0',
            'sell_price' => 'required|numeric|min:0',
            'wholesale_price' => 'nullable|numeric|min:0',
            'het_price' => 'nullable|numeric|min:0',
            'het_markup' => 'nullable|integer|in:0,5,10,15,20,25,30',
            'stock_min' => 'required|integer|min:0',
            'expired_date' => 'nullable|date',
            'is_active' => 'boolean',
            'images' => 'nullable|array|max:6',
            'images.*' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'existing_images' => 'nullable|array|max:6',
        ]);
        $v['requires_prescription'] = $request->boolean('requires_prescription');
        $v['is_active'] = $request->boolean('is_active', true);
        $v['wholesale_price'] = $v['wholesale_price'] ?? 0;
        $v['het_price'] = $v['het_price'] ?? 0;
        $v['het_markup'] = $v['het_markup'] ?? 0;
        $normalized = Product::normalizeSellAgainstHet(
            (float) $v['sell_price'],
            (float) $v['wholesale_price'],
            (float) $v['het_price'],
        );
        $v['sell_price'] = $normalized['sell_price'];
        $v['wholesale_price'] = $normalized['wholesale_price'];

        // Handle existing images kept
        $existingImages = $request->input('existing_images', []);
        $existingImagesFiltered = array_filter($existingImages, fn($val) => !empty($val));

        // Delete physical files of images that were removed
        $oldImages = $product->images ?? [];
        foreach ($oldImages as $oldImg) {
            if (!in_array($oldImg, $existingImagesFiltered)) {
                $filePath = public_path($oldImg);
                if (file_exists($filePath)) {
                    @unlink($filePath);
                }
            }
        }

        // Upload new files
        $newUploaded = [];
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $index => $file) {
                if ($file && $file->isValid()) {
                    $filename = 'prod_' . time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
                    $file->move(public_path('uploads/products'), $filename);
                    $newUploaded[$index] = 'uploads/products/' . $filename;
                }
            }
        }

        // Merge keeping order of slots (0-5)
        $finalImages = [];
        for ($i = 0; $i < 6; $i++) {
            if (isset($newUploaded[$i])) {
                $finalImages[] = $newUploaded[$i];
            } elseif (isset($existingImages[$i]) && !empty($existingImages[$i])) {
                $finalImages[] = $existingImages[$i];
            }
        }
        $v['images'] = $finalImages;

        $oldData = $product->toArray();
        $product->update($v);
        ActivityLogService::updated('Produk', $product->name, $oldData, $product->toArray());
        return $this->redirectToProductsIndex($request, "Produk {$product->name} berhasil diperbarui!");
    }
    public function destroy(Request $request, Product $product) {
        $name = $product->name;
        $oldData = $product->toArray();

        try {
            // Delete physical files
            $images = $product->images ?? [];
            foreach ($images as $img) {
                $filePath = public_path($img);
                if (file_exists($filePath)) {
                    @unlink($filePath);
                }
            }

            $product->delete();
            ActivityLogService::deleted('Produk', $name, $oldData);
            return $this->redirectToProductsIndex($request, "Produk {$name} berhasil dihapus!");
        } catch (\Throwable $e) {
            return back()->with('toast_error', "Gagal menghapus produk {$name}. Produk mungkin masih terkait data transaksi.");
        }
    }
    /**
     * Show product detail page.
     */
    public function show(Request $request, Product $product)
    {
        $categories = Category::active()->orderBy('name')->get();
        $suppliers = Supplier::active()->orderBy('name')->get();
        $units = Unit::orderBy('name')->get();
        $listQuery = $this->productsIndexParams($request);
        return view('products.show', compact('product','categories','suppliers','units','listQuery'));
    }

    /**
     * Toggle tampil/sembunyi satu produk di E-Catalog.
     */
    public function toggleCatalog(Product $product)
    {
        $product->update(['show_in_catalog' => !$product->show_in_catalog]);
        return back()->with('toast_success', $product->show_in_catalog
            ? "{$product->name} sekarang tampil di E-Catalog."
            : "{$product->name} disembunyikan dari E-Catalog.");
    }

    /**
     * Bulk tampil/sembunyi banyak produk sekaligus di E-Catalog.
     */
    public function bulkCatalog(Request $request)
    {
        $v = $request->validate([
            'ids' => 'required|array|min:1',
            'ids.*' => 'exists:products,id',
            'action' => 'required|in:show,hide',
        ]);

        $count = Product::whereIn('id', $v['ids'])
            ->update(['show_in_catalog' => $v['action'] === 'show']);

        $msg = $v['action'] === 'show'
            ? "{$count} produk berhasil ditampilkan di E-Catalog."
            : "{$count} produk berhasil disembunyikan dari E-Catalog.";

        return back()->with('toast_success', $msg);
    }
    public function search(Request $request) {
        $q = $request->q;
        $categoryId = $request->category_id;
        
        $query = Product::active()->with('unit');
        
        if ($categoryId) {
            $query->where('category_id', $categoryId);
        }
        
        $query->searchKeyword($q, 'ops');

        $products = $query->select(['id','name','code','barcode','purchase_price','sell_price','wholesale_price','stock','stock_min','unit_id','images'])
            ->limit(200)->get();

        return response()->json($products->map(fn($p) => [
            'id' => $p->id,
            'name' => $p->name,
            'code' => $p->code,
            'barcode' => $p->barcode,
            'purchase_price' => $p->purchase_price,
            'sell_price' => $p->sell_price,
            'wholesale_price' => $p->wholesale_price,
            'stock' => $p->stock,
            'stock_min' => $p->stock_min,
            'unit' => $p->unit?->name,
            'images' => $p->images,
            'image_url' => $p->image_url,
            'has_image' => $p->has_image,
        ]));
    }

    public function importForm() {
        return view('products.import');
    }

    public function downloadTemplate() {
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Template Import Produk');

        $lastCol = 'R';

        // ─── BARIS 1: Judul ───────────────────────────────────────────
        $sheet->setCellValue('A1', 'TEMPLATE IMPORT PRODUK — APOTEK ALMAIRA (Master Produk)');
        $sheet->mergeCells("A1:{$lastCol}1");
        $sheet->getStyle('A1')->applyFromArray([
            'font' => ['bold' => true, 'size' => 13, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['rgb' => '1a6340']],
            'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER, 'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER],
        ]);
        $sheet->getRowDimension(1)->setRowHeight(28);

        // ─── BARIS 2: Instruksi ───────────────────────────────────────
        $sheet->setCellValue('A2', '✅ Kolom mengikuti form Master Produk. Isi data mulai BARIS 4. Kolom A (NAMA) wajib. Kode kosong = otomatis digenerate. BUTUH RESEP: Ya/Tidak.');
        $sheet->mergeCells("A2:{$lastCol}2");
        $sheet->getStyle('A2')->applyFromArray([
            'font' => ['italic' => true, 'size' => 9, 'color' => ['rgb' => '7c3aed']],
            'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['rgb' => 'ede9fe']],
            'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT, 'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER, 'indent' => 1],
        ]);
        $sheet->getRowDimension(2)->setRowHeight(20);

        // ─── BARIS 3: Header Kolom (sama dengan Master Produk) ───────
        $headers = [
            'A' => ['label' => 'NAMA PRODUK *', 'width' => 32, 'note' => 'Nama obat (WAJIB). Contoh: Paracetamol 500mg'],
            'B' => ['label' => 'KODE PRODUK', 'width' => 16, 'note' => 'Kode unik. Kosong = otomatis. Contoh: OBT-0001'],
            'C' => ['label' => 'BARCODE', 'width' => 16, 'note' => 'Scan/isi barcode produk'],
            'D' => ['label' => 'KATEGORI', 'width' => 16, 'note' => 'Nama kategori. Contoh: Analgesik'],
            'E' => ['label' => 'SATUAN', 'width' => 12, 'note' => 'Tablet, Strip, Kapsul, Botol, dll'],
            'F' => ['label' => 'SUPPLIER', 'width' => 20, 'note' => 'Nama supplier. Contoh: Kimia Farma'],
            'G' => ['label' => 'PABRIK / MERK', 'width' => 18, 'note' => 'Pabrik atau merk. Contoh: INDOFARMA'],
            'H' => ['label' => 'KOMPOSISI', 'width' => 22, 'note' => 'Kandungan aktif. Contoh: Paracetamol 500mg'],
            'I' => ['label' => 'DESKRIPSI / INDIKASI', 'width' => 28, 'note' => 'Kegunaan, cara pakai, dll'],
            'J' => ['label' => 'BUTUH RESEP', 'width' => 12, 'note' => 'Ya / Tidak (obat keras)'],
            'K' => ['label' => 'HARGA BELI *', 'width' => 14, 'note' => 'HPP / harga modal. Contoh: 1200'],
            'L' => ['label' => 'HARGA JUAL *', 'width' => 14, 'note' => 'Harga jual eceran. Contoh: 1800'],
            'M' => ['label' => 'HARGA GROSIR', 'width' => 14, 'note' => 'Harga grosir. Contoh: 1600'],
            'N' => ['label' => 'HET MARKUP %', 'width' => 13, 'note' => '0, 5, 10, 15, 20, 25, atau 30'],
            'O' => ['label' => 'HET', 'width' => 12, 'note' => 'Harga Eceran Tertinggi. Contoh: 2000'],
            'P' => ['label' => 'STOK *', 'width' => 10, 'note' => 'Stok awal. Contoh: 200'],
            'Q' => ['label' => 'STOK MINIMUM', 'width' => 13, 'note' => 'Batas warning stok. Contoh: 10'],
            'R' => ['label' => 'TANGGAL KADALUARSA', 'width' => 18, 'note' => 'Format: YYYY-MM-DD. Contoh: 2027-12-31'],
        ];

        foreach ($headers as $col => $info) {
            $sheet->setCellValue($col . '3', $info['label']);
            $sheet->getColumnDimension($col)->setWidth($info['width']);
            $sheet->getStyle($col . '3')->applyFromArray([
                'font' => ['bold' => true, 'size' => 8, 'color' => ['rgb' => '1a3c34']],
                'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['rgb' => 'a7f3d0']],
                'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER, 'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER, 'wrapText' => true],
                'borders' => ['allBorders' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN, 'color' => ['rgb' => '6ee7b7']]],
            ]);
            $comment = $sheet->getComment($col . '3');
            $comment->getText()->createTextRun($info['note']);
            $comment->setWidth('220pt');
        }
        $sheet->getRowDimension(3)->setRowHeight(28);

        // ─── BARIS 4-7: Contoh Data ──────────────────────────────────
        $sampleRows = [
            4 => [
                'Paracetamol 500mg INDOFARMA', 'OBT-0001', '8991002100011', 'Analgesik', 'Tablet',
                'Kimia Farma', 'INDOFARMA', 'Paracetamol 500mg per tablet', 'Demam dan nyeri ringan',
                'Tidak', 1200, 1800, 1600, 10, 2000, 200, 10, '2027-12-31',
            ],
            5 => [
                'Amoxicillin 500mg HEXPHARM', 'OBT-0002', '8991002100028', 'Antibiotik', 'Kapsul',
                'Hexpharm', 'HEXPHARM', 'Amoxicillin 500mg', 'Infeksi bakteri — butuh resep',
                'Ya', 3000, 4000, 3700, 15, 4500, 150, 15, '2028-06-30',
            ],
            6 => [
                'Vitamin C 500mg KIMIA FARMA', 'OBT-0003', '8991002100035', 'Vitamin', 'Tablet',
                'Kimia Farma', 'KIMIA FARMA', 'Ascorbic Acid 500mg', 'Suplemen daya tahan tubuh',
                'Tidak', 800, 1200, 1100, 0, 1500, 300, 20, '2029-03-31',
            ],
            7 => [
                'Amlodipine 5mg DEXA MEDICA', 'OBT-0004', '8991002100042', 'Antihipertensi', 'Tablet',
                'Dexa Medica', 'DEXA MEDICA', 'Amlodipine besylate 5mg', 'Hipertensi — butuh resep',
                'Ya', 2500, 3500, 3200, 20, 4000, 100, 10, '2027-09-30',
            ],
        ];

        $cols = array_keys($headers);
        foreach ($sampleRows as $rowNum => $rowData) {
            foreach ($cols as $idx => $col) {
                $sheet->setCellValue($col . $rowNum, $rowData[$idx]);
            }
            $sheet->getStyle("A{$rowNum}:{$lastCol}{$rowNum}")->applyFromArray([
                'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['rgb' => 'f0fdf4']],
                'font' => ['size' => 9, 'color' => ['rgb' => '374151']],
                'borders' => ['allBorders' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN, 'color' => ['rgb' => 'd1fae5']]],
            ]);
        }

        // Baris 8 — petunjuk mulai isi
        $sheet->setCellValue('A8', '(Kosongkan baris ini — mulai isi data Anda di sini sesuai kolom Master Produk)');
        $sheet->mergeCells("A8:{$lastCol}8");
        $sheet->getStyle("A8:{$lastCol}8")->applyFromArray([
            'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['rgb' => 'fef9c3']],
            'font' => ['italic' => true, 'size' => 9, 'color' => ['rgb' => 'a16207']],
            'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER],
        ]);

        $sheet->freezePane('A4');

        // ─── Sheet 2: Panduan ─────────────────────────────────────────
        $sheetGuide = $spreadsheet->createSheet();
        $sheetGuide->setTitle('Panduan');
        $sheetGuide->setCellValue('A1', 'PANDUAN TEMPLATE = FORM MASTER PRODUK');
        $sheetGuide->getStyle('A1')->getFont()->setBold(true)->setSize(12);
        $sheetGuide->mergeCells('A1:B1');

        $guideRows = [
            ['Kolom Excel', 'Field di Master Produk'],
            ['A NAMA PRODUK *', 'Nama Produk / Obat'],
            ['B KODE PRODUK', 'Kode Produk'],
            ['C BARCODE', 'Barcode'],
            ['D KATEGORI', 'Kategori'],
            ['E SATUAN', 'Satuan'],
            ['F SUPPLIER', 'Supplier'],
            ['G PABRIK / MERK', 'Pabrik / Merk'],
            ['H KOMPOSISI', 'Komposisi / Kandungan'],
            ['I DESKRIPSI / INDIKASI', 'Deskripsi / Indikasi'],
            ['J BUTUH RESEP', 'Obat Keras / Butuh Resep Dokter (Ya/Tidak)'],
            ['K HARGA BELI *', 'Harga Beli'],
            ['L HARGA JUAL *', 'Harga Jual (Eceran)'],
            ['M HARGA GROSIR', 'Harga Grosir'],
            ['N HET MARKUP %', 'HET Markup (%)'],
            ['O HET', 'HET (Harga Eceran Tertinggi)'],
            ['P STOK *', 'Stok Saat Ini'],
            ['Q STOK MINIMUM', 'Stok Minimum (Warning)'],
            ['R TANGGAL KADALUARSA', 'Tanggal Kadaluarsa'],
        ];
        foreach ($guideRows as $i => $row) {
            $sheetGuide->setCellValue('A' . ($i + 3), $row[0]);
            $sheetGuide->setCellValue('B' . ($i + 3), $row[1]);
            if ($i === 0) {
                $sheetGuide->getStyle('A3:B3')->getFont()->setBold(true);
            }
        }

        $sheetGuide->setCellValue('A23', 'SATUAN YANG DISARANKAN');
        $sheetGuide->getStyle('A23')->getFont()->setBold(true);
        foreach (['Tablet', 'Kapsul', 'Strip', 'Botol', 'Ampul', 'Sachet', 'Tube', 'Pcs', 'Kotak', 'Vial', 'Sirup'] as $i => $s) {
            $sheetGuide->setCellValue('A' . ($i + 24), $s);
        }
        $sheetGuide->getColumnDimension('A')->setWidth(28);
        $sheetGuide->getColumnDimension('B')->setWidth(42);

        $spreadsheet->setActiveSheetIndex(0);

        $response = new \Symfony\Component\HttpFoundation\StreamedResponse(function () use ($spreadsheet) {
            $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
            $writer->save('php://output');
        });

        $response->headers->set('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        $response->headers->set('Content-Disposition', 'attachment;filename="template_import_produk_apotek.xlsx"');
        $response->headers->set('Cache-Control', 'max-age=0, no-cache, no-store, must-revalidate');
        $response->headers->set('Pragma', 'no-cache');
        $response->headers->set('Expires', '0');

        return $response;
    }

    public function import(Request $request) {
        // Validate: file wajib & harus .xlsx/.xls
        $request->validate([
            'file' => [
                'required',
                'file',
                function ($attribute, $value, $fail) {
                    $extension = strtolower($value->getClientOriginalExtension());
                    if (!in_array($extension, ['xlsx', 'xls'])) {
                        $fail('Format file harus .xlsx atau .xls. File yang diunggah: ' . $extension);
                    }
                }
            ]
        ], [
            'file.required' => 'File Excel wajib diunggah. Pilih file terlebih dahulu.',
        ]);

        $file = $request->file('file');

        // Detect the actual number of columns in the uploaded file
        // Our new template uses 9 columns (A-I), but legacy template uses 19 (A-S)
        try {
            $importService = new \App\Services\ProductImportService();
            $result = $importService->import($file->getRealPath());
        } catch (\Throwable $e) {
            \Log::error('Import Excel Error: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return back()->with('error', 'Terjadi kesalahan saat memproses file: ' . $e->getMessage());
        }

        \App\Services\ActivityLogService::log(
            'IMPORT',
            'Produk',
            "Mengimpor produk dari Excel. Berhasil: {$result['success_count']}, Gagal: {$result['failed_count']}"
        );

        return view('products.import_result', [
            'success_count' => $result['success_count'],
            'failed_count' => $result['failed_count'],
            'logs' => $result['logs'],
            'filename' => $file->getClientOriginalName(),
        ])->with('toast_success', "Import selesai! {$result['success_count']} produk berhasil, {$result['failed_count']} gagal.");
    }

    /**
     * Pertahankan filter/pencarian Master Produk (q, cat, status, page).
     *
     * @return array<string, string|int>
     */
    private function productsIndexParams(Request $request): array
    {
        $params = [];
        foreach (['q', 'cat', 'status', 'page'] as $key) {
            $value = $request->input("return_{$key}", $request->query($key));
            if ($value === null || $value === '') {
                continue;
            }
            $params[$key] = $key === 'page' ? (int) $value : (string) $value;
        }

        return $params;
    }

    private function redirectToProductsIndex(Request $request, string $message)
    {
        return redirect()
            ->route('products.index', $this->productsIndexParams($request))
            ->with('toast_success', $message);
    }
}
