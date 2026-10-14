<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLogs;
use App\Services\ActivityLogListingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * The platform-wide trail. This audience is engineers and super admins, so it
 * carries the diagnostic payload the company-facing view deliberately omits.
 */
class ActivityLogController extends Controller
{
    public function __construct(
        protected ActivityLogListingService $listingService
    ) {}

    public function index(Request $request): View
    {
        return view('v2.activity-logs.index', [
            'activity_logs' => $this->listingService->paginate($request),
            'summary' => $this->listingService->summary($request),
            'company' => null,
            'scope' => 'global',
            'technical' => true,
            'filterRoute' => route('admin.logs'),
            'detailRouteName' => 'admin.logs.show',
            'detailRouteParams' => [],
        ]);
    }

    /**
     * The full record behind one row, including stack traces, failing SQL and
     * request headers.
     */
    public function show(ActivityLogs $log): JsonResponse
    {
        return response()->json(
            $this->listingService->detail($log, includeTechnical: true)
        );
    }
}
