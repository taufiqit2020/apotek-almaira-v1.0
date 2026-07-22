<?php
/**
 * Impor 47 produk dari daftar stok/harga (gambar) ke master produk.
 * Field kosong diisi sesuai fungsi farmasi masing-masing.
 *
 * Jalankan: php tools/import_stock_list_47.php
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
 * Data dari daftar:
 * name, stock, unit, purchase (HPP), sell (HPP+10%)
 * Meta farmasi dilengkapi agar master produk lengkap & konsisten.
 */
$items = [
    // Mata / Oftalmik
    ['name' => 'CENDO LYTEERS 15 ml', 'stock' => 20, 'unit' => 'BTL', 'purchase' => 27000, 'sell' => 29700,
        'category' => 'Obat Mata', 'composition' => 'Artificial tears (lubricant ophthalmic)', 'dosage_form' => 'Tetes mata', 'route' => 'Okular',
        'description' => 'Melembapkan mata kering, mengurangi iritasi akibat kekurangan air mata.', 'drug_class' => 'Obat Bebas', 'manufacturer' => 'Cendo', 'requires_prescription' => false],
    ['name' => 'CENDO TROPIN 5 ml', 'stock' => 2, 'unit' => 'BTL', 'purchase' => 15000, 'sell' => 16500,
        'category' => 'Obat Mata', 'composition' => 'Atropine sulfate (mydriatic)', 'dosage_form' => 'Tetes mata', 'route' => 'Okular',
        'description' => 'Melebarkan pupil (midriasis) untuk pemeriksaan/terapi mata.', 'drug_class' => 'Obat Keras', 'manufacturer' => 'Cendo', 'requires_prescription' => true],
    ['name' => 'CENDO VASACON 15 ml', 'stock' => 3, 'unit' => 'BTL', 'purchase' => 20000, 'sell' => 22000,
        'category' => 'Obat Mata', 'composition' => 'Naphazoline HCl', 'dosage_form' => 'Tetes mata', 'route' => 'Okular',
        'description' => 'Mengurangi mata merah akibat iritasi ringan (vasokonstriktor).', 'drug_class' => 'Obat Bebas Terbatas', 'manufacturer' => 'Cendo', 'requires_prescription' => false],
    ['name' => 'CENDO VASACON-A 15 ml', 'stock' => 5, 'unit' => 'BTL', 'purchase' => 27000, 'sell' => 29700,
        'category' => 'Obat Mata', 'composition' => 'Naphazoline HCl + Antazoline', 'dosage_form' => 'Tetes mata', 'route' => 'Okular',
        'description' => 'Meredakan mata merah dan gatal akibat alergi.', 'drug_class' => 'Obat Bebas Terbatas', 'manufacturer' => 'Cendo', 'requires_prescription' => false],
    ['name' => 'CENDO VITROLENTA 5 ml', 'stock' => 4, 'unit' => 'BTL', 'purchase' => 36000, 'sell' => 39600,
        'category' => 'Obat Mata', 'composition' => 'Vitamin / nutrient ophthalmic formula', 'dosage_form' => 'Tetes mata', 'route' => 'Okular',
        'description' => 'Suplemen tetes mata untuk mendukung kesehatan jaringan mata.', 'drug_class' => 'Obat Bebas', 'manufacturer' => 'Cendo', 'requires_prescription' => false],
    ['name' => 'GENOINT SALEP MATA 3,5 gr', 'stock' => 25, 'unit' => 'TUB', 'purchase' => 12000, 'sell' => 13200,
        'category' => 'Obat Mata', 'composition' => 'Gentamicin sulfate', 'dosage_form' => 'Salep mata', 'route' => 'Okular',
        'description' => 'Antibiotik topikal untuk infeksi bakteri pada mata.', 'drug_class' => 'Obat Keras', 'manufacturer' => 'Generic', 'requires_prescription' => true],

    // Respiratori / Injeksi
    ['name' => 'COMBIVENT INJ 2,5 ml UDV', 'stock' => 1, 'unit' => 'KTK', 'purchase' => 148000, 'sell' => 162800,
        'category' => 'Bronkodilator', 'composition' => 'Ipratropium bromide + Salbutamol', 'dosage_form' => 'Injeksi / UDV', 'route' => 'Inhalasi / Parenteral',
        'description' => 'Melebarkan saluran napas pada asma/PPOK (kombinasi bronkodilator).', 'drug_class' => 'Obat Keras', 'manufacturer' => 'Boehringer', 'requires_prescription' => true],

    // Vitamin / Sirup
    ['name' => 'SOLVITA DROPS 15 ml', 'stock' => 15, 'unit' => 'BTL', 'purchase' => 32000, 'sell' => 35200,
        'category' => 'Vitamin & Suplemen', 'composition' => 'Multivitamin drops', 'dosage_form' => 'Tetes oral', 'route' => 'Oral',
        'description' => 'Suplemen vitamin untuk bayi/anak dalam bentuk tetes.', 'drug_class' => 'Obat Bebas', 'manufacturer' => 'Generic', 'requires_prescription' => false],
    ['name' => 'ANTASIDA DOEN SYRUP ERELA 60 ml', 'stock' => 10, 'unit' => 'BTL', 'purchase' => 6500, 'sell' => 7150,
        'category' => 'Saluran Cerna', 'composition' => 'Antasida DOEN (Aluminium hydroxide + Magnesium hydroxide)', 'dosage_form' => 'Sirup', 'route' => 'Oral',
        'description' => 'Meredakan nyeri ulu hati, asam lambung, dan perut kembung.', 'drug_class' => 'Obat Bebas', 'manufacturer' => 'Erela', 'requires_prescription' => false],
    ['name' => 'DOMPERIDONE SYRUP 60 ml', 'stock' => 10, 'unit' => 'BTL', 'purchase' => 12000, 'sell' => 13200,
        'category' => 'Saluran Cerna', 'composition' => 'Domperidone', 'dosage_form' => 'Sirup', 'route' => 'Oral',
        'description' => 'Mengatasi mual dan muntah; meningkatkan motilitas lambung.', 'drug_class' => 'Obat Keras', 'manufacturer' => 'Generic', 'requires_prescription' => true],
    ['name' => 'LACTULOSE SYRUP 60 ml NOVELL', 'stock' => 10, 'unit' => 'BTL', 'purchase' => 33000, 'sell' => 36300,
        'category' => 'Saluran Cerna', 'composition' => 'Lactulose', 'dosage_form' => 'Sirup', 'route' => 'Oral',
        'description' => 'Laksatif osmotik untuk konstipasi; menurunkan amonia pada ensefalopati hepatik.', 'drug_class' => 'Obat Keras', 'manufacturer' => 'Novell', 'requires_prescription' => true],
    ['name' => 'LECOZINC SYRUP 60 ml', 'stock' => 10, 'unit' => 'BTL', 'purchase' => 10000, 'sell' => 11000,
        'category' => 'Vitamin & Suplemen', 'composition' => 'Zinc sulfate', 'dosage_form' => 'Sirup', 'route' => 'Oral',
        'description' => 'Suplemen zinc untuk mendukung daya tahan tubuh dan pertumbuhan.', 'drug_class' => 'Obat Bebas', 'manufacturer' => 'Generic', 'requires_prescription' => false],
    ['name' => 'CETIRIZINE SYRUP 60 ml', 'stock' => 8, 'unit' => 'BTL', 'purchase' => 13000, 'sell' => 14300,
        'category' => 'Antihistamin', 'composition' => 'Cetirizine HCl', 'dosage_form' => 'Sirup', 'route' => 'Oral',
        'description' => 'Meredakan gejala alergi (bersin, gatal, pilek alergi).', 'drug_class' => 'Obat Bebas Terbatas', 'manufacturer' => 'Generic', 'requires_prescription' => false],

    // Tablet / Box
    ['name' => 'ALLOPURINOL 100 mg NOVA', 'stock' => 8, 'unit' => 'BOX', 'purchase' => 20000, 'sell' => 22000,
        'category' => 'Asam Urat', 'composition' => 'Allopurinol 100 mg', 'dosage_form' => 'Tablet', 'route' => 'Oral',
        'description' => 'Menurunkan asam urat; pencegahan gout dan batu ginjal asam urat.', 'drug_class' => 'Obat Keras', 'manufacturer' => 'Nova', 'requires_prescription' => true],
    ['name' => 'ANTASIDA DOEN ERRITA TAB', 'stock' => 2, 'unit' => 'BOX', 'purchase' => 12000, 'sell' => 13200,
        'category' => 'Saluran Cerna', 'composition' => 'Antasida DOEN', 'dosage_form' => 'Tablet kunyah', 'route' => 'Oral',
        'description' => 'Meredakan nyeri ulu hati dan gejala maag.', 'drug_class' => 'Obat Bebas', 'manufacturer' => 'Errita', 'requires_prescription' => false],
    ['name' => 'ASAM MEFENAMAT 500 mg NOVA', 'stock' => 15, 'unit' => 'BOX', 'purchase' => 23000, 'sell' => 25300,
        'category' => 'Analgesik', 'composition' => 'Asam mefenamat 500 mg', 'dosage_form' => 'Tablet', 'route' => 'Oral',
        'description' => 'Pereda nyeri ringan–sedang, termasuk nyeri haid.', 'drug_class' => 'Obat Keras', 'manufacturer' => 'Nova', 'requires_prescription' => true],
    ['name' => 'BETAHISTINE 6 mg DEXA', 'stock' => 6, 'unit' => 'BOX', 'purchase' => 25000, 'sell' => 27500,
        'category' => 'Neurologi', 'composition' => 'Betahistine mesylate 6 mg', 'dosage_form' => 'Tablet', 'route' => 'Oral',
        'description' => 'Mengatasi vertigo dan gangguan keseimbangan (penyakit Meniere).', 'drug_class' => 'Obat Keras', 'manufacturer' => 'Dexa', 'requires_prescription' => true],
    ['name' => 'CEFIXIME 200 mg DEXA', 'stock' => 5, 'unit' => 'BOX', 'purchase' => 110000, 'sell' => 121000,
        'category' => 'Antibiotik', 'composition' => 'Cefixime 200 mg', 'dosage_form' => 'Kapsul / Tablet', 'route' => 'Oral',
        'description' => 'Antibiotik sefalosporin generasi 3 untuk infeksi bakteri sensitif.', 'drug_class' => 'Obat Keras', 'manufacturer' => 'Dexa', 'requires_prescription' => true],
    ['name' => 'CIPROFLOXACIN 500 mg', 'stock' => 4, 'unit' => 'BOX', 'purchase' => 44000, 'sell' => 48400,
        'category' => 'Antibiotik', 'composition' => 'Ciprofloxacin 500 mg', 'dosage_form' => 'Tablet', 'route' => 'Oral',
        'description' => 'Antibiotik fluorokuinolon untuk infeksi bakteri (ISK, saluran napas, dll).', 'drug_class' => 'Obat Keras', 'manufacturer' => 'Generic', 'requires_prescription' => true],
    ['name' => 'COTRIMOXAZOLE 480 mg', 'stock' => 2, 'unit' => 'BOX', 'purchase' => 29000, 'sell' => 31900,
        'category' => 'Antibiotik', 'composition' => 'Sulfamethoxazole + Trimethoprim 480 mg', 'dosage_form' => 'Tablet', 'route' => 'Oral',
        'description' => 'Antibiotik kombinasi untuk infeksi bakteri sensitif.', 'drug_class' => 'Obat Keras', 'manufacturer' => 'Generic', 'requires_prescription' => true],
    ['name' => 'DIMENHYDRINATE 50 mg', 'stock' => 6, 'unit' => 'BOX', 'purchase' => 34000, 'sell' => 37400,
        'category' => 'Antihistamin', 'composition' => 'Dimenhydrinate 50 mg', 'dosage_form' => 'Tablet', 'route' => 'Oral',
        'description' => 'Mencegah/mengatasi mabuk perjalanan, mual, dan muntah.', 'drug_class' => 'Obat Bebas Terbatas', 'manufacturer' => 'Generic', 'requires_prescription' => false],
    ['name' => 'FENOFIBRATE 100 mg', 'stock' => 12, 'unit' => 'BOX', 'purchase' => 48000, 'sell' => 52800,
        'category' => 'Kardiovaskular', 'composition' => 'Fenofibrate 100 mg', 'dosage_form' => 'Kapsul', 'route' => 'Oral',
        'description' => 'Menurunkan trigliserida dan memperbaiki profil lipid.', 'drug_class' => 'Obat Keras', 'manufacturer' => 'Generic', 'requires_prescription' => true],
    ['name' => 'GABAPENTIN 100 mg', 'stock' => 5, 'unit' => 'BOX', 'purchase' => 130000, 'sell' => 143000,
        'category' => 'Neurologi', 'composition' => 'Gabapentin 100 mg', 'dosage_form' => 'Kapsul', 'route' => 'Oral',
        'description' => 'Antikonvulsan / neuropati untuk nyeri saraf dan epilepsi.', 'drug_class' => 'Obat Keras', 'manufacturer' => 'Generic', 'requires_prescription' => true],
    ['name' => 'GABAPENTIN 300 mg', 'stock' => 5, 'unit' => 'BOX', 'purchase' => 52000, 'sell' => 57200,
        'category' => 'Neurologi', 'composition' => 'Gabapentin 300 mg', 'dosage_form' => 'Kapsul', 'route' => 'Oral',
        'description' => 'Antikonvulsan / neuropati untuk nyeri saraf dan epilepsi.', 'drug_class' => 'Obat Keras', 'manufacturer' => 'Generic', 'requires_prescription' => true],
    ['name' => 'ITRACONAZOLE 100 mg', 'stock' => 3, 'unit' => 'BOX', 'purchase' => 142000, 'sell' => 156200,
        'category' => 'Antijamur', 'composition' => 'Itraconazole 100 mg', 'dosage_form' => 'Kapsul', 'route' => 'Oral',
        'description' => 'Antijamur sistemik untuk infeksi jamur (dermatofita, kandidiasis, dll).', 'drug_class' => 'Obat Keras', 'manufacturer' => 'Generic', 'requires_prescription' => true],
    ['name' => 'KETOCONAZOLE 200 mg', 'stock' => 3, 'unit' => 'BOX', 'purchase' => 23500, 'sell' => 25850,
        'category' => 'Antijamur', 'composition' => 'Ketoconazole 200 mg', 'dosage_form' => 'Tablet', 'route' => 'Oral',
        'description' => 'Antijamur untuk infeksi jamur sistemik/kulit sesuai indikasi.', 'drug_class' => 'Obat Keras', 'manufacturer' => 'Generic', 'requires_prescription' => true],
    ['name' => 'KSR TAB', 'stock' => 1, 'unit' => 'BOX', 'purchase' => 452000, 'sell' => 497200,
        'category' => 'Elektrolit', 'composition' => 'Potassium chloride (KCl) slow release', 'dosage_form' => 'Tablet lepas lambat', 'route' => 'Oral',
        'description' => 'Suplemen kalium untuk hipokalemia / menjaga kadar kalium darah.', 'drug_class' => 'Obat Keras', 'manufacturer' => 'Kalbe', 'requires_prescription' => true],
    ['name' => 'LORATADINE 10 mg', 'stock' => 8, 'unit' => 'BOX', 'purchase' => 18500, 'sell' => 20350,
        'category' => 'Antihistamin', 'composition' => 'Loratadine 10 mg', 'dosage_form' => 'Tablet', 'route' => 'Oral',
        'description' => 'Antihistamin non-sedatif untuk gejala alergi.', 'drug_class' => 'Obat Bebas Terbatas', 'manufacturer' => 'Generic', 'requires_prescription' => false],
    ['name' => 'MECOBALAMIN 500 mcg', 'stock' => 2, 'unit' => 'BOX', 'purchase' => 60000, 'sell' => 66000,
        'category' => 'Vitamin & Suplemen', 'composition' => 'Mecobalamin 500 mcg', 'dosage_form' => 'Tablet', 'route' => 'Oral',
        'description' => 'Vitamin B12 aktif untuk neuropati dan defisiensi B12.', 'drug_class' => 'Obat Keras', 'manufacturer' => 'Generic', 'requires_prescription' => true],
    ['name' => 'MELOXICAM 15 mg', 'stock' => 2, 'unit' => 'BOX', 'purchase' => 22000, 'sell' => 24200,
        'category' => 'Analgesik', 'composition' => 'Meloxicam 15 mg', 'dosage_form' => 'Tablet', 'route' => 'Oral',
        'description' => 'NSAID untuk nyeri dan inflamasi (osteoartritis, rheumatoid).', 'drug_class' => 'Obat Keras', 'manufacturer' => 'Generic', 'requires_prescription' => true],
    ['name' => 'MELOXICAM 7,5 mg', 'stock' => 3, 'unit' => 'BOX', 'purchase' => 14000, 'sell' => 15400,
        'category' => 'Analgesik', 'composition' => 'Meloxicam 7,5 mg', 'dosage_form' => 'Tablet', 'route' => 'Oral',
        'description' => 'NSAID untuk nyeri dan inflamasi (dosis rendah).', 'drug_class' => 'Obat Keras', 'manufacturer' => 'Generic', 'requires_prescription' => true],
    ['name' => 'METFORMIN 500 mg', 'stock' => 7, 'unit' => 'BOX', 'purchase' => 39000, 'sell' => 42900,
        'category' => 'Antidiabetes', 'composition' => 'Metformin HCl 500 mg', 'dosage_form' => 'Tablet', 'route' => 'Oral',
        'description' => 'Antidiabetes oral untuk diabetes mellitus tipe 2.', 'drug_class' => 'Obat Keras', 'manufacturer' => 'Generic', 'requires_prescription' => true],
    ['name' => 'METHYLPREDNISOLONE 16 mg', 'stock' => 1, 'unit' => 'BOX', 'purchase' => 61000, 'sell' => 67100,
        'category' => 'Kortikosteroid', 'composition' => 'Methylprednisolone 16 mg', 'dosage_form' => 'Tablet', 'route' => 'Oral',
        'description' => 'Kortikosteroid antiinflamasi/imunosupresan.', 'drug_class' => 'Obat Keras', 'manufacturer' => 'Generic', 'requires_prescription' => true],
    ['name' => 'METHYLPREDNISOLONE 8 mg', 'stock' => 3, 'unit' => 'BOX', 'purchase' => 45000, 'sell' => 49500,
        'category' => 'Kortikosteroid', 'composition' => 'Methylprednisolone 8 mg', 'dosage_form' => 'Tablet', 'route' => 'Oral',
        'description' => 'Kortikosteroid antiinflamasi/imunosupresan (dosis rendah).', 'drug_class' => 'Obat Keras', 'manufacturer' => 'Generic', 'requires_prescription' => true],
    ['name' => 'DICLOFENAC SODIUM 50 mg', 'stock' => 10, 'unit' => 'BOX', 'purchase' => 17000, 'sell' => 18700,
        'category' => 'Analgesik', 'composition' => 'Diclofenac sodium 50 mg', 'dosage_form' => 'Tablet', 'route' => 'Oral',
        'description' => 'NSAID untuk nyeri, inflamasi, dan demam.', 'drug_class' => 'Obat Keras', 'manufacturer' => 'Generic', 'requires_prescription' => true],
    ['name' => 'NIFEDIPINE 10 mg', 'stock' => 3, 'unit' => 'BOX', 'purchase' => 19000, 'sell' => 20900,
        'category' => 'Kardiovaskular', 'composition' => 'Nifedipine 10 mg', 'dosage_form' => 'Kapsul / Tablet', 'route' => 'Oral',
        'description' => 'Calcium channel blocker untuk hipertensi dan angina.', 'drug_class' => 'Obat Keras', 'manufacturer' => 'Generic', 'requires_prescription' => true],
    ['name' => 'OMEPRAZOLE 20 mg', 'stock' => 5, 'unit' => 'BOX', 'purchase' => 28000, 'sell' => 30800,
        'category' => 'Saluran Cerna', 'composition' => 'Omeprazole 20 mg', 'dosage_form' => 'Kapsul', 'route' => 'Oral',
        'description' => 'PPI untuk GERD, tukak lambung, dan hipersekresi asam.', 'drug_class' => 'Obat Keras', 'manufacturer' => 'Generic', 'requires_prescription' => true],
    ['name' => 'ORINOX 90 mg', 'stock' => 1, 'unit' => 'BOX', 'purchase' => 318000, 'sell' => 349800,
        'category' => 'Kardiovaskular', 'composition' => 'Ticagrelor 90 mg (Orinox)', 'dosage_form' => 'Tablet', 'route' => 'Oral',
        'description' => 'Antiplatelet untuk pencegahan kejadian aterotrombotik (ACS).', 'drug_class' => 'Obat Keras', 'manufacturer' => 'Generic', 'requires_prescription' => true],
    ['name' => 'PARACETAMOL 500 mg', 'stock' => 15, 'unit' => 'BOX', 'purchase' => 15500, 'sell' => 17050,
        'category' => 'Analgesik', 'composition' => 'Paracetamol 500 mg', 'dosage_form' => 'Tablet', 'route' => 'Oral',
        'description' => 'Pereda nyeri dan penurun demam.', 'drug_class' => 'Obat Bebas', 'manufacturer' => 'Generic', 'requires_prescription' => false],
    ['name' => 'PSIDII 500 mg', 'stock' => 1, 'unit' => 'BTL', 'purchase' => 328000, 'sell' => 360800,
        'category' => 'Saluran Cerna', 'composition' => 'Ekstrak daun jambu biji (Psidium guajava)', 'dosage_form' => 'Kapsul / Botol', 'route' => 'Oral',
        'description' => 'Membantu meredakan diare dan mendukung kesehatan saluran cerna.', 'drug_class' => 'Obat Herbal / Bebas', 'manufacturer' => 'Generic', 'requires_prescription' => false],
    ['name' => 'RISPERIDONE 2 mg', 'stock' => 3, 'unit' => 'BOX', 'purchase' => 70000, 'sell' => 77000,
        'category' => 'Psikiatri', 'composition' => 'Risperidone 2 mg', 'dosage_form' => 'Tablet', 'route' => 'Oral',
        'description' => 'Antipsikotik untuk skizofrenia dan gangguan bipolar.', 'drug_class' => 'Obat Keras', 'manufacturer' => 'Generic', 'requires_prescription' => true],
    ['name' => 'SIMVASTATIN 10 mg', 'stock' => 5, 'unit' => 'BOX', 'purchase' => 14500, 'sell' => 15950,
        'category' => 'Kardiovaskular', 'composition' => 'Simvastatin 10 mg', 'dosage_form' => 'Tablet', 'route' => 'Oral',
        'description' => 'Statin untuk menurunkan kolesterol LDL.', 'drug_class' => 'Obat Keras', 'manufacturer' => 'Generic', 'requires_prescription' => true],
    ['name' => 'SIMVASTATIN 20 mg', 'stock' => 4, 'unit' => 'BOX', 'purchase' => 21000, 'sell' => 23100,
        'category' => 'Kardiovaskular', 'composition' => 'Simvastatin 20 mg', 'dosage_form' => 'Tablet', 'route' => 'Oral',
        'description' => 'Statin untuk menurunkan kolesterol LDL (dosis lebih tinggi).', 'drug_class' => 'Obat Keras', 'manufacturer' => 'Generic', 'requires_prescription' => true],
    ['name' => 'TABLET TAMBAH DARAH HJ TAB', 'stock' => 5, 'unit' => 'BOX', 'purchase' => 33000, 'sell' => 36300,
        'category' => 'Vitamin & Suplemen', 'composition' => 'Ferrous fumarate / zat besi + asam folat (TTD)', 'dosage_form' => 'Tablet', 'route' => 'Oral',
        'description' => 'Suplemen zat besi untuk anemia dan kebutuhan kehamilan (TTD).', 'drug_class' => 'Obat Bebas', 'manufacturer' => 'Generic', 'requires_prescription' => false],
    ['name' => 'TB VIT 6 TAB', 'stock' => 1, 'unit' => 'BOX', 'purchase' => 62000, 'sell' => 68200,
        'category' => 'Vitamin & Suplemen', 'composition' => 'Vitamin B kompleks / formula TB Vit 6', 'dosage_form' => 'Tablet', 'route' => 'Oral',
        'description' => 'Suplemen vitamin B untuk mendukung metabolisme dan saraf.', 'drug_class' => 'Obat Bebas', 'manufacturer' => 'Generic', 'requires_prescription' => false],
    ['name' => 'VITAMIN B6 10 mg', 'stock' => 6, 'unit' => 'BOX', 'purchase' => 12000, 'sell' => 13200,
        'category' => 'Vitamin & Suplemen', 'composition' => 'Pyridoxine HCl 10 mg', 'dosage_form' => 'Tablet', 'route' => 'Oral',
        'description' => 'Suplemen vitamin B6 untuk defisiensi dan dukungan metabolisme.', 'drug_class' => 'Obat Bebas', 'manufacturer' => 'Generic', 'requires_prescription' => false],
    ['name' => 'LECOZINC 10 mg', 'stock' => 4, 'unit' => 'BOX', 'purchase' => 28000, 'sell' => 30800,
        'category' => 'Vitamin & Suplemen', 'composition' => 'Zinc 10 mg', 'dosage_form' => 'Tablet / Kapsul', 'route' => 'Oral',
        'description' => 'Suplemen zinc untuk daya tahan tubuh dan penyembuhan.', 'drug_class' => 'Obat Bebas', 'manufacturer' => 'Generic', 'requires_prescription' => false],
];

