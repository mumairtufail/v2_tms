<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\Customer;
use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class OrderBookingTest extends TestCase
{
    use DatabaseTransactions;

    private Company $company;

    private User $user;

    private Customer $customer;

    protected function setUp(): void
    {
        parent::setUp();

        $suffix = uniqid();

        $this->company = Company::create([
            'name' => 'Booking Co '.$suffix,
            'slug' => 'booking-co-'.$suffix,
            'shortcode' => 'BKC',
            'address' => '1 Booking St',
            'phone' => '555-0100',
            'is_active' => true,
            'is_deleted' => false,
        ]);

        $this->user = User::create([
            'company_id' => $this->company->id,
            'name' => 'Bea Booker',
            'f_name' => 'Bea',
            'l_name' => 'Booker',
            'email' => "bea.booker.{$suffix}@example.com",
            'password' => Hash::make('password'),
            'status' => 'active',
            'is_active' => true,
            'is_deleted' => false,
            'is_super_admin' => true,
        ]);

        $this->customer = Customer::create([
            'company_id' => $this->company->id,
            'name' => 'Booking Customer',
            'customer_email' => "customer.{$suffix}@example.com",
            'is_active' => true,
            'is_deleted' => false,
        ]);
    }

    private function makeOrder(string $status): Order
    {
        return Order::create([
            'company_id' => $this->company->id,
            'customer_id' => $this->customer->id,
            'order_number' => 'BKC-'.uniqid(),
            'order_type' => 'point_to_point',
            'status' => $status,
        ]);
    }

    private function orderRoute(string $name, Order $order): string
    {
        return route($name, ['company' => $this->company->slug, 'order' => $order->id]);
    }

    public function test_quoted_order_can_be_marked_as_booked(): void
    {
        $order = $this->makeOrder('quoted');

        $this->actingAs($this->user)
            ->from($this->orderRoute('v2.orders.edit', $order))
            ->post($this->orderRoute('v2.orders.book', $order))
            ->assertRedirect($this->orderRoute('v2.orders.edit', $order));

        $this->assertSame('booked', $order->fresh()->status);
        $this->assertDatabaseHas('order_status_histories', [
            'order_id' => $order->id,
            'from_status' => 'quoted',
            'to_status' => 'booked',
            'changed_by_user_id' => $this->user->id,
        ]);
    }

    public function test_order_pages_show_booking_actions_and_short_status_codes(): void
    {
        $quoted = $this->makeOrder('quoted');
        $booked = $this->makeOrder('booked');

        $this->actingAs($this->user)
            ->get($this->orderRoute('v2.orders.edit', $quoted))
            ->assertOk()
            ->assertSee('Mark as Booked')
            ->assertSee('Create a new manifest?')
            ->assertDontSee('animate-ping');

        $this->actingAs($this->user)
            ->get($this->orderRoute('v2.orders.edit', $booked))
            ->assertOk()
            ->assertSee('Revert to Quoted')
            ->assertSee('Current Status: Booked');

        $this->actingAs($this->user)
            ->get(route('v2.orders.index', ['company' => $this->company->slug]))
            ->assertOk()
            ->assertSee('BKD')
            ->assertSee('QTD')
            ->assertSee('Mark as booked');
    }

    public function test_only_quoted_orders_can_be_booked(): void
    {
        foreach (['draft', 'new', 'no_quote', 'in_transit'] as $status) {
            $order = $this->makeOrder($status);

            $this->actingAs($this->user)
                ->from($this->orderRoute('v2.orders.edit', $order))
                ->post($this->orderRoute('v2.orders.book', $order))
                ->assertRedirect();

            $this->assertSame($status, $order->fresh()->status);
            $this->assertDatabaseMissing('order_status_histories', ['order_id' => $order->id]);
        }
    }

    public function test_booking_can_be_reverted_only_while_still_booked(): void
    {
        $booked = $this->makeOrder('booked');

        $this->actingAs($this->user)
            ->from($this->orderRoute('v2.orders.edit', $booked))
            ->post($this->orderRoute('v2.orders.unbook', $booked))
            ->assertRedirect();

        $this->assertSame('quoted', $booked->fresh()->status);

        // Once the driver has started, the booking can't be undone from the web.
        $started = $this->makeOrder('picked_up');

        $this->actingAs($this->user)
            ->from($this->orderRoute('v2.orders.edit', $started))
            ->post($this->orderRoute('v2.orders.unbook', $started))
            ->assertRedirect();

        $this->assertSame('picked_up', $started->fresh()->status);
    }

    public function test_editing_an_order_keeps_status_once_booked(): void
    {
        $payload = [
            'order_type' => 'point_to_point',
            'stops' => '[]',
            'quote_data' => '{}',
            'submission_mode' => 'new',
            'save_as_draft' => '0',
        ];

        // Control: a quoted order is still reset by a plain save, as before.
        $quoted = $this->makeOrder('quoted');
        $this->actingAs($this->user)
            ->from($this->orderRoute('v2.orders.edit', $quoted))
            ->patch($this->orderRoute('v2.orders.update', $quoted), $payload);
        $this->assertSame('new', $quoted->fresh()->status);

        foreach (['booked', 'in_transit'] as $status) {
            $order = $this->makeOrder($status);

            $this->actingAs($this->user)
                ->from($this->orderRoute('v2.orders.edit', $order))
                ->patch($this->orderRoute('v2.orders.update', $order), $payload);

            $this->assertSame($status, $order->fresh()->status);
        }
    }
}
