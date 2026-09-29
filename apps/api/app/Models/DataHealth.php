<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DataHealth extends Model
{
    use HasFactory;

    protected $table = 'data_health';

    protected $fillable = [
        'asset_symbol',
        'broker_slug',
        'timeframe',
        'quality_score',
        'candle_count',
        'gap_count',
        'latest_candle_time',
        'latency_ms',
        'is_fresh',
        'issues',
    ];

    protected function casts(): array
    {
        return [
            'latest_candle_time' => 'datetime',
            'quality_score'      => 'integer',
            'candle_count'       => 'integer',
            'gap_count'          => 'integer',
            'latency_ms'         => 'decimal:2',
            'is_fresh'           => 'boolean',
            'issues'             => 'array',
        ];
    }

    public function scopeForAsset(Builder $query, string $symbol, string $broker, string $timeframe): Builder
    {
        return $query
            ->where('asset_symbol', $symbol)
            ->where('broker_slug', $broker)
            ->where('timeframe', $timeframe);
    }

    public function scopeFresh(Builder $query): Builder
    {
        return $query->where('is_fresh', true);
    }

    public function scopeHealthy(Builder $query, int $minScore = 60): Builder
    {
        return $query->where('quality_score', '>=', $minScore);
    }
}
