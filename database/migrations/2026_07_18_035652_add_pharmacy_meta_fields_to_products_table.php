<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->string('drug_class', 100)->nullable()->after('manufacturer');
            $table->string('dosage_form', 100)->nullable()->after('drug_class');
            $table->string('route', 100)->nullable()->after('dosage_form');
        });

        // Migrasikan data lama: composition "Bentuk / Rute" → kolom terpisah.
        $products = DB::table('products')->select('id', 'composition')->whereNotNull('composition')->get();
        foreach ($products as $product) {
            $composition = trim((string) $product->composition);
            if ($composition === '' || ! str_contains($composition, '/')) {
                continue;
            }

            $parts = array_values(array_filter(array_map('trim', explode('/', $composition))));
            if (count($parts) < 2) {
                continue;
            }

            $bentuk = $parts[0];
            $rute = $parts[1];
            $looksLikeForm = static function (string $value): bool {
                $v = strtolower($value);
                foreach (['tablet', 'kapsul', 'injeksi', 'larutan', 'sirup', 'tetes', 'krim', 'salep', 'gel', 'nebu', 'inhaler', 'serbuk', 'ampul', 'vial', 'botol', 'topikal', 'oftal'] as $needle) {
                    if (str_contains($v, $needle)) {
                        return true;
                    }
                }

                return false;
            };

            if ($looksLikeForm($bentuk) && $looksLikeForm($rute)) {
                DB::table('products')->where('id', $product->id)->update([
                    'dosage_form' => $bentuk,
                    'route' => $rute,
                    'composition' => null,
                ]);
            } elseif ($looksLikeForm($bentuk)) {
                DB::table('products')->where('id', $product->id)->update([
                    'dosage_form' => $bentuk,
                    'route' => $rute,
                    'composition' => null,
                ]);
            }
        }
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['drug_class', 'dosage_form', 'route']);
        });
    }
};
