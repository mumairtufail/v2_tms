<?php
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSingleShipperOrderRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            // Single Shipper Rules
            'ss_company_name' => 'required|string|max:255',
            'ss_address_1' => 'required|string|max:255',
            'ss_address_2' => 'nullable|string|max:255',
            'ss_city' => 'required|string|max:255',
            'ss_state' => 'required|string|max:255',
            'ss_zip' => 'required|string|max:20',
            'ss_country' => 'required|string|max:100',
            'ss_notes' => 'nullable|string',
            'ss_opening_time' => 'nullable|string',
            'ss_closing_time' => 'nullable|string',
            'ss_contact_name' => 'nullable|string|max:255',
            'ss_phone' => 'nullable|string|max:20',
            'ss_contact_email' => 'nullable|string|max:255',
            'ss_ready_start_time' => 'required|date',
            'ss_ready_end_time' => 'required|date',
            'ss_make_appointment' => 'nullable|boolean',

            // Array of Stops (Consignees)
            'stops' => 'required|array|min:1',
            'stops.*.consignee_company_name' => 'required|string|max:255',
            'stops.*.consignee_address_1' => 'required|string|max:255',
            'stops.*.consignee_address_2' => 'nullable|string|max:255',
            'stops.*.consignee_city' => 'required|string|max:255',
            'stops.*.consignee_state' => 'required|string|max:255',
            'stops.*.consignee_zip' => 'required|string|max:20',
            'stops.*.consignee_country' => 'required|string|max:100',
            'stops.*.consignee_notes' => 'nullable|string',
            'stops.*.consignee_opening_time' => 'nullable|string',
            'stops.*.consignee_closing_time' => 'nullable|string',
            'stops.*.consignee_contact_name' => 'nullable|string|max:255',
            'stops.*.consignee_phone' => 'nullable|string|max:20',
            'stops.*.consignee_contact_email' => 'nullable|string|max:255',
            'stops.*.delivery_start_time' => 'required|date',
            'stops.*.delivery_end_time' => 'required|date',
            'stops.*.delivery_appointment' => 'nullable|boolean',

            // Nested Commodities & Accessorials
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