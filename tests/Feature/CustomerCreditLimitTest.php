<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\Customer;
use App\Models\CustomerContact;
use App\Models\Order;
use App\Models\OrderQuote;
use App\Services\CustomerCreditService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

/**
 * Credit is consumed by committed work (booked onward) that is not yet invoiced,
 * plus the unpaid invoice balance from QuickBooks. Drafts and quotes consume nothing.
 */
class CustomerCreditLimitTest extends TestCase
{
    use DatabaseTransactions;

    private Company $company;

    private CustomerCreditService $credit;

    protected function setUp(): void
    {
        parent::setUp();

        $suffix = uniqid();

        $this->company = Company::create([
            'name' => 'Credit Co '.$suffix,
            'slug' => 'credit-co-'.$suffix,
            'shortcode' => 'CRED',
            'is_active' => true,
            'is_deleted' => false,
        ]);

        $this->credit = app(CustomerCreditService::class);
    }

    private function makeCustomer(?float $limit, ?float $balance = null): Customer
    {
        return Customer::create([
            'company_id' => $this->company->id,
            'name' => 'Credit Customer',
            'short_code' => strtoupper(substr(uniqid(), -4)),
            'credit_limit' => $limit,
            'credit_balance' => $balance,
            'is_active' => true,
            'is_deleted' => false,
        ]);
    }

    private function makeOrder(Customer $customer, string $status, float $charge, ?string $invoiceId = null): Order
    {
        $order = Order::create([
            'company_id' => $this->company->id,
            'customer_id' => $customer->id,
            'order_number' => 'CRED-'.uniqid(),
            'order_type' => 'point_to_point',
            'status' => $status,
            'quickbooks_invoice_id' => $invoiceId,
        ]);

        $quote = OrderQuote::create(['order_id' => $order->id]);
        $quote->costs()->create([
            'category' => 'customer', 'type' => 'Freight',
            'description' => 'Freight', 'qty' => 1, 'rate' => $charge, 'cost' => $charge,
        ]);

        return $order;
    }

    public function test_committed_orders_consume_credit(): void
    {
        $customer = $this->makeCustomer(1000);

        $this->makeOrder($customer, 'booked', 200);
        $this->makeOrder($customer, 'in_transit', 150);
        $this->makeOrder($customer, 'delivered', 50);

        $usage = $this->credit->usage($customer->fresh());

        $this->assertSame(400.0, $usage['used']);
        $this->assertSame(600.0, $usage['available']);
        $this->assertFalse($usage['over']);
        $this->assertSame(40, $usage['percent']);
    }

    public function test_drafts_and_quotes_do_not_consume_credit(): void
    {
        $customer = $this->makeCustomer(1000);

        $this->makeOrder($customer, 'draft', 500);
        $this->makeOrder($customer, 'new', 500);
        $this->makeOrder($customer, 'quoted', 500);
        $this->makeOrder($customer, 'cancelled', 500);

        $usage = $this->credit->usage($customer->fresh());

        $this->assertSame(0.0, $usage['used']);
        $this->assertFalse($usage['over']);
    }

    public function test_invoiced_orders_move_from_committed_to_the_quickbooks_balance(): void
    {
        // 300 already invoiced and unpaid in QuickBooks, 200 committed but not invoiced
        $customer = $this->makeCustomer(1000, 300);

        $this->makeOrder($customer, 'delivered', 300, 'QB-1'); // counted via the balance, not twice
        $this->makeOrder($customer, 'booked', 200);

        $usage = $this->credit->usage($customer->fresh());

        $this->assertSame(200.0, $usage['committed']);
        $this->assertSame(300.0, $usage['invoiced']);
        $this->assertSame(500.0, $usage['used']);
    }

    public function test_limit_is_reached_when_used_meets_it(): void
    {
        $customer = $this->makeCustomer(500);
        $this->makeOrder($customer, 'booked', 500);

        $usage = $this->credit->usage($customer->fresh());

        $this->assertTrue($usage['over']);
        $this->assertSame(0.0, $usage['available']);
        $this->assertTrue($this->credit->isOverLimit($customer->fresh()));
    }

    public function test_portal_banner_appears_only_when_credit_gets_tight(): void
    {
        $customer = $this->makeCustomer(500);

        $contact = CustomerContact::create([
            'company_id' => $this->company->id,
            'customer_id' => $customer->id,
            'first_name' => 'Pat',
            'last_name' => 'Portal',
            'email' => 'pat.'.uniqid().'@example.com',
            'password' => 'Str0ng!Passw0rd',
            'portal_access' => true,
        ]);

        $dashboard = route('portal.dashboard', ['company' => $this->company->slug]);

        // Comfortably under the limit: nothing to say
        $this->makeOrder($customer, 'booked', 100);
        $this->actingAs($contact, 'customer')->get($dashboard)
            ->assertOk()
            ->assertDontSee('Credit limit reached')
            ->assertDontSee('Approaching your credit limit');

        // 400 of 500 used: amber warning before it becomes a problem
        $this->makeOrder($customer, 'booked', 300);
        $this->actingAs($contact, 'customer')->get($dashboard)
            ->assertOk()
            ->assertSee('Approaching your credit limit')
            ->assertDontSee('Credit limit reached');

        // At the limit: red banner explaining what happens next
        $this->makeOrder($customer, 'booked', 100);
        $this->actingAs($contact, 'customer')->get($dashboard)
            ->assertOk()
            ->assertSee('Credit limit reached')
            ->assertSee('cannot be submitted at the moment')
            ->assertSee('save them as drafts');
    }

    public function test_customer_without_a_limit_is_never_over(): void
    {
        $customer = $this->makeCustomer(null);
        $this->makeOrder($customer, 'booked', 9999);

        $usage = $this->credit->usage($customer->fresh());

        $this->assertFalse($usage['has_limit']);
        $this->assertNull($usage['available']);
        $this->assertNull($usage['percent']);
        $this->assertFalse($usage['over']);
        $this->assertFalse($this->credit->isOverLimit($customer->fresh()));
    }
}
