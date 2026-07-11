<?php

namespace App\Http\Controllers\V2;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Services\ContactBookService;
use Illuminate\Http\Request;

class ContactBookController extends Controller
{
    public function index(Request $request, Company $company, ContactBookService $service)
    {
        return response()->json([
            'entries' => $service->list($company->id),
        ]);
    }
}
