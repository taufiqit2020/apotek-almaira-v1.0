<?php
namespace App\Http\Controllers;

use App\Models\Setting;
use App\Services\ActivityLogService;
use Illuminate\Http\Request;

class SettingsController extends Controller {
    public function index() {
        $apotekName = Setting::get('apotek_name', 'Apotek Almaira');
        $companyName = Setting::get('apotek_owner', 'PT Nur Madani Farma');
        $address = Setting::get('apotek_address', 'Jl Nuri No.14 RT/RW 001/005, Kel. Komet, Kec. Banjarbaru Utara, Kalsel 70714');
        $phone = Setting::get('apotek_phone', '0851-6665-7070');
        $qrisNmid = Setting::get('qris_nmid', 'ID1026522359276');
        $bankName = Setting::get('bank_name', 'BCA');
        $bankAccount = Setting::get('bank_account', '');
        $bankHolder = Setting::get('bank_holder', 'PT Nur Madani Farma');
        
        $apoteker1Name = Setting::get('apoteker_1_name', 'Apt. Wulan Ageng Sujatmiko, S.Farm., M.M.');
        $apoteker1Sip = Setting::get('apoteker_1_sip', 'NR63722606010965');
        $apoteker1Str = Setting::get('apoteker_1_str', 'AU00001933362763');
        $apoteker2Name = Setting::get('apoteker_2_name', 'Apt. Qory Rahmat Nazri, S.Farm.');
        $apoteker2Sip = Setting::get('apoteker_2_sip', 'NR63722606004748');
        $apoteker2Str = Setting::get('apoteker_2_str', 'DZ00000108645367');
        $pimpinanName = Setting::get('pimpinan_name', 'Hj. Nor Maulida, S.H.');

        $officeAddress = Setting::get('company_office_address', 'Jl. Panglima Batur No. 16, Kel. Komet, Kec. Banjarbaru Utara, Kota Banjarbaru, Kalimantan Selatan 70714');
        $companyEmail = Setting::get('company_email', 'ptnurmadanifarma@gmail.com');
        $companyInstagram = Setting::get('company_instagram', '@apotekalmaira');
        $companyTagline = Setting::get('company_tagline', 'Solusi Kesehatan Terpercaya di Banjarbaru');
        $companyAbout = Setting::get('company_about', '');
        $companyVision = Setting::get('company_vision', '');
        $companyMission = Setting::get('company_mission', '');
        
        $ppnActive = Setting::get('pos_ppn_active', 'false');
        $ppnPercent = Setting::get('pos_ppn_percent', '11');
        $ppnBearer = Setting::get('pos_ppn_bearer', 'buyer'); // 'buyer' or 'seller'
        
        $printerConnection = Setting::get('printer_connection', 'LAN');
        $printerIp = Setting::get('printer_ip', '192.168.1.100');
        $printerPort = Setting::get('printer_port', '9100');
        $printerFooter1 = Setting::get('printer_footer_1', 'Terima kasih telah berbelanja');
        $printerFooter2 = Setting::get('printer_footer_2', 'di Apotek Almaira Banjarbaru');
        $printerFooter3 = Setting::get('printer_footer_3', 'Semoga lekas sembuh dan sehat!');

        $defaultRules = [
            ['min_qty' => 1, 'max_qty' => 10, 'percents' => '1.5, 2.5, 3.5, 4.5, 5.5, 6.5, 7.5, 8.5, 9.5, 10.5'],
            ['min_qty' => 11, 'max_qty' => 20, 'percents' => '11.5, 12.5, 13.5, 14.5, 15.5, 16.5, 17.5, 18.5, 19.5, 20.5'],
            ['min_qty' => 21, 'max_qty' => 999, 'percents' => '21.5, 22.5, 23.5, 24.5, 25.5, 26.5, 27.5, 28.5, 29.5, 30.5']
        ];
        
        $discountRules = json_decode(Setting::get('pos_discount_rules', json_encode($defaultRules)), true);
        if (! is_array($discountRules)) {
            $discountRules = $defaultRules;
        }

        $crmPointMultiplier = Setting::get('crm_point_multiplier', '1000');
        $crmPointValue = Setting::get('crm_point_value', '1');

        $notifAlertWa = Setting::get('notif_alert_wa', 'false');
        $notifWaNumber = Setting::get('notif_wa_number', '0851-6665-7070');
        $notifAlertEmail = Setting::get('notif_alert_email', 'false');
        $notifEmailAddress = Setting::get('notif_email_address', 'owner@apotekalmaira.com');
        $notifAlertStock = Setting::get('notif_alert_stock', 'true');
        $notifAlertBackup = Setting::get('notif_alert_backup', 'true');

        return view('settings.index', compact(
            'apotekName', 'companyName', 'address', 'phone', 'qrisNmid',
            'bankName', 'bankAccount', 'bankHolder',
            'apoteker1Name', 'apoteker1Sip', 'apoteker1Str',
            'apoteker2Name', 'apoteker2Sip', 'apoteker2Str',
            'pimpinanName',
            'officeAddress', 'companyEmail', 'companyInstagram', 'companyTagline',
            'companyAbout', 'companyVision', 'companyMission',
            'ppnActive', 'ppnPercent', 'ppnBearer',
            'printerConnection', 'printerIp', 'printerPort',
            'printerFooter1', 'printerFooter2', 'printerFooter3',
            'discountRules', 'crmPointMultiplier', 'crmPointValue',
            'notifAlertWa', 'notifWaNumber', 'notifAlertEmail', 'notifEmailAddress',
            'notifAlertStock', 'notifAlertBackup'
        ));
    }

