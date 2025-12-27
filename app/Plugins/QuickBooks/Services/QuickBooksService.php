<?php

namespace App\Plugins\QuickBooks\Services;

use Illuminate\Support\Facades\Log;
use Exception;

class QuickBooksService
{
    protected $apiClient;

    public function __construct(ApiClient $apiClient)
    {
        $this->apiClient = $apiClient;
    }

    public function createCustomer($customerData)
    {
        // Map TMS customer data to QuickBooks Customer object
        // https://developer.intuit.com/app/developer/qbo/docs/api/accounting/all-entities/customer
        
        // Split name into First and Last for Given/Family name
        $fullName = $customerData['name'] ?? '';
        $parts = explode(' ', $fullName, 2);
        $firstName = $parts[0] ?? null;
        $lastName = $parts[1] ?? null;

        $payload = [
            'DisplayName' => $customerData['name'], // Required
            'CompanyName' => $customerData['name'], // Map TMS Name to CompanyName
            'GivenName' => $firstName,
            'FamilyName' => $lastName,
            'PrimaryEmailAddr' => [
                'Address' => $customerData['customer_email'] ?? null,
            ],
            // 'PrimaryPhone' => [
            //     'FreeFormNumber' => $customerData['phone'] ?? null, // TMS Customer doesn't seem to have phone in fillable
            // ],
            'BillAddr' => [
                'Line1' => $customerData['address'] ?? null,
                'City' => $customerData['city'] ?? null,
                'CountrySubDivisionCode' => $customerData['state'] ?? null,
                'PostalCode' => $customerData['postal_code'] ?? null,
                'Country' => $customerData['country'] ?? 'USA',
            ],
        ];

        // Filter out null values to avoid sending unnecessary data
        // (Though QBO API usually handles nulls, it's cleaner to remove them)
        // $payload = array_filter($payload, fn($value) => !is_null($value));

        Log::channel('plugins')->info("QuickBooks Service: Creating Customer", [
            'payload' => $payload,
            'customer_id' => $customerData['id'] ?? 'new'
        ]);

        try {
            $response = $this->apiClient->post('customer', $payload);
            Log::channel('plugins')->info("QuickBooks Service: Customer Created Successfully", [
                'customer_id' => $response['Customer']['Id'] ?? 'Unknown',
                'response' => $response
            ]);
            return $response['Customer'] ?? null;
        } catch (Exception $e) {
            Log::channel('plugins')->error("QuickBooks Service: Create Customer Failed", [
                'error' => $e->getMessage(),
                'customer_data' => $customerData
            ]);
            // Log error or rethrow
            // Log::error('QuickBooks Create Customer Failed: ' . $e->getMessage());
            throw $e;
        }
    }
    public function createInvoice($order)
    {
        // Ensure customer is synced
        if (empty($order->customer->quickbooks_id)) {
            throw new Exception("Customer is not synced to QuickBooks. Please sync the customer first.");
        }

        // Ensure order has a quote
        if (!$order->quote) {
            throw new Exception("Order does not have a quote. Please create a quote first.");
        }

        $lines = [];
        foreach ($order->quote->costs as $cost) {
            $lines[] = [
                'DetailType' => 'SalesItemLineDetail',
                'Amount' => $cost->cost,
                'Description' => $cost->description ?? 'Service Charge',
                'SalesItemLineDetail' => [
                    'ItemRef' => [
                        'value' => '1', // Default Service Item ID (usually '1' in QBO, or 'Services')
                        'name' => 'Services'
                    ],
                    'Qty' => 1,
                    'UnitPrice' => $cost->cost
                ]
            ];
        }

        if (empty($lines)) {
            throw new Exception("Quote has no costs. Cannot create a zero-value invoice.");
        }

        $payload = [
            'CustomerRef' => [
                'value' => $order->customer->quickbooks_id
            ],
            'Line' => $lines,
            'DocNumber' => $order->order_number,
            // 'TxnDate' => now()->format('Y-m-d'), // Defaults to today
        ];

        Log::channel('plugins')->info("QuickBooks Service: Creating Invoice", ['payload' => $payload]);

        try {
            $response = $this->apiClient->post('invoice', $payload);
            Log::channel('plugins')->info("QuickBooks Service: Invoice Created Successfully", ['invoice_id' => $response['Invoice']['Id'] ?? 'Unknown']);
            return $response['Invoice'] ?? null;
        } catch (Exception $e) {
            Log::channel('plugins')->error("QuickBooks Service: Create Invoice Failed", ['error' => $e->getMessage()]);
            throw $e;
        }
    }
}
