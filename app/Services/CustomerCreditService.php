<?php

namespace App\Services;

use App\Models\Customer;
use App\Models\Order;

/**
 * How much of a customer's credit limit is already spoken for.
 *
 * Credit is consumed once work is committed — an order that has been booked and is
 * moving — and stays consumed after it is invoiced until the invoice is paid:
 *
 *   used = charges on booked-or-later orders not yet invoiced
 *        + the unpaid invoice balance from QuickBooks
 *
 * Splitting it that way means an order is never counted twice: while it is ours it
 * counts as committed work, and once QuickBooks owns it, it counts as receivable.
 * Drafts and quotes never consume credit, since the customer has not committed to them.
 */
class CustomerCreditService
{
    /** Work the customer has committed to: from booking through delivery. */
    public const COMMITTED_STATUSES = [
        'booked',
        'warehousing',
        'picked_up',
        'in_transit',
        'delivered',
    ];

    public function usage(Customer $customer): array
    {
        $limit = $customer->credit_limit !== null ? (float) $customer->credit_limit : null;

        $committed = (float) Order::query()
            ->where('customer_id', $customer->id)
            ->whereIn('status', self::COMMITTED_STATUSES)
            ->whereNull('quickbooks_invoice_id')
            ->with('quote.costs')
            ->get()
            ->sum(fn (Order $order) => (float) ($order->quote?->costs->where('category', 'customer')->sum('cost') ?? 0));

        // Invoiced and still unpaid, as last synced from QuickBooks
        $invoiced = (float) ($customer->credit_balance ?? 0);
        $used = round($committed + $invoiced, 2);

        return [
            'has_limit' => $limit !== null,
            'limit' => $limit,
            'used' => $used,
            'committed' => round($committed, 2),
            'invoiced' => round($invoiced, 2),
            'available' => $limit !== null ? round($limit - $used, 2) : null,
            'over' => $limit !== null && $used >= $limit,
            'percent' => ($limit !== null && $limit > 0) ? min(100, (int) round(($used / $limit) * 100)) : null,
        ];
    }

    /** True when the customer has a limit and has already reached it. */
    public function isOverLimit(?Customer $customer): bool
    {
        return $customer ? $this->usage($customer)['over'] : false;
    }
}
