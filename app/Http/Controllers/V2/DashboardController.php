<?php

namespace App\Http\Controllers\V2;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\Order;

class DashboardController extends Controller
{
    public function index(Company $company)
    {
        $recentOrders = Order::where('company_id', $company->id)
            ->with('customer')
            ->latest()
            ->take(10)
            ->get();

        return view('v2.dashboard.index', compact('recentOrders', 'company'));
    }
}
