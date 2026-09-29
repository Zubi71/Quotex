<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

final class AuthController
{
    public function login(Request $request): JsonResponse
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            // For demo/development convenience, if default credentials are used, return user or create
            if ($request->email === 'trader@otcsignal.local' && $request->password === 'password123') {
                $user = User::firstOrCreate(
                    ['email' => 'trader@otcsignal.local'],
                    [
                        'name' => 'Quantitative Trader',
                        'password' => Hash::make('password123'),
                        'is_admin' => true,
                    ]
                );
            } else {
                throw ValidationException::withMessages([
                    'email' => ['The provided credentials do not match our records.'],
                ]);
            }
        }

        $token = $user->createToken('auth-token')->plainTextToken;

        return response()->json([
            'token' => $token,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'is_admin' => (bool)$user->is_admin,
            ],
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()?->currentAccessToken()?->delete();

        return response()->json(['message' => 'Logged out successfully']);
    }

    public function user(Request $request): JsonResponse
    {
        $user = $request->user();

        return response()->json([
            'user' => [
                'id' => $user?->id ?? 1,
                'name' => $user?->name ?? 'Quantitative Trader',
                'email' => $user?->email ?? 'trader@otcsignal.local',
                'is_admin' => (bool)($user?->is_admin ?? true),
            ],
        ]);
    }
}
