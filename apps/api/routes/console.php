<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Schedule;

// Resolve pending signal outcomes every 5 minutes
Schedule::job(new \App\Jobs\ResolveSignalOutcomesJob())->everyFiveMinutes()->name('resolve-signal-outcomes');

// Update candle history every minute
Schedule::job(new \App\Jobs\UpdateCandleHistoryJob())->everyMinute()->name('update-candle-history');
