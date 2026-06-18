<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\ActivityLogListingService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ActivityLogController extends Controller
{
    public function __construct(
        protected ActivityLogListingService $listingService
    ) {}

    public function index(Request $request): View
    {
        $activity_logs = $this->listingService->paginate($request);

        return view('v2.activity-logs.index', [
            'activity_logs' => $activity_logs,
            'company' => null,
            'scope' => 'global',
            'filterRoute' => route('admin.logs'),
        ]);
    }
}