// Koreksi nama dari OCR gambar yang ambigu → nama master yang benar
// (gambar: ASAM MAFENAMAT, OMEDRINAT, METFORIN, METHYL 16/8)
$nameAliases = [
    'ASAM MAFENAMAT 500 mg NOVA' => 'ASAM MEFENAMAT 500 mg NOVA',
    'OMEDRINAT 50 mg' => 'DIMENHYDRINATE 50 mg',
    'METFORIN 500 mg' => 'METFORMIN 500 mg',
    'METHYL 16 mg' => 'METHYLPREDNISOLONE 16 mg',
    'METHYL 8 mg' => 'METHYLPREDNISOLONE 8 mg',
];

function resolveUnitId(string $unitName): int
{
    $map = [
        'BTL' => 'Botol',
        'BOX' => 'Box',
        'KTK' => 'Kotak',
        'TUB' => 'Tube',
        'PCS' => 'Pcs',
        'TAB' => 'Tablet',
    ];
    $label = $map[strtoupper($unitName)] ?? $unitName;
    $unit = Unit::firstOrCreate(
        ['name' => $label],
        ['symbol' => strtoupper($unitName)]
    );
    if (empty($unit->symbol)) {
        $unit->update(['symbol' => strtoupper($unitName)]);
    }
    return (int) $unit->id;
}

