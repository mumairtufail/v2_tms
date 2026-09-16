<?php

namespace App\Http\Controllers\V2;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\Customer;
use App\Services\ContactBookService;
use Illuminate\Http\Request;

class ContactBookController extends Controller
{
    public function index(Request $request, Company $company, ContactBookService $service)
    {
        $entries = $service->list($company->id);

        // The order's customer addresses come first, carrying their stop defaults.
        if ($customerId = $request->integer('customer_id')) {
            $customer = Customer::where('company_id', $company->id)->where('is_deleted', false)->find($customerId);

            if ($customer) {
                $customerEntries = $customer->addresses()
                    ->with('accessorials')
                    ->orderByDesc('is_billing')
                    ->orderBy('company_name')
                    ->get()
                    ->map->toContactBookEntry()
                    ->all();

                $entries = array_merge($customerEntries, $entries);
            }
        }

        return response()->json([
            'entries' => $entries,
        ]);
    }
}
