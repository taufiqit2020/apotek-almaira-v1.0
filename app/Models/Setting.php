<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class Setting extends Model {
    public $timestamps = false;
    protected $fillable = ['key', 'value', 'description'];
    const UPDATED_AT = 'updated_at';
    public static function get(string $key, $default = null) {
        return static::where('key', $key)->value('value') ?? $default;
    }
    public static function set(string $key, $value): void {
        static::updateOrCreate(['key' => $key], ['value' => $value, 'updated_at' => now()]);
    }

    /** Opsi HET Markup Grosir (%) untuk dropdown Master Produk (1–30). */
    public static function wholesaleMarkupOptions(): array
    {
        $raw = static::get('product_wholesale_markup_options', implode(',', range(1, 30)));

        return static::parseMarkupPercents((string) $raw);
    }

    /** Default markup grosir saat produk baru / sync (0–30). */
    public static function wholesaleMarkupDefault(): int
    {
        $default = (int) static::get('product_wholesale_markup_default', '5');
        if ($default < 0 || $default > 30) {
            return 5;
        }

        return $default;
    }

    /** @return list<int> */
    public static function parseMarkupPercents(string $raw): array
    {
        $opts = [];
        foreach (preg_split('/[\s,;]+/', trim($raw)) ?: [] as $part) {
            if ($part === '' || ! is_numeric($part)) {
                continue;
            }
            $n = (int) $part;
            if ($n >= 1 && $n <= 30) {
                $opts[$n] = $n;
            }
        }
        $opts = array_values($opts);
        sort($opts);

        return $opts !== [] ? $opts : range(1, 30);
    }
}
