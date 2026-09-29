<?php

declare(strict_types=1);

namespace App\MarketData\DTOs;

/**
 * Immutable asset data transfer object.
 */
final readonly class AssetDTO
{
    public function __construct(
        public string  $symbol,           // e.g., 'EUR/USD'
        public string  $displayName,      // e.g., 'EUR/USD (OTC)'
        public string  $assetType,        // 'OTC', 'FOREX', 'CRYPTO'
        public bool    $isOtc,
        public bool    $isActive,
        public array   $supportedTimeframes, // ['M1', 'M5', 'M15', 'H1']
        public array   $supportedExpiries,   // [60, 120, 180, 300] seconds
        public ?float  $payout,           // Current payout percentage
        public string  $marketStatus,     // 'open', 'closed', 'suspended'
        public string  $source,
    ) {}

    public function toArray(): array
    {
        return [
            'symbol'               => $this->symbol,
            'display_name'         => $this->displayName,
            'asset_type'           => $this->assetType,
            'is_otc'               => $this->isOtc,
            'is_active'            => $this->isActive,
            'supported_timeframes' => $this->supportedTimeframes,
            'supported_expiries'   => $this->supportedExpiries,
            'payout'               => $this->payout,
            'market_status'        => $this->marketStatus,
            'source'               => $this->source,
        ];
    }
}
