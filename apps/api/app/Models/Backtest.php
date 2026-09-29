<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Backtest extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'asset_symbol',
        'broker_slug',
        'timeframe',
        'expiry_seconds',
        'date_from',
        'date_to',
        'confidence_threshold',
        'initial_balance',
        'stake',
        'payout_rate',
        'status',
        'parameters',
        'results',
        'error_message',
        'started_at',
        'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'date_from'           => 'date',
            'date_to'             => 'date',
            'initial_balance'     => 'decimal:2',
            'stake'               => 'decimal:2',
            'payout_rate'         => 'decimal:2',
            'confidence_threshold' => 'integer',
            'expiry_seconds'      => 'integer',
            'parameters'          => 'array',
            'results'             => 'array',
            'started_at'          => 'datetime',
            'completed_at'        => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function backtestResults(): HasMany
    {
        return $this->hasMany(BacktestResult::class);
    }

    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    public function isFailed(): bool
    {
        return $this->status === 'failed';
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function markRunning(): void
    {
        $this->update(['status' => 'running', 'started_at' => now()]);
    }

    public function markCompleted(array $results): void
    {
        $this->update([
            'status'       => 'completed',
            'results'      => $results,
            'completed_at' => now(),
        ]);
    }

    public function markFailed(string $error): void
    {
        $this->update([
            'status'        => 'failed',
            'error_message' => $error,
            'completed_at'  => now(),
        ]);
    }
}
