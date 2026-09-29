<?php

declare(strict_types=1);

namespace App\MarketData\DTOs;

/**
 * Immutable price data transfer object.
 */
final readonly class PriceDTO
{
    public function __construct(
        public string $symbol,
        public float  $bid,
        public float  $ask,
        public float  $mid,         // (bid + ask) / 2
        public int    $timestamp,   // Unix seconds
        public string $source,
    ) {}

    public function spread(): float
    {
        return $this->ask - $this->bid;
    }

    public function toArray(): array
    {
        return [
            'symbol'    => $this->symbol,
            'bid'       => $this->bid,
            'ask'       => $this->ask,
            'mid'       => $this->mid,
            'spread'    => $this->spread(),
            'timestamp' => $this->timestamp,
            'source'    => $this->source,
        ];
    }
}
