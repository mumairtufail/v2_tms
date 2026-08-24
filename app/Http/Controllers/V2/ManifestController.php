<?php

namespace App\Http\Controllers\V2;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\Manifest;
use App\Services\ManifestService;
use App\Support\Toast;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;

class ManifestController extends Controller
{
    protected $manifestService;

    public function __construct(ManifestService $manifestService)
    {
        $this->manifestService = $manifestService;
    }

    public function index(Request $request, Company $company)
    {
        $filters = $request->only(['search', 'status']);
        $manifests = $this->manifestService->getManifests($company->id, $filters);

        return view('v2.company.manifests.index', compact('company', 'manifests'));
    }

    public function store(Request $request, Company $company)
    {
        $request->validate([
            'start_date' => 'required|date',
        ]);

        // Auto-set status to pending if not provided
        $data = $request->all();
        $data['status'] = $data['status'] ?? 'pending';
        
        $manifest = $this->manifestService->createManifest($company->id, $data);

        // Redirect to edit page to add resources
        Toast::success('Manifest created successfully. Add drivers, equipment, and carriers below.');
        return redirect()
            ->route('v2.manifests.edit', ['company' => $company->slug, 'manifest' => $manifest->id]);
    }

    public function quickCreate(Request $request, Company $company)
    {
        // Default to today and pending status
        $data = [
            'start_date' => now()->toDateString(),
            'status' => 'pending'
        ];
        
        try {
            $manifest = $this->manifestService->createManifest($company->id, $data);
            
            return response()->json([
                'success' => true,
                'manifest' => [
                    'id' => $manifest->id,
                    'code' => $manifest->code
                ],
                'message' => 'Manifest created successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create manifest: ' . $e->getMessage()
            ], 500);
        }
    }

    public function edit(Company $company, Manifest $manifest)
    {
        $manifest->load([
            'drivers',
            'carriers',
            'equipments',
            'stops',
            'orderStops.order.customer',
            'orderStops.order.quote.costs',
            'orderStops.commodities',
            'orderStops.accessorials',
        ]);
        
        $drivers = \App\Models\User::where('company_id', $company->id)
            ->where('is_active', true)
            ->where('is_deleted', false)
            ->whereHas('roles', fn($q) => $q->where('name', 'driver'))
            ->get();

        $carriers = \App\Models\Carrier::where('company_id', $company->id)
            ->where('is_active', true)
            ->get();

        $equipment = \App\Models\Equipment::where('company_id', $company->id)
            ->where('status', 'Available')
            ->get();

        return view('v2.company.manifests.edit', compact('company', 'manifest', 'drivers', 'carriers', 'equipment'));
    }

    public function update(Request $request, Company $company, Manifest $manifest)
    {
        $this->manifestService->updateManifest($manifest, $request->all());
        Toast::success('Manifest updated successfully.');
        return back();
    }

    public function destroy(Company $company, Manifest $manifest)
    {
        $this->manifestService->deleteManifest($manifest);
        Toast::success('Manifest deleted successfully.');
        return redirect()->route('v2.manifests.index', $company);
    }