    public function update(Request $request) {
        $request->validate([
            // Tab 1 Info
            'apotek_name' => 'required|string|max:100',
            'company_name' => 'required|string|max:100',
            'address' => 'required|string|max:255',
            'phone' => 'required|string|max:30',
            'qris_nmid' => 'required|string|max:50',
            'bank_name' => 'nullable|string|max:100',
            'bank_account' => 'nullable|string|max:50',
            'bank_holder' => 'nullable|string|max:150',
            'apoteker_1_name' => 'required|string|max:100',
            'apoteker_1_sip' => 'required|string|max:100',
            'apoteker_1_str' => 'required|string|max:100',
            'apoteker_2_name' => 'required|string|max:100',
            'apoteker_2_sip' => 'required|string|max:100',
            'apoteker_2_str' => 'required|string|max:100',
            'pimpinan_name' => 'required|string|max:100',
            'office_address' => 'nullable|string|max:500',
            'company_email' => 'nullable|email|max:100',
            'company_instagram' => 'nullable|string|max:100',
            'company_tagline' => 'nullable|string|max:200',
            'company_about' => 'nullable|string|max:2000',
            'company_vision' => 'nullable|string|max:1000',
            'company_mission' => 'nullable|string|max:3000',
            'crm_point_multiplier' => 'required|integer|min:1',
            'crm_point_value' => 'required|integer|min:0',
            
            // Tab Notification
            'notif_alert_wa' => 'nullable|string',
            'notif_wa_number' => 'required|string|max:30',
            'notif_alert_email' => 'nullable|string',
            'notif_email_address' => 'required|email|max:100',
            'notif_alert_stock' => 'nullable|string',
            'notif_alert_backup' => 'nullable|string',
            
            // Tab 2 PPN
            'pos_ppn_active' => 'nullable|string',
            'pos_ppn_percent' => 'required|numeric|min:0|max:100',
            'pos_ppn_bearer' => 'required|in:buyer,seller',
            
            // Tab 3 Diskon
            'rules' => 'nullable|array',
            'rules.*.min_qty' => 'required|integer|min:1',
            'rules.*.max_qty' => 'required|integer|min:1|gte:rules.*.min_qty',
            'rules.*.percents' => 'required|string',
            
            // Tab 4 Printer
            'printer_connection' => 'required|in:USB,LAN,Serial',
            'printer_ip' => 'required_if:printer_connection,LAN|nullable|ip',
            'printer_port' => 'required_if:printer_connection,LAN|nullable|integer',
            'printer_footer_1' => 'nullable|string|max:100',
            'printer_footer_2' => 'nullable|string|max:100',
            'printer_footer_3' => 'nullable|string|max:100',
        ]);

        $oldSettings = Setting::pluck('value', 'key')->toArray();

        // Save Tab 1
        Setting::set('apotek_name', $request->apotek_name);
        Setting::set('apotek_owner', $request->company_name);
        Setting::set('apotek_address', $request->address);
        Setting::set('apotek_phone', $request->phone);
        Setting::set('qris_nmid', $request->qris_nmid);
        Setting::set('bank_name', $request->bank_name ?? '');
        Setting::set('bank_account', $request->bank_account ?? '');
        Setting::set('bank_holder', $request->bank_holder ?? '');
        Setting::set('apoteker_1_name', $request->apoteker_1_name);
        Setting::set('apoteker_1_sip', $request->apoteker_1_sip);
        Setting::set('apoteker_1_str', $request->apoteker_1_str);
        Setting::set('apoteker_2_name', $request->apoteker_2_name);
        Setting::set('apoteker_2_sip', $request->apoteker_2_sip);
        Setting::set('apoteker_2_str', $request->apoteker_2_str);
        Setting::set('pimpinan_name', $request->pimpinan_name);
        Setting::set('company_office_address', $request->office_address ?? '');
        Setting::set('company_email', $request->company_email ?? '');
        Setting::set('company_instagram', $request->company_instagram ?? '');
        Setting::set('company_tagline', $request->company_tagline ?? '');
        Setting::set('company_about', $request->company_about ?? '');
        Setting::set('company_vision', $request->company_vision ?? '');
        Setting::set('company_mission', $request->company_mission ?? '');
        Setting::set('crm_point_multiplier', $request->crm_point_multiplier);
        Setting::set('crm_point_value', $request->crm_point_value);

        // Save Notification Tab
        Setting::set('notif_alert_wa', $request->has('notif_alert_wa') ? 'true' : 'false');
        Setting::set('notif_wa_number', $request->notif_wa_number);
        Setting::set('notif_alert_email', $request->has('notif_alert_email') ? 'true' : 'false');
        Setting::set('notif_email_address', $request->notif_email_address);
        Setting::set('notif_alert_stock', $request->has('notif_alert_stock') ? 'true' : 'false');
        Setting::set('notif_alert_backup', $request->has('notif_alert_backup') ? 'true' : 'false');

        // Save Tab 2
        Setting::set('pos_ppn_active', $request->has('pos_ppn_active') ? 'true' : 'false');
        Setting::set('pos_ppn_percent', $request->pos_ppn_percent);
        Setting::set('pos_ppn_bearer', $request->pos_ppn_bearer);

        // Save Tab 3 — aturan diskon bertingkat (boleh kosong)
        $rules = [];
        foreach ($request->input('rules', []) as $rule) {
            $rules[] = [
                'min_qty' => (int) $rule['min_qty'],
                'max_qty' => (int) $rule['max_qty'],
                'percents' => trim((string) $rule['percents']),
            ];
        }
        Setting::set('pos_discount_rules', json_encode($rules));

        // Save Tab 4
        Setting::set('printer_connection', $request->printer_connection);
        Setting::set('printer_ip', $request->printer_ip);
        Setting::set('printer_port', $request->printer_port ?? '9100');
        Setting::set('printer_footer_1', $request->printer_footer_1);
        Setting::set('printer_footer_2', $request->printer_footer_2);
        Setting::set('printer_footer_3', $request->printer_footer_3);

        $newSettings = Setting::pluck('value', 'key')->toArray();
        ActivityLogService::updated('Pengaturan', 'Memperbarui pengaturan sistem', $oldSettings, $newSettings);

        return redirect()->route('settings.index')->with('toast_success', 'Pengaturan sistem berhasil diperbarui!');
    }
}
