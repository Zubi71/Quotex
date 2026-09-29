# OTC Signal Intelligence — Signal Engine Methodology

## 1. Core Quantitative Philosophy

The OTC Signal Intelligence platform operates on a single non-negotiable rule:  
**Quality of signal is more important than quantity of signals.**

Traditional retail signal bots generate an endless stream of 50-50 coin-flip signals, forcing a CALL or PUT on every candle. In contrast, this engine acts as a **rejection filter**. It evaluates multiple uncorrelated technical factors and actively rejects trades unless a statistically meaningful confluence exists.

---

## 2. The Multi-Tier Signal Pipeline

Every signal generation request executes strictly through seven sequential phases:

```
[1. Market & Connection Validation]
            ↓
[2. Closed-Candle Ingestion & Quality Gate]
            ↓
[3. Multi-Period Technical Indicator Matrix]
            ↓
[4. Market Regime & Volatility Classification]
            ↓
[5. Modular Strategy Ensemble Evaluation]
            ↓
[6. Weighted Multi-Factor Confidence Engine]
            ↓
[7. Directional Gate: CALL / PUT / NO TRADE]
```

### Critical Non-Repainting Rule
- Signals are calculated **strictly from CLOSED candles**.
- The currently forming candle is **never used as confirmed historical input**.
- Slices passed to indicators never include forward bars ($T+1 \dots N$).

---

## 3. Market Regime Engine

The engine identifies one of six distinct statistical market regimes:

| Regime | Indicators Used | Strategy Reaction |
|---|---|---|
| `TRENDING_BULLISH` | ADX $\ge 24$, Close $>$ EMA20 $>$ EMA50, $+DI > -DI$ | Boosts Trend Following & Pullback weights (+30%) |
| `TRENDING_BEARISH` | ADX $\ge 24$, Close $<$ EMA20 $<$ EMA50, $-DI > +DI$ | Boosts Trend Following & Pullback weights (+30%) |
| `RANGING` | ADX $< 20$, Compressed Bollinger Bandwidth | Boosts Mean Reversion & Support/Resistance (+30%) |
| `HIGH_VOLATILITY` | Current ATR $> 2.2 \times$ 20-period Average ATR | Penalizes confidence score by 15%, adds slippage risk warning |
| `LOW_VOLATILITY` | Current ATR $< 0.45 \times$ 20-period Average ATR | Flags tight price compression warning |
| `UNCERTAIN` | Entangled EMAs, conflicting oscillator directions | **IMMEDIATE NO TRADE REJECTION** |

---

## 4. Technical Indicators Implemented

All calculations are pure mathematical implementations adhering strictly to textbook quant definitions:

1. **Exponential Moving Average (EMA)**: 9, 20, 50, 100, 200 periods (seeded by true SMA, smoothed with $\alpha = \frac{2}{N+1}$).
2. **Simple Moving Average (SMA)**: 20, 50, 100, 200 periods.
3. **Relative Strength Index (RSI)**: 14 periods using Wilder smoothing method.
4. **Moving Average Convergence Divergence (MACD)**: 12 fast, 26 slow, 9 signal with directional histogram analysis.
5. **Bollinger Bands**: 20 periods, 2 standard deviations, calculating %B and Bandwidth squeeze.
6. **Average True Range (ATR)**: 14 periods using Wilder smoothing for volatility normalization.
7. **Average Directional Index (ADX)**: 14 periods computing $+DI$, $-DI$, and directional trend strength.
8. **Stochastic Oscillator**: %K 14, %D 3, Slowing 3.
9. **Commodity Channel Index (CCI)**: 20 periods tracking mean deviation extremes.
10. **Rate of Change (ROC)**: 12 periods tracking velocity of price expansion.
11. **Williams %R**: 14 periods overbought/oversold boundaries (-20 / -80).
12. **Price Action Detector**: 15 distinct candlestick patterns and liquidity sweep structures.

---

## 5. Strategy Modules & Default Weights

Each strategy operates as an independent module implementing `StrategyInterface`:

| Strategy | Default Weight | Key Confirmation Criteria |
|---|---|---|
| **Trend Following** | 20% | Stacked EMAs, positive slope, ADX $> 25$ |
| **Multi-Oscillator Momentum** | 15% | RSI in momentum band, MACD histogram expanding |
| **Mean Reversion** | 12% | Bollinger Band %B $< 0.05$ or $> 0.95$, CCI extreme |
| **Support & Resistance** | 14% | Price reaction within 0.12% of clustered swing levels |
| **Volatility Breakout** | 12% | Bandwidth squeeze followed by confirmed breakout |
| **Trend Pullback** | 14% | 20 EMA dynamic test in prevailing trend direction |
| **Candlestick Confirmation** | 12% | Bullish/Bearish Engulfing, Hammer, Rejection Pin |
| **Higher-Timeframe Confluence** | 15% | M5/M15/H1 trend alignment with M1 execution bar |

---

## 6. Confidence Scoring Formula

The final confidence percentage $C$ is computed as:

$$C = \left( \frac{\sum_{i=1}^M W_i \cdot S_i}{\sum_{i=1}^M W_i} \right) - P_{\text{contradiction}} - P_{\text{quality}}$$

Where:
- $W_i$: Dynamically adjusted strategy weight based on market regime
- $S_i$: Strategy score ($0 \dots 100$)
- $P_{\text{contradiction}}$: Penalty applied if opposing directional momentum is detected
- $P_{\text{quality}}$: Penalty applied if candle continuity or freshness is sub-optimal

---

## 7. The NO TRADE State

A signal is **REJECTED** and output as `NO_TRADE` whenever:
- Final Confidence $C < \text{Configured Threshold}$ (default 70%)
- Market Regime is classified as `UNCERTAIN`
- Bullish and Bearish confluence votes are within 10% of each other (indecision)
- Data Quality Score $< 80\%$
- Asset payout rate $< 70\%$
- Candle history $< 200$ closed bars
