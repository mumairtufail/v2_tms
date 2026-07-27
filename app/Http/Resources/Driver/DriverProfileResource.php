<?php

namespace App\Http\Resources\Driver;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DriverProfileResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'first_name' => $this->f_name,
            'last_name' => $this->l_name,
            'email' => $this->email,
            'phone' => $this->phone,
            'avatar' => $this->avatar,
            'company' => [
                'id' => $this->company?->id,
                'name' => $this->company?->name,
                'slug' => $this->company?->slug,
                'logo_icon' => $this->company?->logo_icon,
            ],
        ];
    }
}
