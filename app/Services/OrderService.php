<?php
namespace App\Services;

use App\Models\Order;
use Illuminate\Support\Facades\DB;

class OrderService
{
    /**
     * Handles the database logic for updating a Point-to-Point order.
     *
     * @param Order $order The order instance to update.
     * @param array $data Validated data from the request.
     * @return void
     */
    public function updatePointToPoint(Order $order, array $data): void
    {
        DB::transaction(function () use ($data, $order) {
            $order->update([
                'order_type' => 'point_to_point',
                'status' => 'new',
                'ref_number' => $data['ref_number'],
                'customer_po_number' => $data['customer_po_number'],
                'manifest_id' => $data['manifest_id'] ?? null,
            ]);

            $pickupStop = $order->stops()->updateOrCreate(
                ['stop_type' => 'pickup', 'sequence_number' => 1],
                [
                    'company_name' => $data['shipper_company_name'], 'address_1' => $data['shipper_address_1'],
                    'address_2' => $data['shipper_address_2'], 'city' => $data['shipper_city'], 'state' => $data['shipper_state'],
                    'postal_code' => $data['shipper_zip'], 'country' => $data['shipper_country'], 'notes' => $data['shipper_notes'],
                    'opening_time' => $data['shipper_opening_time'], 'closing_time' => $data['shipper_closing_time'],
                    'contact_name' => $data['shipper_contact_name'], 'contact_phone' => $data['shipper_phone'],
                    'contact_email' => $data['shipper_contact_email'], 'start_time' => $data['ready_start_time'],
                    'end_time' => $data['ready_end_time'], 'is_appointment' => $data['ready_appointment'] ?? 0,
                ]
            );

            $deliveryStop = $order->stops()->updateOrCreate(
                ['stop_type' => 'delivery', 'sequence_number' => 2],
                [
                    'company_name' => $data['consignee_company_name'], 'address_1' => $data['consignee_address_1'],
                    'address_2' => $data['consignee_address_2'], 'city' => $data['consignee_city'], 'state' => $data['consignee_state'],
                    'postal_code' => $data['consignee_zip'], 'country' => $data['consignee_country'], 'notes' => $data['consignee_notes'],
                    'opening_time' => $data['consignee_opening_time'], 'closing_time' => $data['consignee_closing_time'],
                    'contact_name' => $data['consignee_contact_name'], 'contact_phone' => $data['consignee_phone'],
                    'contact_email' => $data['consignee_contact_email'], 'start_time' => $data['delivery_start_time'],
                    'end_time' => $data['delivery_end_time'], 'is_appointment' => $data['delivery_appointment'] ?? 0,
                ]
            );

            $pickupStop->commodities()->delete();
            
            // Debug: Log commodity data before saving
            \Log::info('Point-to-point commodities data before saving:', [
                'commodities' => $data['commodities']
            ]);
            
            $pickupStop->commodities()->createMany($data['commodities']);
            $deliveryStop->accessorials()->sync($data['accessorials'] ?? []);

            // Handle Quote Data if provided
            if (isset($data['quote_delivery_start']) || isset($data['service_id']) || isset($data['carrier_costs'])) {
                $this->saveQuote($order, $data);
            }
        });
    }

