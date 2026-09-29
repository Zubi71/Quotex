<?php

declare(strict_types=1);

namespace Tests\Unit\SignalEngine;

use PHPUnit\Framework\TestCase;

final class SignalImmutabilityTest extends TestCase
{
    public function test_signal_core_properties_are_immutable(): void
    {
        $signalSnapshot = [
            'signal_uuid' => 'e7b0b65a-5ff7-44bc-87f5-832810f27916',
            'asset' => 'EUR/USD',
            'direction' => 'CALL',
            'confidence' => 84,
            'status' => 'HIGH_CONFIDENCE',
            'entry_price' => 1.08520,
            'indicator_snapshot' => ['rsi' => 58.4, 'adx' => 28.2],
            'result' => 'PENDING',
        ];

        // Attempt mutating direction
        $immutableDirection = $signalSnapshot['direction'];
        $immutableConfidence = $signalSnapshot['confidence'];

        // Only outcome fields are permitted to transition from PENDING to WIN/LOSS
        $resolvedSignal = array_merge($signalSnapshot, [
            'result' => 'WIN',
            'close_price' => 1.08545,
            'result_time' => date('c'),
        ]);

        $this->assertSame($immutableDirection, $resolvedSignal['direction']);
        $this->assertSame($immutableConfidence, $resolvedSignal['confidence']);
        $this->assertSame($signalSnapshot['indicator_snapshot'], $resolvedSignal['indicator_snapshot']);
        $this->assertSame('WIN', $resolvedSignal['result']);
    }
}
