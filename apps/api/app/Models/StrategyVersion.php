<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class StrategyVersion extends Model
{
    use HasFactory;

    protected $fillable = [
        'strategy_id',
        'version',
        'parameters',
        'is_current',
        'change_notes',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'parameters' => 'array',
            'is_current' => 'boolean',
        ];
    }

    public function strategy(): BelongsTo
    {
        return $this->belongsTo(Strategy::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function signals(): HasMany
    {
        return $this->hasMany(Signal::class);
    }

    public function scopeCurrent(Builder $query): Builder
    {
        return $query->where('is_current', true);
    }
}
