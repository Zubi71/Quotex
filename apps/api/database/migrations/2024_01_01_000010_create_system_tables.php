<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('system_logs', function (Blueprint $table): void {
            $table->id();
            $table->string('level', 20);   // debug, info, warning, error, critical
            $table->string('channel', 50);
            $table->text('message');
            $table->json('context')->nullable();
            $table->timestamps();
            $table->index(['level', 'created_at']);
            $table->index('created_at');
        });

        Schema::create('data_health', function (Blueprint $table): void {
            $table->id();
            $table->string('asset_symbol', 20);
            $table->string('broker_slug', 50);
            $table->string('timeframe', 10);
            $table->integer('quality_score');   // 0-100
            $table->integer('candle_count');
            $table->integer('gap_count');
            $table->timestamp('latest_candle_time')->nullable();
            $table->decimal('latency_ms', 8, 2)->nullable();
            $table->boolean('is_fresh');
            $table->json('issues')->nullable();
            $table->timestamps();
            $table->index(['asset_symbol', 'timeframe', 'created_at']);
        });

        Schema::create('settings', function (Blueprint $table): void {
            $table->id();
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->string('type', 20)->default('string'); // string, integer, float, boolean, json
            $table->string('group', 50)->default('general');
            $table->string('label');
            $table->text('description')->nullable();
            $table->boolean('is_admin_only')->default(false);
            $table->timestamps();
        });

        Schema::create('audit_log', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('action', 50);
            $table->string('model_type')->nullable();
            $table->unsignedBigInteger('model_id')->nullable();
            $table->json('old_values')->nullable();
            $table->json('new_values')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent')->nullable();
            $table->timestamps();
            $table->index(['model_type', 'model_id']);
            $table->index(['user_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_log');
        Schema::dropIfExists('settings');
        Schema::dropIfExists('data_health');
        Schema::dropIfExists('system_logs');
    }
};
