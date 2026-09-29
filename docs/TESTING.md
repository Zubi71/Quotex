# OTC Signal Intelligence — Testing & Verification Guide

## 1. Automated Test Suite Structure

The testing suite verifies all mathematical calculations, strategy rules, signal immutability, and zero look-ahead bias:

```
tests/
  Unit/
    Indicators/
      EMATest.php                   # Formula correctness & smoothing verification
      RSITest.php                   # Bounds testing (0-100) and Wilder method
    SignalEngine/
      FutureCandleLeakageTest.php   # Non-repainting & zero look-ahead verification
      SignalImmutabilityTest.php    # Verification that past signals cannot be altered
  Feature/
    SignalGenerationTest.php        # End-to-end signal generation & NO_TRADE enforcement
```

---

## 2. Running Automated Tests

### Backend Unit & Feature Tests (PHPUnit)
```bash
# Inside docker container or local environment
cd apps/api
vendor/bin/phpunit
```
Or via Artisan:
```bash
php artisan test
```

### TypeScript Type Checking & Linting (Frontend)
```bash
cd apps/web
npm run type-check
npm run lint
```

---

## 3. Critical Verification: Future Candle Leakage Test

Located in `tests/Unit/SignalEngine/FutureCandleLeakageTest.php`:

1. Generates 200 chronological closed candles and calculates a signal at index 199.
2. Appends 100 new candles exhibiting an extreme 20% market crash.
3. Re-evaluates the signal at index 199 using the historical slice.
4. Asserts that the regenerated signal direction, confidence score, and status are **bit-for-bit identical**.
5. Confirms that no future candle data can retroactively repaint historical trading signals.
