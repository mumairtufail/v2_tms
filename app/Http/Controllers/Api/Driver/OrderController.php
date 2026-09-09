<?php

namespace App\Http\Controllers\Api\Driver;

use App\Enums\OrderStatus;
use App\Http\Controllers\Controller;
use App\Http\Resources\Driver\DriverOrderResource;
use App\Models\Manifest;
use App\Models\Order;
use App\Models\OrderStatusHistory;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class OrderController extends Controller
{
    public function show(Request $request, Order $order)
    {
        $this->authorizeOrder($request, $order);

        $order->load(['stops', 'customer']);

        return new DriverOrderResource($order);
    }

    /**
     * Any source -> any of the driver-workflow statuses is allowed (no forced
     * sequence). The pre-dispatch, web-only statuses (draft/new/quoted/no_quote)
     * are rejected via the allow-list — drivers never set those. Cancellation
     * still goes through cancel(), which requires a reason.
     */
    public function updateStatus(Request $request, Order $order)
    {
        $this->authorizeOrder($request, $order);

        $allowedStatuses = collect(OrderStatus::driverWorkflow())
            ->push(OrderStatus::Cancelled)
            ->map(fn (OrderStatus $status) => $status->value)
            ->all();

        $data = $request->validate([
            'status' => ['required', 'string', Rule::in($allowedStatuses)],
            'note' => ['nullable', 'string', 'max:500'],
        ]);

        $current = OrderStatus::tryFrom((string) $order->status);
        $target = OrderStatus::from($data['status']);

        $this->recordTransition($order, $current, $target, $request->user()->id, $data['note'] ?? null);

        return new DriverOrderResource($order->refresh());
    }

    public function cancel(Request $request, Order $order)
    {
        $this->authorizeOrder($request, $order);

        $data = $request->validate([
            'reason' => ['required', 'string', 'max:500'],
        ]);

        $current = OrderStatus::tryFrom((string) $order->status);

        if (!$current || !$current->canTransitionTo(OrderStatus::Cancelled)) {
            return response()->json([
                'message' => 'This order can no longer be cancelled.',
            ], 422);
        }

        $this->recordTransition($order, $current, OrderStatus::Cancelled, $request->user()->id, $data['reason']);

        return new DriverOrderResource($order->refresh());
    }

    private function recordTransition(Order $order, ?OrderStatus $from, OrderStatus $to, int $userId, ?string $note): void
    {
        $order->update(['status' => $to->value]);

        OrderStatusHistory::create([
            'order_id' => $order->id,
            'from_status' => $from?->value,
            'to_status' => $to->value,
            'changed_by_user_id' => $userId,
            'note' => $note,
        ]);

        $driver = \App\Models\User::find($userId);
        if ($driver) {
            app(\App\Services\NotificationService::class)->driverUpdatedOrderStatus(
                $order->fresh(['customer', 'company']),
                $driver,
                $from?->value ?? 'unknown',
                $to->value,
                $note
            );

            $fromLabel = str_replace('_', ' ', $from?->value ?? 'unknown');
            $toLabel = str_replace('_', ' ', $to->value);
            app(\App\Services\ActivityLog::class)->log('driver.order.status', [
                'order_id' => $order->id,
                'company_id' => $order->company_id,
                'description' => "Updated order status from {$fromLabel} to {$toLabel}",
            ]);
        }
    }

    private function authorizeOrder(Request $request, Order $order): void
    {
        // The order can be attached to its manifest either directly (orders.manifest_id)
        // or via its stops (order_stops.manifest_id) — check both.
        $manifestIds = $order->manifests()->pluck('manifests.id');
        if ($order->manifest_id) {
            $manifestIds->push($order->manifest_id);
        }

        abort_unless(
            $manifestIds->isNotEmpty() && Manifest::whereIn('id', $manifestIds->unique())
                ->whereHas('drivers', fn ($q) => $q->where('users.id', $request->user()->id))
                ->exists(),
            404
        );
    }
}
