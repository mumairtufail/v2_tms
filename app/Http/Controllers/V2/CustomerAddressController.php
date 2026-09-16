<?php

namespace App\Http\Controllers\V2;

use App\Http\Controllers\Controller;
use App\Http\Controllers\V2\Concerns\ScopesCustomerRecords;
use App\Http\Requests\V2\CustomerAddressRequest;
use App\Models\Company;
use App\Models\Customer;
use App\Models\CustomerAddress;
use App\Support\Toast;

class CustomerAddressController extends Controller
{
    use ScopesCustomerRecords;

    public function store(CustomerAddressRequest $request, Company $company, Customer $customer)
    {
        $this->ensureCustomerInCompany($company, $customer);

        $data = $request->validated();

        // The first address a customer gets is its billing address.
        $data['is_billing'] = $data['is_billing'] || ! $customer->addresses()->exists();

        $address = $customer->addresses()->create($data + [
            'company_id' => $company->id,
            // Label shown when this address is picked on an order.
            'company_name' => $customer->name,
        ]);

        if ($request->boolean('add_another')) {
            session()->flash('address_add_another', true);
        }

        Toast::success("Added {$address->streetLine()}.");

        return $this->customerTab($company, $customer, 'addresses');
    }

    public function update(CustomerAddressRequest $request, Company $company, Customer $customer, CustomerAddress $address)
    {
        $this->ensureCustomerInCompany($company, $customer);
        $this->ensureBelongsToCustomer($customer, $address);

        $data = $request->validated();

        // A customer always keeps a billing address: pick another one to move it.
        if ($address->is_billing) {
            $data['is_billing'] = true;
        }

        $address->update($data);

        Toast::success("Saved {$address->streetLine()}.");

        return $this->customerTab($company, $customer, 'addresses');
    }

    public function destroy(Company $company, Customer $customer, CustomerAddress $address)
    {
        $this->ensureCustomerInCompany($company, $customer);
        $this->ensureBelongsToCustomer($customer, $address);

        if ($address->is_billing) {
            Toast::error('This is the billing address. Make another address the billing address before deleting it.');

            return $this->customerTab($company, $customer, 'addresses');
        }

        $address->delete();

        Toast::success("Deleted {$address->streetLine()}.");

        return $this->customerTab($company, $customer, 'addresses');
    }
}
