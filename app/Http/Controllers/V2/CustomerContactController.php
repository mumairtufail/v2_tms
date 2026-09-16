<?php

namespace App\Http\Controllers\V2;

use App\Enums\CustomerNotificationEvent;
use App\Http\Controllers\Controller;
use App\Http\Controllers\V2\Concerns\ScopesCustomerRecords;
use App\Http\Requests\V2\CustomerContactRequest;
use App\Models\Company;
use App\Models\Customer;
use App\Models\CustomerContact;
use App\Support\Toast;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class CustomerContactController extends Controller
{
    use ScopesCustomerRecords;

    public function store(CustomerContactRequest $request, Company $company, Customer $customer)
    {
        $this->ensureCustomerInCompany($company, $customer);

        $contact = DB::transaction(function () use ($request, $company, $customer) {
            [$attributes, $phones] = $this->attributesFrom($request);

            $contact = $customer->contacts()->create($attributes + ['company_id' => $company->id]);
            $contact->phones()->createMany($phones);

            return $contact;
        });

        Toast::success("Added {$contact->name}.");

        return $this->customerTab($company, $customer, 'people');
    }

    public function update(CustomerContactRequest $request, Company $company, Customer $customer, CustomerContact $contact)
    {
        $this->ensureCustomerInCompany($company, $customer);
        $this->ensureBelongsToCustomer($customer, $contact);

        DB::transaction(function () use ($request, $contact) {
            [$attributes, $phones] = $this->attributesFrom($request);

            $contact->update($attributes);
            $contact->phones()->delete();
            $contact->phones()->createMany($phones);
        });

        Toast::success("Saved {$contact->name}.");

        return $this->customerTab($company, $customer, 'people');
    }

    public function destroy(Company $company, Customer $customer, CustomerContact $contact)
    {
        $this->ensureCustomerInCompany($company, $customer);
        $this->ensureBelongsToCustomer($customer, $contact);

        $name = $contact->name;
        $contact->delete();

        Toast::success("Removed {$name}. They can no longer sign in to the portal.");

        return $this->customerTab($company, $customer, 'people');
    }

    /** @return array{0: array, 1: array} */
    private function attributesFrom(CustomerContactRequest $request): array
    {
        $data = $request->validated();
        $phones = Arr::pull($data, 'phones', []);
        $emailNotifications = Arr::pull($data, 'email_notifications', []);
        $password = Arr::pull($data, 'password');

        $data['notification_prefs'] = CustomerNotificationEvent::prefsFromEmailInput($emailNotifications);

        // Leaving the password blank keeps the current one.
        if (filled($password)) {
            $data['password'] = $password;
        }

        return [$data, $phones];
    }
}
