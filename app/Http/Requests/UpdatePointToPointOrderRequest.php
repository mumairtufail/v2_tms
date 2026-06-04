<?php
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePointToPointOrderRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            // Shipper Rules
            'shipper_company_name' => 'required|string|max:255',
            'shipper_address_1' => 'required|string|max:255',
            'shipper_address_2' => 'nullable|string|max:255',
            'shipper_city' => 'required|string|max:255',
            'shipper_state' => 'required|string|max:255',
            'shipper_zip' => 'required|string|max:20',
            'shipper_country' => 'required|string|max:100',
            'shipper_notes' => 'nullable|string',
            'shipper_opening_time' => 'nullable|string',
            'shipper_closing_time' => 'nullable|string',
            'shipper_contact_name' => 'nullable|string|max:255',
            'shipper_phone' => 'nullable|string|max:20',
            'shipper_contact_email' => 'nullable|string|max:255',
            'ready_start_time' => 'required|date',
            'ready_end_time' => 'required|date',
            'ready_appointment' => 'nullable|boolean',

            // Consignee Rules
            'consignee_company_name' => 'required|string|max:255',
            'consignee_address_1' => 'required|string|max:255',
            'consignee_address_2' => 'nullable|string|max:255',
            'consignee_city' => 'required|string|max:255',
            'consignee_state' => 'required|string|max:255',
            'consignee_zip' => 'required|string|max:20',
            'consignee_country' => 'required|string|max:100',
            'consignee_notes' => 'nullable|string',
            'consignee_opening_time' => 'nullable|string',
            'consignee_closing_time' => 'nullable|string',
            'consignee_contact_name' => 'nullable|string|max:255',
            'consignee_phone' => 'nullable|string|max:20',
            'consignee_contact_email' => 'nullable|string|max:255',
            'delivery_start_time' => 'required|date',
            'delivery_end_time' => 'required|date',
            'delivery_appointment' => 'nullable|boolean',

            // Global & Commodity Rules
            'special_instructions' => 'nullable|string',
            'ref_number' => 'nullable|string|max:255',
            'customer_po_number' => 'nullable|string|max:255',
            'accessorials' => 'nullable|array',
            'accessorials.*' => 'integer|exists:accessorials,id',
            'commodities' => 'required|array|min:1',
            'commodities.*.description' => 'required|string|max:255',
            'commodities.*.quantity' => 'required|numeric|min:0',
            'commodities.*.weight' => 'required|numeric|min:0',
            'commodities.*.length' => 'nullable|numeric|min:0',
            'commodities.*.width' => 'nullable|numeric|min:0',
            'commodities.*.height' => 'nullable|numeric|min:0',

            // Manifest & Quote Rules
            'manifest_id' => 'nullable|exists:manifests,id',
            'service_id' => 'nullable|exists:services,id',
            'quote_notes' => 'nullable|string',
            'quote_delivery_start' => 'nullable|date',
            'quote_delivery_end' => 'nullable|date',
            'carrier_costs' => 'nullable|array',
            'customer_quotes' => 'nullable|array',
        ];
    }
}