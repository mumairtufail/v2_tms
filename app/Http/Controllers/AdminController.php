<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\Customer;
use App\Models\Manifest;
use App\Models\Order;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class AdminController extends Controller
{
    public function index(Request $request)
    {
        $since = Carbon::now()->subDays(30);

        $stats = [
            'organizations' => Company::where('is_deleted', false)->count(),
            'organizations_active' => Company::where('is_deleted', false)->where('is_active', true)->count(),
            'organizations_new' => Company::where('is_deleted', false)->where('created_at', '>=', $since)->count(),

            'users' => User::where('is_deleted', false)->count(),
            'users_active' => User::where('is_deleted', false)->where('is_active', true)->count(),

            'customers' => Customer::where('is_deleted', false)->count(),

            'orders' => Order::count(),
            'orders_new' => Order::where('created_at', '>=', $since)->count(),
            'orders_live' => Order::whereIn('status', ['booked', 'warehousing', 'picked_up', 'in_transit'])->count(),

            'manifests' => Manifest::count(),
        ];

        // Orders per status, for the breakdown bar
        $ordersByStatus = Order::selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        // Busiest organizations, with what sits inside each
        $topCompanies = Company::where('is_deleted', false)
            ->withCount([
                'users as users_count' => fn ($q) => $q->where('is_deleted', false),
                'orders as orders_count',
            ])
            ->orderByDesc('orders_count')
            ->take(5)
            ->get();

        $recentCompanies = Company::where('is_deleted', false)
            ->latest()
            ->take(5)
            ->get();

        return view('v2.admin.dashboard', compact('stats', 'ordersByStatus', 'topCompanies', 'recentCompanies'));
    }
}
