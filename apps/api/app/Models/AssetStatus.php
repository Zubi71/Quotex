<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AssetStatus extends Model
{
    use HasFactory;

    protected $table = 'asset_status';

    protected $fillable = [
        'asset_id',
        'is_available',
        'payout',
        'spread',
        'last_price',
        'market_status',
        'data_timestamp',
    ];

    protected function casts(): array
    {
        return [
            'is_available'   => 'boolean',
            'payout'         => 'decimal:2',
            'spread'         => 'decimal:6',
            'last_price'     => 'decimal:8',
            'data_timestamp' => 'datetime',
        ];
    }

    public function asset(): BelongsTo
    {
        return $this->belongsTo(Asset::class);
    }

    public function isMarketOpen(): bool
    {
        return $this->market_status === 'open' && $this->is_available;
    }
}
