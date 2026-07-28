<?php

namespace App\Http\Controllers\Api\Driver;

use App\Enums\OrderStatus;
use App\Http\Controllers\Controller;
use App\Http\Resources\Driver\DriverOrderResource;
use App\Models\Manifest;
use App\Models\Order;
use App\Models\OrderStatusHistory;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function show(Request $request, Order $order)
    {
        $this->authorizeOrder($request, $order);

        $order->load(['stops', 'customer']);

        return new DriverOrderResource($order);
    }

    public function updateStatus(Request $request, Order $order)
    {
        $this->authorizeOrder($request, $order);

        $data = $request->validate([
            'status' => ['required', 'string'],
            'note' => ['nullable', 'string', 'max:500'],
        ]);

        $current = OrderStatus::tryFrom((string) $order->status);
        $target = OrderStatus::tryFrom($data['status']);

        if (!$current || !$target || !$current->canTransitionTo($target)) {
            return response()->json([
                'message' => "That status change isn't allowed from the order's current status.",
            ], 422);
        }

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

    private function recordTransition(Order $order, OrderStatus $from, OrderStatus $to, int $userId, ?string $note): void
    {
        $order->update(['status' => $to->value]);

        OrderStatusHistory::create([
            'order_id' => $order->id,
            'from_status' => $from->value,
            'to_status' => $to->value,
            'changed_by_user_id' => $userId,
            'note' => $note,
        ]);
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
