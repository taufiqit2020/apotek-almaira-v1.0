<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Setting;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class LandingController extends Controller
{
    public function index(): View|RedirectResponse
    {
        if (auth()->check()) {
            return redirect()->route('dashboard');
        }

        return view('landing.index', self::companyProfile());
    }

    /**
     * Data profil perusahaan — dipakai landing page & dapat dipakai view lain.
     *
     * @return array<string, mixed>
     */
    public static function companyProfile(): array
    {
        $phone = Setting::get('apotek_phone', '0851-6665-7070');
        $waNumber = preg_replace('/^0/', '62', preg_replace('/\D/', '', $phone));

        $missionRaw = Setting::get('company_mission', implode("\n", [
            'Menyediakan obat-obatan, alat kesehatan, dan produk farmasi berkualitas dengan harga kompetitif dan transparan.',
            'Memberikan pelayanan kefarmasian profesional oleh Apoteker Penanggung Jawab bersertifikat sesuai standar regulasi.',
            'Mengembangkan sistem manajemen apotek berbasis teknologi untuk efisiensi, akurasi, dan kepuasan pelanggan.',
            'Membangun jaringan kemitraan B2B yang produktif melalui E-Catalog dan layanan distribusi yang handal.',
            'Berkomitmen pada kepatuhan hukum, etika profesi, dan pengembangan kesehatan masyarakat Banjarbaru serta Kalimantan Selatan.',
            'Meningkatkan kualitas pelayanan melalui pengembangan kompetensi tenaga kefarmasian serta penerapan standar pelayanan prima secara berkelanjutan.',
        ]));

        $missions = array_values(array_filter(array_map('trim', preg_split('/\r\n|\r|\n/', $missionRaw))));

        return [
            'apotekName'      => Setting::get('apotek_name', 'Apotek Almaira'),
            'companyName'     => Setting::get('apotek_owner', 'PT Nur Madani Farma'),
            'apotekAddress'   => Setting::get('apotek_address', 'Jl. Nuri No.14 RT/RW 001/005, Kel. Komet, Kec. Banjarbaru Utara, Kota Banjarbaru, Kalsel 70714'),
            'officeAddress'   => Setting::get('company_office_address', 'Jl. Panglima Batur No. 16, Kel. Komet, Kec. Banjarbaru Utara, Kota Banjarbaru, Kalimantan Selatan 70714'),
            'phone'           => $phone,
            'waNumber'        => $waNumber,
            'email'           => Setting::get('company_email', 'ptnurmadanifarma@gmail.com'),
            'instagram'       => Setting::get('company_instagram', '@apotekalmaira'),
            'tagline'         => Setting::get('company_tagline', 'Solusi Kesehatan Terpercaya di Banjarbaru'),
            'about'           => Setting::get('company_about', 'PT Nur Madani Farma adalah perusahaan farmasi yang mengoperasikan Apotek Almaira Banjarbaru. Berlokasi strategis di Kota Banjarbaru, Kalimantan Selatan, kami hadir melayani masyarakat dengan standar pelayanan farmasi profesional — mulai dari penjualan obat bebas, bebas terbatas, hingga obat keras resep dokter, serta layanan kemitraan B2B untuk institusi dan mitra usaha.'),
            'vision'          => Setting::get('company_vision', 'Menjadi apotek terdepan dan terpercaya di Banjarbaru yang memberikan layanan kefarmasian berkualitas, inovatif, dan berorientasi pada kesehatan masyarakat Kalimantan Selatan.'),
            'missions'        => $missions,
            'bankName'        => Setting::get('bank_name', 'BCA'),
            'bankAccount'     => Setting::get('bank_account', ''),
            'bankHolder'      => Setting::get('bank_holder', 'PT Nur Madani Farma'),
            'apoteker1Name'   => Setting::get('apoteker_1_name', 'Apt. Wulan Ageng Sujatmiko, S.Farm., M.M.'),
            'apoteker1Sip'    => Setting::get('apoteker_1_sip', 'NR63722606010965'),
            'apoteker2Name'   => Setting::get('apoteker_2_name', 'Apt. Qory Rahmat Nazri, S.Farm.'),
            'apoteker2Sip'    => Setting::get('apoteker_2_sip', 'NR63722606004748'),
            'pimpinanName'    => Setting::get('pimpinan_name', 'Hj. Nor Maulida, S.H.'),
            'catalogCount'    => Product::active()->inCatalog()->count(),
        ];
    }
}
