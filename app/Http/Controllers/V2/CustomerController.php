<?php

namespace App\Http\Controllers\V2;

use App\Http\Controllers\Controller;
use App\Http\Controllers\V2\Concerns\ScopesCustomerRecords;
use App\Http\Requests\V2\CustomerDetailsRequest;
use App\Http\Requests\V2\CustomerRequest;
use App\Models\Accessorial;
use App\Models\Company;
use App\Models\Customer;
use App\Models\CustomerBillingSetting;
use App\Plugins\QuickBooks\Services\ApiClient;
use App\Plugins\QuickBooks\Services\QuickBooksService;
use App\Services\CustomerService;
use App\Services\PluginService;
use App\Support\Toast;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    use ScopesCustomerRecords;

    public const TABS = ['details', 'people', 'addresses', 'accounting', 'accessorials', 'commodities'];

    public function __construct(
        protected CustomerService $customerService,
        protected PluginService $pluginService,
    ) {}

    /**
     * Suggest a unique short code from a customer name (scoped to company).
     */
    public function generateShortCode(Request $request, Company $company)
    {
        $name = trim((string) $request->input('name', ''));

        if ($name === '') {
            return response()->json(['short_code' => '']);
        }

        $shortCode = $this->customerService->generateUniqueShortCode(
            $company->id,
            $name,
            $request->integer('exclude_id') ?: null
        );

        return response()->json(['short_code' => $shortCode]);
    }

    public function index(Request $request, Company $company)
    {
        $customers = $this->customerService->getCustomers([
            'company_id' => $company->id,
            'search' => $request->search,
            'status' => $request->status,
            'sort' => $request->sort,
            'direction' => $request->direction,
        ]);

        return view('v2.company.customers.index', compact('customers', 'company'));
    }

    public function create(Company $company)
    {
        return redirect()->route('v2.customers.index', ['company' => $company->slug, 'create' => 1]);
    }

    public function store(CustomerRequest $request, Company $company)
    {
        $data = $request->validated();
        $data['company_id'] = $company->id;

        $customer = $this->customerService->createCustomer($data);

        Toast::success("Created {$customer->name} ({$customer->short_code}).");

        return $this->customerTab($company, $customer, 'details');
    }

    public function show(Request $request, Company $company, Customer $customer)
    {
        $this->ensureCustomerInCompany($company, $customer);

        $tab = in_array($request->query('tab'), self::TABS, true) ? $request->query('tab') : 'details';
        $search = trim((string) $request->query('q', ''));

        $customer->load('billingAddress')->loadCount(['contacts', 'addresses', 'commodities']);

        $data = compact('company', 'customer', 'tab', 'search');

        $companyAccessorials = fn () => Accessorial::forCompany($company->id)
            ->orderBy('category')
            ->orderBy('name')
            ->get();

        switch ($tab) {
            case 'people':
                $data['contacts'] = $customer->contacts()->with('phones')->orderBy('first_name')->orderBy('last_name')->get();
                break;

            case 'addresses':
                $data['addresses'] = $customer->addresses()
                    ->with('accessorials')
                    ->when($search !== '', fn ($q) => $q->where(fn ($q) => $q
                        ->where('company_name', 'like', "%{$search}%")
                        ->orWhere('address_1', 'like', "%{$search}%")
                        ->orWhere('city', 'like', "%{$search}%")))
                    ->orderByDesc('is_billing')
                    ->orderBy('company_name')
                    ->get();
                $data['accessorials'] = $companyAccessorials()->where('is_active', true)->values();
                break;

            case 'accounting':
                $data['settings'] = $customer->billingSettings ?? new CustomerBillingSetting();
                $data['invoiceContacts'] = $customer->contacts()
                    ->where(fn ($q) => $q->where('send_invoices', true)->orWhere('cc_on_invoices', true))
                    ->whereNotNull('email')
                    ->orderBy('first_name')
                    ->get();
                break;

            case 'accessorials':
                $data['accessorials'] = $companyAccessorials();
                $data['enabledIds'] = $customer->accessorials()->pluck('accessorials.id')->all();
                break;

            case 'commodities':
                $data['commodities'] = $customer->commodities()
                    ->when($search !== '', fn ($q) => $q->where(fn ($q) => $q
                        ->where('description', 'like', "%{$search}%")
                        ->orWhere('sku', 'like', "%{$search}%")
                        ->orWhere('nmfc', 'like', "%{$search}%")))
                    ->orderBy('description')
                    ->get();
                break;
        }

        return view('v2.company.customers.show', $data);
    }

    public function edit(Company $company, Customer $customer)
    {
        return $this->customerTab($company, $customer, 'details');
    }

    public function update(CustomerDetailsRequest $request, Company $company, Customer $customer)
    {
        $this->ensureCustomerInCompany($company, $customer);

        $this->customerService->updateDetails(
            $customer,
            $request->validated(),
            $request->file('logo'),
            $request->boolean('remove_logo'),
        );

        Toast::success('Customer details saved.');

        return $this->customerTab($company, $customer, 'details');
    }

    public function destroy(Company $company, Customer $customer)
    {
        $this->ensureCustomerInCompany($company, $customer);

        if (! $this->customerService->canDelete($customer)) {
            Toast::error("{$customer->name} has orders, so it can't be deleted. Mark it inactive instead.");

            return $this->customerTab($company, $customer, 'details');
        }

        $this->customerService->deleteCustomer($customer);

        Toast::success("Deleted {$customer->name}.");

        return redirect()->route('v2.customers.index', ['company' => $company->slug]);
    }

    /**
     * The alternative to deleting a customer that has orders: hide it from new orders, keep its history.
     */
    public function deactivate(Request $request, Company $company, Customer $customer)
    {
        $this->ensureCustomerInCompany($company, $customer);

        $customer->update(['is_active' => false]);

        Toast::success("{$customer->name} is now inactive. Its orders are unchanged.");

        return $request->input('return') === 'details'
            ? $this->customerTab($company, $customer, 'details')
            : redirect()->route('v2.customers.index', ['company' => $company->slug]);
    }

    public function syncToQuickBooks(Company $company, Customer $customer)
    {
        $this->ensureCustomerInCompany($company, $customer);

        try {
            $qbService = $this->quickBooks($company);

            if (! $qbService) {
                return back()->with('error', 'QuickBooks plugin is not active or configured.');
            }

            $payload = $customer->toArray();
            if ($billing = $customer->billingAddress) {
                $payload['address'] = $billing->streetLine();
                $payload['city'] = $billing->city;
                $payload['state'] = $billing->state;
                $payload['postal_code'] = $billing->postal_code;
                $payload['country'] = $billing->country;
            }
            $payload['customer_email'] = $customer->contacts()
                ->where('send_invoices', true)
                ->whereNotNull('email')
                ->value('email') ?? $customer->customer_email;

            $qbCustomer = $qbService->createCustomer($payload);

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

    /**
     * Pull the customer's open balance from QuickBooks.
     */
    public function refreshCreditBalance(Company $company, Customer $customer)
    {
        $this->ensureCustomerInCompany($company, $customer);

        if (! $customer->quickbooks_id) {
            Toast::error('Sync this customer to QuickBooks first to see its balance.');

            return $this->customerTab($company, $customer, 'details');
        }

        try {
            $apiClient = $this->quickBooksClient($company);

            if (! $apiClient) {
                Toast::error('Connect QuickBooks in Plugins to refresh the balance.');

                return $this->customerTab($company, $customer, 'details');
            }

            $response = $apiClient->get('customer/' . $customer->quickbooks_id);
            $balance = $response['Customer']['Balance'] ?? null;

            if (! is_numeric($balance)) {
                Toast::error("QuickBooks didn't return a balance for this customer.");

                return $this->customerTab($company, $customer, 'details');
            }

            $customer->update([
                'credit_balance' => $balance,
                'credit_balance_synced_at' => now(),
            ]);

            Toast::success('Credit balance refreshed from QuickBooks.');
        } catch (\Throwable $e) {
            report($e);
            Toast::error('QuickBooks could not be reached. Try again in a moment.');
        }

        return $this->customerTab($company, $customer, 'details');
    }

    private function quickBooksClient(Company $company): ?ApiClient
    {
        $config = $this->pluginService->getConfiguration($company->id, 'quickbooks');

        if (! $config || ! ($config->is_active ?? false)) {
            return null;
        }

        $configuration = $config->configuration;
        $configuration['config_id'] = $config->id; // For token refresh persistence

        return new ApiClient($configuration);
    }

    private function quickBooks(Company $company): ?QuickBooksService
    {
        $client = $this->quickBooksClient($company);

        return $client ? new QuickBooksService($client) : null;
    }
}
