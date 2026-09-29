<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Services\SignalGenerationService;
use PHPUnit\Framework\TestCase;

final class SignalGenerationTest extends TestCase
{
    public function test_signal_generation_produces_structured_output(): void
    {
        $service = new SignalGenerationService();
        $signal = $service->generate('EUR/USD', 'mock', 'M1', 60);

        $this->assertArrayHasKey('asset', $signal);
        $this->assertArrayHasKey('direction', $signal);
        $this->assertArrayHasKey('confidence', $signal);
        $this->assertArrayHasKey('status', $signal);
        $this->assertArrayHasKey('market_regime', $signal);
        $this->assertArrayHasKey('data_quality', $signal);
        $this->assertArrayHasKey('factors', $signal);
        $this->assertArrayHasKey('reasons', $signal);
        $this->assertArrayHasKey('warnings', $signal);

        $this->assertContains($signal['direction'], ['CALL', 'PUT', 'NO_TRADE']);
        $this->assertGreaterThanOrEqual(0, $signal['confidence']);
        $this->assertLessThanOrEqual(100, $signal['confidence']);
    }

    public function test_no_trade_is_returned_when_quality_threshold_fails(): void
    {
        $service = new SignalGenerationService();
        $signal = $service->generate('EUR/USD', 'mock', 'M1', 60, [
            'min_data_quality' => 101, // Impossible quality threshold
        ]);

        $this->assertEquals('NO_TRADE', $signal['direction']);
        $this->assertEquals('NO_TRADE', $signal['status']);
        $this->assertNotEmpty($signal['rejection_reasons']);
    }
}
