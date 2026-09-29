<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PerformanceSnapshot extends Model
{
    use HasFactory;

    protected $fillable = [
        'period',
        'asset_symbol',
        'total_signals',
        'total_trades',
        'wins',
        'losses',
        'void_trades',
        'no_trades',
        'win_rate',
        'average_confidence',
        'profit_factor',
        'max_drawdown',
        'longest_win_streak',
        'longest_loss_streak',
        'expectancy',
        'calculated_at',
    ];

    protected function casts(): array
    {
        return [
            'win_rate'           => 'decimal:2',
            'average_confidence' => 'decimal:2',
            'profit_factor'      => 'decimal:4',
            'max_drawdown'       => 'decimal:4',
            'expectancy'         => 'decimal:4',
            'calculated_at'      => 'datetime',
            'total_signals'      => 'integer',
            'total_trades'       => 'integer',
            'wins'               => 'integer',
            'losses'             => 'integer',
            'void_trades'        => 'integer',
            'no_trades'          => 'integer',
            'longest_win_streak' => 'integer',
            'longest_loss_streak' => 'integer',
        ];
    }

    public function scopeForPeriod(Builder $query, string $period): Builder
    {
        return $query->where('period', $period);
    }

    public function scopeForAsset(Builder $query, ?string $symbol): Builder
    {
        return $symbol
            ? $query->where('asset_symbol', $symbol)
            : $query->whereNull('asset_symbol');
    }

    public function scopeLatest(Builder $query): Builder
    {
        return $query->orderByDesc('calculated_at');
    }
}
