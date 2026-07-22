<?php
/**
 * Impor 21 produk ALKES ke master produk.
 * HPP = harga dasar, Jual = HPP + 20%, field kosong dilengkapi sesuai fungsi.
 *
 * Jalankan: php tools/import_alkes_21.php
 */

require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Category;
use App\Models\Product;
use App\Models\Unit;
use App\Services\ActivityLogService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * purchase = HPP/unit, sell = HPP+20% (dari daftar).
 */
$items = [
    ['name' => 'SAFEGLOVE EXAM S 100 BH', 'stock' => 10, 'unit' => 'BOX', 'purchase' => 59718, 'sell' => 71662,
        'composition' => 'Sarung tangan pemeriksaan lateks/nitril ukuran S, isi 100', 'dosage_form' => 'Sarung tangan', 'route' => 'Topikal / Pemeriksaan',
        'description' => 'Sarung tangan pemeriksaan sekali pakai ukuran Small untuk proteksi kontak.', 'manufacturer' => 'Safeglove'],
    ['name' => 'SAFEGLOVE EXAM M 100 BH', 'stock' => 10, 'unit' => 'BOX', 'purchase' => 59718, 'sell' => 71662,
        'composition' => 'Sarung tangan pemeriksaan lateks/nitril ukuran M, isi 100', 'dosage_form' => 'Sarung tangan', 'route' => 'Topikal / Pemeriksaan',
        'description' => 'Sarung tangan pemeriksaan sekali pakai ukuran Medium untuk proteksi kontak.', 'manufacturer' => 'Safeglove'],
    ['name' => 'SAFEGLOVE EXAM L 100 BH', 'stock' => 10, 'unit' => 'BOX', 'purchase' => 59718, 'sell' => 71662,
        'composition' => 'Sarung tangan pemeriksaan lateks/nitril ukuran L, isi 100', 'dosage_form' => 'Sarung tangan', 'route' => 'Topikal / Pemeriksaan',
        'description' => 'Sarung tangan pemeriksaan sekali pakai ukuran Large untuk proteksi kontak.', 'manufacturer' => 'Safeglove'],

    ['name' => 'DR.J PAD 60x90 KARTON 100', 'stock' => 1, 'unit' => 'KARTON', 'purchase' => 325008, 'sell' => 390010,
        'composition' => 'Underpad / alas penyerap 60x90 cm, isi 100 lembar/karton', 'dosage_form' => 'Underpad', 'route' => 'Eksternal',
        'description' => 'Alas penyerap untuk perawatan pasien, mencegah kebocoran cairan.', 'manufacturer' => 'Dr.J'],

    ['name' => 'URINE BAG STERIL 2 LT', 'stock' => 100, 'unit' => 'BH', 'purchase' => 7104, 'sell' => 8525,
        'composition' => 'Kantong urin steril kapasitas 2 liter', 'dosage_form' => 'Kantong urin', 'route' => 'Drainase',
        'description' => 'Kantong penampung urin steril untuk kateterisasi / drainase urin.', 'manufacturer' => 'Onemed'],

    ['name' => 'MASKER HIJAB HIJAU BOX ISI 50', 'stock' => 40, 'unit' => 'BOX', 'purchase' => 21978, 'sell' => 26374,
        'composition' => 'Masker medis hijab warna hijau, isi 50 pcs/box', 'dosage_form' => 'Masker', 'route' => 'Pernapasan / Proteksi',
        'description' => 'Masker bedah model hijab untuk proteksi droplet di fasilitas kesehatan.', 'manufacturer' => 'Generic'],
    ['name' => 'MASKER BEDAH KARET HIJAU', 'stock' => 60, 'unit' => 'BOX', 'purchase' => 19980, 'sell' => 23976,
        'composition' => 'Masker bedah dengan pengikat karet, warna hijau', 'dosage_form' => 'Masker', 'route' => 'Pernapasan / Proteksi',
        'description' => 'Masker bedah earloop untuk proteksi terhadap droplet.', 'manufacturer' => 'Generic'],
    ['name' => 'MASKER BEDAH TALI', 'stock' => 40, 'unit' => 'BOX', 'purchase' => 21978, 'sell' => 26374,
        'composition' => 'Masker bedah dengan pengikat tali', 'dosage_form' => 'Masker', 'route' => 'Pernapasan / Proteksi',
        'description' => 'Masker bedah bertali untuk proteksi droplet, cocok untuk operasi/prosedur.', 'manufacturer' => 'Generic'],

    ['name' => 'UMBILICAL CORD NYLON OM', 'stock' => 2, 'unit' => 'BOX', 'purchase' => 148296, 'sell' => 177955,
        'composition' => 'Klem / tali pusat (umbilical cord clamp) nylon Onemed', 'dosage_form' => 'Klem umbilical', 'route' => 'Obstetri',
        'description' => 'Klem tali pusat bayi baru lahir (umbilical cord clamp).', 'manufacturer' => 'Onemed'],

    ['name' => 'ONEMED EXAM GLOVE S', 'stock' => 10, 'unit' => 'BOX', 'purchase' => 64602, 'sell' => 77522,
        'composition' => 'Sarung tangan pemeriksaan Onemed ukuran S', 'dosage_form' => 'Sarung tangan', 'route' => 'Topikal / Pemeriksaan',
        'description' => 'Sarung tangan exam sekali pakai ukuran Small.', 'manufacturer' => 'Onemed'],
    ['name' => 'ONEMED EXAM GLOVE M', 'stock' => 10, 'unit' => 'BOX', 'purchase' => 64602, 'sell' => 77522,
        'composition' => 'Sarung tangan pemeriksaan Onemed ukuran M', 'dosage_form' => 'Sarung tangan', 'route' => 'Topikal / Pemeriksaan',
        'description' => 'Sarung tangan exam sekali pakai ukuran Medium.', 'manufacturer' => 'Onemed'],
    ['name' => 'ONEMED EXAM GLOVE L', 'stock' => 10, 'unit' => 'BOX', 'purchase' => 64602, 'sell' => 77522,
        'composition' => 'Sarung tangan pemeriksaan Onemed ukuran L', 'dosage_form' => 'Sarung tangan', 'route' => 'Topikal / Pemeriksaan',
        'description' => 'Sarung tangan exam sekali pakai ukuran Large.', 'manufacturer' => 'Onemed'],

    ['name' => "ESU TIP CLEANER ONEMED 1'S", 'stock' => 20, 'unit' => 'BH', 'purchase' => 16761, 'sell' => 20113,
        'composition' => 'Pembersih ujung elektroda ESU (electrosurgical unit)', 'dosage_form' => 'Aksesori bedah', 'route' => 'Bedah',
        'description' => 'Membersihkan tip/elektroda electrosurgery agar tetap efektif saat operasi.', 'manufacturer' => 'Onemed'],

    ['name' => 'MEDIGLOVE SURGICAL STERILE PP 6,5', 'stock' => 4, 'unit' => 'BOX', 'purchase' => 409923, 'sell' => 491908,
        'composition' => 'Sarung tangan bedah steril powder-free ukuran 6.5', 'dosage_form' => 'Sarung tangan bedah', 'route' => 'Bedah',
        'description' => 'Sarung tangan surgical steril ukuran 6,5 untuk prosedur operasi.', 'manufacturer' => 'Mediglove'],
    ['name' => 'MEDIGLOVE SURGICAL STERILE PP 7', 'stock' => 4, 'unit' => 'BOX', 'purchase' => 409923, 'sell' => 491908,
        'composition' => 'Sarung tangan bedah steril powder-free ukuran 7', 'dosage_form' => 'Sarung tangan bedah', 'route' => 'Bedah',
        'description' => 'Sarung tangan surgical steril ukuran 7 untuk prosedur operasi.', 'manufacturer' => 'Mediglove'],
    ['name' => 'MEDIGLOVE SURGICAL STERILE PP 7,5', 'stock' => 8, 'unit' => 'BOX', 'purchase' => 409923, 'sell' => 491908,
        'composition' => 'Sarung tangan bedah steril powder-free ukuran 7.5', 'dosage_form' => 'Sarung tangan bedah', 'route' => 'Bedah',
        'description' => 'Sarung tangan surgical steril ukuran 7,5 untuk prosedur operasi.', 'manufacturer' => 'Mediglove'],
    ['name' => 'MEDIGLOVE SURGICAL STERILE PP 8', 'stock' => 4, 'unit' => 'BOX', 'purchase' => 409923, 'sell' => 491908,
        'composition' => 'Sarung tangan bedah steril powder-free ukuran 8', 'dosage_form' => 'Sarung tangan bedah', 'route' => 'Bedah',
        'description' => 'Sarung tangan surgical steril ukuran 8 untuk prosedur operasi.', 'manufacturer' => 'Mediglove'],

    ['name' => 'POV IODINE 10% 5 LT ONEMED', 'stock' => 3, 'unit' => 'GLN', 'purchase' => 608280, 'sell' => 729936,
        'composition' => 'Povidone iodine 10% larutan antiseptik, 5 liter', 'dosage_form' => 'Larutan antiseptik', 'route' => 'Topikal',
        'description' => 'Antiseptik povidone iodine 10% untuk desinfeksi kulit/luka (kemasan gallon 5 L).', 'manufacturer' => 'Onemed'],

    ['name' => 'THREE WAY STOPCOCK OM NEW', 'stock' => 5, 'unit' => 'BOX', 'purchase' => 167388, 'sell' => 200866,
        'composition' => 'Three way stopcock (katup tiga arah) Onemed', 'dosage_form' => 'Aksesori infus', 'route' => 'Intravena',
        'description' => 'Stopcock 3 arah untuk jalur infus/injeksi IV.', 'manufacturer' => 'Onemed'],
    ['name' => 'ONEMED DOUBLE SPIKE', 'stock' => 6, 'unit' => 'BOX', 'purchase' => 167388, 'sell' => 200866,
        'composition' => 'Double spike / transfer set Onemed', 'dosage_form' => 'Aksesori infus', 'route' => 'Intravena',
        'description' => 'Spike ganda untuk transfer cairan infus antar kantong/botol.', 'manufacturer' => 'Onemed'],
    ['name' => 'INFUS SET DWS Y TUBE ONEMED', 'stock' => 2, 'unit' => 'BOX', 'purchase' => 319680, 'sell' => 383616,
        'composition' => 'Infus set dengan Y-tube / DWS Onemed', 'dosage_form' => 'Infus set', 'route' => 'Intravena',
        'description' => 'Set infus dengan Y-connector untuk pemberian cairan IV.', 'manufacturer' => 'Onemed'],
];

