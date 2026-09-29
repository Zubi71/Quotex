<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class Signal extends Model
{
    use HasFactory;

    /**
     * Columns that can be set on creation only.
     */
    protected $fillable = [
        'signal_uuid',
        'asset_id',
        'strategy_version_id',
        'asset_symbol',
        'broker_slug',
        'timeframe',
        'expiry_seconds',
        'direction',
        'confidence',
        'status',
        'market_regime',
        'data_quality_score',
        'entry_price',
        'signal_time',
        'candle_time',
        'expiry_time',
        'indicator_snapshot',
        'reasons',
        'warnings',
        'factors',
        'result',
        'close_price',
        'result_time',
        'is_mock',
    ];

    /**
     * Columns that may be updated post-creation (result lifecycle only).
     *
     * @var list<string>
     */
    protected static array $mutableAfterCreation = [
        'result',
        'close_price',
        'result_time',
    ];

    protected function casts(): array
    {
        return [
            'signal_time'        => 'datetime',
            'candle_time'        => 'datetime',
            'expiry_time'        => 'datetime',
            'result_time'        => 'datetime',
            'indicator_snapshot' => 'array',
            'reasons'            => 'array',
            'warnings'           => 'array',
            'factors'            => 'array',
            'entry_price'        => 'decimal:8',
            'close_price'        => 'decimal:8',
            'confidence'         => 'integer',
            'data_quality_score' => 'integer',
            'expiry_seconds'     => 'integer',
            'is_mock'            => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        // Auto-generate UUID before creation
        static::creating(function (Signal $signal): void {
            if (empty($signal->signal_uuid)) {
                $signal->signal_uuid = Str::uuid()->toString();
            }
            if (empty($signal->result)) {
                $signal->result = 'PENDING';
            }
        });

        // Guard immutable columns after creation
        static::updating(function (Signal $signal): void {
            $dirty = $signal->getDirty();
            $immutable = array_diff(
                array_keys($dirty),
                static::$mutableAfterCreation
            );
            if (! empty($immutable)) {
                foreach ($immutable as $col) {
                    // Silently revert immutable column changes
                    $signal->setAttribute($col, $signal->getOriginal($col));
                }
            }
        });
    }

    // ─── Relationships ─────────────────────────────────────────────────────

    public function asset(): BelongsTo
    {
        return $this->belongsTo(Asset::class);
    }

    public function strategyVersion(): BelongsTo
    {
        return $this->belongsTo(StrategyVersion::class);
    }

    // ─── Scopes ────────────────────────────────────────────────────────────

    public function scopePending(Builder $query): Builder
    {
        return $query->where('result', 'PENDING');
    }

    public function scopeResolved(Builder $query): Builder
    {
        return $query->whereNotIn('result', ['PENDING', null]);
    }

    public function scopeHighConfidence(Builder $query): Builder
    {
        return $query->where('status', 'HIGH_CONFIDENCE');
    }

    public function scopeForAsset(Builder $query, string $symbol): Builder
    {
        return $query->where('asset_symbol', $symbol);
    }

    public function scopeForBroker(Builder $query, string $brokerSlug): Builder
    {
        return $query->where('broker_slug', $brokerSlug);
    }

    public function scopeExpiredAndPending(Builder $query): Builder
    {
        return $query
            ->where('result', 'PENDING')
            ->where('direction', '!=', 'NO_TRADE')
            ->where('expiry_time', '<=', now());
    }

    // ─── Helpers ───────────────────────────────────────────────────────────

    public function isResolved(): bool
    {
        return ! in_array($this->result, ['PENDING', null], true);
    }

    public function isWin(): bool
    {
        return $this->result === 'WIN';
    }

    public function isLoss(): bool
    {
        return $this->result === 'LOSS';
    }

    public function markResult(string $result, float $closePrice): void
    {
        $this->update([
            'result'      => $result,
            'close_price' => $closePrice,
            'result_time' => now(),
        ]);
    }
}
