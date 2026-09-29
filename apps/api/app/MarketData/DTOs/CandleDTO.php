<?php

declare(strict_types=1);

namespace App\MarketData\DTOs;

/**
 * Immutable candle data transfer object.
 * Represents a single OHLCV candle from any broker.
 */
final readonly class CandleDTO
{
    public function __construct(
        public string $symbol,
        public string $timeframe,
        public int    $timestamp,   // Unix seconds — start of candle period
        public float  $open,
        public float  $high,
        public float  $low,
        public float  $close,
        public float  $volume,
        public bool   $isClosed,    // Only closed candles should be used for signals
        public string $source,      // 'mock', 'quotex', etc.
    ) {}

    public function toArray(): array
    {
        return [
            'symbol'    => $this->symbol,
            'timeframe' => $this->timeframe,
            'timestamp' => $this->timestamp,
            'open'      => $this->open,
            'high'      => $this->high,
            'low'       => $this->low,
            'close'     => $this->close,
            'volume'    => $this->volume,
            'is_closed' => $this->isClosed,
            'source'    => $this->source,
        ];
    }

    /**
     * Body size (absolute difference between open and close).
     */
    public function bodySize(): float
    {
        return abs($this->close - $this->open);
    }

    /**
     * Total candle range (high to low).
     */
    public function range(): float
    {
        return $this->high - $this->low;
    }

    /**
     * True if this is a bullish (green) candle.
     */
    public function isBullish(): bool
    {
        return $this->close >= $this->open;
    }

    /**
     * Upper wick size.
     */
    public function upperWick(): float
    {
        return $this->high - max($this->open, $this->close);
    }

    /**
     * Lower wick size.
     */
    public function lowerWick(): float
    {
        return min($this->open, $this->close) - $this->low;
    }

    /**
     * Body as a fraction of total range (0–1).
     */
    public function bodyRatio(): float
    {
        if ($this->range() < PHP_FLOAT_EPSILON) {
            return 0.0;
        }
        return $this->bodySize() / $this->range();
    }

    public static function fromArray(array $data): self
    {
        return new self(
            symbol:    $data['symbol'],
            timeframe: $data['timeframe'],
            timestamp: (int) $data['timestamp'],
            open:      (float) $data['open'],
            high:      (float) $data['high'],
            low:       (float) $data['low'],
            close:     (float) $data['close'],
            volume:    (float) ($data['volume'] ?? 0.0),
            isClosed:  (bool) ($data['is_closed'] ?? true),
            source:    $data['source'] ?? 'unknown',
        );
    }
}
