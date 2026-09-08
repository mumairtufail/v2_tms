<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Services\NotificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    public function __construct(
        protected NotificationService $notificationService
    ) {}

    public function index(Company $company): JsonResponse
    {
        $customer = Auth::guard('customer')->user();

        return response()->json(
            $this->notificationService->listForNotifiable($customer)
        );
    }

    public function markRead(Company $company, string $notification): JsonResponse
    {
        $customer = Auth::guard('customer')->user();
        $this->notificationService->markRead($customer, $notification);

        return response()->json(['success' => true]);
    }

    public function markAllRead(Company $company): JsonResponse
    {
        $customer = Auth::guard('customer')->user();
        $this->notificationService->markAllRead($customer);

        return response()->json(['success' => true]);
    }
}
