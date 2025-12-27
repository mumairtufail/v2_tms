<?php

namespace App\Http\Controllers\V2;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\Manifest;
use App\Models\Customer;
use App\Models\ActivityLogs;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    /**
     * Display the V2 dashboard.
     */
    public function index()
    {
        $company = app('current.company');

        // Get statistics
        $stats = [
            'total_orders' => Order::count(),
            'active_manifests' => Manifest::where('status', 'active')->count(),
            'total_customers' => Customer::count(),
            'revenue' => Order::whereMonth('created_at', now()->month)
                ->sum('declared_value'),
        ];

        // Get recent orders
        $recentOrders = Order::with('customer')
            ->latest()
            ->take(5)
            ->get();

        // Get activity logs
        $activityLogs = ActivityLogs::with('user')
            ->latest()
            ->take(10)
            ->get();

        return view('v2.dashboard.index', compact('stats', 'recentOrders', 'activityLogs'));
    }
}