function resolveCategoryId(string $categoryName): int
{
    $category = Category::firstOrCreate(
        ['name' => $categoryName],
        ['slug' => Str::slug($categoryName), 'is_active' => true]
    );
    return (int) $category->id;
}

function makeCode(string $category, string $name, int $seq): string
{
    $prefix = match (true) {
        str_contains(strtolower($category), 'mata') => 'EYE',
        str_contains(strtolower($category), 'bronko') => 'INH',
        str_contains(strtolower($category), 'antibiotik') => 'ABX',
        str_contains(strtolower($category), 'analgesik') => 'ANL',
        str_contains(strtolower($category), 'vitamin') => 'VIT',
        str_contains(strtolower($category), 'kardiovaskular') => 'CVD',
        str_contains(strtolower($category), 'saluran cerna') => 'GI',
        str_contains(strtolower($category), 'antihistamin') => 'AH',
        str_contains(strtolower($category), 'neurologi') => 'NEU',
        str_contains(strtolower($category), 'antidiabetes') => 'DM',
        str_contains(strtolower($category), 'kortikosteroid') => 'STE',
        str_contains(strtolower($category), 'antijamur') => 'AF',
        str_contains(strtolower($category), 'psikiatri') => 'PSY',
        str_contains(strtolower($category), 'asam urat') => 'UA',
        str_contains(strtolower($category), 'elektrolit') => 'ELC',
        default => 'PRD',
    };

    return $prefix . '-' . str_pad((string) $seq, 4, '0', STR_PAD_LEFT);
}

