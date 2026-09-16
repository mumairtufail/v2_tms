<?php

namespace App\Http\Controllers\V2;

use App\Http\Controllers\Controller;
use App\Http\Controllers\V2\Concerns\ScopesCustomerRecords;
use App\Models\Company;
use App\Models\Customer;
use App\Support\Toast;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CustomerAccessorialController extends Controller
{
    use ScopesCustomerRecords;

    /**
     * Which accessorials this customer's orders can use.
     */
    public function update(Request $request, Company $company, Customer $customer)
    {
        $this->ensureCustomerInCompany($company, $customer);

        $validated = $request->validate([
            'accessorial_ids' => ['array'],
            'accessorial_ids.*' => ['integer', Rule::exists('accessorials', 'id')->where('company_id', $company->id)],
        ]);

        $ids = array_map('intval', $validated['accessorial_ids'] ?? []);
        $customer->accessorials()->sync($ids);

        Toast::success(count($ids) === 1 ? '1 accessorial enabled for this customer.' : count($ids) . ' accessorials enabled for this customer.');

        return $this->customerTab($company, $customer, 'accessorials');
    }
}
