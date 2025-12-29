<?php

namespace App\Http\Controllers\V2;

use App\Http\Controllers\Controller;
use App\Http\Requests\V2\CustomerRequest;
use App\Models\Company;
use App\Models\Customer;
use App\Services\CustomerService;
use App\Services\PluginService;
use App\Plugins\QuickBooks\Services\QuickBooksService;
use App\Plugins\QuickBooks\Services\ApiClient;
use App\Support\Toast;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    protected $customerService;
    protected $pluginService;

    public function __construct(CustomerService $customerService, PluginService $pluginService)
    {
        $this->customerService = $customerService;
        $this->pluginService = $pluginService;
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

    public function syncToQuickBooks(Company $company, Customer $customer)
    {
        try {
            $config = $this->pluginService->getConfiguration($company->id, 'quickbooks');
            
            if (!$config || !($config->is_active ?? false)) {
                return back()->with('error', 'QuickBooks plugin is not active or configured.');
            }

            $configuration = $config->configuration;
            $configuration['config_id'] = $config->id; // For token refresh persistence

            $apiClient = new ApiClient($configuration);
            $qbService = new QuickBooksService($apiClient);

            $qbCustomer = $qbService->createCustomer($customer->toArray());

            if ($qbCustomer && isset($qbCustomer['Id'])) {
                $customer->update(['quickbooks_id' => $qbCustomer['Id']]);
                Toast::success('Customer synced to QuickBooks successfully!');
            } else {
                Toast::error('Failed to sync customer to QuickBooks.');
            }

            return back();
        } catch (\Exception $e) {
            return back()->with('error', 'QuickBooks sync failed: ' . $e->getMessage());
        }
    }
}
