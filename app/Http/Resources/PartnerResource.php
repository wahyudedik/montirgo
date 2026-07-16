<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PartnerResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'workshop_name' => $this->workshop_name,
            'workshop_address' => $this->workshop_address,
            'workshop_lat' => $this->workshop_lat,
            'workshop_lng' => $this->workshop_lng,
            'status' => $this->status,
            'is_online' => $this->is_online,
            'is_available' => $this->is_available,
            'rating' => $this->rating,
            'total_orders' => $this->total_orders,
            'description' => $this->description,
            'operating_hours' => $this->operating_hours,
            'created_at' => $this->created_at->toIso8601String(),
        ];
    }
}
