<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Asset extends Model
{
    use HasFactory;

    protected $fillable = [
        'broker_id',
        'symbol',
        'display_name',
        'asset_type',
        'currency_pair',
        'is_otc',
        'is_active',
        'supported_timeframes',
        'supported_expiries',
    ];

    protected function casts(): array
    {
        return [
            'is_otc'                => 'boolean',
            'is_active'             => 'boolean',
            'supported_timeframes'  => 'array',
            'supported_expiries'    => 'array',
        ];
    }

    // ─── Relationships ─────────────────────────────────────────────────────

    public function broker(): BelongsTo
    {
        return $this->belongsTo(Broker::class);
    }

    public function candles(): HasMany
    {
        return $this->hasMany(Candle::class, 'asset_symbol', 'symbol');
    }

    public function signals(): HasMany
    {
        return $this->hasMany(Signal::class);
    }

    public function assetStatuses(): HasMany
    {
        return $this->hasMany(AssetStatus::class);
    }

    public function latestStatus(): HasMany
    {
        return $this->hasMany(AssetStatus::class)->latestOfMany();
    }

    // ─── Scopes ────────────────────────────────────────────────────────────

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeOtc(Builder $query): Builder
    {
        return $query->where('is_otc', true);
    }

    public function scopeForBroker(Builder $query, string $brokerSlug): Builder
    {
        return $query->whereHas('broker', fn(Builder $q) => $q->where('slug', $brokerSlug));
    }

    // ─── Helpers ───────────────────────────────────────────────────────────

    public function supportsTimeframe(string $timeframe): bool
    {
        return in_array($timeframe, $this->supported_timeframes ?? [], true);
    }

    public function supportsExpiry(int $seconds): bool
    {
        return in_array($seconds, $this->supported_expiries ?? [], true);
    }
}
