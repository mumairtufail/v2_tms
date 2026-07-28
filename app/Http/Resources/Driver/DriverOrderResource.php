<?php

namespace App\Http\Resources\Driver;

use App\Enums\OrderStatus;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DriverOrderResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $status = OrderStatus::tryFrom((string) $this->status);

        return [
            'id' => $this->id,
            'order_number' => $this->order_number,
            'manifest_id' => $this->manifest_id
                ?? ($this->relationLoaded('stops') ? $this->stops->pluck('manifest_id')->filter()->first() : null),
            'status' => $this->status,
            'status_label' => $status?->label() ?? $this->status,
            'next_status' => $status?->nextDriverStatus()?->value,
            'next_status_label' => $status?->nextDriverStatus()?->label(),
            'order_type' => $this->order_type,
            'ref_number' => $this->ref_number,
            'customer_po_number' => $this->customer_po_number,
            'special_instructions' => $this->special_instructions,
            'customer' => $this->whenLoaded('customer', fn () => [
                'id' => $this->customer->id,
                'name' => $this->customer->name,
            ]),
            'stops' => $this->whenLoaded('stops', function () {
                $sequence = 0;

                return $this->stops
                    ->sortBy('sequence_number')
                    ->values()
                    ->flatMap(function ($stop) use (&$sequence) {
                        // A 'mixed' row stores the pickup (shipper) location as real columns
                        // and the delivery (consignee) location in consignee_data — split it
                        // into two stops so the app sees both legs, not just the pickup.
                        if ($stop->stop_type !== 'mixed') {
                            return [[
                                'id' => $stop->id,
                                'type' => $stop->stop_type,
                                'sequence_number' => ++$sequence,
                                'company_name' => $stop->company_name,
                                'address' => $this->formatStopAddress($stop->address_1, $stop->address_2, $stop->city, $stop->state, $stop->postal_code),
                                'contact_name' => $stop->contact_name,
                                'contact_phone' => $stop->contact_phone,
                                'is_appointment' => $stop->is_appointment,
                                'start_time' => $stop->start_time,
                                'end_time' => $stop->end_time,
                                'lat' => $stop->lat,
                                'lng' => $stop->lng,
                            ]];
                        }

                        $consignee = $stop->consignee_data ?? [];

                        return [
                            [
                                'id' => $stop->id,
                                'type' => 'pickup',
                                'sequence_number' => ++$sequence,
                                'company_name' => $stop->company_name,
                                'address' => $this->formatStopAddress($stop->address_1, $stop->address_2, $stop->city, $stop->state, $stop->postal_code),
                                'contact_name' => $stop->contact_name,
                                'contact_phone' => $stop->contact_phone,
                                'is_appointment' => $stop->is_appointment,
                                'start_time' => $stop->start_time,
                                'end_time' => $stop->start_time,
                                'lat' => $stop->lat,
                                'lng' => $stop->lng,
                            ],
                            [
                                'id' => $stop->id,
                                'type' => 'delivery',
                                'sequence_number' => ++$sequence,
                                'company_name' => $consignee['company_name'] ?? null,
                                'address' => $this->formatStopAddress(
                                    $consignee['address_1'] ?? null,
                                    $consignee['address_2'] ?? null,
                                    $consignee['city'] ?? null,
                                    $consignee['state'] ?? null,
                                    $consignee['zip'] ?? null,
                                ),
                                'contact_name' => $consignee['contact_name'] ?? null,
                                'contact_phone' => $consignee['phone'] ?? null,
                                'is_appointment' => (bool) ($consignee['appointment'] ?? false),
                                'start_time' => $consignee['requested_start_at'] ?? null,
                                'end_time' => $consignee['requested_end_at'] ?? $stop->end_time,
                                'lat' => $consignee['lat'] ?? null,
                                'lng' => $consignee['lng'] ?? null,
                            ],
                        ];
                    })
                    ->values();
            }),
        ];
    }

    private function formatStopAddress(?string $line1, ?string $line2, ?string $city, ?string $state, ?string $postalCode): string
    {
        return implode(', ', array_filter([$line1, $line2, $city, $state, $postalCode]));
    }
}
