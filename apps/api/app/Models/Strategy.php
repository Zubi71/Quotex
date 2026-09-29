<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Strategy extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'class_name',
        'description',
        'is_active',
        'default_weight',
    ];

    protected function casts(): array
    {
        return [
            'is_active'      => 'boolean',
            'default_weight' => 'integer',
        ];
    }

    public function versions(): HasMany
    {
        return $this->hasMany(StrategyVersion::class);
    }

    public function currentVersion(): HasMany
    {
        return $this->hasMany(StrategyVersion::class)->where('is_current', true);
    }

    public function getCurrentVersion(): ?StrategyVersion
    {
        return $this->versions()->where('is_current', true)->latest()->first();
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }
}