    /**
     * Handles the database logic for updating a Single Shipper order.
     *
     * @param Order $order The order instance to update.
     * @param array $data Validated data from the request.
     * @return void
     */
    public function updateSingleShipper(Order $order, array $data): void
    {
        DB::transaction(function () use ($data, $order) {
            $order->update(['order_type' => 'single_shipper', 'status' => 'new']);

            $order->stops()->updateOrCreate(
                ['stop_type' => 'pickup', 'sequence_number' => 1],
                [
                    'company_name' => $data['ss_company_name'], 'address_1' => $data['ss_address_1'], 'address_2' => $data['ss_address_2'],
                    'city' => $data['ss_city'], 'state' => $data['ss_state'], 'postal_code' => $data['ss_zip'], 'country' => $data['ss_country'],
                    'notes' => $data['ss_notes'], 'opening_time' => $data['ss_opening_time'], 'closing_time' => $data['ss_closing_time'],
                    'contact_name' => $data['ss_contact_name'], 'contact_phone' => $data['ss_phone'], 'contact_email' => $data['ss_contact_email'],
                    'start_time' => $data['ss_ready_start_time'], 'end_time' => $data['ss_ready_end_time'], 'is_appointment' => $data['ss_make_appointment'] ?? 0,
                ]
            );
            
            $order->stops()->where('stop_type', 'delivery')->delete();
            
            foreach ($data['stops'] as $index => $stopData) {
                $deliveryStop = $order->stops()->create([
                    'stop_type' => 'delivery', 'sequence_number' => $index + 2,
                    'company_name' => $stopData['consignee_company_name'], 'address_1' => $stopData['consignee_address_1'],
                    'address_2' => $stopData['consignee_address_2'], 'city' => $stopData['consignee_city'], 'state' => $stopData['consignee_state'],
                    'postal_code' => $stopData['consignee_zip'], 'country' => $stopData['consignee_country'], 'notes' => $stopData['consignee_notes'],
                    'opening_time' => $stopData['consignee_opening_time'], 'closing_time' => $stopData['consignee_closing_time'],
                    'contact_name' => $stopData['consignee_contact_name'], 'contact_phone' => $stopData['consignee_phone'],
                    'contact_email' => $stopData['consignee_contact_email'], 'start_time' => $stopData['delivery_start_time'],
                    'end_time' => $stopData['delivery_end_time'], 'is_appointment' => $stopData['delivery_appointment'] ?? 0,
                ]);

                if (!empty($stopData['commodities'])) { $deliveryStop->commodities()->createMany($stopData['commodities']); }
                if (!empty($stopData['accessorials'])) { $deliveryStop->accessorials()->sync($stopData['accessorials']); }
            }
        });
    }

    /**
     * Handles the database logic for updating a Single Consignee order.
     *
     * @param Order $order The order instance to update.
     * @param array $data Validated data from the request.
     * @return void
     */
    public function updateSingleConsignee(Order $order, array $data): void
    {
        DB::transaction(function () use ($data, $order) {
            $order->update(['order_type' => 'single_consignee', 'status' => 'new']);

            // 1. Process all the Shipper (Pickup) stops from the form's 'stops' array
            //    First, delete all old pickup stops to handle removals.
            $order->stops()->where('stop_type', 'pickup')->delete();

            if (isset($data['stops'])) {
                foreach ($data['stops'] as $index => $stopData) {
                    $pickupStop = $order->stops()->create([
                        'stop_type' => 'pickup',
                        'sequence_number' => $index + 1, // Pickups are first, so they start at 1
                        'company_name' => $stopData['shipper_company_name'],
                        'address_1' => $stopData['shipper_address_1'],
                        'city' => $stopData['shipper_city'],
                        'state' => $stopData['shipper_state'],
                        'postal_code' => $stopData['shipper_zip'],
                        'country' => $stopData['shipper_country'] ?? 'US',
                        'start_time' => $stopData['ready_start_time'],
                        'end_time' => $stopData['ready_end_time'],
                    ]);

                    // Attach Commodities and Accessorials to *this specific pickup stop*
                    if (!empty($stopData['commodities'])) {
                        $commodities = is_string($stopData['commodities']) ? json_decode($stopData['commodities'], true) : $stopData['commodities'];
                        $pickupStop->commodities()->createMany($commodities);
                    }
                    if (!empty($stopData['accessorials'])) {
                        $accessorialIds = is_string($stopData['accessorials']) ? json_decode($stopData['accessorials'], true) : $stopData['accessorials'];
                        $pickupStop->accessorials()->sync($accessorialIds);
                    }
                }
            }

            // 2. Create or Update the single Consignee (the final Delivery Stop)
            //    Its sequence number will be the last one.
            $finalSequence = (isset($data['stops']) ? count($data['stops']) : 0) + 1;
            
            $order->stops()->updateOrCreate(
                ['stop_type' => 'delivery'], // There's only one delivery stop in this order type
                [
                    'sequence_number' => $finalSequence,
                    'company_name' => $data['sc_company_name'],
                    'address_1' => $data['sc_address_1'],
                    'city' => $data['sc_city'],
                    'state' => $data['sc_state'],
                    'postal_code' => $data['sc_zip'],
                    'country' => $data['sc_country'] ?? 'US',
                    'start_time' => $data['sc_delivery_start_time'],
                    'end_time' => $data['sc_delivery_end_time'],
                    'is_appointment' => $data['sc_make_appointment'] ?? 0,
                ]
            );
        });
    }

