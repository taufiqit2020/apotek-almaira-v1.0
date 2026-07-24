<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
class Category extends Model {
    protected $fillable = ['name', 'slug', 'description', 'is_active'];
    protected $casts = ['is_active' => 'boolean'];
    protected static function boot() {
        parent::boot();
        static::creating(fn($m) => $m->slug = $m->slug ?: Str::slug($m->name));
    }
    public function products() { return $this->hasMany(Product::class); }
    public function scopeActive($q) { return $q->where('is_active', true); }

    /**
     * Hasilkan kode produk berikutnya secara otomatis berdasarkan kategori.
     */
    public static function generateNextProductCode(int $categoryId, ?int $ignoreProductId = null): string
    {
        $category = self::find($categoryId);
        if (!$category) return '';

        $existingQuery = Product::where('category_id', $categoryId);
        if ($ignoreProductId) {
            $existingQuery->where('id', '!=', $ignoreProductId);
        }
        $existingCodes = $existingQuery->whereNotNull('code')->pluck('code');

        $prefix = null;
        $maxNum = 0;
        $padding = 4;

        foreach ($existingCodes as $code) {
            $trimmed = trim((string) $code);
            if (preg_match('/^([A-Za-z0-9]+)-(\d+)$/i', $trimmed, $matches)) {
                $prefix = strtoupper($matches[1]);
                $num = (int) $matches[2];
                if ($num > $maxNum) {
                    $maxNum = $num;
                }
                $padding = max(strlen($matches[2]), $padding);
            }
        }

        if (!$prefix) {
            $name = trim((string) $category->name);
            $words = preg_split('/\s+/', $name);
            if (count($words) >= 3) {
                $prefix = strtoupper(substr($words[0], 0, 1) . substr($words[1], 0, 1) . substr($words[2], 0, 1));
            } elseif (count($words) == 2) {
                $p1 = strtoupper(substr($words[0], 0, 2));
                $p2 = strtoupper(substr($words[1], 0, 1));
                $prefix = $p1 . $p2;
            } else {
                $clean = preg_replace('/[^A-Za-z0-9]/', '', $name);
                $prefix = strtoupper(substr($clean, 0, 3));
            }
            $prefix = preg_replace('/[^A-Z0-9]/', '', $prefix);
            if (strlen($prefix) < 2) $prefix = 'PRD';
        }

        $allWithPrefix = Product::where('code', 'like', $prefix . '-%')->pluck('code');
        foreach ($allWithPrefix as $code) {
            $trimmed = trim((string) $code);
            if (preg_match('/^' . preg_quote($prefix, '/') . '-(\d+)$/i', $trimmed, $matches)) {
                $num = (int) $matches[1];
                if ($num > $maxNum) {
                    $maxNum = $num;
                }
                $padding = max(strlen($matches[1]), $padding);
            }
        }

        $nextNum = $maxNum + 1;
        return sprintf('%s-%0' . $padding . 'd', $prefix, $nextNum);
    }
}
