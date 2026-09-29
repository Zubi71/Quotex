<?php

declare(strict_types=1);

namespace Tests\Unit\Indicators;

use App\Indicators\EMA;
use PHPUnit\Framework\TestCase;

final class EMATest extends TestCase
{
    public function test_ema_returns_correct_length_and_handles_insufficient_data(): void
    {
        $ema = new EMA(5);
        $candles = [
            ['close' => 10.0],
            ['close' => 11.0],
            ['close' => 12.0],
        ];

        $result = $ema->calculate($candles);
        $this->assertCount(3, $result);
        $this->assertNull($result[0]);
        $this->assertNull($result[1]);
        $this->assertNull($result[2]);
    }

    public function test_ema_calculates_correct_seeded_values(): void
    {
        $ema = new EMA(3);
        $candles = [
            ['close' => 10.0],
            ['close' => 12.0],
            ['close' => 14.0], // SMA(3) = 12.0
            ['close' => 16.0], // multiplier = 2/(3+1) = 0.5. EMA = 16*0.5 + 12*0.5 = 14.0
        ];

        $result = $ema->calculate($candles);
        $this->assertNull($result[0]);
        $this->assertNull($result[1]);
        $this->assertEquals(12.0, $result[2]);
        $this->assertEquals(14.0, $result[3]);
    }
}
