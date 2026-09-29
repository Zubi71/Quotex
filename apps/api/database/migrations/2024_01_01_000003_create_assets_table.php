<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('assets', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('broker_id')->constrained()->cascadeOnDelete();
            $table->string('symbol', 30);           // EUR/USD
            $table->string('display_name', 60);     // EUR/USD (OTC)
            $table->string('asset_type', 20)->default('OTC'); // OTC, FOREX, CRYPTO
            $table->string('currency_pair', 10)->nullable();
            $table->boolean('is_otc')->default(true);
            $table->boolean('is_active')->default(true);
            $table->json('supported_timeframes')->nullable(); // ["M1","M5","M15","H1"]
            $table->json('supported_expiries')->nullable();   // [60, 120, 300]
            $table->timestamps();
            $table->unique(['broker_id', 'symbol']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('assets');
    }
};
