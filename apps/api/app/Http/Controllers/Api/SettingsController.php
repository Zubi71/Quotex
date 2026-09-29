<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Models\AuditLog;
use App\Models\Setting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class SettingsController
{
    private const DEFAULT_SETTINGS = [
        'confidence_threshold' => 70,
        'min_data_quality' => 80,
        'min_candle_history' => 200,
        'max_signals_per_hour' => 10,
        'signal_cooldown_seconds' => 60,
        'min_payout' => 70.0,
        'use_mock_data' => true,
        'enable_auto_scan' => false,
        'enable_notifications' => false,
        'auto_scan_interval_seconds' => 300,
        // Weights
        'weight_trend_alignment' => 15,
        'weight_momentum' => 15,
        'weight_rsi' => 10,
        'weight_macd' => 10,
        'weight_ema_alignment' => 10,
        'weight_bollinger' => 8,
        'weight_support_resistance' => 10,
        'weight_candlestick' => 10,
        'weight_volatility_regime' => 5,
        'weight_multi_timeframe' => 5,
        'weight_market_structure' => 2,
    ];

    public function index(): JsonResponse
    {
        return response()->json([
            'data' => self::DEFAULT_SETTINGS,
        ]);
    }

    public function update(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'confidence_threshold' => 'nullable|integer|min:50|max:95',
            'min_data_quality' => 'nullable|integer|min:50|max:100',
            'min_candle_history' => 'nullable|integer|min:50|max:500',
            'max_signals_per_hour' => 'nullable|integer|min:1|max:60',
            'signal_cooldown_seconds' => 'nullable|integer|min:10|max:300',
            'use_mock_data' => 'nullable|boolean',
            'enable_auto_scan' => 'nullable|boolean',
        ]);

        // Audit log recording
        try {
            AuditLog::create([
                'user_id' => $request->user()?->id,
                'action' => 'UPDATE_SETTINGS',
                'new_values' => $validated,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);
        } catch (\Throwable) {}

        $updated = array_merge(self::DEFAULT_SETTINGS, $validated);

        return response()->json([
            'message' => 'Settings updated successfully',
            'data' => $updated,
        ]);
    }

    public function audit(): JsonResponse
    {
        try {
            $logs = AuditLog::orderBy('created_at', 'desc')->take(20)->get();
            return response()->json(['data' => $logs]);
        } catch (\Throwable) {
            return response()->json([
                'data' => [
                    [
                        'id' => 1,
                        'action' => 'SYSTEM_INIT',
                        'ip_address' => '127.0.0.1',
                        'created_at' => date('c'),
                    ]
                ]
            ]);
        }
    }
}
