<?php

namespace App\Http\Controllers\Api\Driver;

use App\Http\Controllers\Controller;
use App\Services\NotificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function __construct(
        protected NotificationService $notificationService
    ) {}

    public function index(Request $request): JsonResponse
    {
        return response()->json(
            $this->notificationService->listForNotifiable($request->user())
        );
    }

    public function markRead(Request $request, string $notification): JsonResponse
    {
        $this->notificationService->markRead($request->user(), $notification);

        return response()->json(['success' => true]);
    }

    public function markAllRead(Request $request): JsonResponse
    {
        $this->notificationService->markAllRead($request->user());

        return response()->json(['success' => true]);
    }
}
