<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\GooglePlacesService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Throwable;

class GooglePlacesController extends Controller
{
    public function __construct(private readonly GooglePlacesService $googlePlacesService)
    {
    }

    public function autocomplete(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'input' => ['required', 'string', 'min:3'],
            'sessionToken' => ['nullable', 'string'],
        ]);

        try {
            $results = $this->googlePlacesService->autocomplete(
                $validated['input'],
                $validated['sessionToken'] ?? null
            );

            return response()->json([
                'success' => true,
                'data' => $results,
            ]);
        } catch (Throwable $exception) {
            report($exception);

            return response()->json([
                'success' => false,
                'message' => $exception->getMessage(),
            ], 500);
        }
    }

    public function details(Request $request, string $placeId): JsonResponse
    {
        $validated = $request->validate([
            'sessionToken' => ['nullable', 'string'],
        ]);

        try {
            $place = $this->googlePlacesService->details(
                $placeId,
                $validated['sessionToken'] ?? null
            );

            return response()->json([
                'success' => true,
                'data' => $place,
                'parsed' => $this->googlePlacesService->parseAddress($place),
            ]);
        } catch (Throwable $exception) {
            report($exception);

            return response()->json([
                'success' => false,
                'message' => $exception->getMessage(),
            ], 500);
        }
    }
}