function resolveUnitId(string $unitName): int
{
    $map = [
        'BOX' => 'Box',
        'KARTON' => 'Karton',
        'BH' => 'Buah',
        'GLN' => 'Gallon',
        'BTL' => 'Botol',
        'PCS' => 'Pcs',
    ];
    $label = $map[strtoupper($unitName)] ?? ucfirst(strtolower($unitName));
    $unit = Unit::firstOrCreate(
        ['name' => $label],
        ['symbol' => strtoupper($unitName)]
    );
    return (int) $unit->id;
}

function resolveCategoryId(): int
{
    $category = Category::firstOrCreate(
        ['name' => 'Alat Kesehatan'],
        ['slug' => 'alat-kesehatan', 'description' => 'ALKES — alat dan bahan kesehatan non-obat', 'is_active' => true]
    );
    return (int) $category->id;
}

function findExisting(string $name): ?Product
{
    $normalized = preg_replace('/\s+/', ' ', strtoupper(trim($name)));
    $exact = Product::withTrashed()->whereRaw('UPPER(name) = ?', [$normalized])->first();
    if ($exact) {
        return $exact;
    }

    return Product::withTrashed()->get(['id', 'name', 'code', 'deleted_at'])
        ->first(fn (Product $p) => preg_replace('/\s+/', ' ', strtoupper(trim($p->name))) === $normalized);
}

