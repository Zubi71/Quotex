<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('strategies', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('class_name');
            $table->text('description');
            $table->boolean('is_active')->default(true);
            $table->integer('default_weight')->default(100); // relative weight in ensemble
            $table->timestamps();
        });

        Schema::create('strategy_versions', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('strategy_id')->constrained()->cascadeOnDelete();
            $table->string('version', 20);         // v1.0.0
            $table->json('parameters');             // full parameter snapshot
            $table->boolean('is_current')->default(false);
            $table->text('change_notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->index(['strategy_id', 'is_current']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('strategy_versions');
        Schema::dropIfExists('strategies');
    }
};
