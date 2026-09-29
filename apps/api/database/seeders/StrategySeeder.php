<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Strategy;
use App\Models\StrategyVersion;
use Illuminate\Database\Seeder;

final class StrategySeeder extends Seeder
{
    public function run(): void
    {
        $strategies = [
            ['name' => 'Trend Following Confluence', 'slug' => 'trend_following', 'class_name' => 'App\\Strategies\\TrendFollowingStrategy', 'description' => 'EMA multi-tier stack and ADX trend directional analysis', 'weight' => 20],
            ['name' => 'Multi-Oscillator Momentum', 'slug' => 'momentum', 'class_name' => 'App\\Strategies\\MomentumStrategy', 'description' => 'RSI, MACD Histogram, and Stochastic oscillator alignment', 'weight' => 15],
            ['name' => 'Bollinger & Oscillator Mean Reversion', 'slug' => 'mean_reversion', 'class_name' => 'App\\Strategies\\MeanReversionStrategy', 'description' => 'Bollinger Band envelope extremes with oscillator exhaustion', 'weight' => 12],
            ['name' => 'Key Level Support & Resistance', 'slug' => 'support_resistance', 'class_name' => 'App\\Strategies\\SupportResistanceStrategy', 'description' => 'Clustered swing highs and lows horizontal reaction zones', 'weight' => 14],
            ['name' => 'Volatility Squeeze & Breakout', 'slug' => 'breakout', 'class_name' => 'App\\Strategies\\BreakoutStrategy', 'description' => 'Bollinger Band contraction followed by ATR expansion', 'weight' => 12],
            ['name' => 'Trend Pullback & Continuation', 'slug' => 'pullback', 'class_name' => 'App\\Strategies\\PullbackStrategy', 'description' => 'Dynamic EMA test in prevailing trend direction', 'weight' => 14],
            ['name' => 'Candlestick Pattern Confirmation', 'slug' => 'candlestick_confirmation', 'class_name' => 'App\\Strategies\\CandlestickConfirmationStrategy', 'description' => '15 classic price action reversal and continuation patterns', 'weight' => 12],
            ['name' => 'Multi-Timeframe Trend Confluence', 'slug' => 'mtf_confluence', 'class_name' => 'App\\Strategies\\MultiTimeframeConfluenceStrategy', 'description' => 'Higher timeframe directional alignment confirmation', 'weight' => 15],
        ];

        foreach ($strategies as $s) {
            $strategy = Strategy::firstOrCreate(
                ['slug' => $s['slug']],
                [
                    'name' => $s['name'],
                    'class_name' => $s['class_name'],
                    'description' => $s['description'],
                    'default_weight' => $s['weight'],
                    'is_active' => true,
                ]
            );

            StrategyVersion::firstOrCreate(
                ['strategy_id' => $strategy->id, 'version' => 'v1.0.0'],
                [
                    'parameters' => ['default_weight' => $s['weight']],
                    'is_current' => true,
                    'change_notes' => 'Initial release v1.0.0',
                ]
            );
        }
    }
}
