<?php
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSingleConsigneeOrderRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            // Single Consignee (Main Form) Rules
            'sc_company_name' => 'required|string|max:255',
            'sc_address_1' => 'required|string|max:255',
            'sc_city' => 'required|string|max:255',
            'sc_state' => 'required|string|max:255',
            'sc_country' => 'required|string|max:100',
            'sc_zip' => 'required|string|max:20',
            'sc_delivery_start_time' => 'required|date',
            'sc_delivery_end_time' => 'required|date',
            'sc_make_appointment' => 'nullable|boolean',

            // Array of Stops (Shippers)
            'stops' => 'required|array|min:1',
            'stops.*.shipper_company_name' => 'required|string|max:255',
            'stops.*.shipper_address_1' => 'required|string|max:255',
            'stops.*.shipper_city' => 'required|string|max:255',
            'stops.*.shipper_state' => 'required|string|max:255',
            'stops.*.shipper_country' => 'required|string|max:100',
            'stops.*.shipper_zip' => 'required|string|max:20',
            'stops.*.ready_start_time' => 'required|date',
            'stops.*.ready_end_time' => 'required|date',

            // Nested Commodities & Accessorials for each shipper stop
            'stops.*.accessorials' => 'nullable|array',
            'stops.*.accessorials.*' => 'integer|exists:accessorials,id',
            'stops.*.commodities' => 'required|array|min:1',
            'stops.*.commodities.*.description' => 'required|string|max:255',
            'stops.*.commodities.*.quantity' => 'required|numeric|min:0',
            'stops.*.commodities.*.weight' => 'required|numeric|min:0',
            'stops.*.commodities.*.length' => 'nullable|numeric|min:0',
            'stops.*.commodities.*.width' => 'nullable|numeric|min:0',
            'stops.*.commodities.*.height' => 'nullable|numeric|min:0',
        ];
    }
}