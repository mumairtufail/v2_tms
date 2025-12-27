<?php

namespace App\Http\Controllers;

use App\Models\Equipment;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class EquipmentController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $query = Equipment::where('company_id', $user->company_id);

        // Apply search filter
        if ($request->has('search')) {
            $search = $request->get('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('desc', 'like', "%{$search}%")
                    ->orWhere('last_location', 'like', "%{$search}%");
            });
        }

        // Apply type filter
        if ($request->has('type') && $request->type != '') {
            $query->where('type', $request->type);
        }

        // Apply status filter
        if ($request->has('status') && $request->status != '') {
            $query->where('status', $request->status);
        }

        // Get page size from request or default to 15
        $perPage = $request->get('per_page', 15);
        $perPage = in_array($perPage, [10, 15, 25, 50, 100]) ? $perPage : 15;
        
        $equipments = $query->orderBy('name')->paginate($perPage);
        
        // Append query parameters to pagination links
        $equipments->appends($request->query());

        return view('dashboard.equipment.index', compact('equipments'));
    }

    public function create()
    {
        return view('dashboard.equipment.create');
    }
    public function store(Request $request)
    {
        // Validate the request data
        $validator = Validator::make($request->all(), [
            'name' => 'required|string',  // Ensure 'name' is a string and required
            'desc' => 'nullable|string',  // Nullable and string type
            'type' => 'required|string',  // Ensure 'type' is required and a string
            'sub_type' => 'nullable|string', // Nullable and string type
            'status' => 'nullable|string', // If status is a string, validate as such
            'company_id' => 'nullable|exists:companies,id', // Validate company_id exists in the companies table
            'manifest_id' => 'nullable|exists:manifests,id', // Validate manifest_id exists in the manifests table
            'last_seen' => 'nullable|string',  // Nullable and string type for 'last_seen'
            'last_location' => 'nullable|string', // Nullable and string type for 'last_location'
        ]);

        // Check if validation fails
        if ($validator->fails()) {
            // Return the validation error message
            $errorMessage = implode(', ', $validator->errors()->all());
            return back()->with('error', 'Failed to store. Please try again. ' . $errorMessage);
        }

        // Get the validated data
        $validated = $validator->validated();

        try {
            // Attach the authenticated user's company_id to the validated data
            $company_id = Auth::user()->company_id;
            $validated['company_id'] = $company_id;
            $equipment = Equipment::create($validated);

            $source = $request->input('source');
            $manifestId = $request->input('manifest_id');
            
            if ($source === 'manifest.edit' && $manifestId) {
                return redirect()->route('manifest.edit', ['id' => $manifestId])
                    ->with('success', 'Equipment created successfully and returned to Manifest Edit.');
            } elseif ($source === 'manifest.create') {
                return redirect()->route('manifest.create')
                    ->with('success', 'Equipment created successfully and returned to Manifest Create.');
            }

            return redirect()->route('equipment.index')->with('success', 'Equipment Created successfully!');
        } catch (Exception $e) {
            // Catch any exceptions and return an error message
            return redirect()->back()->with('error', 'Error occurred: ' . $e->getMessage());
        }
    }


    public function edit($id)
    {
        $equipment = Equipment::findOrFail($id);
        return view('dashboard.equipment.edit', compact('equipment'));
    }
    public function update($id, Request $request)
    {
        // Validate the request data
        $validator = Validator::make($request->all(), [
            'name' => 'required|string',  // Ensure 'name' is a string and required
            'desc' => 'nullable|string',  // Nullable and string type
            'type' => 'required|string',  // Ensure 'type' is required and a string
            'sub_type' => 'nullable|string', // Nullable and string type for 'sub_type'
            'status' => 'nullable|string', // If status is a string, validate as such
            'company_id' => 'nullable|exists:companies,id', // Validate company_id exists in the companies table
            'manifest_id' => 'nullable|exists:manifests,id', // Validate manifest_id exists in the manifests table
            'last_seen' => 'nullable|string',  // Nullable and string type for 'last_seen'
            'last_location' => 'nullable|string', // Nullable and string type for 'last_location'
        ]);

        // Check if validation fails
        if ($validator->fails()) {
            // Return the validation error message
            $errorMessage = implode(', ', $validator->errors()->all());
            return back()->with('error', 'Failed to update. Please try again. ' . $errorMessage);
        }

        // Get the validated data
        $validated = $validator->validated();

        try {
            // Find the equipment by ID or fail if not found
            $equipment = Equipment::findOrFail($id);

            // Update the equipment record with validated data
            $equipment->update($validated);

            // Optionally, return a success message or redirect
            return redirect()->route('equipment.index')->with('success', 'Equipment updated successfully!');
        } catch (Exception $e) {
            // Catch any exceptions and return an error message
            return redirect()->back()->with('error', 'Error occurred: ' . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        $equipment = Equipment::findOrFail($id);
        $equipment->delete();
        return redirect()->route('equipment.index')->with('success', 'Equipment deleted successfully.');
    }
}
