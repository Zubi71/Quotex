<?php

declare(strict_types=1);

namespace Tests\Unit\Indicators;

use App\Indicators\RSI;
use PHPUnit\Framework\TestCase;

final class RSITest extends TestCase
{
    public function test_rsi_bounds_and_calculation(): void
    {
        $rsi = new RSI(14);
        $candles = [];
        for ($i = 0; $i < 30; $i++) {
            $candles[] = ['close' => 100.0 + ($i % 2 === 0 ? 1.0 : -0.5)];
        }

        $result = $rsi->calculate($candles);
        $this->assertCount(30, $result);
        
        $validValues = array_filter($result, fn($v) => $v !== null);
        foreach ($validValues as $val) {
            $this->assertGreaterThanOrEqual(0.0, $val);
            $this->assertLessThanOrEqual(100.0, $val);
        }
    }
}
