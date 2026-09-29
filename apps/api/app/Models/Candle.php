<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Candle extends Model
{
    use HasFactory;

    protected $fillable = [
        'asset_symbol',
        'broker_slug',
        'timeframe',
        'candle_time',
        'open',
        'high',
        'low',
        'close',
        'volume',
        'is_closed',
        'source',
    ];

    protected function casts(): array
    {
        return [
            'candle_time' => 'datetime',
            'open'        => 'decimal:8',
            'high'        => 'decimal:8',
            'low'         => 'decimal:8',
            'close'       => 'decimal:8',
            'volume'      => 'decimal:2',
            'is_closed'   => 'boolean',
        ];
    }

    // ─── Relationships ─────────────────────────────────────────────────────

    public function asset(): BelongsTo
    {
        return $this->belongsTo(Asset::class, 'asset_symbol', 'symbol');
    }

    // ─── Scopes ────────────────────────────────────────────────────────────

    public function scopeForAsset(Builder $query, string $symbol): Builder
    {
        return $query->where('asset_symbol', $symbol);
    }

    public function scopeForBroker(Builder $query, string $brokerSlug): Builder
    {
        return $query->where('broker_slug', $brokerSlug);
    }

    public function scopeForTimeframe(Builder $query, string $timeframe): Builder
    {
        return $query->where('timeframe', $timeframe);
    }

    public function scopeClosed(Builder $query): Builder
    {
        return $query->where('is_closed', true);
    }

    public function scopeInDateRange(Builder $query, string $from, string $to): Builder
    {
        return $query->whereBetween('candle_time', [$from, $to]);
    }

    public function scopeRecent(Builder $query, int $count): Builder
    {
        return $query->orderByDesc('candle_time')->limit($count);
    }

    public function scopeChronological(Builder $query): Builder
    {
        return $query->orderBy('candle_time');
    }

    // ─── Helpers ───────────────────────────────────────────────────────────

    public function toOhlcv(): array
    {
        return [
            'timestamp' => $this->candle_time->timestamp,
            'time'      => $this->candle_time->toIso8601String(),
            'open'      => (float) $this->open,
            'high'      => (float) $this->high,
            'low'       => (float) $this->low,
            'close'     => (float) $this->close,
            'volume'    => (float) $this->volume,
            'is_closed' => $this->is_closed,
        ];
    }

    public function bodySize(): float
    {
        return abs((float) $this->close - (float) $this->open);
    }

    public function totalRange(): float
    {
        return (float) $this->high - (float) $this->low;
    }

    public function isBullish(): bool
    {
        return (float) $this->close >= (float) $this->open;
    }

    public function isBearish(): bool
    {
        return (float) $this->close < (float) $this->open;
    }

    public function upperWick(): float
    {
        return (float) $this->high - max((float) $this->open, (float) $this->close);
    }

    public function lowerWick(): float
    {
        return min((float) $this->open, (float) $this->close) - (float) $this->low;
    }
}
