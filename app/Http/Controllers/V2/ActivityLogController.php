<?php

namespace App\Http\Controllers\V2;

use App\Http\Controllers\Controller;
use App\Models\ActivityLogs;
use App\Models\Company;
use App\Services\ActivityLogListingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * A company's own trail.
 *
 * This is a business record, not a debugging tool: who did what, to which
 * record, and whether it worked. Stack traces, SQL, headers and internal
 * routing stay behind the super admin view, because they describe the platform
 * rather than the company's own activity.
 */
class ActivityLogController extends Controller
{
    public function __construct(
        protected ActivityLogListingService $listingService
    ) {}

    public function index(Request $request, Company $company): View
    {
        $privileged = $this->canSeeTechnical();

        return view('v2.activity-logs.index', [
            'activity_logs' => $this->listingService->paginate($request, $company->id, $privileged),
            'summary' => $this->listingService->summary($request, $company->id, $privileged),
            'company' => $company,
            'scope' => 'company',
            'technical' => $privileged,
            'filterRoute' => route('v2.activity-logs.index', ['company' => $company->slug]),
            'detailRouteName' => 'v2.activity-logs.show',
            'detailRouteParams' => ['company' => $company->slug],
        ]);
    }

    public function show(Company $company, ActivityLogs $log): JsonResponse
    {
        abort_unless($log->company_id === $company->id, 404);

        // Support access and platform errors are not part of a company's own
        // record, so they are not reachable through its endpoint either.
        abort_if(
            ! $this->canSeeTechnical()
                && ($log->category === 'impersonation' || in_array($log->severity, ['error', 'critical'], true)),
            404
        );

        return response()->json(
            $this->listingService->detail($log, includeTechnical: $this->canSeeTechnical())
        );
    }

    /**
     * A super admin browsing a company's logs keeps their diagnostic view;
     * everyone else in the company portal gets the business record only.
     */
    protected function canSeeTechnical(): bool
    {
        return (bool) auth()->user()?->is_super_admin;
    }
}
