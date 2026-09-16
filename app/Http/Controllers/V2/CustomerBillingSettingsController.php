<?php

namespace App\Http\Controllers\V2;

use App\Http\Controllers\Controller;
use App\Http\Controllers\V2\Concerns\ScopesCustomerRecords;
use App\Http\Requests\V2\CustomerBillingSettingsRequest;
use App\Models\Company;
use App\Models\Customer;
use App\Support\Toast;

class CustomerBillingSettingsController extends Controller
{
    use ScopesCustomerRecords;

    public function update(CustomerBillingSettingsRequest $request, Company $company, Customer $customer)
    {
        $this->ensureCustomerInCompany($company, $customer);

        $customer->billingSettings()->updateOrCreate([], $request->validated());

        Toast::success('Invoice settings saved.');

        return $this->customerTab($company, $customer, 'accounting');
    }
}
