<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

final class SettingSeeder extends Seeder
{
    public function run(): void
    {
        $settings = [
            ['key' => 'confidence_threshold', 'value' => '70', 'type' => 'integer', 'group' => 'signal_engine', 'label' => 'Confidence Threshold (0-100)'],
            ['key' => 'min_data_quality', 'value' => '80', 'type' => 'integer', 'group' => 'signal_engine', 'label' => 'Minimum Data Quality Score (0-100)'],
            ['key' => 'min_candle_history', 'value' => '200', 'type' => 'integer', 'group' => 'signal_engine', 'label' => 'Minimum Required Candles'],
            ['key' => 'max_signals_per_hour', 'value' => '10', 'type' => 'integer', 'group' => 'signal_engine', 'label' => 'Maximum Signals Per Hour'],
            ['key' => 'signal_cooldown_seconds', 'value' => '60', 'type' => 'integer', 'group' => 'signal_engine', 'label' => 'Cooldown Between Signals (Seconds)'],
            ['key' => 'min_payout', 'value' => '70', 'type' => 'float', 'group' => 'signal_engine', 'label' => 'Minimum Broker Payout Rate %'],
            ['key' => 'use_mock_data', 'value' => 'true', 'type' => 'boolean', 'group' => 'broker', 'label' => 'Use Mock Data Provider'],
            ['key' => 'enable_auto_scan', 'value' => 'false', 'type' => 'boolean', 'group' => 'scanner', 'label' => 'Enable Auto-Scan Engine'],
        ];

        foreach ($settings as $s) {
            Setting::firstOrCreate(['key' => $s['key']], $s);
        }
    }
}
