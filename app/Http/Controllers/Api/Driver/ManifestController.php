<?php

namespace App\Http\Controllers\Api\Driver;

use App\Enums\ManifestStatus;
use App\Enums\OrderStatus;
use App\Http\Controllers\Controller;
use App\Http\Resources\Driver\DriverManifestResource;
use App\Models\Manifest;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ManifestController extends Controller
{
    public function index(Request $request)
    {
        $query = $this->driverManifests($request)->withCount(['directOrders', 'orders']);

        if ($status = $request->query('status')) {
            $query->where('status', $status);
        }

        return DriverManifestResource::collection(
            $query->orderByDesc('start_date')->paginate(15)
        );
    }

    public function show(Request $request, Manifest $manifest)
    {
        $this->authorizeManifest($request, $manifest);

        // Orders can be attached to a manifest either directly (orders.manifest_id)
        // or via their stops (order_stops.manifest_id) — merge both so none are missed.
        $manifest->load(['directOrders.customer', 'directOrders.stops', 'orders.customer', 'orders.stops']);
        $manifest->setRelation(
            'orders',
            $manifest->directOrders->merge($manifest->orders)->unique('id')->values()
        );

        return new DriverManifestResource($manifest);
    }

    public function start(Request $request, Manifest $manifest)
    {
        $this->authorizeManifest($request, $manifest);

        if (!in_array($manifest->status, [ManifestStatus::Pending->value, ManifestStatus::Dispatched->value], true)) {
            return response()->json([
                'message' => 'This manifest cannot be started from its current status.',
            ], 422);
        }

        $manifest->update(['status' => ManifestStatus::InTransit->value]);

        $manifest->loadMissing('company');
        if ($manifest->company) {
            app(\App\Services\NotificationService::class)->driverUpdatedManifestStatus(
                $manifest,
                $request->user(),
                ManifestStatus::InTransit->value,
                $manifest->company
            );
        }

        return new DriverManifestResource($manifest);
    }

    public function complete(Request $request, Manifest $manifest)
    {
        $this->authorizeManifest($request, $manifest);

        if ($manifest->status !== ManifestStatus::InTransit->value) {
            return response()->json([
                'message' => 'Only an in-transit manifest can be completed.',
            ], 422);
        }

        if ($this->hasUnresolvedOrders($manifest)) {
            return response()->json([
                'message' => 'Complete all orders before completing the manifest.',
            ], 422);
        }

        $manifest->update(['status' => ManifestStatus::Completed->value]);

        $manifest->loadMissing('company');
        if ($manifest->company) {
            app(\App\Services\NotificationService::class)->driverUpdatedManifestStatus(
                $manifest,
                $request->user(),
                ManifestStatus::Completed->value,
                $manifest->company
            );
        }

        return new DriverManifestResource($manifest);
    }

    /**
     * Generic status update: any source -> any target status is allowed, unlike
     * start()/complete() which each only accept one fixed transition. `note` is
     * accepted for forward-compatibility but isn't persisted anywhere yet — there's
     * no manifest-level status history table (unlike orders' OrderStatusHistory).
     */
    public function updateStatus(Request $request, Manifest $manifest)
    {
        $this->authorizeManifest($request, $manifest);

        $data = $request->validate([
            'status' => ['required', 'string', Rule::in(array_column(ManifestStatus::cases(), 'value'))],
            'note' => ['nullable', 'string', 'max:500'],
        ]);

        $target = ManifestStatus::from($data['status']);

        if ($target === ManifestStatus::Completed && $this->hasUnresolvedOrders($manifest)) {
            return response()->json([
                'message' => 'Complete all orders before completing the manifest.',
            ], 422);
        }

        $manifest->update(['status' => $target->value]);

        $manifest->loadMissing('company');
        if ($manifest->company) {
            app(\App\Services\NotificationService::class)->driverUpdatedManifestStatus(
                $manifest,
                $request->user(),
                $target->value,
                $manifest->company
            );
        }

        $manifest->load(['directOrders.customer', 'directOrders.stops', 'orders.customer', 'orders.stops']);
        $manifest->setRelation(
            'orders',
            $manifest->directOrders->merge($manifest->orders)->unique('id')->values()
        );

        return new DriverManifestResource($manifest);
    }

    private function hasUnresolvedOrders(Manifest $manifest): bool
    {
        $orderIds = $manifest->directOrders()->pluck('id')
            ->merge($manifest->orders()->pluck('orders.id'))
            ->unique();

        return Order::whereIn('id', $orderIds)
            ->whereNotIn('status', [OrderStatus::Delivered->value, OrderStatus::Cancelled->value])
            ->exists();
    }

    private function driverManifests(Request $request)
    {
        return Manifest::query()
            ->whereHas('drivers', fn ($q) => $q->where('users.id', $request->user()->id));
    }

    private function authorizeManifest(Request $request, Manifest $manifest): void
    {
        abort_unless(
            $manifest->drivers()->where('users.id', $request->user()->id)->exists(),
            404
        );
    }
}
