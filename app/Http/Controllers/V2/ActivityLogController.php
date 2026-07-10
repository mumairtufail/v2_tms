<?php

namespace App\Http\Controllers\V2;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Services\ActivityLogListingService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ActivityLogController extends Controller
{
    public function __construct(
        protected ActivityLogListingService $listingService
    ) {}

    public function index(Request $request, Company $company): View
    {
        $activity_logs = $this->listingService->paginate($request, $company->id);

        return view('v2.activity-logs.index', [
            'activity_logs' => $activity_logs,
            'company' => $company,
            'scope' => 'company',
            'filterRoute' => route('v2.activity-logs.index', ['company' => $company->slug]),
        ]);
    }
}
