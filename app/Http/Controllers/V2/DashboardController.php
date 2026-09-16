<?php

namespace App\Http\Controllers\V2;

use App\Http\Controllers\Controller;
use App\Models\Carrier;
use App\Models\Company;
use App\Models\Customer;
use App\Models\Manifest;
use App\Models\Order;
use Illuminate\Support\Carbon;

class DashboardController extends Controller
{
    public function index(Company $company)
    {
        $since = Carbon::now()->subDays(30);

        $stats = [
            'orders' => Order::where('company_id', $company->id)->count(),
            'orders_live' => Order::where('company_id', $company->id)
                ->whereIn('status', ['booked', 'warehousing', 'picked_up', 'in_transit'])
                ->count(),
            'orders_new' => Order::where('company_id', $company->id)->where('created_at', '>=', $since)->count(),

            'customers' => Customer::where('company_id', $company->id)->where('is_deleted', false)->count(),

            'manifests' => Manifest::where('company_id', $company->id)->count(),
            'manifests_open' => Manifest::where('company_id', $company->id)
                ->whereIn('status', ['pending', 'dispatched', 'in_transit'])
                ->count(),

            'carriers' => Carrier::where('company_id', $company->id)->where('is_active', true)->count(),
        ];

        $recentOrders = Order::where('company_id', $company->id)
            ->with('customer')
            ->latest()
            ->take(10)
            ->get();

        return view('v2.dashboard.index', compact('recentOrders', 'company', 'stats'));
    }
}
