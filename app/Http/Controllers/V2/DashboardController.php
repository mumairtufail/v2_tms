<?php

namespace App\Http\Controllers\V2;

use App\Http\Controllers\Controller;
use App\Models\Order;

class DashboardController extends Controller
{
    /**
     * Display the V2 dashboard.
     */
    public function index()
    {
        $recentOrders = Order::with('customer')
            ->latest()
            ->take(10)
            ->get();

        return view('v2.dashboard.index', compact('recentOrders'));
    }
}
