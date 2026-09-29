<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

final class UserSeeder extends Seeder
{
    public function run(): void
    {
        User::firstOrCreate(
            ['email' => 'trader@otcsignal.local'],
            [
                'name' => 'Quantitative Trader',
                'password' => Hash::make('password123'),
                'is_admin' => true,
            ]
        );
    }
}
