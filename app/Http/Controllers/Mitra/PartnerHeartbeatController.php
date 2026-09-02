<?php

namespace App\Http\Controllers\Mitra;

use App\Http\Controllers\Controller;
use App\Services\PartnerOnlineService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PartnerHeartbeatController extends Controller
{
    /**
     * Handle lightweight background GPS & liveness heartbeat for Mitra.
     * Bypasses Livewire component lifecycle and DOM serialization entirely.
     */
    public function __invoke(Request $request, PartnerOnlineService $service): JsonResponse
    {
        $validated = $request->validate([
            'latitude'  => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
        ]);

        $user = $request->user();

        if (!$user) {
            return response()->json(['error' => 'Unauthenticated'], 401);
        }

        $lat = isset($validated['latitude']) ? (float) $validated['latitude'] : null;
        $lng = isset($validated['longitude']) ? (float) $validated['longitude'] : null;

        $state = $service->heartbeat($user, $lat, $lng);

        return response()->json([
            'status'          => 'ok',
            'matching_status' => $state->matching_status,
            'server_time'     => now()->toIso8601String(),
        ]);
    }
}
