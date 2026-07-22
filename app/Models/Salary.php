<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Salary extends Model
{
    use SoftDeletes;

    public const ENTITY_PT = 'pt';
    public const ENTITY_APOTEK = 'apotek';

    protected $fillable = [
        'user_id',
        'employee_id',
        'entity',
        'period_month',
        'period_year',
        'basic_salary',
        'overtime',
        'allowance',
        'bpjs_kesehatan',
        'bpjs_ketenagakerjaan',
        'deduction',
        'net_salary',
        'payment_date',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'basic_salary' => 'decimal:2',
        'overtime' => 'decimal:2',
        'allowance' => 'decimal:2',
        'bpjs_kesehatan' => 'decimal:2',
        'bpjs_ketenagakerjaan' => 'decimal:2',
        'deduction' => 'decimal:2',
        'net_salary' => 'decimal:2',
        'payment_date' => 'date',
    ];

    /** @return array<string, string> */
    public static function entities(): array
    {
        return [
            self::ENTITY_PT => 'PT NUR MADANI FARMA',
            self::ENTITY_APOTEK => 'APOTEK ALMAIRA',
        ];
    }

    public function isApotek(): bool
    {
        return ($this->entity ?? self::ENTITY_PT) === self::ENTITY_APOTEK;
    }

    public function getEntityLabelAttribute(): string
    {
        $key = $this->entity ?? self::ENTITY_PT;

        return self::entities()[$key] ?? strtoupper((string) $key);
    }

    public function getEntityShortLabelAttribute(): string
    {
        return $this->isApotek() ? 'Apotek Almaira' : 'PT Nur Madani Farma';
    }

    /**
     * Branding kop slip sesuai entitas.
     *
     * @return array{name: string, tagline: string, address: string, phone: string, email: string, ig: string, logo: string, watermark: string, accent: string, accent_dark: string, slip_prefix: string}
     */
    public function branding(): array
    {
        $phone = Setting::get('apotek_phone', '0851-6665-7070');
        $email = Setting::get('company_email', 'ptnurmadanifarma@gmail.com');
        $ig = Setting::get('company_instagram', '@apotekalmaira');

        if ($this->isApotek()) {
            return [
                // Kop slip Apotek — teks tetap (konsisten).
                'name' => 'Apotek Almaira',
                'tagline' => 'Pelayanan Kesehatan & Kefarmasian Terpercaya',
                'address' => Setting::get(
                    'apotek_address',
                    'Jl. Nuri No. 14 RT/RW 001/005, Kel. Komet, Kec. Banjarbaru Utara, Kota Banjarbaru, Kalsel 70714'
                ),
                'phone' => $phone,
                'email' => $email,
                'ig' => $ig,
                'logo' => 'assets/images/logo-apotek.png',
                'watermark' => 'assets/images/watermark-apotek.png',
                'accent' => '#0369a1',
                'accent_dark' => '#0c4a6e',
                'slip_prefix' => 'SG-AA',
            ];
        }

        return [
            // Kop slip PT — teks tetap (konsisten), tidak bergantung tagline landing.
            'name' => 'PT. Nur Madani Farma',
            'tagline' => 'Distributor & Mitra Pengadaan Alat Kesehatan & Farmasi',
            'address' => Setting::get(
                'company_office_address',
                'Jl. Panglima Batur No. 16, Kel. Komet, Kec. Banjarbaru Utara, Kota Banjarbaru, Kalsel 70714'
            ),
            'phone' => $phone,
            'email' => $email,
            'ig' => $ig,
            'logo' => 'assets/images/logo-ptnmf.png',
            'watermark' => 'assets/images/watermark-ptnmf.png',
            'accent' => '#047857',
            'accent_dark' => '#064e3b',
            'slip_prefix' => 'SG-NMF',
        ];
    }

    /** Relasi ke master karyawan (utama). */
    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id')->withTrashed();
    }

    /** Relasi legacy ke user (kompatibilitas data lama). */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id')->withTrashed();
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by')->withTrashed();
    }

    public function getEmployeeNameAttribute(): string
    {
        return $this->employee?->name
            ?? $this->user?->name
            ?? '—';
    }

    /**
     * Nama untuk slip: Title Case, gelar tetap S.Kom / S.H. (bukan S.KOM).
     */
    public static function formatPersonName(?string $name): string
    {
        $name = trim((string) $name);
        if ($name === '' || $name === '—') {
            return '—';
        }

        // Jika seluruhnya kapital / acak, normalisasi ke Title Case dulu.
        $letters = preg_replace('/[^a-zA-ZÀ-ÿ]/u', '', $name) ?? '';
        if ($letters !== '' && (mb_strtoupper($letters, 'UTF-8') === $letters || mb_strtolower($letters, 'UTF-8') === $letters)) {
            $name = mb_convert_case(mb_strtolower($name, 'UTF-8'), MB_CASE_TITLE, 'UTF-8');
        }

        $patterns = [
            '/\bHj\.*/iu' => 'Hj.',
            '/\bDrs\.*/iu' => 'Drs.',
            '/\bDr\.*/iu' => 'Dr.',
            '/\bIr\.*/iu' => 'Ir.',
            '/\bApt\.*/iu' => 'Apt.',
            '/\bS\.?\s*Kom\.*/iu' => 'S.Kom.',
            '/\bS\.?\s*Ked\.*/iu' => 'S.Ked.',
            '/\bS\.?\s*Farm\.*/iu' => 'S.Farm.',
            '/\bS\.?\s*Si\.*/iu' => 'S.Si.',
            '/\bS\.?\s*H\.*/iu' => 'S.H.',
            '/\bS\.?\s*E\.*/iu' => 'S.E.',
            '/\bM\.?\s*Kom\.*/iu' => 'M.Kom.',
            '/\bM\.?\s*Farm\.*/iu' => 'M.Farm.',
            '/\bM\.?\s*Kes\.*/iu' => 'M.Kes.',
            '/\bM\.?\s*M\.*/iu' => 'M.M.',
            '/\bH\.(?=\s)/u' => 'H.',
        ];

        foreach ($patterns as $pattern => $replacement) {
            $name = preg_replace($pattern, $replacement, $name) ?? $name;
        }

        $name = preg_replace('/\.{2,}/', '.', $name) ?? $name;
        $name = preg_replace('/\s*,\s*/', ', ', $name) ?? $name;
        $name = preg_replace('/\s+/', ' ', $name) ?? $name;

        return trim($name);
    }

    public function getEmployeePositionAttribute(): string
    {
        return $this->employee?->position
            ?? $this->user?->role?->name
            ?? '—';
    }

    public function getSlipNumberAttribute()
    {
        $prefix = $this->branding()['slip_prefix'];

        return $prefix.'/'.$this->period_year.'/'.sprintf('%02d', $this->period_month).'/'.sprintf('%04d', $this->id);
    }

    public static function penyebut($nilai)
    {
        $nilai = abs($nilai);
        $huruf = ['', 'Satu', 'Dua', 'Tiga', 'Empat', 'Lima', 'Enam', 'Tujuh', 'Delapan', 'Sembilan', 'Sepuluh', 'Sebelas'];
        $temp = '';
        if ($nilai < 12) {
            $temp = ' '.$huruf[$nilai];
        } elseif ($nilai < 20) {
            $temp = self::penyebut($nilai - 10).' Belas';
        } elseif ($nilai < 100) {
            $temp = self::penyebut((int) ($nilai / 10)).' Puluh'.self::penyebut($nilai % 10);
        } elseif ($nilai < 200) {
            $temp = ' Seratus'.self::penyebut($nilai - 100);
        } elseif ($nilai < 1000) {
            $temp = self::penyebut((int) ($nilai / 100)).' Ratus'.self::penyebut($nilai % 100);
        } elseif ($nilai < 2000) {
            $temp = ' Seribu'.self::penyebut($nilai - 1000);
        } elseif ($nilai < 1000000) {
            $temp = self::penyebut((int) ($nilai / 1000)).' Ribu'.self::penyebut($nilai % 1000);
        } elseif ($nilai < 1000000000) {
            $temp = self::penyebut((int) ($nilai / 1000000)).' Juta'.self::penyebut($nilai % 1000000);
        } elseif ($nilai < 1000000000000) {
            $temp = self::penyebut((int) ($nilai / 1000000000)).' Milyar'.self::penyebut(fmod($nilai, 1000000000));
        } elseif ($nilai < 1000000000000000) {
            $temp = self::penyebut((int) ($nilai / 1000000000000)).' Triliun'.self::penyebut(fmod($nilai, 1000000000000));
        }

        return $temp;
    }

    public function getTerbilangAttribute()
    {
        $nilai = (int) $this->net_salary;
        if ($nilai < 0) {
            $hasil = 'Minus '.trim(self::penyebut($nilai));
        } else {
            $hasil = trim(self::penyebut($nilai));
        }

        return $hasil.' Rupiah';
    }
}
