<?php

declare(strict_types=1);

namespace App\Services;

use App\Indicators\ADX;
use App\Indicators\ATR;
use App\Indicators\BollingerBands;
use App\Indicators\CCI;
use App\Indicators\EMA;
use App\Indicators\MACD;
use App\Indicators\ROC;
use App\Indicators\RSI;
use App\Indicators\SMA;
use App\Indicators\Stochastic;
use App\Indicators\WilliamsR;

final class IndicatorService
{
    /**
     * Compute full technical indicator snapshot for the provided closed candle array.
     *
     * @param array $candles
     * @return array
     */
    public function computeAll(array $candles): array
    {
        $ema9   = (new EMA(9))->calculate($candles);
        $ema20  = (new EMA(20))->calculate($candles);
        $ema50  = (new EMA(50))->calculate($candles);
        $ema100 = (new EMA(100))->calculate($candles);
        $ema200 = (new EMA(200))->calculate($candles);

        $sma20  = (new SMA(20))->calculate($candles);
        $sma50  = (new SMA(50))->calculate($candles);
        $sma100 = (new SMA(100))->calculate($candles);
        $sma200 = (new SMA(200))->calculate($candles);

        $rsi    = (new RSI(14))->calculate($candles);
        $macd   = (new MACD(12, 26, 9))->calculate($candles);
        $bb     = (new BollingerBands(20, 2.0))->calculate($candles);
        $atr    = (new ATR(14))->calculate($candles);
        $adx    = (new ADX(14))->calculate($candles);
        $stoch  = (new Stochastic(14, 3, 3))->calculate($candles);
        $cci    = (new CCI(20))->calculate($candles);
        $roc    = (new ROC(12))->calculate($candles);
        $williamsR = (new WilliamsR(14))->calculate($candles);

        return [
            'ema9' => $ema9,
            'ema20' => $ema20,
            'ema50' => $ema50,
            'ema100' => $ema100,
            'ema200' => $ema200,
            'sma20' => $sma20,
            'sma50' => $sma50,
            'sma100' => $sma100,
            'sma200' => $sma200,
            'rsi' => $rsi,
            'macd' => $macd,
            'bollinger' => $bb,
            'atr' => $atr,
            'adx' => $adx,
            'stochastic' => $stoch,
            'cci' => $cci,
            'roc' => $roc,
            'williams_r' => $williamsR,
        ];
    }
}
