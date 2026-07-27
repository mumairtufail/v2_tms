<?php

namespace App\Http\Controllers\Api\Driver;

use App\Enums\ManifestStatus;
use App\Enums\OrderStatus;
use App\Http\Controllers\Controller;
use App\Http\Resources\Driver\DriverManifestResource;
use App\Models\Manifest;
use Illuminate\Http\Request;

class ManifestController extends Controller
{
    public function index(Request $request)
    {
        $query = $this->driverManifests($request)->withCount('directOrders');

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

        $manifest->load(['directOrders.customer']);

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

        $unresolvedOrders = $manifest->directOrders()
            ->whereNotIn('status', [OrderStatus::Delivered->value, OrderStatus::Cancelled->value])
            ->exists();

        if ($unresolvedOrders) {
            return response()->json([
                'message' => 'Complete all orders before completing the manifest.',
            ], 422);
        }

        $manifest->update(['status' => ManifestStatus::Completed->value]);

        return new DriverManifestResource($manifest);
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
