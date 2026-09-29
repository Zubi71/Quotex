<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('asset_status', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('asset_id')->constrained()->cascadeOnDelete();
            $table->boolean('is_available')->default(true);
            $table->decimal('payout', 5, 2)->nullable();      // percentage e.g. 80.00
            $table->decimal('spread', 10, 6)->nullable();
            $table->decimal('last_price', 15, 8)->nullable();
            $table->string('market_status', 20)->default('open'); // open, closed, suspended
            $table->timestamp('data_timestamp')->nullable();
            $table->timestamps();
            $table->index(['asset_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('asset_status');
    }
};
