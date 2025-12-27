<?php

namespace App\Http\Controllers;

use App\Models\Carrier;
use Illuminate\Http\Request;

class CarrierController extends Controller
{
    public function index(Request $request)
    {
        $query = Carrier::query();

        // Apply filters
        if ($request->filled('search')) {
            $query->where('carrier_name', 'LIKE', '%' . $request->search . '%');
        }

        if ($request->filled('dot_id')) {
            $query->where('dot_id', 'LIKE', '%' . $request->dot_id . '%');
        }

        if ($request->filled('currency')) {
            $query->where('currency', $request->currency);
        }

        if ($request->filled('country')) {
            $query->where('country', $request->country);
        }

        if ($request->filled('status')) {
            $query->where('is_active', $request->status);
        }

        // Sort in descending order
        $query->orderBy('created_at', 'desc');

        // Get page size from request or default to 15
        $perPage = $request->get('per_page', 15);
        $perPage = in_array($perPage, [10, 15, 25, 50, 100]) ? $perPage : 15;

        $carriers = $query->paginate($perPage);
        
        // Append query parameters to pagination links
        $carriers->appends($request->query());
        
        return view('dashboard.carriers.index', compact('carriers'));
    }

    public function create()
    {
        return view('dashboard.carriers.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'carrier_name' => 'required|string|max:255',
            'dot_id' => 'nullable|string|max:50',
            'docket_number' => 'nullable|string|max:50',
            'address_1' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'country' => 'nullable|string|max:50',
            'post_code' => 'nullable|string|max:20',
            'currency' => 'nullable|string|max:10',
            'is_active' => 'sometimes|boolean',
        ]);

        // Handle the checkbox value properly
        $validated['is_active'] = $request->has('is_active') ? 1 : 0;

        try {
            Carrier::create($validated);

            $source = $request->input('source');
            $manifestId = $request->input('manifest_id');

            if ($source === 'manifest.edit' && $manifestId) {
                return redirect()->route('manifest.edit', ['id' => $manifestId])
                    ->with('success', 'Carrier created successfully');
            } elseif ($source === 'manifest.create') {
                return redirect()->route('manifest.create')
                    ->with('success', 'Carrier created successfully.');
            }

            return redirect()->route('carriers.index')->with('success', 'Carrier created successfully.');
        } catch (\Exception $e) {
            return redirect()->back()->withInput()->with('error', 'Failed to create carrier. ' . $e->getMessage());
        }
    }

    public function edit($id)
    {
        $carrier = Carrier::findOrFail($id);
        return view('dashboard.carriers.edit', compact('carrier'));
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'carrier_name' => 'required|string|max:255',
            'dot_id' => 'nullable|string|max:50',
            'docket_number' => 'nullable|string|max:50',
            'address_1' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'country' => 'nullable|string|max:50',
            'post_code' => 'nullable|string|max:20',
            'currency' => 'nullable|string|max:10',
            'is_active' => 'sometimes|boolean',
        ]);

        // Handle the checkbox value properly
        $validated['is_active'] = $request->has('is_active') ? 1 : 0;

        try {
            $carrier = Carrier::findOrFail($id);
            $carrier->update($validated);
            return redirect()->route('carriers.index')->with('success', 'Carrier updated successfully.');
        } catch (\Exception $e) {
            return redirect()->back()->withInput()->with('error', 'Failed to update carrier. ' . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        try {
            Carrier::destroy($id);
            return redirect()->route('carriers.index')->with('success', 'Carrier deleted successfully.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to delete carrier. ' . $e->getMessage());
        }
    }
}
