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
}