    /**
     * Handles the database logic for updating a Sequence order.
     *
     * @param Order $order The order instance to update.
     * @param array $data Validated data from the request.
     * @return void
     */
    public function updateSequence(Order $order, array $data): void
    {
        DB::transaction(function () use ($data, $order) {
            // Update the main order
            $order->update([
                'order_type' => 'sequence',
                'status' => (isset($data['save_as_draft']) && $data['save_as_draft']) ? 'draft' : 'new',
                'special_instructions' => $data['special_instructions'] ?? null,
                'ref_number' => $data['ref_number'] ?? null,
                'customer_po_number' => $data['customer_po_number'] ?? null,
            ]);

            // Clear existing stops
            $order->stops()->delete();

            // Process each stop in the sequence (if any)
            if (!empty($data['stops']) && is_array($data['stops'])) {
            foreach ($data['stops'] as $index => $stopData) {
                $sequenceNumber = $index + 1;
                
                // Debug logging to help identify missing fields
                \Log::info('Processing sequence stop', [
                    'stop_index' => $index,
                    'stop_data_keys' => array_keys($stopData),
                    'has_shipper_city' => isset($stopData['shipper_city']),
                    'shipper_city_value' => $stopData['shipper_city'] ?? 'NOT_SET'
                ]);
                
                // Create the stop with all the data
                $stop = $order->stops()->create([
                    'stop_type' => 'mixed', // Sequence orders can have mixed pickup/delivery
                    'sequence_number' => $sequenceNumber,
                    'manifest_id' => !empty($stopData['manifest_id']) ? $stopData['manifest_id'] : null,
                    
                    // Shipper/Pickup data
                    'company_name' => $stopData['shipper_company_name'] ?? null,
                    'address_1' => $stopData['shipper_address_1'] ?? null,
                    'address_2' => $stopData['shipper_address_2'] ?? null,
                    'city' => $stopData['shipper_city'] ?? null,
                    'state' => $stopData['shipper_state'] ?? null,
                    'postal_code' => $stopData['shipper_zip'] ?? null,
                    'country' => $stopData['shipper_country'] ?? 'US',
                    'contact_name' => $stopData['shipper_contact_name'] ?? null,
                    'contact_phone' => $stopData['shipper_phone'] ?? null,
                    'contact_email' => $stopData['shipper_contact_email'] ?? null,
                    'notes' => $stopData['shipper_notes'] ?? null,
                    'opening_time' => $stopData['shipper_opening_time'] ?? null,
                    'closing_time' => $stopData['shipper_closing_time'] ?? null,
                    'start_time' => $stopData['ready_start_time'] ?? $stopData['delivery_start_time'] ?? now()->format('Y-m-d H:i:s'),
                    'end_time' => $stopData['ready_end_time'] ?? $stopData['delivery_end_time'] ?? now()->addHours(2)->format('Y-m-d H:i:s'),
                    'is_appointment' => isset($stopData['ready_appointment']) ? 1 : 0,
                    
                    // Store consignee data as JSON in notes for now (we'll create proper columns later)
                    'consignee_data' => json_encode([
                        'company_name' => $stopData['consignee_company_name'] ?? null,
                        'address_1' => $stopData['consignee_address_1'] ?? null,
                        'address_2' => $stopData['consignee_address_2'] ?? null,
                        'city' => $stopData['consignee_city'] ?? null,
                        'state' => $stopData['consignee_state'] ?? null,
                        'zip' => $stopData['consignee_zip'] ?? null,
                        'country' => $stopData['consignee_country'] ?? 'US',
                        'opening_time' => $stopData['consignee_opening_time'] ?? null,
                        'closing_time' => $stopData['consignee_closing_time'] ?? null,
                        'contact_name' => $stopData['consignee_contact_name'] ?? null,
                        'phone' => $stopData['consignee_phone'] ?? null,
                        'email' => $stopData['consignee_contact_email'] ?? null,
                        'notes' => $stopData['consignee_notes'] ?? null,
                        'delivery_start_time' => $stopData['delivery_start_time'] ?? null,
                        'delivery_end_time' => $stopData['delivery_end_time'] ?? null,
                        'delivery_appointment' => isset($stopData['delivery_appointment']) ? 1 : 0,
                    ]),
                    
                    // Store additional information data as JSON
                    'billing_data' => json_encode([
                        'customs_broker' => $stopData['customs_broker'] ?? null,
                        'port_of_entry' => $stopData['port_of_entry'] ?? null,
                        'declared_value' => $stopData['declared_value'] ?? null,
                        'currency' => $stopData['currency'] ?? 'USD',
                        'container_number' => $stopData['container_number'] ?? null,
                        'ref_number' => $stopData['ref_number'] ?? null,
                        'customer_po_number' => $stopData['customer_po_number'] ?? null,
                    ]),
                ]);

                // Handle commodities if provided
                if (isset($stopData['commodities'])) {
                    foreach ($stopData['commodities'] as $commodityData) {
                        if (!empty($commodityData['description'])) {
                            $stop->commodities()->create([
                                'description' => $commodityData['description'],
                                'quantity' => $commodityData['quantity'] ?? $commodityData['pieces'] ?? 1,
                                'weight' => $commodityData['weight'] ?? 0,
                                'length' => $commodityData['length'] ?? null,
                                'width' => $commodityData['width'] ?? null,
                                'height' => $commodityData['height'] ?? null,
                            ]);
                        }
                    }
                }

                // Handle accessorials if provided
                if (isset($stopData['accessorials'])) {
                    $accessorialIds = array_filter($stopData['accessorials']);
                    if (!empty($accessorialIds)) {
                        $stop->accessorials()->sync($accessorialIds);
                    }
                }
            }
            }

            // Handle Quote Data if provided
            if (isset($data['quote_delivery_start']) || isset($data['service_id']) || isset($data['carrier_costs'])) {
                // Create or update Quote
                $quote = $order->quote()->updateOrCreate(
                    ['order_id' => $order->id],
                    [
                        'service_id' => $data['service_id'] ?? null,
                        'notes' => $data['quote_notes'] ?? null,
                        'delivery_start_date' => $data['quote_delivery_start'] ?? null,
                        'delivery_end_date' => $data['quote_delivery_end'] ?? null,
                    ]
                );

                // Sync Carrier Costs
                $quote->costs()->delete();

                if (!empty($data['carrier_costs'])) {
                    foreach ($data['carrier_costs'] as $cost) {
                        if (empty($cost['type']) && empty($cost['cost'])) continue;
                        $quote->costs()->create([
                            'category' => 'carrier',
                            'type' => $cost['type'] ?? null,
                            'description' => $cost['description'] ?? null,
                            'cost' => $cost['cost'] ?? 0,
                            'percentage' => $cost['percentage'] ?? 0,
                        ]);
                    }
                }

                if (!empty($data['customer_quotes'])) {
                    foreach ($data['customer_quotes'] as $item) {
                        if (empty($item['type']) && empty($item['cost'])) continue;
                        $quote->costs()->create([
                            'category' => 'quote',
                            'type' => $item['type'] ?? null,
                            'description' => $item['description'] ?? null,
                            'cost' => $item['cost'] ?? 0,
                            'percentage' => $item['percentage'] ?? 0,
                        ]);
                    }
                }
            }
        });
    }
    /**
     * Handles the database logic for saving an order quote.
     *
     * @param Order $order The order instance.
     * @param array $data Validated data from the request.
     * @return void
     */
    public function saveQuote(Order $order, array $data): void
    {
        DB::transaction(function () use ($data, $order) {
            // Create Quote
            $quote = \App\Models\OrderQuote::create([
                'order_id' => $order->id,
                'service_id' => $data['service_id'] ?? null,
                'notes' => $data['quote_notes'] ?? null,
                'delivery_start_date' => $data['quote_delivery_start'] ?? null,
                'delivery_end_date' => $data['quote_delivery_end'] ?? null,
            ]);

            // Save Carrier Costs
            if (!empty($data['carrier_costs'])) {
                foreach ($data['carrier_costs'] as $cost) {
                    // Skip empty rows
                    if (empty($cost['type']) && empty($cost['cost'])) continue;
                    
                    $quote->costs()->create([
                        'category' => 'carrier',
                        'type' => $cost['type'] ?? null,
                        'description' => $cost['description'] ?? null,
                        'cost' => $cost['cost'] ?? 0,
                        'percentage' => $cost['percentage'] ?? 0,
                    ]);
                }
            }

            // Save Customer Quote Items
            if (!empty($data['customer_quotes'])) {
                foreach ($data['customer_quotes'] as $item) {
                    // Skip empty rows
                    if (empty($item['type']) && empty($item['cost'])) continue;

                    $quote->costs()->create([
                        'category' => 'quote',
                        'type' => $item['type'] ?? null,
                        'description' => $item['description'] ?? null,
                        'cost' => $item['cost'] ?? 0,
                        'percentage' => $item['percentage'] ?? 0,
                    ]);
                }
            }
        });
    }
}