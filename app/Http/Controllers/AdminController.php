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
        return view('v2.admin.dashboard');
    }
}