function nextAlkesCode(int $offset): string
{
    $max = 0;
    foreach (Product::withTrashed()->where('code', 'like', 'ALK-%')->pluck('code') as $code) {
        if (preg_match('/ALK-(\d+)/', (string) $code, $m)) {
            $max = max($max, (int) $m[1]);
        }
    }
    return 'ALK-' . str_pad((string) ($max + $offset), 4, '0', STR_PAD_LEFT);
}

echo "=== IMPORT ALKES (21 item) ===\n";
echo 'Item: ' . count($items) . PHP_EOL;

$categoryId = resolveCategoryId();
$created = 0;
$updated = 0;
$failed = 0;
$logs = [];
$seq = 1;

DB::beginTransaction();
try {
    foreach ($items as $row) {
        $purchase = (float) $row['purchase'];
        $sell = (float) round($purchase * 1.20); // konsisten markup 20%
        // pakai sell dari daftar jika selisih kecil (pembulatan)
        if (abs($sell - (float) $row['sell']) <= 2) {
            $sell = (float) $row['sell'];
        }
        $wholesale = (float) round($sell * 0.99);
        $hetMarkup = 20;
        $het = $sell;

        $normalized = Product::normalizeSellAgainstHet($sell, $wholesale, $het);
        $sell = $normalized['sell_price'];
        $wholesale = $normalized['wholesale_price'];

        $payload = [
            'name' => $row['name'],
            'category_id' => $categoryId,
            'unit_id' => resolveUnitId($row['unit']),
            'composition' => $row['composition'],
            'dosage_form' => $row['dosage_form'],
            'route' => $row['route'],
            'description' => $row['description'],
            'drug_class' => 'Alat Kesehatan',
            'manufacturer' => $row['manufacturer'],
            'requires_prescription' => false,
            'purchase_price' => $purchase,
            'sell_price' => $sell,
            'wholesale_price' => $wholesale,
            'het_markup' => $hetMarkup,
            'het_price' => $het,
            'stock' => (int) $row['stock'],
            'stock_min' => max(3, (int) ceil($row['stock'] * 0.25)),
            'is_active' => true,
            'show_in_catalog' => true,
        ];

        try {
            $existing = findExisting($row['name']);
            if ($existing) {
                if ($existing->trashed()) {
                    $existing->restore();
                }
                $existing->update($payload);
                $updated++;
                $logs[] = "UPDATED #{$existing->id} {$existing->code} | {$row['name']}";
            } else {
                $code = nextAlkesCode($seq);
                while (Product::withTrashed()->where('code', $code)->exists()) {
                    $seq++;
                    $code = nextAlkesCode($seq);
                }
                $product = Product::create(array_merge($payload, ['code' => $code]));
                $created++;
                $seq++;
                $logs[] = "CREATED #{$product->id} {$code} | {$row['name']}";
            }
        } catch (Throwable $e) {
            $failed++;
            $logs[] = 'FAILED ' . $row['name'] . ': ' . $e->getMessage();
        }
    }
    DB::commit();
} catch (Throwable $e) {
    DB::rollBack();
    fwrite(STDERR, 'ROLLBACK: ' . $e->getMessage() . "\n");
    exit(1);
}

ActivityLogService::log(
    'IMPORT',
    'Produk',
    "Impor ALKES 21 item. Created: {$created}, Updated: {$updated}, Failed: {$failed}"
);

$logPath = storage_path('app/imports/import-alkes-21-' . date('Ymd-His') . '.json');
if (! is_dir(dirname($logPath))) {
    mkdir(dirname($logPath), 0775, true);
}
file_put_contents($logPath, json_encode([
    'created' => $created,
    'updated' => $updated,
    'failed' => $failed,
    'logs' => $logs,
], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

echo "Created : {$created}\n";
echo "Updated : {$updated}\n";
echo "Failed  : {$failed}\n";
echo 'Total products: ' . Product::count() . PHP_EOL;
echo "Log: {$logPath}\n";
foreach (array_slice($logs, 0, 6) as $line) {
    echo "- {$line}\n";
}
echo "Selesai OK.\n";
