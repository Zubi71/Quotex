<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('performance_snapshots', function (Blueprint $table): void {
            $table->id();
            $table->string('period', 20);             // all_time, 7_days, 30_days, today
            $table->string('asset_symbol', 20)->nullable(); // null = all assets
            $table->integer('total_signals');
            $table->integer('total_trades');
            $table->integer('wins');
            $table->integer('losses');
            $table->integer('void_trades');
            $table->integer('no_trades');
            $table->decimal('win_rate', 5, 2);
            $table->decimal('average_confidence', 5, 2);
            $table->decimal('profit_factor', 8, 4)->nullable();
            $table->decimal('max_drawdown', 8, 4)->nullable();
            $table->integer('longest_win_streak');
            $table->integer('longest_loss_streak');
            $table->decimal('expectancy', 8, 4)->nullable();
            $table->timestamp('calculated_at');
            $table->timestamps();
            $table->index(['period', 'asset_symbol', 'calculated_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('performance_snapshots');
    }
};
