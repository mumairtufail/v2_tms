<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Services\Portal\CustomerPortalService;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __construct(
        protected CustomerPortalService $portalService
    ) {}

    public function index(Company $company): View
    {
        $customer = Auth::guard('customer')->user();
        $stats = $this->portalService->getDashboardStats($customer);

        return view('portal.dashboard.index', compact('company', 'customer', 'stats'));
    }
}
