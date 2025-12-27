<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Company;
use App\Models\User;
use App\Models\Order;

class AdminController extends Controller
{
    public function index(Request $request)
    {
        // Global admin dashboard for superadmins
        $totalCompanies = Company::where('is_deleted', false)->count();
        $totalUsers = User::where('is_deleted', false)->count();
        $totalOrders = Order::count();
        
        // Recent data
        $recentCompanies = Company::where('is_deleted', false)
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();
            
        $recentUsers = User::where('is_deleted', false)
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        return view('v2.admin.dashboard', [
            'totalCompanies' => $totalCompanies,
            'totalUsers' => $totalUsers,
            'totalOrders' => $totalOrders,
            'recentCompanies' => $recentCompanies,
            'recentUsers' => $recentUsers,
        ]);
    }
}
