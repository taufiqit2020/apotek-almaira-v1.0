<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class Employee extends Model
{
    use SoftDeletes;

    public const ENTITY_PT = 'pt';
    public const ENTITY_APOTEK = 'apotek';
    public const ENTITY_BOTH = 'both';

    protected $fillable = [
        'code', 'name', 'position', 'job_position_id', 'entity_scope',
        'phone', 'email', 'address',
        'join_date', 'birth_date', 'gender', 'nik',
        'bank_name', 'bank_account', 'bank_holder',
        'photo', 'user_id', 'is_active', 'notes',
    ];

    protected $casts = [
        'join_date' => 'date',
        'birth_date' => 'date',
        'is_active' => 'boolean',
    ];

    protected $appends = ['photo_url', 'initials', 'entity_label'];

    /** @return array<string, string> */
    public static function entityScopes(): array
    {
        return [
            self::ENTITY_BOTH => 'PT & Apotek (Keduanya)',
            self::ENTITY_PT => 'PT NUR MADANI FARMA',
            self::ENTITY_APOTEK => 'APOTEK ALMAIRA',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class)->withTrashed();
    }

    public function jobPosition()
    {
        return $this->belongsTo(JobPosition::class);
    }

    public function salaries()
    {
        return $this->hasMany(Salary::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function getEntityLabelAttribute(): string
    {
        return self::entityScopes()[$this->entity_scope ?? self::ENTITY_BOTH]
            ?? strtoupper((string) $this->entity_scope);
    }

    public function getInitialsAttribute(): string
    {
        $parts = preg_split('/\s+/', trim((string) $this->name)) ?: [];
        $initials = '';
        foreach (array_slice($parts, 0, 2) as $part) {
            $initials .= mb_strtoupper(mb_substr($part, 0, 1));
        }

        return $initials !== '' ? $initials : 'K';
    }

    public function getPhotoUrlAttribute(): string
    {
        if ($this->photo && file_exists(public_path($this->photo))) {
            return asset($this->photo);
        }

        return '';
    }

    public function canReceiveForEntity(string $entity): bool
    {
        $scope = $this->entity_scope ?? self::ENTITY_BOTH;
        if ($scope === self::ENTITY_BOTH) {
            return true;
        }

        return $scope === $entity;
    }

    public static function nextCode(): string
    {
        $last = static::withTrashed()
            ->where('code', 'like', 'KRY-%')
            ->orderByDesc('id')
            ->value('code');

        $num = 1;
        if ($last && preg_match('/KRY-(\d+)/i', $last, $m)) {
            $num = ((int) $m[1]) + 1;
        }

        do {
            $code = 'KRY-'.str_pad((string) $num, 4, '0', STR_PAD_LEFT);
            $exists = static::withTrashed()->where('code', $code)->exists();
            $num++;
        } while ($exists);

        return $code;
    }
}
