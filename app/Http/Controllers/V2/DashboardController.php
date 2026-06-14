<?php

namespace App\Http\Controllers\V2;

use App\Http\Controllers\Controller;

class DashboardController extends Controller
{
    public function index()
    {
        return view('v2.dashboard.index');
    }
}
