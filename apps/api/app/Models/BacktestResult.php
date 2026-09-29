<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BacktestResult extends Model
{
    use HasFactory;

    protected $fillable = [
        'backtest_id',
        'candle_time',
        'direction',
        'confidence',
        'result',
        'entry_price',
        'close_price',
        'profit_loss',
        'balance_after',
        'indicator_snapshot',
        'reasons',
    ];

    protected function casts(): array
    {
        return [
            'candle_time'        => 'datetime',
            'entry_price'        => 'decimal:8',
            'close_price'        => 'decimal:8',
            'profit_loss'        => 'decimal:2',
            'balance_after'      => 'decimal:2',
            'confidence'         => 'integer',
            'indicator_snapshot' => 'array',
            'reasons'            => 'array',
        ];
    }

    public function backtest(): BelongsTo
    {
        return $this->belongsTo(Backtest::class);
    }

    public function isWin(): bool
    {
        return $this->result === 'WIN';
    }

    public function isLoss(): bool
    {
        return $this->result === 'LOSS';
    }
}
