<?php

declare(strict_types=1);

namespace App\Indicators;

/**
 * Average Directional Index (ADX)
 *
 * +DM = current_high - prev_high  if > 0 and > |current_low - prev_low|, else 0
 * -DM = prev_low - current_low    if > 0 and > |current_high - prev_high|, else 0
 * TR  = max(High-Low, |High-PrevClose|, |Low-PrevClose|)
 *
 * Wilder smooth +DM, -DM, TR over period.
 * +DI = 100 * smoothed+DM / smoothedTR
 * -DI = 100 * smoothed-DM / smoothedTR
 * DX  = 100 * |+DI - -DI| / (+DI + -DI)
 * ADX = Wilder smooth of DX
 */
final class ADX implements IndicatorInterface
{
    public function __construct(private readonly int $period = 14) {}

    public function getName(): string
    {
        return "ADX({$this->period})";
    }

    /**
     * @param  array<int, array{open: float, high: float, low: float, close: float, volume: float, timestamp: int}> $candles
     * @return array{adx: float[]|null[], plus_di: float[]|null[], minus_di: float[]|null[]}
     */
    public function calculate(array $candles): array
    {
        $count  = count($candles);
        $adx    = array_fill(0, $count, null);
        $plusDi = array_fill(0, $count, null);
        $minusDi = array_fill(0, $count, null);

        if ($count < $this->period * 2 + 1) {
            return ['adx' => $adx, 'plus_di' => $plusDi, 'minus_di' => $minusDi];
        }

        // Step 1: Raw directional movements and TR for each bar
        $rawPlusDm  = [0.0];
        $rawMinusDm = [0.0];
        $rawTr      = [0.0];

        for ($i = 1; $i < $count; $i++) {
            $high      = (float) $candles[$i]['high'];
            $low       = (float) $candles[$i]['low'];
            $prevHigh  = (float) $candles[$i - 1]['high'];
            $prevLow   = (float) $candles[$i - 1]['low'];
            $prevClose = (float) $candles[$i - 1]['close'];

            $upMove   = $high - $prevHigh;
            $downMove = $prevLow - $low;

            $rawPlusDm[$i]  = ($upMove > 0.0 && $upMove > $downMove) ? $upMove : 0.0;
            $rawMinusDm[$i] = ($downMove > 0.0 && $downMove > $upMove) ? $downMove : 0.0;
            $rawTr[$i]      = max(
                $high - $low,
                abs($high - $prevClose),
                abs($low  - $prevClose),
            );
        }

        // Step 2: Seed Wilder smooth with SMA of first `period` values (indices 1..period)
        $smPlusDm  = array_sum(array_slice($rawPlusDm, 1, $this->period));
        $smMinusDm = array_sum(array_slice($rawMinusDm, 1, $this->period));
        $smTr      = array_sum(array_slice($rawTr, 1, $this->period));

        $dxValues = [];

        $computeDiAndDx = function (float $sPDM, float $sMDM, float $sTR) use (&$dxValues): array {
            $pDI = ($sTR > 1e-10) ? 100.0 * $sPDM / $sTR : 0.0;
            $mDI = ($sTR > 1e-10) ? 100.0 * $sMDM / $sTR : 0.0;
            $sum = $pDI + $mDI;
            $dx  = ($sum > 1e-10) ? 100.0 * abs($pDI - $mDI) / $sum : 0.0;
            $dxValues[] = $dx;
            return [$pDI, $mDI];
        };

        [$pDI, $mDI] = $computeDiAndDx($smPlusDm, $smMinusDm, $smTr);
        $plusDi[$this->period]  = $pDI;
        $minusDi[$this->period] = $mDI;

        // Step 3: Apply Wilder smooth for subsequent bars
        for ($i = $this->period + 1; $i < $count; $i++) {
            $smPlusDm  = $smPlusDm  - ($smPlusDm / $this->period)  + $rawPlusDm[$i];
            $smMinusDm = $smMinusDm - ($smMinusDm / $this->period) + $rawMinusDm[$i];
            $smTr      = $smTr      - ($smTr / $this->period)      + $rawTr[$i];

            [$pDI, $mDI] = $computeDiAndDx($smPlusDm, $smMinusDm, $smTr);
            $plusDi[$i]  = $pDI;
            $minusDi[$i] = $mDI;
        }

        // Step 4: Seed ADX = SMA of first `period` DX values
        if (count($dxValues) >= $this->period) {
            $adxSeed = array_sum(array_slice($dxValues, 0, $this->period)) / $this->period;
            $adxIdx  = $this->period + $this->period;
            $adx[$adxIdx] = $adxSeed;
            $prevAdx = $adxSeed;

            $dxOffset = $this->period; // dx starts at period
            for ($i = $adxIdx + 1; $i < $count; $i++) {
                $dxIndex = $i - $this->period;
                if ($dxIndex < count($dxValues)) {
                    $adxVal  = ($prevAdx * ($this->period - 1) + $dxValues[$dxIndex]) / $this->period;
                    $adx[$i] = $adxVal;
                    $prevAdx = $adxVal;
                }
            }
        }

        return [
            'adx'      => $adx,
            'plus_di'  => $plusDi,
            'minus_di' => $minusDi,
        ];
    }
}
