<?php
namespace Database\Seeders;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
class InitialSeeder extends Seeder {
    public function run(): void {
        DB::table('roles')->insertOrIgnore([
            ['id'=>1,'name'=>'Kepala Operasional','slug'=>'super_admin','description'=>'Akses penuh kepala operasional','created_at'=>now(),'updated_at'=>now()],
            ['id'=>2,'name'=>'Kepala Keuangan dan Administrasi','slug'=>'admin_keuangan','description'=>'Akses kepala keuangan dan administrasi','created_at'=>now(),'updated_at'=>now()],
            ['id'=>3,'name'=>'Staff Administrasi dan Kasir','slug'=>'kasir','description'=>'Akses staff administrasi dan kasir','created_at'=>now(),'updated_at'=>now()],
            ['id'=>4,'name'=>'Mitra Katalog','slug'=>'mitra','description'=>'Akses portal mitra untuk order/PO dari e-catalog','created_at'=>now(),'updated_at'=>now()],
            ['id'=>5,'name'=>'Staff Operasional','slug'=>'staff_operasional','description'=>'Akses staff operasional apotek','created_at'=>now(),'updated_at'=>now()],
        ]);
        // Pastikan nama role tetap sesuai jabatan (updateOrIgnore tidak mengubah baris yang sudah ada)
        DB::table('roles')->where('slug', 'super_admin')->update([
            'name' => 'Kepala Operasional',
            'description' => 'Akses penuh kepala operasional',
            'updated_at' => now(),
        ]);
        DB::table('roles')->where('slug', 'admin_keuangan')->update([
            'name' => 'Kepala Keuangan dan Administrasi',
            'description' => 'Akses kepala keuangan dan administrasi',
            'updated_at' => now(),
        ]);
        DB::table('roles')->where('slug', 'kasir')->update([
            'name' => 'Staff Administrasi dan Kasir',
            'description' => 'Akses staff administrasi dan kasir',
            'updated_at' => now(),
        ]);
        DB::table('roles')->where('slug', 'staff_operasional')->update([
            'name' => 'Staff Operasional',
            'description' => 'Akses staff operasional apotek',
            'updated_at' => now(),
        ]);
        DB::table('users')->insertOrIgnore([
            ['name'=>'Taufiqurrahman, S.Kom.','username'=>'taufiq','email'=>'taufiq@apotekalmaira.com','password'=>Hash::make('Almaira@2026'),'role_id'=>1,'is_active'=>1,'email_verified_at'=>now(),'created_at'=>now(),'updated_at'=>now()],
            ['name'=>'Siti Kamariah, S.Pd.','username'=>'siti','email'=>'siti@apotekalmaira.com','password'=>Hash::make('Almaira@2026'),'role_id'=>2,'is_active'=>1,'email_verified_at'=>now(),'created_at'=>now(),'updated_at'=>now()],
            ['name'=>'Alyaiqlima, S.Farm.','username'=>'alya','email'=>'alya@apotekalmaira.com','password'=>Hash::make('Almaira@2026'),'role_id'=>3,'is_active'=>1,'email_verified_at'=>now(),'created_at'=>now(),'updated_at'=>now()],
            ['name'=>'SARAH','username'=>'sarah','email'=>'sarah@apotekalmaira.com','password'=>Hash::make('Almaira@2026'),'role_id'=>3,'is_active'=>1,'email_verified_at'=>now(),'created_at'=>now(),'updated_at'=>now()],
        ]);

        $opsRoleId = DB::table('roles')->where('slug', 'staff_operasional')->value('id');
        if ($opsRoleId && ! DB::table('users')->where('username', 'taufiqop')->exists()) {
            DB::table('users')->insert([
                'name' => 'TAUFIQ',
                'username' => 'taufiqop',
                'email' => 'taufiqop@apotekalmaira.com',
                'password' => Hash::make('Almaira@2026'),
                'role_id' => $opsRoleId,
                'is_active' => 1,
                'email_verified_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
        $settings = [
            ['key'=>'apotek_name','value'=>'Apotek Almaira'],
            ['key'=>'apotek_phone','value'=>'0851-6665-7070'],
            ['key'=>'apotek_address','value'=>'Jl. Nuri No.14 RT/RW 001/005, Kel. Komet, Kec. Banjarbaru Utara, Kota Banjarbaru, Kalsel 70714'],
            ['key'=>'apotek_owner','value'=>'PT Nur Madani Farma'],
            ['key'=>'company_office_address','value'=>'Jl. Panglima Batur No. 16, Kel. Komet, Kec. Banjarbaru Utara, Kota Banjarbaru, Kalimantan Selatan 70714'],
            ['key'=>'company_email','value'=>'ptnurmadanifarma@gmail.com'],
            ['key'=>'company_instagram','value'=>'@apotekalmaira'],
            ['key'=>'company_tagline','value'=>'Solusi Kesehatan Terpercaya di Banjarbaru'],
            ['key'=>'company_about','value'=>'PT Nur Madani Farma adalah perusahaan farmasi yang mengoperasikan Apotek Almaira Banjarbaru. Berlokasi strategis di Kota Banjarbaru, Kalimantan Selatan, kami hadir melayani masyarakat dengan standar pelayanan farmasi profesional — mulai dari penjualan obat bebas, bebas terbatas, hingga obat keras resep dokter, serta layanan kemitraan B2B untuk institusi dan mitra usaha.'],
            ['key'=>'company_vision','value'=>'Menjadi apotek terdepan dan terpercaya di Banjarbaru yang memberikan layanan kefarmasian berkualitas, inovatif, dan berorientasi pada kesehatan masyarakat Kalimantan Selatan.'],
            ['key'=>'company_mission','value'=>"Menyediakan obat-obatan, alat kesehatan, dan produk farmasi berkualitas dengan harga kompetitif dan transparan.\nMemberikan pelayanan kefarmasian profesional oleh Apoteker Penanggung Jawab bersertifikat sesuai standar regulasi.\nMengembangkan sistem manajemen apotek berbasis teknologi untuk efisiensi, akurasi, dan kepuasan pelanggan.\nMembangun jaringan kemitraan B2B yang produktif melalui E-Catalog dan layanan distribusi yang handal.\nBerkomitmen pada kepatuhan hukum, etika profesi, dan pengembangan kesehatan masyarakat Banjarbaru serta Kalimantan Selatan.\nMeningkatkan kualitas pelayanan melalui pengembangan kompetensi tenaga kefarmasian serta penerapan standar pelayanan prima secara berkelanjutan."],
            ['key'=>'qris_nmid','value'=>'ID1026522359276'],
            ['key'=>'ppn_default','value'=>'11'],
            ['key'=>'ppn_active','value'=>'1'],
            ['key'=>'invoice_prefix','value'=>'APK'],
        ];
        foreach ($settings as $s) DB::table('settings')->updateOrInsert(['key'=>$s['key']],array_merge($s,['updated_at'=>now()]));
        $this->command->info('✅ Seeder selesai! Login: taufiq / Almaira@2026');
    }
}
