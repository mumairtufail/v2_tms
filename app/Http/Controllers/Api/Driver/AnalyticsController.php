<?php

namespace App\Http\Controllers\Api\Driver;

use App\Enums\ManifestStatus;
use App\Enums\OrderStatus;
use App\Http\Controllers\Controller;
use App\Http\Resources\Driver\DriverOrderStatusHistoryResource;
use App\Models\Manifest;
use App\Models\Order;
use App\Models\OrderStatusHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class AnalyticsController extends Controller
{
    public function overview(Request $request)
    {
        $range = $request->query('range', 'today');
        [$from, $to] = $this->resolveRange($range);
        $driverId = $request->user()->id;

        $manifestsCompleted = Manifest::whereHas('drivers', fn ($q) => $q->where('users.id', $driverId))
            ->where('status', ManifestStatus::Completed->value)
            ->whereBetween('updated_at', [$from, $to])
            ->count();

        $deliveredHistories = OrderStatusHistory::where('changed_by_user_id', $driverId)
            ->where('to_status', OrderStatus::Delivered->value)
            ->whereBetween('created_at', [$from, $to])
            ->with('order.stops')
            ->get();

        $cancelledCount = OrderStatusHistory::where('changed_by_user_id', $driverId)
            ->where('to_status', OrderStatus::Cancelled->value)
            ->whereBetween('created_at', [$from, $to])
            ->count();

        // "On-time" has no explicit promised-delivery field on the order today,
        // so this approximates it against the delivery stop's appointment window
        // (order_stops.end_time) when one exists, and counts it on-time otherwise.
        $onTimeCount = $deliveredHistories->filter(function (OrderStatusHistory $history) {
            $deliveryStop = $history->order?->stops
                ->whereIn('stop_type', ['delivery', 'mixed'])
                ->sortByDesc('sequence_number')
                ->first();

            if (!$deliveryStop || !$deliveryStop->end_time) {
                return true;
            }

            return $history->created_at->lessThanOrEqualTo($deliveryStop->end_time);
        })->count();

        $deliveredCount = $deliveredHistories->count();

        return response()->json([
            'range' => $range,
            'manifests_completed' => $manifestsCompleted,
            'orders_delivered' => $deliveredCount,
            'on_time_percentage' => $deliveredCount > 0 ? (int) round(($onTimeCount / $deliveredCount) * 100) : null,
            'cancelled' => $cancelledCount,
        ]);
    }

    public function statusBreakdown(Request $request)
    {
        $driverId = $request->user()->id;

        $counts = Order::whereHas('manifest.drivers', fn ($q) => $q->where('users.id', $driverId))
            ->selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        $breakdown = collect(OrderStatus::driverWorkflow())
            ->push(OrderStatus::Cancelled)
            ->map(fn (OrderStatus $status) => [
                'status' => $status->value,
                'label' => $status->label(),
                'count' => (int) ($counts[$status->value] ?? 0),
            ])
            ->values();

        return response()->json(['data' => $breakdown]);
    }

    public function history(Request $request)
    {
        $driverId = $request->user()->id;

        $history = OrderStatusHistory::where('changed_by_user_id', $driverId)
            ->with('order:id,order_number')
            ->latest()
            ->paginate(20);

        return DriverOrderStatusHistoryResource::collection($history);
    }

    /**
     * @return array{0: Carbon, 1: Carbon}
     */
    private function resolveRange(string $range): array
    {
        $now = now();

        return match ($range) {
            'week' => [$now->copy()->startOfWeek(), $now->copy()->endOfWeek()],
            'month' => [$now->copy()->startOfMonth(), $now->copy()->endOfMonth()],
            default => [$now->copy()->startOfDay(), $now->copy()->endOfDay()],
        };
    }
}
