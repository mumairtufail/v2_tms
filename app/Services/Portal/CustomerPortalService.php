<?php

namespace App\Services\Portal;

use App\Models\Company;
use App\Models\Customer;
use App\Models\Order;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class CustomerPortalService
{
    public function getDashboardStats(Customer $customer): array
    {
        $baseQuery = Order::query()
            ->where('company_id', $customer->company_id)
            ->where('customer_id', $customer->id);

        return [
            'total' => (clone $baseQuery)->count(),
            'draft' => (clone $baseQuery)->where('status', 'draft')->count(),
            'new' => (clone $baseQuery)->where('status', 'new')->count(),
            'quoted' => (clone $baseQuery)->where('status', 'quoted')->count(),
            'booked' => (clone $baseQuery)->where('status', 'booked')->count(),
            'in_transit' => (clone $baseQuery)->where('status', 'in_transit')->count(),
            'delivered' => (clone $baseQuery)->where('status', 'delivered')->count(),
        ];
    }

    public function getOrders(Customer $customer, array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = Order::query()
            ->where('company_id', $customer->company_id)
            ->where('customer_id', $customer->id)
            ->with([
                'stops',
                'manifest',
                'quote.costs' => fn ($q) => $q->where('category', 'customer'),
            ]);

        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('order_number', 'LIKE', "%{$search}%")
                    ->orWhere('ref_number', 'LIKE', "%{$search}%")
                    ->orWhere('customer_po_number', 'LIKE', "%{$search}%");
            });
        }

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        return $query->latest()->paginate($perPage)->withQueryString();
    }

    public function getOrderDetail(Customer $customer, int $orderId): Order
    {
        $order = Order::query()
            ->where('company_id', $customer->company_id)
            ->where('customer_id', $customer->id)
            ->with([
                'stops.commodities',
                'stops.accessorials',
                'manifest',
                'quote.costs' => fn ($q) => $q->where('category', 'customer'),
            ])
            ->find($orderId);

        if (!$order) {
            throw new ModelNotFoundException('Order not found.');
        }

        return $order;
    }

    public function assertOrderBelongsToCustomer(Order $order, Customer $customer): void
    {
        if ($order->customer_id !== $customer->id || $order->company_id !== $customer->company_id) {
            abort(403, 'You do not have access to this order.');
        }
    }

    public function assertCustomerBelongsToCompany(Customer $customer, Company $company): void
    {
        if ($customer->company_id !== $company->id) {
            abort(403, 'You do not have access to this company portal.');
        }
    }

    public function assertOrderEditableByCustomer(Order $order, Customer $customer): void
    {
        $this->assertOrderBelongsToCustomer($order, $customer);

        if ($order->status !== 'draft') {
            abort(403, 'Only draft orders can be edited.');
        }

        if ($order->order_type !== 'point_to_point') {
            abort(403, 'This order type cannot be edited in the customer portal.');
        }
    }
}