function findExistingProduct(string $name): ?Product
{
    $exact = Product::withTrashed()->whereRaw('UPPER(name) = ?', [strtoupper($name)])->first();
    if ($exact) {
        return $exact;
    }

    // Cocokkan longgar: nama tanpa spasi berlebih
    $normalized = preg_replace('/\s+/', ' ', strtoupper(trim($name)));
    return Product::withTrashed()
        ->get(['id', 'name', 'code', 'deleted_at'])
        ->first(function (Product $p) use ($normalized) {
            return preg_replace('/\s+/', ' ', strtoupper(trim($p->name))) === $normalized;
        });
}

echo "=== IMPORT 47 PRODUK KE MASTER ===\n";
echo 'Total item di daftar: ' . count($items) . PHP_EOL;

$created = 0;
$updated = 0;
$failed = 0;
$logs = [];
$seqBase = (int) Product::withTrashed()->max('id') + 1;

DB::beginTransaction();
try {
    foreach ($items as $i => $row) {
        $name = $row['name'];
        $purchase = (float) $row['purchase'];
        $sell = (float) $row['sell'];
        $stock = (int) $row['stock'];
        $wholesale = (float) round($sell * 0.99);
        $hetMarkup = 10;
        $het = (float) round($purchase * (1 + $hetMarkup / 100)); // = sell jika margin 10%

        // Pastikan sell = purchase + 10% (konsisten dengan daftar)
        $expectedSell = (float) round($purchase * 1.10);
        if (abs($sell - $expectedSell) > 1) {
            $sell = $expectedSell;
            $wholesale = (float) round($sell * 0.99);
            $het = $expectedSell;
        }

        $normalized = Product::normalizeSellAgainstHet($sell, $wholesale, $het);
        $sell = $normalized['sell_price'];
        $wholesale = $normalized['wholesale_price'];

        $categoryId = resolveCategoryId($row['category']);
        $unitId = resolveUnitId($row['unit']);

        $existing = findExistingProduct($name);
        // Coba juga alias OCR dari gambar
        if (! $existing) {
            foreach ($nameAliases as $ocr => $canonical) {
                if (strcasecmp($canonical, $name) === 0) {
                    $existing = findExistingProduct($ocr);
                    break;
                }
            }
        }

        $payload = [
            'name' => $name,
            'category_id' => $categoryId,
            'unit_id' => $unitId,
            'composition' => $row['composition'],
            'dosage_form' => $row['dosage_form'],
            'route' => $row['route'],
            'description' => $row['description'],
            'drug_class' => $row['drug_class'],
            'manufacturer' => $row['manufacturer'],
            'requires_prescription' => (bool) $row['requires_prescription'],
            'purchase_price' => $purchase,
            'sell_price' => $sell,
            'wholesale_price' => $wholesale,
            'het_markup' => $hetMarkup,
            'het_price' => $het,
            'stock' => $stock,
            'stock_min' => max(5, (int) ceil($stock * 0.3)),
            'is_active' => true,
            'show_in_catalog' => true,
        ];

        try {
            if ($existing) {
                if ($existing->trashed()) {
                    $existing->restore();
                }
                // Pertahankan kode lama jika sudah ada
                $existing->update($payload);
                $updated++;
                $logs[] = "UPDATED #{$existing->id} {$existing->code} | {$name}";
            } else {
                $code = makeCode($row['category'], $name, $seqBase + $i);
                // Pastikan unik
                while (Product::withTrashed()->where('code', $code)->exists()) {
                    $seqBase++;
                    $code = makeCode($row['category'], $name, $seqBase + $i);
                }
                $product = Product::create(array_merge($payload, ['code' => $code]));
                $created++;
                $logs[] = "CREATED #{$product->id} {$code} | {$name}";
            }
        } catch (Throwable $e) {
            $failed++;
            $logs[] = "FAILED {$name}: " . $e->getMessage();
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
    "Impor daftar stok 47 produk (gambar). Created: {$created}, Updated: {$updated}, Failed: {$failed}"
);

echo "Created : {$created}\n";
echo "Updated : {$updated}\n";
echo "Failed  : {$failed}\n";
echo 'Total products now: ' . Product::count() . PHP_EOL;
echo "\nSample logs:\n";
foreach (array_slice($logs, 0, 8) as $line) {
    echo "- {$line}\n";
}
if (count($logs) > 8) {
    echo '... (' . (count($logs) - 8) . " more)\n";
}

$logPath = storage_path('app/imports/import-47-' . date('Ymd-His') . '.json');
if (! is_dir(dirname($logPath))) {
    mkdir(dirname($logPath), 0775, true);
}
file_put_contents($logPath, json_encode([
    'created' => $created,
    'updated' => $updated,
    'failed' => $failed,
    'logs' => $logs,
], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
echo "\nLog: {$logPath}\n";
echo "Selesai OK.\n";
