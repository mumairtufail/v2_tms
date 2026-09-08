<?php

namespace App\Http\Controllers\V2;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Services\NotificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function __construct(
        protected NotificationService $notificationService
    ) {}

    public function index(Request $request, Company $company): JsonResponse
    {
        return response()->json(
            $this->notificationService->listForNotifiable($request->user())
        );
    }

    public function markRead(Request $request, Company $company, string $notification): JsonResponse
    {
        $this->notificationService->markRead($request->user(), $notification);

        return response()->json(['success' => true]);
    }

    public function markAllRead(Request $request, Company $company): JsonResponse
    {
        $this->notificationService->markAllRead($request->user());

        return response()->json(['success' => true]);
    }
}
