<?php

namespace App\Http\Controllers\V2;

use App\Http\Controllers\Controller;
use App\Http\Requests\V2\CustomerRequest;
use App\Models\Company;
use App\Models\Customer;
use App\Services\CustomerService;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    protected $customerService;

    public function __construct(CustomerService $customerService)
    {
        $this->customerService = $customerService;
    }

    public function index(Request $request, Company $company)
    {
        $customers = $this->customerService->getCustomers([
            'company_id' => $company->id,
            'search' => $request->search,
            'status' => $request->status,
        ]);

        return view('v2.company.customers.index', compact('customers', 'company'));
    }

    public function create(Company $company)
    {
        return view('v2.company.customers.form', compact('company'));
    }

    public function store(CustomerRequest $request, Company $company)
    {
        $data = $request->validated();
        $data['company_id'] = $company->id;

        $this->customerService->createCustomer($data);

        return redirect()
            ->route('v2.customers.index', ['company' => $company->slug])
            ->with('success', 'Customer created successfully.');
    }

    public function edit(Company $company, Customer $customer)
    {
        return view('v2.company.customers.form', compact('company', 'customer'));
    }

    public function update(CustomerRequest $request, Company $company, Customer $customer)
    {
        $this->customerService->updateCustomer($customer, $request->validated());

        return redirect()
            ->route('v2.customers.index', ['company' => $company->slug])
            ->with('success', 'Customer updated successfully.');
    }

    public function destroy(Company $company, Customer $customer)
    {
        $this->customerService->deleteCustomer($customer);

        return redirect()
            ->route('v2.customers.index', ['company' => $company->slug])
            ->with('success', 'Customer deleted successfully.');
    }
}
