<?php

namespace App\Http\Resources\Driver;

use App\Enums\ManifestStatus;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DriverManifestResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'code' => $this->code,
            'status' => $this->status,
            'status_label' => ManifestStatus::tryFrom((string) $this->status)?->label() ?? $this->status,
            'start_date' => $this->start_date,
            'previous_stop' => $this->previous_stop,
            'next_stop' => $this->next_stop,
            'freight' => $this->freight,
            'orders_count' => ($this->direct_orders_count !== null || $this->orders_count !== null)
                ? ($this->direct_orders_count ?? 0) + ($this->orders_count ?? 0)
                : $this->whenLoaded('orders', fn () => $this->orders->count()),
            'orders' => DriverOrderResource::collection($this->whenLoaded('orders')),
        ];
    }
}
