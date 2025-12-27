<?php

namespace App\Http\Controllers\V2;

use App\Http\Controllers\Controller;
use App\Http\Requests\V2\CarrierRequest;
use App\Models\Company;
use App\Models\Carrier;
use App\Services\CarrierService;
use Illuminate\Http\Request;

class CarrierController extends Controller
{
    protected $carrierService;

    public function __construct(CarrierService $carrierService)
    {
        $this->carrierService = $carrierService;
    }

    public function index(Request $request, Company $company)
    {
        $carriers = $this->carrierService->getCarriers([
            'search' => $request->search,
            'status' => $request->status,
        ]);

        return view('v2.company.carriers.index', compact('carriers', 'company'));
    }

    public function create(Company $company)
    {
        return view('v2.company.carriers.form', compact('company'));
    }

    public function store(CarrierRequest $request, Company $company)
    {
        $this->carrierService->createCarrier($request->validated());

        return redirect()
            ->route('v2.carriers.index', ['company' => $company->slug])
            ->with('success', 'Carrier created successfully.');
    }

    public function edit(Company $company, Carrier $carrier)
    {
        return view('v2.company.carriers.form', compact('company', 'carrier'));
    }

    public function update(CarrierRequest $request, Company $company, Carrier $carrier)
    {
        $this->carrierService->updateCarrier($carrier, $request->validated());

        return redirect()
            ->route('v2.carriers.index', ['company' => $company->slug])
            ->with('success', 'Carrier updated successfully.');
    }

    public function destroy(Company $company, Carrier $carrier)
    {
        $this->carrierService->deleteCarrier($carrier);

        return redirect()
            ->route('v2.carriers.index', ['company' => $company->slug])
            ->with('success', 'Carrier deleted successfully.');
    }
}
