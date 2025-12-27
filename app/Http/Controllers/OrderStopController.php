<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderStop;
use App\Models\OrderStopShipper;
use App\Models\OrderStopConsignee;
use App\Models\OrderStopCommodity;
use App\Models\Accessorial;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class OrderStopController extends Controller
{
    /**
     * Store a new stop for an order
     */
    public function store(Request $request, $orderId)
    {
        $request->validate([
            'stop_name' => 'required|string|max:255',
            'stop_type' => 'required|in:pickup,delivery,pickup_delivery',
            'sequence_number' => 'integer|min:1',
        ]);

        $order = Order::findOrFail($orderId);
        
        // Ensure the order belongs to the current user's company
        if ($order->company_id !== Auth::user()->company_id) {
            abort(403);
        }

        $stop = $order->stops()->create([
            'stop_name' => $request->stop_name,
            'stop_type' => $request->stop_type,
            'sequence_number' => $request->sequence_number ?? ($order->stops()->max('sequence_number') + 1),
            'scheduled_date' => $request->scheduled_date,
            'scheduled_time_from' => $request->scheduled_time_from,
            'scheduled_time_to' => $request->scheduled_time_to,
            'is_appointment_required' => $request->boolean('is_appointment_required'),
            'special_instructions' => $request->special_instructions,
            'reference_number' => $request->reference_number,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Stop created successfully',
            'stop' => $stop->load(['shippers', 'consignees', 'commodities', 'accessorials'])
        ]);
    }

    /**
     * Update a stop
     */
    public function update(Request $request, $orderId, $stopId)
    {
        $request->validate([
            'stop_name' => 'required|string|max:255',
            'stop_type' => 'required|in:pickup,delivery,pickup_delivery',
        ]);

        $order = Order::findOrFail($orderId);
        $stop = $order->stops()->findOrFail($stopId);
        
        // Ensure the order belongs to the current user's company
        if ($order->company_id !== Auth::user()->company_id) {
            abort(403);
        }

        $stop->update($request->only([
            'stop_name',
            'stop_type',
            'sequence_number',
            'location_name',
            'address_1',
            'address_2',
            'city',
            'state',
            'postal_code',
            'country',
            'latitude',
            'longitude',
            'scheduled_date',
            'scheduled_time_from',
            'scheduled_time_to',
            'is_appointment_required',
            'special_instructions',
            'reference_number',
        ]));

        return response()->json([
            'success' => true,
            'message' => 'Stop updated successfully',
            'stop' => $stop->load(['shippers', 'consignees', 'commodities', 'accessorials'])
        ]);
    }

    /**
     * Delete a stop
     */
    public function destroy($orderId, $stopId)
    {
        $order = Order::findOrFail($orderId);
        $stop = $order->stops()->findOrFail($stopId);
        
        // Ensure the order belongs to the current user's company
        if ($order->company_id !== Auth::user()->company_id) {
            abort(403);
        }

        $stop->delete();

        return response()->json([
            'success' => true,
            'message' => 'Stop deleted successfully'
        ]);
    }

    /**
     * Add shipper to a stop
     */
    public function addShipper(Request $request, $orderId, $stopId)
    {
        $request->validate([
            'company_name' => 'required|string|max:255',
            'address_1' => 'required|string|max:255',
            'city' => 'required|string|max:255',
        ]);

        $order = Order::findOrFail($orderId);
        $stop = $order->stops()->findOrFail($stopId);
        
        if ($order->company_id !== Auth::user()->company_id) {
            abort(403);
        }

        $shipper = $stop->shippers()->create($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Shipper added successfully',
            'shipper' => $shipper
        ]);
    }

    /**
     * Add consignee to a stop
     */
    public function addConsignee(Request $request, $orderId, $stopId)
    {
        $request->validate([
            'company_name' => 'required|string|max:255',
            'address_1' => 'required|string|max:255',
            'city' => 'required|string|max:255',
        ]);

        $order = Order::findOrFail($orderId);
        $stop = $order->stops()->findOrFail($stopId);
        
        if ($order->company_id !== Auth::user()->company_id) {
            abort(403);
        }

        $consignee = $stop->consignees()->create($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Consignee added successfully',
            'consignee' => $consignee
        ]);
    }

    /**
     * Add commodity to a stop
     */
    public function addCommodity(Request $request, $orderId, $stopId)
    {
        $request->validate([
            'description' => 'required|string|max:255',
            'service_type' => 'required|in:LTL,FTL,Partial',
            'quantity' => 'required|integer|min:1',
            'weight' => 'required|numeric|min:0',
        ]);

        $order = Order::findOrFail($orderId);
        $stop = $order->stops()->findOrFail($stopId);
        
        if ($order->company_id !== Auth::user()->company_id) {
            abort(403);
        }

        $commodity = $stop->commodities()->create($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Commodity added successfully',
            'commodity' => $commodity
        ]);
    }

    /**
     * Sync accessorials for a stop
     */
    public function syncAccessorials(Request $request, $orderId, $stopId)
    {
        $request->validate([
            'accessorials' => 'array',
            'accessorials.*' => 'exists:accessorials,id',
        ]);

        $order = Order::findOrFail($orderId);
        $stop = $order->stops()->findOrFail($stopId);
        
        if ($order->company_id !== Auth::user()->company_id) {
            abort(403);
        }

        $stop->accessorials()->sync($request->accessorials ?? []);

        return response()->json([
            'success' => true,
            'message' => 'Accessorials updated successfully'
        ]);
    }
}
