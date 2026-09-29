<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('signals', function (Blueprint $table): void {
            $table->id();
            $table->uuid('signal_uuid')->unique();
            $table->foreignId('asset_id')->constrained();
            $table->foreignId('strategy_version_id')->constrained();
            $table->string('asset_symbol', 20);
            $table->string('broker_slug', 50);
            $table->string('timeframe', 10);
            $table->integer('expiry_seconds');
            $table->string('direction', 10);   // CALL, PUT, NO_TRADE
            $table->integer('confidence');     // 0-100
            $table->string('status', 30);      // HIGH_CONFIDENCE, MODERATE, WEAK, NO_TRADE
            $table->string('market_regime', 30);
            $table->integer('data_quality_score'); // 0-100
            $table->decimal('entry_price', 15, 8);
            $table->timestamp('signal_time');
            $table->timestamp('candle_time');
            $table->timestamp('expiry_time')->nullable();
            $table->json('indicator_snapshot');  // Full indicator state at signal time
            $table->json('reasons');             // Array of reason strings
            $table->json('warnings');            // Array of warning strings
            $table->json('factors');             // Array of {name, score, weight, contribution}
            $table->string('result', 20)->nullable(); // WIN, LOSS, VOID, PENDING
            $table->decimal('close_price', 15, 8)->nullable();
            $table->timestamp('result_time')->nullable();
            $table->boolean('is_mock')->default(false);
            $table->timestamps();
            $table->index(['asset_symbol', 'signal_time']);
            $table->index(['direction', 'confidence']);
            $table->index(['result', 'signal_time']);
            $table->index('signal_time');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('signals');
    }
};
