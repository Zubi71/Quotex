<?php

declare(strict_types=1);

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\BacktestController;
use App\Http\Controllers\Api\BrokerController;
use App\Http\Controllers\Api\HealthController;
use App\Http\Controllers\Api\MarketController;
use App\Http\Controllers\Api\PerformanceController;
use App\Http\Controllers\Api\SettingsController;
use App\Http\Controllers\Api\SignalController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

Route::prefix('v1')->group(function (): void {

    // ─── Authentication ───────────────────────────────────────────────────
    Route::post('/auth/login', [AuthController::class, 'login'])
        ->middleware('throttle:10,1')
        ->name('auth.login');

    Route::middleware('auth:sanctum')->group(function (): void {
        Route::post('/auth/logout', [AuthController::class, 'logout'])->name('auth.logout');
        Route::get('/auth/user', [AuthController::class, 'user'])->name('auth.user');
        Route::put('/settings', [SettingsController::class, 'update'])->name('settings.update');
        Route::get('/settings/audit', [SettingsController::class, 'audit'])->name('settings.audit');
    });

    // ─── Terminal & Market Routes (Protected by rate limiter) ───────────────
    Route::middleware(['throttle:120,1'])->group(function (): void {

        // Brokers
        Route::get('/brokers', [BrokerController::class, 'index'])->name('brokers.index');
        Route::get('/brokers/{broker}/assets', [BrokerController::class, 'assets'])->name('brokers.assets');

        // Market Data
        Route::prefix('/market/{asset}')->group(function (): void {
            Route::get('/candles', [MarketController::class, 'candles'])->name('market.candles');
            Route::get('/price', [MarketController::class, 'price'])->name('market.price');
            Route::get('/status', [MarketController::class, 'status'])->name('market.status');
            Route::get('/payout', [MarketController::class, 'payout'])->name('market.payout');
        });

        // Signals
        Route::post('/signals/generate', [SignalController::class, 'generate'])
            ->middleware('throttle:60,1')
            ->name('signals.generate');
        Route::get('/signals', [SignalController::class, 'index'])->name('signals.index');
        Route::get('/signals/{signal}', [SignalController::class, 'show'])->name('signals.show');

        // Performance
        Route::get('/performance', [PerformanceController::class, 'index'])->name('performance.index');
        Route::get('/performance/summary', [PerformanceController::class, 'summary'])->name('performance.summary');

        // Backtesting
        Route::post('/backtests', [BacktestController::class, 'store'])->name('backtests.store');
        Route::get('/backtests', [BacktestController::class, 'index'])->name('backtests.index');
        Route::get('/backtests/{backtest}', [BacktestController::class, 'show'])->name('backtests.show');
        Route::get('/backtests/{backtest}/results', [BacktestController::class, 'results'])->name('backtests.results');

        // System Health
        Route::get('/system/health', [HealthController::class, 'system'])->name('health.system');
        Route::get('/data/health', [HealthController::class, 'data'])->name('health.data');

        // Settings (Read)
        Route::get('/settings', [SettingsController::class, 'index'])->name('settings.index');
    });
});
