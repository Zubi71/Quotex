<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('brokers', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('adapter_class');
            $table->boolean('is_active')->default(true);
            $table->boolean('is_mock')->default(false);
            $table->json('config')->nullable();
            $table->timestamp('last_connected_at')->nullable();
            $table->string('connection_status', 20)->default('unknown'); // connected, disconnected, error
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('brokers');
    }
};
