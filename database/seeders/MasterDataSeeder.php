<?php
namespace Database\Seeders;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
class MasterDataSeeder extends Seeder {
    public function run(): void {
        // Units
        DB::table('units')->insertOrIgnore([
            ['name'=>'Tablet','symbol'=>'tab','created_at'=>now(),'updated_at'=>now()],
            ['name'=>'Kapsul','symbol'=>'kaps','created_at'=>now(),'updated_at'=>now()],
            ['name'=>'Strip','symbol'=>'strip','created_at'=>now(),'updated_at'=>now()],
            ['name'=>'Box','symbol'=>'box','created_at'=>now(),'updated_at'=>now()],
            ['name'=>'Botol','symbol'=>'btl','created_at'=>now(),'updated_at'=>now()],
            ['name'=>'Tube','symbol'=>'tube','created_at'=>now(),'updated_at'=>now()],
            ['name'=>'Sachet','symbol'=>'sach','created_at'=>now(),'updated_at'=>now()],
            ['name'=>'Ampul','symbol'=>'amp','created_at'=>now(),'updated_at'=>now()],
            ['name'=>'Vial','symbol'=>'vial','created_at'=>now(),'updated_at'=>now()],
            ['name'=>'Pcs','symbol'=>'pcs','created_at'=>now(),'updated_at'=>now()],
            ['name'=>'Liter','symbol'=>'L','created_at'=>now(),'updated_at'=>now()],
            ['name'=>'ml','symbol'=>'ml','created_at'=>now(),'updated_at'=>now()],
        ]);
        // Categories
        $cats = ['Obat Bebas','Obat Bebas Terbatas','Obat Keras','Suplemen & Vitamin','Alat Kesehatan','Perawatan Bayi','Perawatan Luka','Kosmetik & Skincare','Obat Herbal','Obat Generik'];
        foreach ($cats as $c) {
            DB::table('categories')->insertOrIgnore(['name'=>$c,'slug'=>\Illuminate\Support\Str::slug($c),'is_active'=>1,'created_at'=>now(),'updated_at'=>now()]);
        }
        $this->command->info('✅ Master data seeder selesai!');
    }
}
