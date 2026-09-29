<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Broker;
use Illuminate\Database\Seeder;

final class BrokerSeeder extends Seeder
{
    public function run(): void
    {
        Broker::firstOrCreate(
            ['slug' => 'mock'],
            [
                'name' => 'Mock Broker (Demo)',
                'adapter_class' => 'App\\MarketData\\Adapters\\MockBrokerAdapter',
                'is_active' => true,
                'is_mock' => true,
                'connection_status' => 'connected',
            ]
        );

        Broker::firstOrCreate(
            ['slug' => 'quotex'],
            [
                'name' => 'Quotex',
                'adapter_class' => 'App\\MarketData\\Adapters\\QuotexAdapter',
                'is_active' => false,
                'is_mock' => false,
                'connection_status' => 'disconnected',
            ]
        );
    }
}
