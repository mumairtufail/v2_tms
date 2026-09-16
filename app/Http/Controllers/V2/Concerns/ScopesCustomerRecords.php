<?php

namespace App\Http\Controllers\V2\Concerns;

use App\Models\Company;
use App\Models\Customer;
use Illuminate\Database\Eloquent\Model;

/**
 * Route model binding resolves customers and their child records by id alone,
 * so every action confirms they belong to the company (and customer) in the URL.
 */
trait ScopesCustomerRecords
{
    protected function ensureCustomerInCompany(Company $company, Customer $customer): void
    {
        abort_unless((int) $customer->company_id === (int) $company->id && ! $customer->is_deleted, 404);
    }

    protected function ensureBelongsToCustomer(Customer $customer, Model $record): void
    {
        abort_unless((int) $record->getAttribute('customer_id') === (int) $customer->id, 404);
    }

    protected function customerTab(Company $company, Customer $customer, string $tab)
    {
        return redirect()->route('v2.customers.show', [
            'company' => $company->slug,
            'customer' => $customer->id,
            'tab' => $tab,
        ]);
    }
}
