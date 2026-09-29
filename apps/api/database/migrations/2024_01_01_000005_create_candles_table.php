<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('candles', function (Blueprint $table): void {
            $table->id();
            $table->string('asset_symbol', 20);  // Denormalised for query performance
            $table->string('broker_slug', 50);
            $table->string('timeframe', 10);     // M1, M5, M15, H1, H4, D1
            $table->timestamp('candle_time');
            $table->decimal('open', 15, 8);
            $table->decimal('high', 15, 8);
            $table->decimal('low', 15, 8);
            $table->decimal('close', 15, 8);
            $table->decimal('volume', 20, 2)->nullable();
            $table->boolean('is_closed')->default(true);
            $table->string('source', 50)->default('mock'); // mock, quotex, etc.
            $table->timestamps();
            $table->unique(['asset_symbol', 'broker_slug', 'timeframe', 'candle_time']);
            $table->index(['asset_symbol', 'timeframe', 'candle_time']);
            $table->index(['broker_slug', 'asset_symbol', 'timeframe']);
            $table->index('candle_time');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('candles');
    }
};
