<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class JobPosition extends Model
{
    protected $fillable = ['name', 'slug', 'description', 'is_active'];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function (JobPosition $model) {
            $model->slug = $model->slug ?: static::uniqueSlug($model->name);
        });

        static::updating(function (JobPosition $model) {
            if ($model->isDirty('name')) {
                $model->slug = static::uniqueSlug($model->name, $model->id);
            }
        });

        static::updated(function (JobPosition $model) {
            if ($model->wasChanged('name')) {
                $model->employees()->update(['position' => $model->name]);
            }
        });
    }

    public function employees()
    {
        return $this->hasMany(Employee::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public static function uniqueSlug(string $name, ?int $ignoreId = null): string
    {
        $base = Str::slug($name) ?: 'jabatan';
        $slug = $base;
        $i = 1;

        while (
            static::query()
                ->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))
                ->where('slug', $slug)
                ->exists()
        ) {
            $slug = $base.'-'.$i;
            $i++;
        }

        return $slug;
    }
}
