<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\CostEstimate;
use App\Models\Cost;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Redirect;
use App\Models\Manifest;

class CostEstimateController extends Controller
{
    //
    public function store(Request $request)
    {
        $request->validate([
            'manifest_id' => 'required|integer',
            'estimates' => 'required|array',
            'estimates.*.type' => 'nullable|string',
            'estimates.*.description' => 'nullable|string',
            'estimates.*.qty' => 'nullable|numeric',
            'estimates.*.rate' => 'nullable|numeric',
        ]);
    
        foreach ($request->estimates as $estimate) {
            $estCost = ($estimate['qty'] ?? 0) * ($estimate['rate'] ?? 0);
    
            \App\Models\CostEstimate::create([
                'manifest_id' => $request->manifest_id,
                'type' => $estimate['type'],
                'description' => $estimate['description'],
                'qty' => $estimate['qty'],
                'rate' => $estimate['rate'],
                'est_cost' => $estCost,
            ]);
        }
    
        return response()->json(['success' => true]);
    }

    public function update(Request $request, $manifest_id)
    {
        $validated = $request->validate([
            'type' => 'required|array',
            'description' => 'required|array',
            'qty' => 'required|array',
            'rate' => 'required|array',
        ]);

        DB::beginTransaction();
        try {
            // Delete existing cost estimates for this manifest
            CostEstimate::where('manifest_id', $manifest_id)->delete();
            
            // Loop through all form inputs and create new cost estimates
            $count = count($validated['type']);
            
            for ($i = 0; $i < $count; $i++) {
                // Calculate estimated cost
                $estCost = ($validated['qty'][$i] ?? 0) * ($validated['rate'][$i] ?? 0);
                
                // Create new cost estimate
                CostEstimate::create([
                    'manifest_id' => $manifest_id,
                    'type' => $validated['type'][$i],
                    'description' => $validated['description'][$i],
                    'qty' => $validated['qty'][$i],
                    'rate' => $validated['rate'][$i],
                    'est_cost' => $estCost,
                ]);
            }

            $manifest = Manifest::findOrFail($request->manifest_id);
            if ($manifest->draft == 1) {
                $manifest->draft = 0;
                $manifest->save();
            }
            
            DB::commit();
            
            return redirect()->route('manifest.edit', $manifest_id)
                             ->with('success', 'Cost estimates updated successfully!');
            
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error("Cost Estimate Update Error: " . $e->getMessage());
            
            return redirect()->back()->with('error', 'Failed to update cost estimates: ' . $e->getMessage());
        }
    }
}
