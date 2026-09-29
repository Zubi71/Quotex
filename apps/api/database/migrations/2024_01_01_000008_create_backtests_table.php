<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('backtests', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('asset_symbol', 20);
            $table->string('broker_slug', 50);
            $table->string('timeframe', 10);
            $table->integer('expiry_seconds');
            $table->date('date_from');
            $table->date('date_to');
            $table->integer('confidence_threshold');
            $table->decimal('initial_balance', 15, 2)->default(1000.00);
            $table->decimal('stake', 15, 2)->default(10.00);
            $table->decimal('payout_rate', 5, 2)->default(80.00); // percentage
            $table->string('status', 20)->default('pending');      // pending, running, completed, failed
            $table->json('parameters')->nullable();                 // strategy parameters used
            $table->json('results')->nullable();                    // Aggregated results
            $table->string('error_message')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
            $table->index(['user_id', 'status']);
        });

        Schema::create('backtest_results', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('backtest_id')->constrained()->cascadeOnDelete();
            $table->timestamp('candle_time');
            $table->string('direction', 10);   // CALL, PUT, NO_TRADE
            $table->integer('confidence');
            $table->string('result', 20)->nullable(); // WIN, LOSS, VOID, NO_TRADE
            $table->decimal('entry_price', 15, 8);
            $table->decimal('close_price', 15, 8)->nullable();
            $table->decimal('profit_loss', 15, 2)->default(0);
            $table->decimal('balance_after', 15, 2);
            $table->json('indicator_snapshot')->nullable();
            $table->json('reasons')->nullable();
            $table->timestamps();
            $table->index(['backtest_id', 'candle_time']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('backtest_results');
        Schema::dropIfExists('backtests');
    }
};
