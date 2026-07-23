<?php
namespace Database\Seeders;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
class InitialSeeder extends Seeder {
    public function run(): void {
        DB::table('roles')->insertOrIgnore([
            ['id'=>1,'name'=>'Kepala IT','slug'=>'super_admin','description'=>'Akses penuh sistem (IT & seluruh modul)','created_at'=>now(),'updated_at'=>now()],
            ['id'=>2,'name'=>'Staff Keuangan','slug'=>'admin_keuangan','description'=>'Akses keuangan, laporan, inventori, dan master data terkait','created_at'=>now(),'updated_at'=>now()],
            ['id'=>3,'name'=>'Khusus Kasir','slug'=>'kasir','description'=>'Hanya akses kasir/POS, resep, pelanggan, dan riwayat penjualan sendiri','created_at'=>now(),'updated_at'=>now()],
            ['id'=>4,'name'=>'Mitra Katalog','slug'=>'mitra','description'=>'Akses portal mitra untuk order/PO dari e-catalog','created_at'=>now(),'updated_at'=>now()],
            ['id'=>5,'name'=>'Staff Operasional','slug'=>'staff_operasional','description'=>'Operasional harian: POS, inventori, stok (tanpa pengadaan & keuangan)','created_at'=>now(),'updated_at'=>now()],
        ]);

        foreach ([
            ['slug' => 'super_admin', 'name' => 'Kepala IT', 'description' => 'Akses penuh sistem (IT & seluruh modul)'],
            ['slug' => 'admin_keuangan', 'name' => 'Staff Keuangan', 'description' => 'Akses keuangan, laporan, inventori, dan master data terkait'],
            ['slug' => 'kasir', 'name' => 'Khusus Kasir', 'description' => 'Hanya akses kasir/POS, resep, pelanggan, dan riwayat penjualan sendiri'],
            ['slug' => 'staff_operasional', 'name' => 'Staff Operasional', 'description' => 'Operasional harian: POS, inventori, stok (tanpa pengadaan & keuangan)'],
            ['slug' => 'staff_it', 'name' => 'Staff IT', 'description' => 'Manajemen user, backup, log, dan dukungan inventori'],
            ['slug' => 'kepala_operasional', 'name' => 'Kepala Operasional', 'description' => 'Operasional apotek tanpa akses sistem IT & keuangan murni'],
        ] as $role) {
            $exists = DB::table('roles')->where('slug', $role['slug'])->exists();
            if ($exists) {
                DB::table('roles')->where('slug', $role['slug'])->update([
                    'name' => $role['name'],
                    'description' => $role['description'],
                    'updated_at' => now(),
                ]);
            } else {
                DB::table('roles')->insert(array_merge($role, [
                    'created_at' => now(),
                    'updated_at' => now(),
                ]));
            }
        }

        $roleIds = DB::table('roles')->pluck('id', 'slug');
        $password = Hash::make('Almaira@2026');

        $users = [
            ['name'=>'TAUFIQURRAHMAN, S.Kom','username'=>'taufiqit','email'=>'taufiqit2020@gmail.com','role'=>'super_admin'],
            ['name'=>'RAHMAN, S.Kom','username'=>'rahman','email'=>'rahman@apotekalmaira.com','role'=>'staff_it'],
            ['name'=>'TAUFIQURRAHMAN, S.Kom','username'=>'taufiq','email'=>'taufiq@apotekalmaira.com','role'=>'kepala_operasional'],
            ['name'=>'TAUFIQ','username'=>'taufiqop','email'=>'taufiqop@apotekalmaira.com','role'=>'staff_operasional'],
            ['name'=>'Staff Keuangan','username'=>'keuangan1','email'=>'keuangan1@apotekalmaira.com','role'=>'admin_keuangan'],
            ['name'=>'Siti Kamariah, S.Pd.','username'=>'siti','email'=>'siti@apotekalmaira.com','role'=>'admin_keuangan'],
            ['name'=>'Alyaiqlima, S.Farm.','username'=>'alya','email'=>'alya@apotekalmaira.com','role'=>'kasir'],
            ['name'=>'SARAH','username'=>'sarah','email'=>'sarah@apotekalmaira.com','role'=>'kasir'],
        ];

        for ($i = 1; $i <= 5; $i++) {
            $users[] = [
                'name' => 'Kasir Almaira '.$i,
                'username' => 'kasiralmaira'.$i,
                'email' => 'kasiralmaira'.$i.'@apotekalmaira.com',
                'role' => 'kasir',
            ];
        }

        foreach ($users as $u) {
            $roleId = $roleIds[$u['role']] ?? null;
            if (! $roleId) {
                continue;
            }
            $existing = DB::table('users')->where('username', $u['username'])->first();
            if ($existing) {
                DB::table('users')->where('id', $existing->id)->update([
                    'name' => $u['name'],
                    'email' => $u['email'],
                    'role_id' => $roleId,
                    'is_active' => 1,
                    'updated_at' => now(),
                ]);
                continue;
            }
            if (DB::table('users')->where('email', $u['email'])->exists()) {
                $u['email'] = $u['username'].'+seed@apotekalmaira.com';
            }
            DB::table('users')->insert([
                'name' => $u['name'],
                'username' => $u['username'],
                'email' => $u['email'],
                'password' => $password,
                'role_id' => $roleId,
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
        $this->command->info('✅ Seeder selesai! Login Kepala IT: taufiqit / Almaira@2026');
    }
}
