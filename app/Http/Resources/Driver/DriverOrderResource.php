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
            'manifest_id' => $this->manifest_id,
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
            'stops' => $this->whenLoaded('stops', fn () => $this->stops
                ->sortBy('sequence_number')
                ->values()
                ->map(fn ($stop) => [
                    'id' => $stop->id,
                    'type' => $stop->stop_type,
                    'sequence_number' => $stop->sequence_number,
                    'company_name' => $stop->company_name,
                    'address' => implode(', ', array_filter([
                        $stop->address_1,
                        $stop->address_2,
                        $stop->city,
                        $stop->state,
                        $stop->postal_code,
                    ])),
                    'contact_name' => $stop->contact_name,
                    'contact_phone' => $stop->contact_phone,
                    'is_appointment' => $stop->is_appointment,
                    'start_time' => $stop->start_time,
                    'end_time' => $stop->end_time,
                    'lat' => $stop->lat,
                    'lng' => $stop->lng,
                ])),
        ];
    }
}
