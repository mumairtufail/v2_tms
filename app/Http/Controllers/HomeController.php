<?php

namespace App\Http\Controllers;

use App\Models\ActivityLogs;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        return view('dashboard.index');
    }

    /**
     * Display activity logs with search and pagination.
     */
    public function activity_logs(Request $request)
    {
        $query = ActivityLogs::with('user');

        // Search functionality
        if ($request->has('search') && !empty($request->search)) {
            $searchTerm = $request->search;
            $query->where(function ($q) use ($searchTerm) {
                $q->where('action', 'LIKE', "%{$searchTerm}%")
                    ->orWhereHas('user', function ($q2) use ($searchTerm) {
                        $q2->where('name', 'LIKE', "%{$searchTerm}%")
                            ->orWhere('f_name', 'LIKE', "%{$searchTerm}%")
                            ->orWhere('l_name', 'LIKE', "%{$searchTerm}%");
                    });
            });
        }

        // Filter by method
        if ($request->has('method') && !empty($request->method)) {
            $query->where('method', $request->method);
        }

        $activity_logs = $query->orderBy('created_at', 'desc')->paginate(20);

        return view('v2.admin.logs.index', compact('activity_logs'));
    }

    public function viewLogs($id)
    {
        $logs = ActivityLogs::find($id);
        return view('dashboard.activity_logs.view', compact('logs'));
    }
}
