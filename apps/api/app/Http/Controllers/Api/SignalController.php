<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Models\Signal;
use App\Services\SignalGenerationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class SignalController
{
    private SignalGenerationService $signalService;

    public function __construct()
    {
        $this->signalService = new SignalGenerationService();
    }

    public function generate(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'asset' => 'required|string',
            'broker' => 'nullable|string',
            'timeframe' => 'nullable|string',
            'expiry_seconds' => 'nullable|integer',
            'confidence_threshold' => 'nullable|integer',
        ]);

        $asset = $validated['asset'];
        $broker = $validated['broker'] ?? 'mock';
        $timeframe = $validated['timeframe'] ?? 'M1';
        $expiry = (int)($validated['expiry_seconds'] ?? 60);

        $configOverrides = [];
        if (isset($validated['confidence_threshold'])) {
            $configOverrides['confidence_threshold'] = (int)$validated['confidence_threshold'];
        }

        $signal = $this->signalService->generate($asset, $broker, $timeframe, $expiry, $configOverrides);

        return response()->json([
            'status' => 'success',
            'data' => $signal,
        ]);
    }

    public function index(Request $request): JsonResponse
    {
        $query = Signal::query()->orderBy('signal_time', 'desc');

        if ($request->has('asset') && $request->asset !== '') {
            $query->where('asset_symbol', $request->asset);
        }
        if ($request->has('direction') && $request->direction !== '') {
            $query->where('direction', $request->direction);
        }
        if ($request->has('result') && $request->result !== '') {
            $query->where('result', $request->result);
        }
        if ($request->has('min_confidence')) {
            $query->where('confidence', '>=', (int)$request->min_confidence);
        }

        $perPage = (int)$request->query('per_page', 25);
        $signals = $query->paginate($perPage);

        return response()->json($signals);
    }

    public function show(int $id): JsonResponse
    {
        $signal = Signal::findOrFail($id);

        return response()->json(['data' => $signal]);
    }
}
