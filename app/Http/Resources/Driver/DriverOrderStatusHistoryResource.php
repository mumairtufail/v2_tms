<?php

namespace App\Http\Resources\Driver;

use App\Enums\OrderStatus;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DriverOrderStatusHistoryResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'order_id' => $this->order_id,
            'order_number' => $this->order?->order_number,
            'from_status' => $this->from_status,
            'to_status' => $this->to_status,
            'to_status_label' => OrderStatus::tryFrom((string) $this->to_status)?->label() ?? $this->to_status,
            'note' => $this->note,
            'created_at' => $this->created_at,
        ];
    }
}
