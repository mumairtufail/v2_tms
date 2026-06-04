<?php

namespace App\Http\Controllers\V2;

use App\Http\Controllers\Controller;
use App\Http\Requests\V2\EquipmentRequest;
use App\Models\Company;
use App\Models\Equipment;
use App\Services\EquipmentService;
use Illuminate\Http\Request;

class EquipmentController extends Controller
{
    protected $equipmentService;

    public function __construct(EquipmentService $equipmentService)
    {
        $this->equipmentService = $equipmentService;
    }

    public function index(Request $request, Company $company)
    {
        $equipment = $this->equipmentService->getEquipment([
            'company_id' => $company->id,
            'search' => $request->search,
            'status' => $request->status,
        ]);

        return view('v2.company.equipment.index', compact('equipment', 'company'));
    }

    public function create(Company $company)
    {
        return view('v2.company.equipment.form', compact('company'));
    }

    public function store(EquipmentRequest $request, Company $company)
    {
        $data = $request->validated();
        $data['company_id'] = $company->id;

        $this->equipmentService->createEquipment($data);

        return redirect()
            ->route('v2.equipment.index', ['company' => $company->slug])
            ->with('success', 'Equipment created successfully.');
    }

    public function edit(Company $company, Equipment $equipment)
    {
        return view('v2.company.equipment.form', compact('company', 'equipment'));
    }

    public function update(EquipmentRequest $request, Company $company, Equipment $equipment)
    {
        $this->equipmentService->updateEquipment($equipment, $request->validated());

        return redirect()
            ->route('v2.equipment.index', ['company' => $company->slug])
            ->with('success', 'Equipment updated successfully.');
    }

    public function destroy(Company $company, Equipment $equipment)
    {
        $this->equipmentService->deleteEquipment($equipment);

        return redirect()
            ->route('v2.equipment.index', ['company' => $company->slug])
            ->with('success', 'Equipment deleted successfully.');
    }
}