    public function bulkDestroy(Request $request, Company $company)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:manifests,id'
        ]);

        $ids = $request->input('ids');
        $count = 0;

        foreach ($ids as $id) {
            $manifest = Manifest::where('company_id', $company->id)->find($id);
            if ($manifest) {
                $this->manifestService->deleteManifest($manifest);
                $count++;
            }
        }

        Toast::success("{$count} manifest(s) deleted successfully.");
        return back();
    }

    public function assignDriver(Request $request, Company $company, Manifest $manifest)
    {
        $request->validate(['driver_id' => 'required|exists:users,id']);
        $this->manifestService->assignDriver($manifest, $request->driver_id);
        Toast::success('Driver assigned successfully.');
        return back();
    }

    public function removeDriver(Request $request, Company $company, Manifest $manifest, $driverId)
    {
        $this->manifestService->removeDriver($manifest, $driverId);
        Toast::success('Driver removed successfully.');
        return back();
    }
    
    public function assignCarrier(Request $request, Company $company, Manifest $manifest)
    {
        $request->validate(['carrier_id' => 'required|exists:carriers,id']);
        $this->manifestService->assignCarrier($manifest, $request->carrier_id);
        Toast::success('Carrier assigned successfully.');
        return back();
    }

    public function removeCarrier(Request $request, Company $company, Manifest $manifest, $carrierId)
    {
        $this->manifestService->removeCarrier($manifest, $carrierId);
        Toast::success('Carrier removed successfully.');
        return back();
    }

    public function assignEquipment(Request $request, Company $company, Manifest $manifest)
    {
        $request->validate(['equipment_id' => 'required|exists:equipment,id']);
        $this->manifestService->assignEquipment($manifest, $request->equipment_id);
        Toast::success('Equipment assigned successfully.');
        return back();
    }

    public function removeEquipment(Request $request, Company $company, Manifest $manifest, $equipmentId)
    {
        $this->manifestService->removeEquipment($manifest, $equipmentId);
        Toast::success('Equipment removed successfully.');
        return back();
    }

    public function addStop(Request $request, Company $company, Manifest $manifest)
    {
        $request->validate([
            'location' => 'required|string',
            'city' => 'required|string',
            'state' => 'required|string',
        ]);
        $this->manifestService->addStop($manifest, $request->all());
        Toast::success('Stop added successfully.');
        return back();
    }

    public function removeStop(Request $request, Company $company, Manifest $manifest, $stopId)
    {
        $this->manifestService->removeStop($stopId);
        return back()->with('success', 'Stop removed successfully.');
    }

    // API Endpoints for Modals
    
    public function availableDrivers(Company $company, Manifest $manifest)
    {
        $allDrivers = \App\Models\User::where('company_id', $company->id)
            ->where('is_active', true)
            ->where('is_deleted', false)
            ->whereHas('roles', fn($q) => $q->where('name', 'driver'))
            ->get(['id', 'f_name', 'l_name', 'email'])
            ->map(function($user) {
                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email
                ];
            });
        
        $assigned = $manifest->drivers->map(function($driver) {
            return [
                'id' => $driver->id,
                'name' => $driver->name
            ];
        });

        return response()->json([
            'success' => true,
            'drivers' => $allDrivers,
            'assigned' => $assigned
        ]);
    }

    public function syncDrivers(Request $request, Company $company, Manifest $manifest)
    {
        $driverIds = $request->input('driver_ids', []);
        $manifest->drivers()->sync($driverIds);
        Toast::success(count($driverIds) . ' driver(s) assigned successfully.');

        return response()->json([
            'success' => true,
            'message' => 'Drivers synced successfully'
        ]);
    }

    public function availableEquipment(Company $company, Manifest $manifest)
    {
        $assignedEquipmentIds = $manifest->equipments->pluck('id');

        $allEquipment = \App\Models\Equipment::where('company_id', $company->id)
            ->where(function ($query) use ($assignedEquipmentIds) {
                $query->where('status', 'Available')
                    ->orWhereIn('id', $assignedEquipmentIds);
            })
            ->get(['id', 'name', 'type', 'status']);
        
        $assigned = $manifest->equipments->map(function($eq) {
            return [
                'id' => $eq->id,
                'name' => $eq->name
            ];
        });

        return response()->json([
            'success' => true,
            'equipment' => $allEquipment,
            'assigned' => $assigned
        ]);
    }

    /**
     * Download the Rate Confirmation PDF for the manifest.
     */
    public function downloadRateConfirmation(Request $request, Company $company, Manifest $manifest)
    {
        $manifest->load([
            'carriers',
            'stops',
            'costEstimates',
            'orderStops.order.customer',
            'orderStops.commodities',
        ]);

        $pdf = Pdf::loadView('v2.company.manifests.pdf.rate-confirmation', [
            'manifest' => $manifest,
            'company' => $company,
            'user' => auth()->user(),
        ]);

        // Standard setup for TMS documents
        $pdf->setPaper('a4', 'portrait');
        $pdf->setOption(['isRemoteEnabled' => true, 'isHtml5ParserEnabled' => true]);

        $filename = "Rate-Confirmation-{$manifest->code}.pdf";

        // ?preview=1 opens the PDF in the browser (for printing) instead of downloading
        if ($request->boolean('preview')) {
            return $pdf->stream($filename);
        }

        return $pdf->download($filename);
    }

    public function syncEquipment(Request $request, Company $company, Manifest $manifest)
    {
        $equipmentIds = collect($request->input('equipment_ids', []))->map(fn ($id) => (int) $id);
        $previouslyAssigned = $manifest->equipments()->pluck('equipment.id');

        $manifest->equipments()->sync($equipmentIds);

        if ($equipmentIds->isNotEmpty()) {
            \App\Models\Equipment::whereIn('id', $equipmentIds)->update(['status' => 'In Use']);
        }

        $previouslyAssigned->diff($equipmentIds)->each(function (int $equipmentId) {
            $stillAssigned = \App\Models\ManifestEquipment::where('equipment_id', $equipmentId)->exists();

            if (! $stillAssigned) {
                \App\Models\Equipment::where('id', $equipmentId)->update(['status' => 'Available']);
            }
        });

        Toast::success($equipmentIds->count().' equipment assigned successfully.');

        return response()->json([
            'success' => true,
            'message' => 'Equipment synced successfully'
        ]);
    }

    public function availableCarriers(Company $company, Manifest $manifest)
    {
        $allCarriers = \App\Models\Carrier::where('company_id', $company->id)
            ->where('is_active', true)
            ->get(['id', 'carrier_name', 'dot_id', 'docket_number']);
        
        $assigned = $manifest->carriers->map(function($carrier) {
            return [
                'id' => $carrier->id,
                'carrier_name' => $carrier->carrier_name
            ];
        });

        return response()->json([
            'success' => true,
            'carriers' => $allCarriers,
            'assigned' => $assigned
        ]);
    }

    public function syncCarriers(Request $request, Company $company, Manifest $manifest)
    {
        $carrierIds = $request->input('carrier_ids', []);
        $manifest->carriers()->sync($carrierIds);
        Toast::success(count($carrierIds) . ' carrier(s) assigned successfully.');

        return response()->json([
            'success' => true,
            'message' => 'Carriers synced successfully'
        ]);
    }

    /**
     * Add cost estimates to a manifest from order processing.
     */
    public function addCostEstimates(Request $request, Company $company, Manifest $manifest)
    {
        $validated = $request->validate([
            'cost_estimates' => 'required|array',
            'cost_estimates.*.type' => 'nullable|string',
            'cost_estimates.*.description' => 'nullable|string',
            'cost_estimates.*.cost' => 'nullable|numeric',
            'cost_estimates.*.percentage' => 'nullable|numeric|min:0|max:100',
        ]);

        $freightSubtotal = collect($validated['cost_estimates'])
            ->filter(function ($estimate) {
                return strtolower((string) ($estimate['type'] ?? '')) !== 'fuel';
            })
            ->sum(function ($estimate) {
                return (float) ($estimate['cost'] ?? 0);
            });

        DB::transaction(function () use ($manifest, $validated, $freightSubtotal) {
            // Replace existing rows so order->manifest sync stays deterministic.
            $manifest->costEstimates()->delete();

            foreach ($validated['cost_estimates'] as $estimate) {
                $type = strtolower((string) ($estimate['type'] ?? 'freight'));
                $description = (string) ($estimate['description'] ?? '');
                $cost = (float) ($estimate['cost'] ?? 0);
                $percentage = isset($estimate['percentage']) ? (float) $estimate['percentage'] : null;

                if ($type === 'fuel') {
                    $pct = max(0, min(100, $percentage ?? 0));
                    $computedCost = round($freightSubtotal * ($pct / 100), 2);

                    if ($computedCost <= 0 && $description === '' && $pct <= 0) {
                        continue;
                    }

                    $manifest->costEstimates()->create([
                        'type' => 'fuel',
                        'description' => $description,
                        'qty' => $pct,
                        'rate' => $freightSubtotal,
                        'est_cost' => $computedCost,
                    ]);
                    continue;
                }

                if ($cost <= 0 && $description === '') {
                    continue;
                }

                $manifest->costEstimates()->create([
                    'type' => $type ?: 'freight',
                    'description' => $description,
                    'qty' => 1,
                    'rate' => $cost,
                    'est_cost' => $cost,
                ]);
            }

            $this->manifestService->syncOrderQuotesFromManifestCosts($manifest);
        });

        return response()->json(['success' => true, 'message' => 'Cost estimates added successfully']);
    }
}
